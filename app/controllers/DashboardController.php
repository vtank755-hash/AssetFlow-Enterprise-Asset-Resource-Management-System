<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;

class DashboardController extends Controller {
    /**
     * Aggregates stats and renders dashboard home
     */
    public function index() {
        $this->checkAccess();

        $role = Session::getRole();
        $userId = Session::getUserId();
        
        $db = \App\Core\Database::getConnection();

        // Standard placeholders
        $totalAssets = 0;
        $totalValuation = 0.00;
        $activeMaintenance = 0;
        $lowStockCount = 0;
        $recentAllocations = [];
        $recentWorkOrders = [];
        $staffAssignedAssets = [];

        if ($role === 'Staff') {
            // Staff dashboard: fetch only assets assigned to them
            $stmt = $db->prepare("
                SELECT al.*, a.name as asset_name, a.asset_tag, a.location, a.status as asset_status, ab.name as allocator_name
                FROM allocations al
                JOIN assets a ON al.asset_id = a.id
                JOIN users ab ON al.allocated_by = ab.id
                WHERE al.user_id = :user_id AND al.status = 'Active'
                ORDER BY al.allocated_date DESC
            ");
            $stmt->execute([':user_id' => $userId]);
            $staffAssignedAssets = $stmt->fetchAll();

            $totalAssets = count($staffAssignedAssets);
        } else {
            // Admin/Manager dashboard: fetch enterprise-wide aggregates
            // 1. Total Assets count
            $totalAssets = $db->query("SELECT COUNT(*) FROM assets WHERE status != 'Disposed'")->fetchColumn();

            // 2. Total Valuation
            $totalValuation = $db->query("SELECT SUM(purchase_cost) FROM assets WHERE status != 'Disposed'")->fetchColumn() ?: 0.00;

            // 3. Maintenance events currently active
            $activeMaintenance = $db->query("SELECT COUNT(*) FROM assets WHERE status = 'Maintenance'")->fetchColumn();

            // 4. Low stock consumable warning count
            $lowStockCount = $db->query("SELECT COUNT(*) FROM inventory WHERE quantity < min_threshold")->fetchColumn();

            // 5. Recent 5 allocations
            $recentAllocations = $db->query("
                SELECT al.*, a.name as asset_name, a.asset_tag, u.name as user_name
                FROM allocations al
                JOIN assets a ON al.asset_id = a.id
                JOIN users u ON al.user_id = u.id
                ORDER BY al.allocated_date DESC, al.id DESC
                LIMIT 5
            ")->fetchAll();

            // 6. Recent 5 maintenance events
            $recentWorkOrders = $db->query("
                SELECT m.*, a.name as asset_name, a.asset_tag
                FROM maintenance_schedules m
                JOIN assets a ON m.asset_id = a.id
                ORDER BY m.scheduled_date DESC, m.id DESC
                LIMIT 5
            ")->fetchAll();
        }

        $this->view('dashboard/index', [
            'title' => 'Dashboard Overview',
            'role' => $role,
            'totalAssets' => $totalAssets,
            'totalValuation' => $totalValuation,
            'activeMaintenance' => $activeMaintenance,
            'lowStockCount' => $lowStockCount,
            'recentAllocations' => $recentAllocations,
            'recentWorkOrders' => $recentWorkOrders,
            'staffAssignedAssets' => $staffAssignedAssets
        ]);
    }
}
