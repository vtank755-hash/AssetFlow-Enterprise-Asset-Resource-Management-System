<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Session;
use Exception;

class Maintenance extends Model {
    /**
     * Fetch all work orders.
     */
    public function getAll() {
        $stmt = $this->db->prepare("
            SELECT m.*, a.name as asset_name, a.asset_tag
            FROM maintenance_requests m
            JOIN assets a ON m.asset_id = a.id
            ORDER BY m.status ASC, m.scheduled_date ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Fetch single work order by ID.
     */
    public function getById($id) {
        $stmt = $this->db->prepare("
            SELECT m.*, a.name as asset_name, a.asset_tag, a.status as asset_status
            FROM maintenance_requests m
            JOIN assets a ON m.asset_id = a.id
            WHERE m.id = :id
            LIMIT 1
        ");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Register a new maintenance request (Transaction Safe).
     */
    public function create($assetId, $title, $description, $scheduledDate, $performedBy = '', $notes = '') {
        try {
            $this->db->beginTransaction();

            $requestedBy = Session::getUserId() ?: 1; // Retrieve creator employee ID

            // 1. Create order
            $stmt = $this->db->prepare("
                INSERT INTO maintenance_requests (asset_id, requested_by, title, description, scheduled_date, status, performed_by, notes)
                VALUES (:asset_id, :requested_by, :title, :description, :scheduled_date, 'Pending', :performed_by, :notes)
            ");
            $stmt->execute([
                ':asset_id' => $assetId,
                ':requested_by' => $requestedBy,
                ':title' => $title,
                ':description' => $description,
                ':scheduled_date' => $scheduledDate,
                ':performed_by' => $performedBy,
                ':notes' => $notes
            ]);
            $orderId = $this->db->lastInsertId();

            // 2. Put asset into Maintenance state
            $stmt = $this->db->prepare("UPDATE assets SET status = 'Maintenance' WHERE id = ?");
            $stmt->execute([$assetId]);

            // 3. Log Audit
            $this->logAction($requestedBy, 'CREATE_WORK_ORDER', 'maintenance_requests', $orderId, "Scheduled maintenance order ID {$orderId} for asset ID {$assetId}");

            $this->db->commit();
            return $orderId;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Failed to create work order: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update maintenance work order parameters (Transaction Safe).
     */
    public function updateWorkOrder($id, $title, $description, $scheduledDate, $completionDate, $cost, $status, $performedBy, $notes) {
        try {
            $this->db->beginTransaction();

            // 1. Fetch current order
            $stmt = $this->db->prepare("SELECT * FROM maintenance_requests WHERE id = ? FOR UPDATE");
            $stmt->execute([$id]);
            $order = $stmt->fetch();
            if (!$order) {
                throw new Exception("Maintenance order not found.");
            }

            // 2. Update database values
            $stmt = $this->db->prepare("
                UPDATE maintenance_requests
                SET title = :title, description = :description, scheduled_date = :scheduled_date,
                    completion_date = :completion_date, cost = :cost, status = :status,
                    performed_by = :performed_by, notes = :notes
                WHERE id = :id
            ");
            $stmt->execute([
                ':title' => $title,
                ':description' => $description,
                ':scheduled_date' => $scheduledDate,
                ':completion_date' => !empty($completionDate) ? $completionDate : null,
                ':cost' => (float)$cost,
                ':status' => $status,
                ':performed_by' => $performedBy,
                ':notes' => $notes,
                ':id' => $id
            ]);

            // 3. Conditionally update asset status
            if ($status === 'Resolved' || $status === 'Rejected') {
                // Return asset to Available once resolved or rejected
                $stmt = $this->db->prepare("UPDATE assets SET status = 'Available' WHERE id = ?");
                $stmt->execute([$order['asset_id']]);
            } else {
                // Keep in maintenance state for Pending, Approved, Tech Assigned, In Progress statuses
                $stmt = $this->db->prepare("UPDATE assets SET status = 'Maintenance' WHERE id = ?");
                $stmt->execute([$order['asset_id']]);
            }

            // 4. Log Audit
            $userId = Session::getUserId();
            $this->logAction($userId, 'UPDATE_WORK_ORDER', 'maintenance_requests', $id, "Updated status to {$status} for order ID {$id}");

            // 5. Trigger Maintenance Approved Notification if transition is met
            if ($status === 'Approved' && $order['status'] !== 'Approved') {
                $stmtNotif = $this->db->prepare("INSERT INTO notifications (employee_id, title, message) VALUES (?, 'Maintenance Approved', ?)");
                $stmtNotif->execute([$order['requested_by'], "Your scheduled maintenance request '{$title}' has been approved by management."]);
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Failed to update work order: " . $e->getMessage());
            return false;
        }
    }
}
