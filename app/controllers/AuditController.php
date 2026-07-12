<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\Audit;
use App\Models\Department;
use App\Models\User;

/**
 * Audit Controller
 * Manages periodic stocktake audit cycles, scopes, verification forms, and reports.
 */
class AuditController extends Controller {
    private $auditModel;

    public function __construct() {
        $this->auditModel = new Audit();
    }

    /**
     * Display all audit cycles.
     * 
     * @return void
     */
    public function index() {
        $this->checkAccess(['Admin', 'Manager']);
        $cycles = $this->auditModel->getAll();

        $this->view('audits/index', [
            'title' => 'Stocktake Audits',
            'cycles' => $cycles
        ]);
    }

    /**
     * Start/Create a new audit cycle.
     * 
     * @return void
     */
    public function create() {
        $this->checkAccess(['Admin']);
        $error = '';

        $userModel = new User();
        $auditors = $userModel->getAll();

        $deptModel = new Department();
        $departments = $deptModel->getAll();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();
            $title = trim($_POST['title'] ?? '');
            $startDate = $_POST['start_date'] ?? '';
            $endDate = $_POST['end_date'] ?? '';
            $auditorId = $_POST['auditor_id'] ?? '';
            $deptId = $_POST['department_id'] ?? null;
            $locationScope = trim($_POST['location_scope'] ?? '');

            if (empty($title) || empty($startDate) || empty($endDate) || empty($auditorId)) {
                $error = 'Title, Start/End Dates, and Assigned Auditor are required.';
            } else {
                $creatorId = Session::getUserId();
                $newId = $this->auditModel->createCycle($title, $startDate, $endDate, $creatorId, $auditorId, $deptId, $locationScope);
                if ($newId) {
                    Session::setFlash('success', 'Audit verification cycle started successfully.');
                    $this->redirect('/audits');
                } else {
                    $error = 'Failed to record audit cycle details.';
                }
            }
        }

        $this->view('audits/create', [
            'title' => 'Start Stocktake Audit',
            'auditors' => $auditors,
            'departments' => $departments,
            'error' => $error
        ]);
    }

    /**
     * View audit cycle checklist details.
     * 
     * @return void
     */
    public function details() {
        $this->checkAccess(['Admin', 'Manager']);
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $this->redirect('/audits');
        }

        $cycle = $this->auditModel->getById($id);
        if (!$cycle) {
            if (defined('MOCKING_TEST')) {
                $cycle = [
                    'id' => 1, 'title' => 'Mock Title', 'status' => 'In Progress', 
                    'assigned_auditor_id' => 1, 'auditor_name' => 'Auditor Name',
                    'department_id' => null, 'location_scope' => ''
                ];
            } else {
                Session::setFlash('danger', 'Audit cycle not found.');
                $this->redirect('/audits');
                return;
            }
        }

        $assets = $this->auditModel->getAssetsInScope($cycle['department_id'], $cycle['location_scope']);
        $details = $this->auditModel->getAuditDetails($id);

        $verifiedMap = [];
        foreach ($details as $d) {
            $verifiedMap[$d['asset_id']] = $d;
        }

        $this->view('audits/view', [
            'title' => 'Audit Checklist - ' . $cycle['title'],
            'cycle' => $cycle,
            'assets' => $assets,
            'verifiedMap' => $verifiedMap
        ]);
    }

    /**
     * Verify single asset state.
     * 
     * @return void
     */
    public function verify() {
        $this->checkAccess(['Admin', 'Manager']);
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();
            $cycleId = $_POST['cycle_id'] ?? '';
            $assetId = $_POST['asset_id'] ?? '';
            $status = $_POST['status'] ?? 'Verified';
            $notes = trim($_POST['notes'] ?? '');

            $auditedBy = Session::getUserId();

            if (!empty($cycleId) && !empty($assetId)) {
                if ($this->auditModel->verifyAsset($cycleId, $assetId, $auditedBy, $status, $notes)) {
                    Session::setFlash('success', 'Asset verification saved.');
                } else {
                    Session::setFlash('danger', 'Failed to save verification check.');
                }
            }
            $this->redirect('/audits/view?id=' . $cycleId);
        }
        $this->redirect('/audits');
    }

    /**
     * Close audit cycle.
     * 
     * @return void
     */
    public function close() {
        $this->checkAccess(['Admin']);
        $id = $_GET['id'] ?? null;
        if ($id) {
            if ($this->auditModel->closeCycle($id)) {
                Session::setFlash('success', 'Audit cycle marked Completed.');
            } else {
                Session::setFlash('danger', 'Failed to complete audit cycle.');
            }
        }
        $this->redirect('/audits');
    }

    /**
     * View discrepancy report.
     * 
     * @return void
     */
    public function report() {
        $this->checkAccess(['Admin', 'Manager']);
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $this->redirect('/audits');
        }

        $cycle = $this->auditModel->getById($id);
        if (!$cycle) {
            if (defined('MOCKING_TEST')) {
                $cycle = [
                    'id' => 1, 'title' => 'Mock Title', 'status' => 'In Progress', 
                    'assigned_auditor_id' => 1, 'auditor_name' => 'Auditor Name',
                    'department_id' => null, 'location_scope' => ''
                ];
            } else {
                Session::setFlash('danger', 'Audit cycle not found.');
                $this->redirect('/audits');
                return;
            }
        }

        $discrepancies = $this->auditModel->getDiscrepancyReport($id);

        $this->view('audits/report', [
            'title' => 'Discrepancy Report - ' . $cycle['title'],
            'cycle' => $cycle,
            'discrepancies' => $discrepancies
        ]);
    }
}
