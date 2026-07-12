<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\Inventory;

class InventoryController extends Controller {
    private $invModel;

    public function __construct() {
        $this->invModel = new Inventory();
    }

    /**
     * Lists inventory items (Accessible by All Roles)
     */
    public function index() {
        $this->checkAccess();
        $items = $this->invModel->getAll();

        $this->view('inventory/index', [
            'title' => 'Consumables Inventory',
            'items' => $items
        ]);
    }

    /**
     * Creates new consumable item (Manager/Admin Only)
     */
    public function create() {
        $this->checkAccess(['Admin', 'Manager']);
        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();
            $name = trim($_POST['name'] ?? '');
            $sku = trim($_POST['sku'] ?? '');
            $quantity = $_POST['quantity'] ?? 0;
            $minThreshold = $_POST['min_threshold'] ?? 5;
            $unitPrice = $_POST['unit_price'] ?? 0.00;
            $location = trim($_POST['location'] ?? '');

            if (empty($name) || empty($sku) || empty($location)) {
                $error = 'Name, SKU, and Location are required.';
            } else {
                // Check unique SKU
                $db = \App\Core\Database::getConnection();
                $chk = $db->prepare("SELECT id FROM inventory WHERE sku = ?");
                $chk->execute([$sku]);

                if ($chk->fetch()) {
                    $error = 'SKU identifier already in use.';
                } else {
                    $successId = $this->invModel->create($name, $sku, $quantity, $minThreshold, $unitPrice, $location);
                    if ($successId) {
                        $userId = Session::getUserId();
                        $this->invModel->logAction($userId, 'CREATE_INVENTORY', 'inventory', $successId, "Registered consumable SKU {$sku}");
                        Session::setFlash('success', "Consumable item '{$name}' created successfully.");
                        $this->redirect('/inventory');
                    } else {
                        $error = 'Failed to record inventory item.';
                    }
                }
            }
        }

        $this->view('inventory/create', [
            'title' => 'Register Consumable Stock Item',
            'error' => $error
        ]);
    }

    /**
     * Handles metadata edits AND direct quantity adjustments (Manager/Admin Only)
     */
    public function edit() {
        $this->checkAccess(['Admin', 'Manager']);
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $this->redirect('/inventory');
        }

        $item = $this->invModel->getById($id);
        if (!$item) {
            Session::setFlash('danger', 'Consumable item not found.');
            $this->redirect('/inventory');
        }

        $error = '';
        $action = $_GET['action'] ?? 'edit';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();
            $userId = Session::getUserId();

            if ($action === 'adjust') {
                // Handle stock quantity adjustment (adds/consumes stock)
                $qtyChange = (int)($_POST['quantity_change'] ?? 0);
                $reason = trim($_POST['reason'] ?? '');

                if ($qtyChange === 0) {
                    $error = 'Adjustment quantity cannot be zero.';
                } else {
                    $success = $this->invModel->adjustQuantity($id, $qtyChange, $userId, $reason);
                    if ($success) {
                        Session::setFlash('success', 'Inventory stock adjusted successfully.');
                        $this->redirect('/inventory');
                    } else {
                        $error = 'Failed to adjust stock. Check if adjustment reduces stock level below zero.';
                    }
                }
            } else {
                // Handle standard item metadata updates
                $name = trim($_POST['name'] ?? '');
                $sku = trim($_POST['sku'] ?? '');
                $minThreshold = $_POST['min_threshold'] ?? 5;
                $unitPrice = $_POST['unit_price'] ?? 0.00;
                $location = trim($_POST['location'] ?? '');

                if (empty($name) || empty($sku) || empty($location)) {
                    $error = 'Name, SKU, and Location are required.';
                } else {
                    // Check duplicate SKU
                    $db = \App\Core\Database::getConnection();
                    $chk = $db->prepare("SELECT id FROM inventory WHERE sku = ? AND id != ?");
                    $chk->execute([$sku, $id]);

                    if ($chk->fetch()) {
                        $error = 'SKU is already in use by another item.';
                    } else {
                        $success = $this->invModel->update($id, $name, $sku, $minThreshold, $unitPrice, $location);
                        if ($success) {
                            $this->invModel->logAction($userId, 'UPDATE_INVENTORY', 'inventory', $id, "Updated consumable metadata for SKU {$sku}");
                            Session::setFlash('success', 'Consumable item updated successfully.');
                            $this->redirect('/inventory');
                        } else {
                            $error = 'Failed to update item details.';
                        }
                    }
                }
            }
        }

        $this->view('inventory/edit', [
            'title' => 'Update Consumable - ' . $item['sku'],
            'item' => $item,
            'action' => $action,
            'error' => $error
        ]);
    }
}
