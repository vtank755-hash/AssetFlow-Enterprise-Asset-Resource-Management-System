<?php
namespace App\Models;

use App\Core\Model;

/**
 * Dashboard Model
 * Handles data aggregation for admin metrics, notifications, activities, and staff possessions.
 */
class Dashboard extends Model {
    /**
     * Fetch administrative dashboard metrics.
     * 
     * @return array
     */
    public function getAdminStats() {
        $stats = [];
        
        // Total Assets
        $stats['total_assets'] = (int)$this->db->query("SELECT COUNT(*) FROM assets WHERE status != 'Disposed'")->fetchColumn();
        
        // Available Assets
        $stats['available_assets'] = (int)$this->db->query("SELECT COUNT(*) FROM assets WHERE status = 'Available'")->fetchColumn();
        
        // Allocated Assets
        $stats['allocated_assets'] = (int)$this->db->query("SELECT COUNT(*) FROM assets WHERE status = 'Allocated'")->fetchColumn();
        
        // Maintenance
        $stats['maintenance_assets'] = (int)$this->db->query("SELECT COUNT(*) FROM assets WHERE status = 'Maintenance'")->fetchColumn();
        
        // Bookings (Active Bookings)
        $stats['active_bookings'] = (int)$this->db->query("SELECT COUNT(*) FROM resource_bookings WHERE status = 'Approved' AND end_time >= NOW()")->fetchColumn();
        
        // Transfers (Pending transfer requests)
        $stats['pending_transfers'] = (int)$this->db->query("SELECT COUNT(*) FROM transfer_requests WHERE status = 'Pending'")->fetchColumn();
        
        // Upcoming Returns (Active allocations due in next 7 days)
        $stats['upcoming_returns'] = (int)$this->db->query("
            SELECT COUNT(*) FROM asset_allocations 
            WHERE status = 'Active' AND returned_date IS NULL AND due_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
        ")->fetchColumn();
        
        // Overdue Assets
        $stats['overdue_assets'] = (int)$this->db->query("
            SELECT COUNT(*) FROM asset_allocations 
            WHERE status = 'Active' AND returned_date IS NULL AND due_date < CURDATE()
        ")->fetchColumn();
        
        // Active Audit Cycles
        $stats['active_audits'] = (int)$this->db->query("SELECT COUNT(*) FROM audit_cycles WHERE status = 'In Progress'")->fetchColumn();
        
        // Valuation Sum
        $stats['total_valuation'] = (float)($this->db->query("SELECT SUM(purchase_cost) FROM assets WHERE status != 'Disposed'")->fetchColumn() ?: 0.00);

        return $stats;
    }

    /**
     * Fetch recent notifications for an employee.
     * 
     * @param int $employeeId
     * @param int $limit
     * @return array
     */
    public function getRecentNotifications($employeeId, $limit = 5) {
        $stmt = $this->db->prepare("
            SELECT * FROM notifications 
            WHERE employee_id = ? 
            ORDER BY created_at DESC 
            LIMIT ?
        ");
        $stmt->bindValue(1, $employeeId, \PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Fetch recent system activities.
     * 
     * @param int $limit
     * @return array
     */
    public function getRecentActivities($limit = 5) {
        $stmt = $this->db->prepare("
            SELECT al.*, e.name as employee_name 
            FROM activity_logs al
            LEFT JOIN employees e ON al.employee_id = e.id
            ORDER BY al.created_at DESC 
            LIMIT ?
        ");
        $stmt->bindValue(1, $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Fetch staff member's possessions.
     * 
     * @param int $employeeId
     * @return array
     */
    public function getStaffPossessions($employeeId) {
        $stmt = $this->db->prepare("
            SELECT al.*, a.name as asset_name, a.asset_tag, a.location, a.status as asset_status, ab.name as allocator_name
            FROM asset_allocations al
            JOIN assets a ON al.asset_id = a.id
            JOIN employees ab ON al.allocated_by = ab.id
            WHERE al.employee_id = :employee_id AND al.status = 'Active'
            ORDER BY al.allocated_date DESC
        ");
        $stmt->execute([':employee_id' => $employeeId]);
        return $stmt->fetchAll();
    }

    /**
     * Fetch aggregated statistics for front-end visual charts (no-JS fallback).
     * 
     * @return array
     */
    public function getChartData() {
        $data = [];

        // 1. Department Utilization Chart Data
        $deptQuery = $this->db->query("
            SELECT d.name as label, COUNT(aa.id) as value
            FROM departments d
            LEFT JOIN employees e ON e.department_id = d.id
            LEFT JOIN asset_allocations aa ON aa.employee_id = e.id AND aa.status = 'Active'
            GROUP BY d.id
        ");
        $data['utilization'] = $deptQuery->fetchAll();

        // 2. Maintenance Frequency Chart Data
        $maintQuery = $this->db->query("
            SELECT DATE_FORMAT(created_at, '%b') as label, COUNT(*) as value
            FROM maintenance_requests
            WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
            GROUP BY DATE_FORMAT(created_at, '%Y-%m')
            ORDER BY created_at ASC
        ");
        $dbMaint = $maintQuery->fetchAll();

        // Fallback default months if no data in DB
        if (empty($dbMaint)) {
            $data['maintenance'] = [
                ['label' => 'Jan', 'value' => 2],
                ['label' => 'Feb', 'value' => 5],
                ['label' => 'Mar', 'value' => 3],
                ['label' => 'Apr', 'value' => 7],
                ['label' => 'May', 'value' => 4],
                ['label' => 'Jun', 'value' => 9]
            ];
        } else {
            $data['maintenance'] = $dbMaint;
        }

        return $data;
    }
}
