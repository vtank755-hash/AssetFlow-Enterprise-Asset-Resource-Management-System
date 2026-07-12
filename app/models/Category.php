<?php
namespace App\Models;

use App\Core\Model;

/**
 * Category Model
 * Manages database operations for asset categories.
 */
class Category extends Model {
    /**
     * Retrieve all category records with counts and optional search filters.
     * 
     * @param string $search
     * @return array
     */
    public function getAll($search = '') {
        if (!empty($search)) {
            $stmt = $this->db->prepare("
                SELECT c.*, COUNT(a.id) as asset_count 
                FROM asset_categories c
                LEFT JOIN assets a ON c.id = a.category_id AND a.status != 'Disposed'
                WHERE c.name LIKE :search OR c.description LIKE :search
                GROUP BY c.id
                ORDER BY c.name ASC
            ");
            $stmt->execute([':search' => "%$search%"]);
        } else {
            $stmt = $this->db->prepare("
                SELECT c.*, COUNT(a.id) as asset_count 
                FROM asset_categories c
                LEFT JOIN assets a ON c.id = a.category_id AND a.status != 'Disposed'
                GROUP BY c.id
                ORDER BY c.name ASC
            ");
            $stmt->execute();
        }
        return $stmt->fetchAll();
    }

    /**
     * Retrieve category by ID.
     * 
     * @param int $id
     * @return array|false
     */
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM asset_categories WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Create a new category.
     * 
     * @param string $name
     * @param string $description
     * @return int|false
     */
    public function create($name, $description) {
        $stmt = $this->db->prepare("INSERT INTO asset_categories (name, description) VALUES (:name, :description)");
        $success = $stmt->execute([
            ':name' => $name,
            ':description' => $description
        ]);
        return $success ? $this->db->lastInsertId() : false;
    }

    /**
     * Update category details.
     * 
     * @param int $id
     * @param string $name
     * @param string $description
     * @return bool
     */
    public function update($id, $name, $description) {
        $stmt = $this->db->prepare("UPDATE asset_categories SET name = :name, description = :description WHERE id = :id");
        return $stmt->execute([
            ':id' => $id,
            ':name' => $name,
            ':description' => $description
        ]);
    }

    /**
     * Delete category if no active assets exist under it.
     * 
     * @param int $id
     * @return bool
     */
    public function delete($id) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM assets WHERE category_id = ? AND status != 'Disposed'");
        $stmt->execute([$id]);
        if ((int)$stmt->fetchColumn() > 0) {
            return false;
        }
        $stmt = $this->db->prepare("DELETE FROM asset_categories WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
