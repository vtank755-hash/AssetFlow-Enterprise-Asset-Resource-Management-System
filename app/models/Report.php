<?php
namespace App\Models;

use App\Core\Model;
use App\Models\Asset;

class Report extends Model {
    /**
     * Compile Asset Valuation Report (including straight-line depreciation)
     */
    public function getValuationData() {
        $assetModel = new Asset();
        $assets = $assetModel->getAllFiltered();
        
        $report = [
            'assets' => [],
            'totals' => [
                'purchase_cost' => 0.0,
                'accumulated_depreciation' => 0.0,
                'book_value' => 0.0
            ]
        ];

        foreach ($assets as $asset) {
            $deprec = $assetModel->calculateDepreciation($asset);
            
            $report['assets'][] = [
                'asset_tag' => $asset['asset_tag'],
                'name' => $asset['name'],
                'category_name' => $asset['category_name'],
                'purchase_date' => $asset['purchase_date'],
                'purchase_cost' => $deprec['purchase_cost'],
                'accumulated_depreciation' => $deprec['accumulated_depreciation'],
                'book_value' => $deprec['book_value'],
                'status' => $asset['status']
            ];

            $report['totals']['purchase_cost'] += $deprec['purchase_cost'];
            $report['totals']['accumulated_depreciation'] += $deprec['accumulated_depreciation'];
            $report['totals']['book_value'] += $deprec['book_value'];
        }

        return $report;
    }

    /**
     * Compile Maintenance Expenses Report (using Resolved status)
     */
    public function getMaintenanceExpenseData() {
        $stmt = $this->db->prepare("
            SELECT a.asset_tag, a.name as asset_name, c.name as category_name,
                   COUNT(m.id) as total_events,
                   SUM(m.cost) as total_cost,
                   SUM(CASE WHEN m.completion_date IS NOT NULL THEN DATEDIFF(m.completion_date, m.scheduled_date) ELSE 0 END) as total_downtime_days
            FROM assets a
            JOIN asset_categories c ON a.category_id = c.id
            LEFT JOIN maintenance_requests m ON a.id = m.asset_id AND m.status = 'Resolved'
            GROUP BY a.id
            ORDER BY total_cost DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Compile Resource Utilization Report
     */
    public function getUtilizationData() {
        $stmt = $this->db->prepare("
            SELECT c.name as category_name,
                   COUNT(a.id) as total_assets,
                   SUM(CASE WHEN a.status = 'Allocated' THEN 1 ELSE 0 END) as currently_allocated,
                   COUNT(al.id) as total_historical_allocations,
                   ROUND((SUM(CASE WHEN a.status = 'Allocated' THEN 1 ELSE 0 END) / COUNT(a.id)) * 100, 1) as utilization_rate
            FROM asset_categories c
            JOIN assets a ON a.category_id = c.id
            LEFT JOIN asset_allocations al ON a.id = al.asset_id
            GROUP BY c.id
            ORDER BY utilization_rate DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Compile Department Asset Allocation Report
     */
    public function getDepartmentAssetData() {
        $stmt = $this->db->prepare("
            SELECT d.name as department_name, d.code as department_code, 
                   COUNT(a.id) as total_assets, 
                   SUM(a.purchase_cost) as total_valuation
            FROM departments d
            LEFT JOIN employees e ON d.id = e.department_id
            LEFT JOIN asset_allocations al ON e.id = al.employee_id AND al.status = 'Active'
            LEFT JOIN assets a ON al.asset_id = a.id AND a.status != 'Disposed'
            GROUP BY d.id
            ORDER BY d.name ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Compile Booking Frequency Report
     */
    public function getBookingData() {
        $stmt = $this->db->prepare("
            SELECT c.name as category_name, 
                   COUNT(rb.id) as total_bookings,
                   SUM(CASE WHEN rb.status = 'Upcoming' THEN 1 ELSE 0 END) as upcoming_bookings,
                   SUM(CASE WHEN rb.status = 'Ongoing' THEN 1 ELSE 0 END) as ongoing_bookings,
                   SUM(CASE WHEN rb.status = 'Completed' THEN 1 ELSE 0 END) as completed_bookings,
                   SUM(CASE WHEN rb.status = 'Cancelled' THEN 1 ELSE 0 END) as cancelled_bookings
            FROM resource_bookings rb
            JOIN assets a ON rb.asset_id = a.id
            JOIN asset_categories c ON a.category_id = c.id
            GROUP BY c.id
            ORDER BY c.name ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Compile Audit Verification Cycles Report
     */
    public function getAuditSummaryData() {
        $stmt = $this->db->prepare("
            SELECT ac.title as cycle_title, ac.status as cycle_status,
                   COUNT(ad.id) as total_checked,
                   SUM(CASE WHEN ad.status = 'Verified' THEN 1 ELSE 0 END) as verified_count,
                   SUM(CASE WHEN ad.status = 'Missing' THEN 1 ELSE 0 END) as missing_count,
                   SUM(CASE WHEN ad.status = 'Damaged' THEN 1 ELSE 0 END) as damaged_count
            FROM audit_cycles ac
            LEFT JOIN audit_details ad ON ac.id = ad.audit_cycle_id
            GROUP BY ac.id
            ORDER BY ac.created_at DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
