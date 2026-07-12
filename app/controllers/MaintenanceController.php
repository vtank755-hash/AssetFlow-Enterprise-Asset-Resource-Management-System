<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\Maintenance;
use App\Models\Asset;

class MaintenanceController extends Controller {
    private $maintModel;

    public function __construct() {
        $this->maintModel = new Maintenance();
    }

    /**
     * Lists all maintenance work orders
     */
    public function index() {
        $this->checkAccess();
        $orders = $this->maintModel->getAll();

        $this->view('maintenance/index', [
            'title' => 'Maintenance Registry',
            'orders' => $orders
        ]);
    }

    /**
     * Schedules a new maintenance work order (Manager/Admin Only)
     */
    public function create() {
        $this->checkAccess(['Admin', 'Manager']);
        $error = '';
        $preSelectedAssetId = $_GET['asset_id'] ?? null;

        // Fetch list of all assets for scheduling
        $assetModel = new Asset();
        $assets = $assetModel->getAllFiltered();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();
            $assetId = $_POST['asset_id'] ?? '';
            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $scheduledDate = $_POST['scheduled_date'] ?? '';
            $performedBy = trim($_POST['performed_by'] ?? '');
            $notes = trim($_POST['notes'] ?? '');

            if (empty($assetId) || empty($title) || empty($scheduledDate)) {
                $error = 'Asset choice, Title, and Scheduled Date are required.';
            } else {
                $successId = $this->maintModel->create($assetId, $title, $description, $scheduledDate, $performedBy, $notes);
                if ($successId) {
                    // Record who performed it (Session User ID) in audit log
                    $userId = Session::getUserId();
                    $this->maintModel->logAction($userId, 'CREATE_WORK_ORDER', 'maintenance_schedules', $successId, "Work order created for asset ID {$assetId}");
                    
                    Session::setFlash('success', 'Maintenance work order scheduled successfully.');
                    $this->redirect('/maintenance');
                } else {
                    $error = 'Failed to create work order.';
                }
            }
        }

        $this->view('maintenance/create', [
            'title' => 'Schedule Maintenance Work Order',
            'assets' => $assets,
            'preSelectedAssetId' => $preSelectedAssetId,
            'error' => $error
        ]);
    }

    /**
     * Modifies/Updates maintenance order parameters (Manager/Admin Only)
     */
    public function edit() {
        $this->checkAccess(['Admin', 'Manager']);
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $this->redirect('/maintenance');
        }

        $order = $this->maintModel->getById($id);
        if (!$order) {
            Session::setFlash('danger', 'Maintenance order not found.');
            $this->redirect('/maintenance');
        }

        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();
            $title = trim($_POST['title'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $scheduledDate = $_POST['scheduled_date'] ?? '';
            $completionDate = $_POST['completion_date'] ?? '';
            $cost = $_POST['cost'] ?? 0.00;
            $status = $_POST['status'] ?? 'Pending';
            $performedBy = trim($_POST['performed_by'] ?? '');
            $notes = trim($_POST['notes'] ?? '');

            if (empty($title) || empty($scheduledDate) || empty($status)) {
                $error = 'Title, Scheduled Date, and Status are required fields.';
            } else {
                $success = $this->maintModel->updateWorkOrder($id, $title, $description, $scheduledDate, $completionDate, $cost, $status, $performedBy, $notes);
                if ($success) {
                    $userId = Session::getUserId();
                    $this->maintModel->logAction($userId, 'UPDATE_WORK_ORDER', 'maintenance_schedules', $id, "Updated work order ID {$id} to status: {$status}");
                    
                    Session::setFlash('success', 'Maintenance order updated successfully.');
                    $this->redirect('/maintenance');
                } else {
                    $error = 'Failed to update work order.';
                }
            }
        }

        $this->view('maintenance/edit', [
            'title' => 'Update Work Order - ' . $order['asset_tag'],
            'order' => $order,
            'error' => $error
        ]);
    }
}
