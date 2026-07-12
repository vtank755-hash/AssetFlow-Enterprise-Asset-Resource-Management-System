<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\Dashboard;

/**
 * Dashboard Controller
 * Orchestrates dashboard index rendering, loading statistics, notifications, and logs.
 */
class DashboardController extends Controller {
    private $dashboardModel;

    public function __construct() {
        $this->dashboardModel = new Dashboard();
    }

    /**
     * Aggregates stats and renders dashboard home.
     * 
     * @return void
     */
    public function index() {
        $this->checkAccess();

        $role = Session::getRole();
        $userId = Session::getUserId();
        
        $stats = [];
        $recentActivities = [];
        $recentNotifications = [];
        $staffAssignedAssets = [];

        // Fetch notifications for the current logged-in employee
        $recentNotifications = $this->dashboardModel->getRecentNotifications($userId, 5);

        if ($role === 'Staff') {
            $staffAssignedAssets = $this->dashboardModel->getStaffPossessions($userId);
            $totalAssets = count($staffAssignedAssets);
        } else {
            $stats = $this->dashboardModel->getAdminStats();
            $recentActivities = $this->dashboardModel->getRecentActivities(5);
            $totalAssets = $stats['total_assets'];
        }

        $this->view('dashboard/index', [
            'title' => 'Dashboard Overview',
            'role' => $role,
            'stats' => $stats,
            'recentActivities' => $recentActivities,
            'recentNotifications' => $recentNotifications,
            'staffAssignedAssets' => $staffAssignedAssets,
            'totalAssets' => $totalAssets
        ]);
    }

    /**
     * Renders 403 Forbidden page.
     */
    public function forbidden() {
        $this->view('errors/403', [
            'title' => 'Access Forbidden',
            'no_layout' => true
        ]);
    }
}
