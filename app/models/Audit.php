<?php
namespace App\Models;

use App\Core\Model;
use Exception;

/**
 * Audit Model
 * Handles stocktake verification cycles, scoping, and discrepancy reports.
 */
class Audit extends Model {
    /**
     * Get all audit cycles.
     */
    public function getAll() {
        $stmt = $this->db->prepare("
            SELECT ac.*, ec.name as creator_name, ea.name as auditor_name, d.name as department_name
            FROM audit_cycles ac
            JOIN employees ec ON ac.created_by = ec.id
            LEFT JOIN employees ea ON ac.assigned_auditor_id = ea.id
            LEFT JOIN departments d ON ac.department_id = d.id
            ORDER BY ac.created_at DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get single cycle by ID.
     */
    public function getById($id) {
        $stmt = $this->db->prepare("
            SELECT ac.*, ec.name as creator_name, ea.name as auditor_name, d.name as department_name
            FROM audit_cycles ac
            JOIN employees ec ON ac.created_by = ec.id
            LEFT JOIN employees ea ON ac.assigned_auditor_id = ea.id
            LEFT JOIN departments d ON ac.department_id = d.id
            WHERE ac.id = ?
            LIMIT 1
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Create a new audit cycle.
     */
    public function createCycle($title, $startDate, $endDate, $createdBy, $auditorId, $departmentId = null, $locationScope = '') {
        $stmt = $this->db->prepare("
            INSERT INTO audit_cycles (title, start_date, end_date, status, created_by, assigned_auditor_id, department_id, location_scope)
            VALUES (:title, :start_date, :end_date, 'In Progress', :created_by, :auditor_id, :dept_id, :loc_scope)
        ");
        
        $success = $stmt->execute([
            ':title' => $title,
            ':start_date' => $startDate,
            ':end_date' => $endDate,
            ':created_by' => $createdBy,
            ':auditor_id' => $auditorId,
            ':dept_id' => !empty($departmentId) ? (int)$departmentId : null,
            ':loc_scope' => !empty($locationScope) ? $locationScope : null
        ]);

        if ($success) {
            $cycleId = $this->db->lastInsertId();
            $stmtNotif = $this->db->prepare("INSERT INTO notifications (employee_id, title, message) VALUES (?, 'Audit Reminder', ?)");
            $stmtNotif->execute([$auditorId, "You have been assigned as the auditor for cycle: '{$title}'. Scheduled from {$startDate} to {$endDate}."]);
            return $cycleId;
        }
        return false;
    }

    /**
     * Close/Complete an audit cycle.
     */
    public function closeCycle($cycleId) {
        $stmtCycle = $this->db->prepare("SELECT title, assigned_auditor_id FROM audit_cycles WHERE id = ?");
        $stmtCycle->execute([$cycleId]);
        $cycle = $stmtCycle->fetch();

        $stmt = $this->db->prepare("UPDATE audit_cycles SET status = 'Completed' WHERE id = ?");
        $success = $stmt->execute([$cycleId]);

        if ($success && $cycle) {
            $stmtNotif = $this->db->prepare("INSERT INTO notifications (employee_id, title, message) VALUES (?, 'Audit Completion', ?)");
            $stmtNotif->execute([$cycle['assigned_auditor_id'], "The stocktake verification cycle '{$cycle['title']}' has been marked as Completed and closed."]);
        }
        return $success;
    }

    /**
     * Fetch list of assets falling in the scope of this cycle.
     */
    public function getAssetsInScope($departmentId = null, $locationScope = null) {
        $sql = "
            SELECT DISTINCT a.*, c.name as category_name, d.name as department_name, e.name as custodian_name
            FROM assets a
            JOIN asset_categories c ON a.category_id = c.id
            LEFT JOIN asset_allocations al ON a.id = al.asset_id AND al.status = 'Active'
            LEFT JOIN employees e ON al.employee_id = e.id
            LEFT JOIN departments d ON e.department_id = d.id
            WHERE a.status != 'Disposed'
        ";
        $params = [];

        if (!empty($departmentId)) {
            $sql .= " AND e.department_id = :dept_id";
            $params[':dept_id'] = (int)$departmentId;
        }

        if (!empty($locationScope)) {
            $sql .= " AND a.location LIKE :loc_scope";
            $params[':loc_scope'] = '%' . $locationScope . '%';
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Get checklist items details for a specific cycle.
     */
    public function getAuditDetails($cycleId) {
        $stmt = $this->db->prepare("
            SELECT ad.*, a.name as asset_name, a.asset_tag, a.serial_number, a.location, ea.name as auditor_name
            FROM audit_details ad
            JOIN assets a ON ad.asset_id = a.id
            JOIN employees ea ON ad.audited_by = ea.id
            WHERE ad.audit_cycle_id = ?
        ");
        $stmt->execute([$cycleId]);
        return $stmt->fetchAll();
    }

    /**
     * Verify an asset under a cycle (Transaction Safe).
     */
    public function verifyAsset($cycleId, $assetId, $auditedBy, $status, $notes = '') {
        try {
            $this->db->beginTransaction();

            // 1. Insert or Update verification detail record
            $stmt = $this->db->prepare("
                SELECT id FROM audit_details 
                WHERE audit_cycle_id = ? AND asset_id = ?
            ");
            $stmt->execute([$cycleId, $assetId]);
            $existingId = $stmt->fetchColumn();

            if ($existingId) {
                $stmt = $this->db->prepare("
                    UPDATE audit_details 
                    SET audited_by = :audited_by, status = :status, notes = :notes, audit_date = CURRENT_TIMESTAMP
                    WHERE id = :id
                ");
                $stmt->execute([
                    ':audited_by' => $auditedBy,
                    ':status' => $status,
                    ':notes' => $notes,
                    ':id' => $existingId
                ]);
            } else {
                $stmt = $this->db->prepare("
                    INSERT INTO audit_details (audit_cycle_id, asset_id, audited_by, status, notes)
                    VALUES (:cycle_id, :asset_id, :audited_by, :status, :notes)
                ");
                $stmt->execute([
                    ':cycle_id' => $cycleId,
                    ':asset_id' => $assetId,
                    ':audited_by' => $auditedBy,
                    ':status' => $status,
                    ':notes' => $notes
                ]);
            }

            // 2. Automatically Update Asset Status
            if ($status === 'Missing') {
                $stmt = $this->db->prepare("UPDATE assets SET status = 'Lost' WHERE id = ?");
                $stmt->execute([$assetId]);
            } elseif ($status === 'Damaged') {
                $stmt = $this->db->prepare("UPDATE assets SET status = 'Maintenance' WHERE id = ?");
                $stmt->execute([$assetId]);
            } else {
                // If it was lost/damaged but now verified, return to Available
                $stmt = $this->db->prepare("SELECT status FROM assets WHERE id = ?");
                $stmt->execute([$assetId]);
                $curr = $stmt->fetchColumn();
                if ($curr === 'Lost' || $curr === 'Maintenance') {
                    $stmt = $this->db->prepare("UPDATE assets SET status = 'Available' WHERE id = ?");
                    $stmt->execute([$assetId]);
                }
            }

            // 3. Log Audit Activity
            $this->logAction($auditedBy, 'VERIFY_AUDIT_ASSET', 'audit_details', $cycleId, "Verified asset ID {$assetId} as {$status} in cycle ID {$cycleId}");

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Failed to verify audit asset: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Get Discrepancy report list for a cycle.
     */
    public function getDiscrepancyReport($cycleId) {
        $stmt = $this->db->prepare("
            SELECT ad.*, a.name as asset_name, a.asset_tag, a.serial_number, a.location, ea.name as auditor_name
            FROM audit_details ad
            JOIN assets a ON ad.asset_id = a.id
            JOIN employees ea ON ad.audited_by = ea.id
            WHERE ad.audit_cycle_id = ? AND ad.status IN ('Missing', 'Damaged')
        ");
        $stmt->execute([$cycleId]);
        return $stmt->fetchAll();
    }
}
