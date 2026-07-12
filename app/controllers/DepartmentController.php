<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\Department;

/**
 * Department Controller
 * Handles administrative actions for departments creation, updates, and hierarchy displays.
 */
class DepartmentController extends Controller {
    private $deptModel;

    public function __construct() {
        $this->deptModel = new Department();
    }

    /**
     * Display all departments with search and hierarchy details.
     * 
     * @return void
     */
    public function index() {
        $this->checkAccess(['Admin', 'Manager']);
        $search = trim($_GET['search'] ?? '');
        $departments = $this->deptModel->getAll($search);

        $this->view('departments/index', [
            'title' => 'Departments Setup',
            'departments' => $departments,
            'search' => $search
        ]);
    }

    /**
     * Create a new department.
     * 
     * @return void
     */
    public function create() {
        $this->checkAccess(['Admin']);
        $error = '';
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();
            $name = trim($_POST['name'] ?? '');
            $code = trim($_POST['code'] ?? '');

            if (empty($name) || empty($code)) {
                $error = 'All fields are required.';
            } else {
                $newId = $this->deptModel->create($name, $code);
                if ($newId) {
                    $userId = Session::getUserId();
                    $this->deptModel->logAction($userId, 'CREATE_DEPT', 'departments', $newId, "Created department: $name ($code)");
                    Session::setFlash('success', 'Department created successfully.');
                    $this->redirect('/departments');
                } else {
                    $error = 'Failed to create department. Code/Name may already be in use.';
                }
            }
        }

        $this->view('departments/create', [
            'title' => 'Add Department',
            'error' => $error
        ]);
    }

    /**
     * Edit department details.
     * 
     * @return void
     */
    public function edit() {
        $this->checkAccess(['Admin']);
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $this->redirect('/departments');
        }

        $dept = $this->deptModel->getById($id);
        if (!$dept) {
            Session::setFlash('danger', 'Department not found.');
            $this->redirect('/departments');
        }

        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();
            $name = trim($_POST['name'] ?? '');
            $code = trim($_POST['code'] ?? '');

            if (empty($name) || empty($code)) {
                $error = 'All fields are required.';
            } else {
                if ($this->deptModel->update($id, $name, $code)) {
                    $userId = Session::getUserId();
                    $this->deptModel->logAction($userId, 'UPDATE_DEPT', 'departments', $id, "Updated department: $name ($code)");
                    Session::setFlash('success', 'Department updated successfully.');
                    $this->redirect('/departments');
                } else {
                    $error = 'Failed to update department.';
                }
            }
        }

        $this->view('departments/edit', [
            'title' => 'Edit Department',
            'dept' => $dept,
            'error' => $error
        ]);
    }

    /**
     * Delete department.
     * 
     * @return void
     */
    public function delete() {
        $this->checkAccess(['Admin']);
        $id = $_GET['id'] ?? null;
        if ($id) {
            if ($this->deptModel->delete($id)) {
                $userId = Session::getUserId();
                $this->deptModel->logAction($userId, 'DELETE_DEPT', 'departments', $id, "Deleted department ID: $id");
                Session::setFlash('success', 'Department deleted successfully.');
            } else {
                Session::setFlash('danger', 'Cannot delete department. Make sure no employees are assigned to it first.');
            }
        }
        $this->redirect('/departments');
    }
}
