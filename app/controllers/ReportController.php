<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\Report;

class ReportController extends Controller {
    private $reportModel;

    public function __construct() {
        $this->reportModel = new Report();
    }

    /**
     * Report modules navigation index page (Manager/Admin Only)
     */
    public function index() {
        $this->checkAccess(['Admin', 'Manager']);
        
        $this->view('reports/index', [
            'title' => 'Analytics & Reports'
        ]);
    }

    /**
     * Asset Valuation Report View
     */
    public function valuation() {
        $this->checkAccess(['Admin', 'Manager']);
        $data = $this->reportModel->getValuationData();

        $this->view('reports/valuation', [
            'title' => 'Asset Valuation Report',
            'assets' => $data['assets'],
            'totals' => $data['totals']
        ]);
    }

    /**
     * Maintenance Expenses Report View
     */
    public function maintenance() {
        $this->checkAccess(['Admin', 'Manager']);
        $records = $this->reportModel->getMaintenanceExpenseData();

        $this->view('reports/maintenance', [
            'title' => 'Maintenance Expenditures',
            'records' => $records
        ]);
    }

    /**
     * Utilization Report View
     */
    public function utilization() {
        $this->checkAccess(['Admin', 'Manager']);
        $records = $this->reportModel->getUtilizationData();

        $this->view('reports/utilization', [
            'title' => 'Resource Utilization',
            'records' => $records
        ]);
    }

    /**
     * Department Allocations Report View
     */
    public function department() {
        $this->checkAccess(['Admin', 'Manager']);
        $records = $this->reportModel->getDepartmentAssetData();

        $this->view('reports/department', [
            'title' => 'Department Asset Allocation',
            'records' => $records
        ]);
    }

    /**
     * Booking Report View
     */
    public function booking() {
        $this->checkAccess(['Admin', 'Manager']);
        $records = $this->reportModel->getBookingData();

        $this->view('reports/booking', [
            'title' => 'Booking Frequency Analytics',
            'records' => $records
        ]);
    }

    /**
     * Audit Stocktake Report View
     */
    public function audit() {
        $this->checkAccess(['Admin', 'Manager']);
        $records = $this->reportModel->getAuditSummaryData();

        $this->view('reports/audit', [
            'title' => 'Stocktake Audit Summary',
            'records' => $records
        ]);
    }

    /**
     * Streams CSV file dynamically for download
     */
    public function export() {
        $this->checkAccess(['Admin', 'Manager']);
        $type = $_GET['type'] ?? '';

        // Clear output buffer to avoid corruption
        if (ob_get_level()) {
            ob_end_clean();
        }

        switch ($type) {
            case 'valuation':
                $filename = "asset_valuation_" . date('Ymd') . ".csv";
                header('Content-Type: text/csv; charset=utf-8');
                header('Content-Disposition: attachment; filename=' . $filename);
                
                $output = fopen('php://output', 'w');
                fputcsv($output, ['Asset Tag', 'Asset Name', 'Category', 'Purchase Date', 'Purchase Cost (₹)', 'Accumulated Depreciation (₹)', 'Current Book Value (₹)', 'Status']);
                
                $data = $this->reportModel->getValuationData();
                foreach ($data['assets'] as $row) {
                    fputcsv($output, [
                        $row['asset_tag'],
                        $row['name'],
                        $row['category_name'],
                        $row['purchase_date'],
                        number_format($row['purchase_cost'], 2, '.', ''),
                        number_format($row['accumulated_depreciation'], 2, '.', ''),
                        number_format($row['book_value'], 2, '.', ''),
                        $row['status']
                    ]);
                }
                
                // Add totals row
                fputcsv($output, []);
                fputcsv($output, ['TOTALS', '', '', '', number_format($data['totals']['purchase_cost'], 2, '.', ''), number_format($data['totals']['accumulated_depreciation'], 2, '.', ''), number_format($data['totals']['book_value'], 2, '.', ''), '']);
                fclose($output);
                exit;

            case 'maintenance':
                $filename = "maintenance_expenses_" . date('Ymd') . ".csv";
                header('Content-Type: text/csv; charset=utf-8');
                header('Content-Disposition: attachment; filename=' . $filename);
                
                $output = fopen('php://output', 'w');
                fputcsv($output, ['Asset Tag', 'Asset Name', 'Category', 'Total Maintenance Events', 'Total Maintenance Cost (₹)', 'Total Downtime (Days)']);
                
                $records = $this->reportModel->getMaintenanceExpenseData();
                foreach ($records as $row) {
                    fputcsv($output, [
                        $row['asset_tag'],
                        $row['asset_name'],
                        $row['category_name'],
                        $row['total_events'],
                        number_format($row['total_cost'] ?: 0, 2, '.', ''),
                        $row['total_downtime_days'] ?: 0
                    ]);
                }
                fclose($output);
                exit;

            case 'utilization':
                $filename = "resource_utilization_" . date('Ymd') . ".csv";
                header('Content-Type: text/csv; charset=utf-8');
                header('Content-Disposition: attachment; filename=' . $filename);
                
                $output = fopen('php://output', 'w');
                fputcsv($output, ['Category', 'Total Registered Assets', 'Currently Checked Out', 'Historical Checkout Count', 'Utilization Rate (%)']);
                
                $records = $this->reportModel->getUtilizationData();
                foreach ($records as $row) {
                    fputcsv($output, [
                        $row['category_name'],
                        $row['total_assets'],
                        $row['currently_allocated'],
                        $row['total_historical_allocations'],
                        $row['utilization_rate']
                    ]);
                }
                fclose($output);
                exit;

            case 'department':
                $filename = "department_allocations_" . date('Ymd') . ".csv";
                header('Content-Type: text/csv; charset=utf-8');
                header('Content-Disposition: attachment; filename=' . $filename);
                
                $output = fopen('php://output', 'w');
                fputcsv($output, ['Department Name', 'Department Code', 'Total Active Assets', 'Total Portfolio Valuation (₹)']);
                
                $records = $this->reportModel->getDepartmentAssetData();
                foreach ($records as $row) {
                    fputcsv($output, [
                        $row['department_name'],
                        $row['department_code'],
                        $row['total_assets'],
                        number_format($row['total_valuation'] ?: 0, 2, '.', '')
                    ]);
                }
                fclose($output);
                exit;

            case 'booking':
                $filename = "resource_bookings_" . date('Ymd') . ".csv";
                header('Content-Type: text/csv; charset=utf-8');
                header('Content-Disposition: attachment; filename=' . $filename);
                
                $output = fopen('php://output', 'w');
                fputcsv($output, ['Category/Class Name', 'Total Bookings', 'Upcoming', 'Ongoing', 'Completed', 'Cancelled']);
                
                $records = $this->reportModel->getBookingData();
                foreach ($records as $row) {
                    fputcsv($output, [
                        $row['category_name'],
                        $row['total_bookings'],
                        $row['upcoming_bookings'],
                        $row['ongoing_bookings'],
                        $row['completed_bookings'],
                        $row['cancelled_bookings']
                    ]);
                }
                fclose($output);
                exit;

            case 'audit':
                $filename = "stocktake_audits_" . date('Ymd') . ".csv";
                header('Content-Type: text/csv; charset=utf-8');
                header('Content-Disposition: attachment; filename=' . $filename);
                
                $output = fopen('php://output', 'w');
                fputcsv($output, ['Audit Cycle Title', 'Status', 'Total Checked', 'Verified (Match)', 'Missing (Lost)', 'Damaged (Repairs)']);
                
                $records = $this->reportModel->getAuditSummaryData();
                foreach ($records as $row) {
                    fputcsv($output, [
                        $row['cycle_title'],
                        $row['cycle_status'],
                        $row['total_checked'],
                        $row['verified_count'],
                        $row['missing_count'],
                        $row['damaged_count']
                    ]);
                }
                fclose($output);
                exit;

            default:
                Session::setFlash('danger', 'Invalid export report type.');
                $this->redirect('/reports');
        }
    }
}
