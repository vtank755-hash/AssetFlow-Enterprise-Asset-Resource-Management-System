<?php
namespace App\Models;

use App\Core\Model;

/**
 * Department Model
 * Manages database operations for departments, hierarchy checks, and validation.
 */
class Department extends Model {
    /**
     * Retrieve all department records with optional search query.
     * 
     * @param string $search
     * @return array
     */
    public function getAll($search = '') {
        if (!empty($search)) {
            $stmt = $this->db->prepare("
                SELECT d.*, COUNT(e.id) as employee_count 
                FROM departments d
                LEFT JOIN employees e ON d.id = e.department_id
                WHERE d.name LIKE :search OR d.code LIKE :search 
                GROUP BY d.id
                ORDER BY d.name ASC
            ");
            $stmt->execute([':search' => "%$search%"]);
        } else {
            $stmt = $this->db->prepare("
                SELECT d.*, COUNT(e.id) as employee_count 
                FROM departments d
                LEFT JOIN employees e ON d.id = e.department_id
                GROUP BY d.id
                ORDER BY d.name ASC
            ");
            $stmt->execute();
        }
        return $stmt->fetchAll();
    }

    /**
     * Retrieve department record by ID.
     * 
     * @param int $id
     * @return array|false
     */
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM departments WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /**
     * Create a new department.
     * 
     * @param string $name
     * @param string $code
     * @return int|false
     */
    public function create($name, $code) {
        $stmt = $this->db->prepare("INSERT INTO departments (name, code) VALUES (:name, :code)");
        $success = $stmt->execute([
            ':name' => $name,
            ':code' => $code
        ]);
        return $success ? $this->db->lastInsertId() : false;
    }

    /**
     * Update department details.
     * 
     * @param int $id
     * @param string $name
     * @param string $code
     * @return bool
     */
    public function update($id, $name, $code) {
        $stmt = $this->db->prepare("UPDATE departments SET name = :name, code = :code WHERE id = :id");
        return $stmt->execute([
            ':id' => $id,
            ':name' => $name,
            ':code' => $code
        ]);
    }

    /**
     * Delete department if no active employees are assigned.
     * 
     * @param int $id
     * @return bool
     */
    public function delete($id) {
        // Prevent deletion if employees are currently assigned to this department
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM employees WHERE department_id = ?");
        $stmt->execute([$id]);
        if ((int)$stmt->fetchColumn() > 0) {
            return false;
        }
        $stmt = $this->db->prepare("DELETE FROM departments WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
