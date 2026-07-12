<?php
namespace App\Models;

use App\Core\Model;
use Exception;

class Allocation extends Model {
    /**
     * Get allocations register list.
     * 
     * @param int|null $employeeId Filter by specific custodian (for Staff role)
     * @return array
     */
    public function getAll($employeeId = null) {
        $sql = "
            SELECT al.*, a.name as asset_name, a.asset_tag, e.name as user_name, ab.name as allocator_name
            FROM asset_allocations al
            JOIN assets a ON al.asset_id = a.id
            JOIN employees e ON al.employee_id = e.id
            JOIN employees ab ON al.allocated_by = ab.id
        ";
        $params = [];

        if ($employeeId !== null) {
            $sql .= " WHERE al.employee_id = :employee_id";
            $params[':employee_id'] = (int)$employeeId;
        }

        $sql .= " ORDER BY al.status ASC, al.due_date ASC, al.allocated_date DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Fetch single allocation by ID.
     */
    public function getById($id) {
        $stmt = $this->db->prepare("
            SELECT al.*, a.name as asset_name, a.asset_tag, e.name as user_name
            FROM asset_allocations al
            JOIN assets a ON al.asset_id = a.id
            JOIN employees e ON al.employee_id = e.id
            WHERE al.id = :id
            LIMIT 1
        ");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Perform Resource Checkout (Transaction Safe).
     */
    public function checkout($assetId, $employeeId, $allocatedBy, $dueDate, $notes = '') {
        try {
            $this->db->beginTransaction();

            // 1. Double check asset is available
            $stmt = $this->db->prepare("SELECT status FROM assets WHERE id = ? FOR UPDATE");
            $stmt->execute([$assetId]);
            $asset = $stmt->fetch();

            if (!$asset || $asset['status'] !== 'Available') {
                throw new Exception("Asset is not available for check-out.");
            }

            // 2. Create allocation record
            $stmt = $this->db->prepare("
                INSERT INTO asset_allocations (asset_id, employee_id, allocated_by, allocated_date, due_date, status, notes)
                VALUES (:asset_id, :employee_id, :allocated_by, :allocated_date, :due_date, 'Active', :notes)
            ");
            $stmt->execute([
                ':asset_id' => $assetId,
                ':employee_id' => $employeeId,
                ':allocated_by' => $allocatedBy,
                ':allocated_date' => date('Y-m-d'),
                ':due_date' => $dueDate,
                ':notes' => $notes
            ]);
            $allocationId = $this->db->lastInsertId();

            // 3. Update asset status to Allocated
            $stmt = $this->db->prepare("UPDATE assets SET status = 'Allocated' WHERE id = ?");
            $stmt->execute([$assetId]);

            // 4. Record Audit Log
            $this->logAction($allocatedBy, 'CHECKOUT_ASSET', 'asset_allocations', $allocationId, "Checked out asset ID {$assetId} to employee ID {$employeeId}");

            $this->db->commit();
            return $allocationId;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Checkout Transaction Failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Perform Resource Check-in / Return (Transaction Safe).
     */
    public function returnAsset($allocationId, $returnedBy, $notes = '') {
        try {
            $this->db->beginTransaction();

            // 1. Fetch current allocation record
            $stmt = $this->db->prepare("SELECT * FROM asset_allocations WHERE id = ? FOR UPDATE");
            $stmt->execute([$allocationId]);
            $alloc = $stmt->fetch();

            if (!$alloc || $alloc['status'] === 'Returned') {
                throw new Exception("Allocation record invalid or already returned.");
            }

            // 2. Update allocation row details
            $stmt = $this->db->prepare("
                UPDATE asset_allocations 
                SET returned_date = :returned_date, status = 'Returned', notes = CONCAT(IFNULL(notes,''), :note_append) 
                WHERE id = :id
            ");
            $noteAppend = !empty($notes) ? "\n[Return Notes]: " . $notes : "";
            $stmt->execute([
                ':returned_date' => date('Y-m-d'),
                ':note_append' => $noteAppend,
                ':id' => $allocationId
            ]);

            // 3. Set asset status back to Available
            $stmt = $this->db->prepare("UPDATE assets SET status = 'Available' WHERE id = ?");
            $stmt->execute([$alloc['asset_id']]);

            // 4. Log audit details
            $this->logAction($returnedBy, 'CHECKIN_ASSET', 'asset_allocations', $allocationId, "Returned asset ID {$alloc['asset_id']} from employee ID {$alloc['employee_id']}");

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Return Transaction Failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Fetch list of available assets for check-out dropdown selection.
     */
    public function getAvailableAssets() {
        $stmt = $this->db->prepare("SELECT id, name, asset_tag FROM assets WHERE status = 'Available' ORDER BY name ASC");
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
