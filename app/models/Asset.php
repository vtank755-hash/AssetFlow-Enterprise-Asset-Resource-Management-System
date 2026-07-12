<?php
namespace App\Models;

use App\Core\Model;

class Asset extends Model {
    /**
     * Get categories list for selects.
     * 
     * @return array
     */
    public function getCategories() {
        $stmt = $this->db->prepare("SELECT * FROM categories ORDER BY name ASC");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Fetch filtered assets.
     */
    public function getAllFiltered($search = '', $category = '', $status = '', $location = '') {
        $sql = "
            SELECT a.*, c.name as category_name 
            FROM assets a
            JOIN categories c ON a.category_id = c.id
            WHERE 1=1
        ";
        $params = [];

        if (!empty($search)) {
            $sql .= " AND (a.name LIKE :search OR a.asset_tag LIKE :search OR a.serial_number LIKE :search OR a.model LIKE :search)";
            $params[':search'] = '%' . $search . '%';
        }

        if (!empty($category)) {
            $sql .= " AND a.category_id = :category";
            $params[':category'] = (int)$category;
        }

        if (!empty($status)) {
            $sql .= " AND a.status = :status";
            $params[':status'] = $status;
        }

        if (!empty($location)) {
            $sql .= " AND a.location LIKE :location";
            $params[':location'] = '%' . $location . '%';
        }

        $sql .= " ORDER BY a.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Fetch asset by ID.
     */
    public function getById($id) {
        $stmt = $this->db->prepare("
            SELECT a.*, c.name as category_name 
            FROM assets a
            JOIN categories c ON a.category_id = c.id
            WHERE a.id = :id 
            LIMIT 1
        ");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Create an asset.
     */
    public function create($tag, $categoryId, $name, $model, $serial, $purchaseDate, $cost, $deprecRate, $status, $location) {
        $stmt = $this->db->prepare("
            INSERT INTO assets (asset_tag, category_id, name, model, serial_number, purchase_date, purchase_cost, depreciation_rate, status, location)
            VALUES (:tag, :category_id, :name, :model, :serial, :purchase_date, :cost, :deprec_rate, :status, :location)
        ");
        $success = $stmt->execute([
            ':tag' => $tag,
            ':category_id' => $categoryId,
            ':name' => $name,
            ':model' => $model,
            ':serial' => $serial,
            ':purchase_date' => $purchaseDate,
            ':cost' => $cost,
            ':deprec_rate' => $deprecRate,
            ':status' => $status,
            ':location' => $location
        ]);
        return $success ? $this->db->lastInsertId() : false;
    }

    /**
     * Update asset.
     */
    public function update($id, $categoryId, $name, $model, $serial, $purchaseDate, $cost, $deprecRate, $status, $location) {
        $stmt = $this->db->prepare("
            UPDATE assets 
            SET category_id = :category_id, name = :name, model = :model, serial_number = :serial, 
                purchase_date = :purchase_date, purchase_cost = :cost, depreciation_rate = :deprec_rate, 
                status = :status, location = :location
            WHERE id = :id
        ");
        return $stmt->execute([
            ':id' => $id,
            ':category_id' => $categoryId,
            ':name' => $name,
            ':model' => $model,
            ':serial' => $serial,
            ':purchase_date' => $purchaseDate,
            ':cost' => $cost,
            ':deprec_rate' => $deprecRate,
            ':status' => $status,
            ':location' => $location
        ]);
    }

    /**
     * Deletes or decommissions (marks Disposed) asset.
     */
    public function dispose($id) {
        $stmt = $this->db->prepare("UPDATE assets SET status = 'Disposed' WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    /**
     * Calculate Straight-Line Depreciation.
     * Returns:
     * - purchase_cost
     * - annual_depreciation
     * - accumulated_depreciation
     * - book_value
     * - years_held
     */
    public function calculateDepreciation($asset) {
        $cost = (float)$asset['purchase_cost'];
        $rate = (float)$asset['depreciation_rate'] / 100;
        
        $purchaseDate = new \DateTime($asset['purchase_date']);
        $today = new \DateTime();
        
        $interval = $purchaseDate->diff($today);
        $years = $interval->y + ($interval->m / 12) + ($interval->d / 365);
        
        $annualDeprec = $cost * $rate;
        $accumDeprec = $annualDeprec * $years;
        
        if ($accumDeprec > $cost) {
            $accumDeprec = $cost;
        }
        
        $bookValue = $cost - $accumDeprec;
        
        return [
            'purchase_cost' => $cost,
            'annual_depreciation' => round($annualDeprec, 2),
            'accumulated_depreciation' => round($accumDeprec, 2),
            'book_value' => round($bookValue, 2),
            'years_held' => round($years, 2)
        ];
    }

    /**
     * Get allocations history for single asset view page.
     */
    public function getAllocationsHistory($assetId) {
        $stmt = $this->db->prepare("
            SELECT al.*, u.name as user_name, ab.name as allocator_name
            FROM allocations al
            JOIN users u ON al.user_id = u.id
            JOIN users ab ON al.allocated_by = ab.id
            WHERE al.asset_id = :asset_id
            ORDER BY al.allocated_date DESC
        ");
        $stmt->execute([':asset_id' => $assetId]);
        return $stmt->fetchAll();
    }

    /**
     * Get maintenance history for single asset view page.
     */
    public function getMaintenanceHistory($assetId) {
        $stmt = $this->db->prepare("
            SELECT * FROM maintenance_schedules
            WHERE asset_id = :asset_id
            ORDER BY scheduled_date DESC
        ");
        $stmt->execute([':asset_id' => $assetId]);
        return $stmt->fetchAll();
    }
}
