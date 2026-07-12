<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\Asset;

class AssetController extends Controller {
    private $assetModel;

    public function __construct() {
        $this->assetModel = new Asset();
    }

    /**
     * Lists assets with filters
     */
    public function index() {
        $this->checkAccess();

        // Get filter inputs
        $search = trim($_GET['search'] ?? '');
        $category = $_GET['category'] ?? '';
        $status = $_GET['status'] ?? '';
        $location = trim($_GET['location'] ?? '');

        $assets = $this->assetModel->getAllFiltered($search, $category, $status, $location);
        $categories = $this->assetModel->getCategories();

        $this->view('assets/index', [
            'title' => 'Assets Directory',
            'assets' => $assets,
            'categories' => $categories,
            'filters' => [
                'search' => $search,
                'category' => $category,
                'status' => $status,
                'location' => $location
            ]
        ]);
    }

    /**
     * Single Asset Details Page
     */
    public function show() {
        $this->checkAccess();
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $this->redirect('/assets');
        }

        $asset = $this->assetModel->getById($id);
        if (!$asset) {
            Session::setFlash('danger', 'Asset not found.');
            $this->redirect('/assets');
        }

        // Calculate Straight-Line Depreciation
        $depreciation = $this->assetModel->calculateDepreciation($asset);
        
        // Fetch histories
        $allocations = $this->assetModel->getAllocationsHistory($id);
        $maintenances = $this->assetModel->getMaintenanceHistory($id);

        // Fetch documents
        $db = \App\Core\Database::getConnection();
        $docStmt = $db->prepare("SELECT * FROM asset_documents WHERE asset_id = ?");
        $docStmt->execute([$id]);
        $documents = $docStmt->fetchAll();

        $activeTab = $_GET['tab'] ?? 'info';

        $this->view('assets/view', [
            'title' => 'Asset Profile - ' . $asset['asset_tag'],
            'asset' => $asset,
            'depreciation' => $depreciation,
            'allocations' => $allocations,
            'maintenances' => $maintenances,
            'documents' => $documents,
            'activeTab' => $activeTab
        ]);
    }

    /**
     * Creates new asset
     */
    public function create() {
        $this->checkAccess(['Admin', 'Manager']);
        $error = '';
        $categories = $this->assetModel->getCategories();

        // Generate suggested asset tag (Format: AF-######)
        $db = \App\Core\Database::getConnection();
        $maxId = $db->query("SELECT MAX(id) FROM assets")->fetchColumn() ?: 0;
        $suggestedTag = 'AF-' . str_pad($maxId + 1, 6, '0', STR_PAD_LEFT);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();
            $tag = trim($_POST['asset_tag'] ?? '');
            $categoryId = $_POST['category_id'] ?? '';
            $newCatName = trim($_POST['new_category_name'] ?? '');
            $name = trim($_POST['name'] ?? '');
            $model = trim($_POST['model'] ?? '');
            $serial = trim($_POST['serial_number'] ?? '');
            $purchaseDate = $_POST['purchase_date'] ?? '';
            $cost = $_POST['purchase_cost'] ?? '';
            $deprecRate = $_POST['depreciation_rate'] ?? '';
            $status = $_POST['status'] ?? 'Available';
            $location = trim($_POST['location'] ?? '');

            // Process New Category Name if provided
            if (empty($categoryId) && !empty($newCatName)) {
                $chkCat = $db->prepare("SELECT id FROM asset_categories WHERE name = ?");
                $chkCat->execute([$newCatName]);
                $existingCatId = $chkCat->fetchColumn();
                
                if ($existingCatId) {
                    $categoryId = $existingCatId;
                } else {
                    $insCat = $db->prepare("INSERT INTO asset_categories (name) VALUES (?)");
                    if ($insCat->execute([$newCatName])) {
                        $categoryId = $db->lastInsertId();
                    }
                }
            }

            if (empty($tag) || empty($categoryId) || empty($name) || empty($serial) || empty($purchaseDate) || empty($cost) || empty($deprecRate) || empty($location)) {
                $error = 'Please fill in all required fields (select a category or enter a new one).';
            } else {
                // Validate unique tag and serial
                $chk1 = $db->prepare("SELECT id FROM assets WHERE asset_tag = ?");
                $chk1->execute([$tag]);
                
                $chk2 = $db->prepare("SELECT id FROM assets WHERE serial_number = ?");
                $chk2->execute([$serial]);

                if ($chk1->fetch()) {
                    $error = 'Asset Tag already in use.';
                } elseif ($chk2->fetch()) {
                    $error = 'Serial Number already in use.';
                } else {
                    $newId = $this->assetModel->create($tag, $categoryId, $name, $model, $serial, $purchaseDate, $cost, $deprecRate, $status, $location);
                    if ($newId) {
                        $userId = Session::getUserId();
                        
                        // Handle photo upload
                        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                            $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
                            if (in_array($ext, ['png', 'jpg', 'jpeg'])) {
                                $photoName = 'photo_' . $newId . '_' . time() . '.' . $ext;
                                $photoDest = dirname(dirname(__DIR__)) . '/public/uploads/' . $photoName;
                                if (move_uploaded_file($_FILES['photo']['tmp_name'], $photoDest)) {
                                    $photoPath = 'uploads/' . $photoName;
                                    $db->prepare("UPDATE assets SET photo_path = ? WHERE id = ?")->execute([$photoPath, $newId]);
                                }
                            }
                        }

                        // Handle receipts/manual documents uploads
                        if (isset($_FILES['documents']) && is_array($_FILES['documents']['name'])) {
                            for ($i = 0; $i < count($_FILES['documents']['name']); $i++) {
                                if ($_FILES['documents']['error'][$i] === UPLOAD_ERR_OK) {
                                    $origName = $_FILES['documents']['name'][$i];
                                    $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
                                    if (in_array($ext, ALLOWED_DOC_EXTENSIONS)) {
                                        $docName = 'doc_' . $newId . '_' . $i . '_' . time() . '.' . $ext;
                                        $docDest = dirname(dirname(__DIR__)) . '/public/uploads/' . $docName;
                                        if (move_uploaded_file($_FILES['documents']['tmp_name'][$i], $docDest)) {
                                            $filePath = 'uploads/' . $docName;
                                            $db->prepare("INSERT INTO asset_documents (asset_id, document_name, file_path) VALUES (?, ?, ?)")
                                               ->execute([$newId, $origName, $filePath]);
                                        }
                                    }
                                }
                            }
                        }

                        $this->assetModel->logAction($userId, 'CREATE_ASSET', 'assets', $newId, "Created asset: $name (Tag: $tag)");
                        Session::setFlash('success', 'Asset created successfully.');
                        $this->redirect('/assets/view?id=' . $newId);
                    } else {
                        $error = 'Failed to record asset.';
                    }
                }
            }
        }

        $this->view('assets/create', [
            'title' => 'Register Asset',
            'categories' => $categories,
            'suggestedTag' => $suggestedTag,
            'error' => $error
        ]);
    }

    /**
     * Edits an asset
     */
    public function edit() {
        $this->checkAccess(['Admin', 'Manager']);
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $this->redirect('/assets');
        }

        $asset = $this->assetModel->getById($id);
        if (!$asset) {
            Session::setFlash('danger', 'Asset not found.');
            $this->redirect('/assets');
        }

        $error = '';
        $categories = $this->assetModel->getCategories();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();
            $categoryId = $_POST['category_id'] ?? '';
            $newCatName = trim($_POST['new_category_name'] ?? '');
            $name = trim($_POST['name'] ?? '');
            $model = trim($_POST['model'] ?? '');
            $serial = trim($_POST['serial_number'] ?? '');
            $purchaseDate = $_POST['purchase_date'] ?? '';
            $cost = $_POST['purchase_cost'] ?? '';
            $deprecRate = $_POST['depreciation_rate'] ?? '';
            $status = $_POST['status'] ?? 'Available';
            $location = trim($_POST['location'] ?? '');

            // Process New Category Name if provided
            if (empty($categoryId) && !empty($newCatName)) {
                $db = \App\Core\Database::getConnection();
                $chkCat = $db->prepare("SELECT id FROM asset_categories WHERE name = ?");
                $chkCat->execute([$newCatName]);
                $existingCatId = $chkCat->fetchColumn();
                
                if ($existingCatId) {
                    $categoryId = $existingCatId;
                } else {
                    $insCat = $db->prepare("INSERT INTO asset_categories (name) VALUES (?)");
                    if ($insCat->execute([$newCatName])) {
                        $categoryId = $db->lastInsertId();
                    }
                }
            }

            if (empty($categoryId) || empty($name) || empty($serial) || empty($purchaseDate) || empty($cost) || empty($deprecRate) || empty($location)) {
                $error = 'Please fill in all required fields (select a category or enter a new one).';
            } else {
                // Check serial duplicate excluding current
                $db = \App\Core\Database::getConnection();
                $chk = $db->prepare("SELECT id FROM assets WHERE serial_number = ? AND id != ?");
                $chk->execute([$serial, $id]);

                if ($chk->fetch()) {
                    $error = 'Serial Number already in use by another asset.';
                } else {
                    $success = $this->assetModel->update($id, $categoryId, $name, $model, $serial, $purchaseDate, $cost, $deprecRate, $status, $location);
                    if ($success) {
                        $userId = Session::getUserId();

                        // Handle photo upload
                        if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                            $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
                            if (in_array($ext, ['png', 'jpg', 'jpeg'])) {
                                $photoName = 'photo_' . $id . '_' . time() . '.' . $ext;
                                $photoDest = dirname(dirname(__DIR__)) . '/public/uploads/' . $photoName;
                                if (move_uploaded_file($_FILES['photo']['tmp_name'], $photoDest)) {
                                    $photoPath = 'uploads/' . $photoName;
                                    $db->prepare("UPDATE assets SET photo_path = ? WHERE id = ?")->execute([$photoPath, $id]);
                                }
                            }
                        }

                        // Handle receipts/manual documents uploads
                        if (isset($_FILES['documents']) && is_array($_FILES['documents']['name'])) {
                            for ($i = 0; $i < count($_FILES['documents']['name']); $i++) {
                                if ($_FILES['documents']['error'][$i] === UPLOAD_ERR_OK) {
                                    $origName = $_FILES['documents']['name'][$i];
                                    $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
                                    if (in_array($ext, ALLOWED_DOC_EXTENSIONS)) {
                                        $docName = 'doc_' . $id . '_' . $i . '_' . time() . '.' . $ext;
                                        $docDest = dirname(dirname(__DIR__)) . '/public/uploads/' . $docName;
                                        if (move_uploaded_file($_FILES['documents']['tmp_name'][$i], $docDest)) {
                                            $filePath = 'uploads/' . $docName;
                                            $db->prepare("INSERT INTO asset_documents (asset_id, document_name, file_path) VALUES (?, ?, ?)")
                                               ->execute([$id, $origName, $filePath]);
                                        }
                                    }
                                }
                            }
                        }

                        $this->assetModel->logAction($userId, 'UPDATE_ASSET', 'assets', $id, "Updated asset: $name (Tag: {$asset['asset_tag']})");
                        Session::setFlash('success', 'Asset details updated successfully.');
                        $this->redirect('/assets/view?id=' . $id);
                    } else {
                        $error = 'Failed to update asset.';
                    }
                }
            }
        }

        $this->view('assets/edit', [
            'title' => 'Edit Asset - ' . $asset['asset_tag'],
            'asset' => $asset,
            'categories' => $categories,
            'error' => $error
        ]);
    }

    /**
     * Decommission / Dispose Asset
     */
    public function delete() {
        $this->checkAccess(['Admin', 'Manager']);
        $id = $_GET['id'] ?? null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();
            $id = $_POST['id'] ?? null;
        }

        if (!$id) {
            $this->redirect('/assets');
        }

        $asset = $this->assetModel->getById($id);
        if ($asset) {
            $this->assetModel->dispose($id);
            $userId = Session::getUserId();
            $this->assetModel->logAction($userId, 'DISPOSE_ASSET', 'assets', $id, "Decommissioned/Disposed asset: {$asset['name']} (Tag: {$asset['asset_tag']})");
            Session::setFlash('success', 'Asset decommissioned and marked as Disposed.');
        } else {
            Session::setFlash('danger', 'Asset not found.');
        }

        $this->redirect('/assets');
    }
}
