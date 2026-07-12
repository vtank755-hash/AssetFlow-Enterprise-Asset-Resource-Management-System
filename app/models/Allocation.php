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

            // 5. Trigger Asset Assigned Notification
            $stmt = $this->db->prepare("INSERT INTO notifications (employee_id, title, message) VALUES (?, 'Asset Assigned', ?)");
            $stmt->execute([$employeeId, "A new asset (ID {$assetId}) has been checked out and assigned to you. Expected return date: {$dueDate}."]);

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

            // 5. Trigger Asset Return Notification
            $stmtNotif = $this->db->prepare("INSERT INTO notifications (employee_id, title, message) VALUES (?, 'Asset Return', ?)");
            $stmtNotif->execute([$alloc['employee_id'], "The asset (ID {$alloc['asset_id']}) you checked out has been marked as returned."]);

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

    /**
     * Get pending transfer requests.
     */
    public function getPendingTransfers($employeeId = null) {
        $sql = "
            SELECT tr.*, a.name as asset_name, a.asset_tag, 
                   es.name as source_employee, et.name as target_employee, er.name as requester_name
            FROM transfer_requests tr
            JOIN assets a ON tr.asset_id = a.id
            JOIN employees es ON tr.source_employee_id = es.id
            JOIN employees et ON tr.target_employee_id = et.id
            JOIN employees er ON tr.requested_by = er.id
        ";
        $params = [];
        if ($employeeId !== null) {
            $sql .= " WHERE tr.source_employee_id = :emp OR tr.target_employee_id = :emp";
            $params[':emp'] = (int)$employeeId;
        }
        $sql .= " ORDER BY tr.requested_date DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Create a transfer request.
     */
    public function createTransferRequest($assetId, $sourceEmployeeId, $targetEmployeeId, $requestedBy, $notes = '') {
        $stmt = $this->db->prepare("
            INSERT INTO transfer_requests (asset_id, source_employee_id, target_employee_id, requested_by, status, requested_date, notes)
            VALUES (:asset_id, :source_id, :target_id, :req_by, 'Pending', :req_date, :notes)
        ");
        $success = $stmt->execute([
            ':asset_id' => $assetId,
            ':source_id' => $sourceEmployeeId,
            ':target_id' => $targetEmployeeId,
            ':req_by' => $requestedBy,
            ':req_date' => date('Y-m-d'),
            ':notes' => $notes
        ]);

        if ($success) {
            $stmtNotif = $this->db->prepare("INSERT INTO notifications (employee_id, title, message) VALUES (?, 'Transfer Request', ?)");
            $stmtNotif->execute([$targetEmployeeId, "A custodian transfer request has been initiated to assign Asset ID {$assetId} to you. Pending your custody approval."]);
            $stmtNotif->execute([$sourceEmployeeId, "A custodian transfer request has been initiated to transfer Asset ID {$assetId} away from you."]);
        }
        return $success;
    }

    /**
     * Approve transfer request (Transaction Safe).
     */
    public function approveTransferRequest($requestId, $actionBy) {
        try {
            $this->db->beginTransaction();

            // 1. Fetch transfer request
            $stmt = $this->db->prepare("SELECT * FROM transfer_requests WHERE id = ? AND status = 'Pending' FOR UPDATE");
            $stmt->execute([$requestId]);
            $req = $stmt->fetch();
            if (!$req) {
                throw new Exception("Transfer request not found or not pending.");
            }

            // 2. Find active allocation of source employee
            $stmt = $this->db->prepare("
                SELECT * FROM asset_allocations 
                WHERE asset_id = ? AND employee_id = ? AND status = 'Active' 
                LIMIT 1 FOR UPDATE
            ");
            $stmt->execute([$req['asset_id'], $req['source_employee_id']]);
            $alloc = $stmt->fetch();

            $dueDate = date('Y-m-d', strtotime('+30 days')); // Default fallback
            if ($alloc) {
                $dueDate = $alloc['due_date'];
                // Terminate active source allocation
                $stmt = $this->db->prepare("
                    UPDATE asset_allocations 
                    SET returned_date = CURDATE(), status = 'Returned', notes = CONCAT(IFNULL(notes,''), '\n[Transferred to Employee ID ', ?, ']')
                    WHERE id = ?
                ");
                $stmt->execute([$req['target_employee_id'], $alloc['id']]);
            }

            // 3. Create new active allocation for target employee
            $stmt = $this->db->prepare("
                INSERT INTO asset_allocations (asset_id, employee_id, allocated_by, allocated_date, due_date, status, notes)
                VALUES (:asset_id, :employee_id, :allocated_by, CURDATE(), :due_date, 'Active', :notes)
            ");
            $stmt->execute([
                ':asset_id' => $req['asset_id'],
                ':employee_id' => $req['target_employee_id'],
                ':allocated_by' => $actionBy,
                ':due_date' => $dueDate,
                ':notes' => 'Custodian transfer approved. Request notes: ' . $req['notes']
            ]);
            
            // 4. Update transfer request status
            $stmt = $this->db->prepare("
                UPDATE transfer_requests 
                SET status = 'Approved', action_date = CURDATE() 
                WHERE id = ?
            ");
            $stmt->execute([$requestId]);

            // 5. Log action
            $this->logAction($actionBy, 'TRANSFER_ASSET', 'transfer_requests', $requestId, "Approved asset custody transfer ID {$req['asset_id']} to employee ID {$req['target_employee_id']}");

            // 6. Trigger Transfer Approved Notifications
            $stmt = $this->db->prepare("INSERT INTO notifications (employee_id, title, message) VALUES (?, 'Transfer Approved', ?)");
            $stmt->execute([$req['target_employee_id'], "Custodian transfer request approved. You are now the active custodian for Asset ID {$req['asset_id']}."]);
            $stmt->execute([$req['source_employee_id'], "Custodian transfer request approved. Custody of Asset ID {$req['asset_id']} has been transferred to Employee ID {$req['target_employee_id']}."]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Transfer Approval Transaction Failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Cancel/Reject transfer request.
     */
    public function rejectTransferRequest($requestId, $actionBy) {
        $stmt = $this->db->prepare("
            UPDATE transfer_requests 
            SET status = 'Cancelled', action_date = CURDATE() 
            WHERE id = ? AND status = 'Pending'
        ");
        $success = $stmt->execute([$requestId]);
        if ($success) {
            $this->logAction($actionBy, 'TRANSFER_REJECT', 'transfer_requests', $requestId, "Cancelled transfer request ID {$requestId}");
        }
        return $success;
    }
}
