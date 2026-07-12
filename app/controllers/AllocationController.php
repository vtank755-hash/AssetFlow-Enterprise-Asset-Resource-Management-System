<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\Allocation;
use App\Models\User;

class AllocationController extends Controller {
    private $allocModel;

    public function __construct() {
        $this->allocModel = new Allocation();
    }

    /**
     * Lists active and historical allocations (Role Encapsulated)
     */
    public function index() {
        $this->checkAccess();

        $role = Session::getRole();
        $userId = Session::getUserId();

        // Staff members are restricted to viewing only their assigned assets
        $filterUserId = ($role === 'Staff') ? $userId : null;
        $allocations = $this->allocModel->getAll($filterUserId);

        // Check if overdue. If due_date < today and status is Active, mark status as Overdue dynamically
        $today = date('Y-m-d');
        foreach ($allocations as &$alloc) {
            if ($alloc['status'] === 'Active' && $alloc['due_date'] < $today) {
                $alloc['status'] = 'Overdue';
                
                // Update database status to Overdue if desired
                $db = \App\Core\Database::getConnection();
                $stmt = $db->prepare("UPDATE asset_allocations SET status = 'Overdue' WHERE id = ?");
                $stmt->execute([$alloc['id']]);

                // Trigger Overdue Return notification
                $stmtNotif = $db->prepare("INSERT INTO notifications (employee_id, title, message) VALUES (?, 'Overdue Return', ?)");
                $stmtNotif->execute([$alloc['employee_id'], "The asset '{$alloc['asset_name']}' (Tag: {$alloc['asset_tag']}) is overdue. Please return it immediately."]);
            }
        }

        $this->view('allocations/index', [
            'title' => 'Resource Allocations',
            'allocations' => $allocations
        ]);
    }

    /**
     * Creates checkout allocation (Manager/Admin Only)
     */
    public function create() {
        $this->checkAccess(['Admin', 'Manager']);
        $error = '';

        $preSelectedAssetId = $_GET['asset_id'] ?? null;
        $assets = $this->allocModel->getAvailableAssets();
        
        $userModel = new User();
        $users = $userModel->getAll();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();
            $assetId = $_POST['asset_id'] ?? '';
            $userId = $_POST['user_id'] ?? '';
            $dueDate = $_POST['due_date'] ?? '';
            $notes = trim($_POST['notes'] ?? '');

            if (empty($assets)) {
                $error = 'No available assets are currently in the system inventory. Please register a new asset or complete active return handovers before proceeding.';
            } elseif (empty($assetId) || empty($userId) || empty($dueDate)) {
                $error = 'All fields are required.';
            } else {
                $allocatorId = Session::getUserId();
                $successId = $this->allocModel->checkout($assetId, $userId, $allocatorId, $dueDate, $notes);
                
                if ($successId) {
                    Session::setFlash('success', 'Asset checked out successfully.');
                    $this->redirect('/allocations');
                } else {
                    $error = 'Failed to execute check-out. Ensure the asset is still marked Available.';
                }
            }
        }

        $this->view('allocations/create', [
            'title' => 'Check-Out Asset Resource',
            'assets' => $assets,
            'users' => $users,
            'preSelectedAssetId' => $preSelectedAssetId,
            'error' => $error
        ]);
    }

    /**
     * Performs Check-in / Return resource (Manager/Admin Only)
     */
    public function return() {
        $this->checkAccess(['Admin', 'Manager']);
        $allocationId = $_GET['id'] ?? $_POST['id'] ?? null;
        
        if (!$allocationId) {
            $this->redirect('/allocations');
        }

        $allocation = $this->allocModel->getById($allocationId);
        if (!$allocation) {
            Session::setFlash('danger', 'Allocation record not found.');
            $this->redirect('/allocations');
        }

        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();
            $notes = trim($_POST['return_notes'] ?? '');
            $userId = Session::getUserId();

            $success = $this->allocModel->returnAsset($allocationId, $userId, $notes);

            if ($success) {
                Session::setFlash('success', 'Asset checked in successfully.');
                $this->redirect('/allocations');
            } else {
                $error = 'Failed to process asset check-in.';
            }
        }

        $this->view('allocations/return', [
            'title' => 'Confirm Asset Check-in',
            'allocation' => $allocation,
            'error' => $error
        ]);
    }
}
