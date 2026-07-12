<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\Category;

/**
 * Category Controller
 * Handles administrative actions for asset categories creation, updates, and listings.
 */
class CategoryController extends Controller {
    private $categoryModel;

    public function __construct() {
        $this->categoryModel = new Category();
    }

    /**
     * Display all asset categories with active counts and search query filters.
     * 
     * @return void
     */
    public function index() {
        $this->checkAccess(['Admin', 'Manager']);
        $search = trim($_GET['search'] ?? '');
        $categories = $this->categoryModel->getAll($search);

        $this->view('categories/index', [
            'title' => 'Asset Categories Setup',
            'categories' => $categories,
            'search' => $search
        ]);
    }

    /**
     * Create a new asset category.
     * 
     * @return void
     */
    public function create() {
        $this->checkAccess(['Admin', 'Manager']);
        $error = '';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');

            if (empty($name)) {
                $error = 'Category name is required.';
            } else {
                $newId = $this->categoryModel->create($name, $description);
                if ($newId) {
                    $userId = Session::getUserId();
                    $this->categoryModel->logAction($userId, 'CREATE_CATEGORY', 'asset_categories', $newId, "Created category: $name");
                    Session::setFlash('success', 'Asset category created successfully.');
                    $this->redirect('/categories');
                } else {
                    $error = 'Failed to create category. Name may already exist.';
                }
            }
        }

        $this->view('categories/create', [
            'title' => 'Add Asset Category',
            'error' => $error
        ]);
    }

    /**
     * Edit asset category.
     * 
     * @return void
     */
    public function edit() {
        $this->checkAccess(['Admin', 'Manager']);
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $this->redirect('/categories');
        }

        $cat = $this->categoryModel->getById($id);
        if (!$cat) {
            Session::setFlash('danger', 'Asset category not found.');
            $this->redirect('/categories');
        }

        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();
            $name = trim($_POST['name'] ?? '');
            $description = trim($_POST['description'] ?? '');

            if (empty($name)) {
                $error = 'Category name is required.';
            } else {
                if ($this->categoryModel->update($id, $name, $description)) {
                    $userId = Session::getUserId();
                    $this->categoryModel->logAction($userId, 'UPDATE_CATEGORY', 'asset_categories', $id, "Updated category: $name");
                    Session::setFlash('success', 'Asset category updated successfully.');
                    $this->redirect('/categories');
                } else {
                    $error = 'Failed to update category.';
                }
            }
        }

        $this->view('categories/edit', [
            'title' => 'Edit Asset Category',
            'cat' => $cat,
            'error' => $error
        ]);
    }

    /**
     * Delete asset category.
     * 
     * @return void
     */
    public function delete() {
        $this->checkAccess(['Admin']);
        $id = $_GET['id'] ?? null;
        if ($id) {
            if ($this->categoryModel->delete($id)) {
                $userId = Session::getUserId();
                $this->categoryModel->logAction($userId, 'DELETE_CATEGORY', 'asset_categories', $id, "Deleted category ID: $id");
                Session::setFlash('success', 'Asset category deleted successfully.');
            } else {
                Session::setFlash('danger', 'Cannot delete category. Make sure no active assets belong to it first.');
            }
        }
        $this->redirect('/categories');
    }
}
