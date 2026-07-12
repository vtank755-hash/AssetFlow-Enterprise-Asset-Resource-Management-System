<?php
namespace App\Models;

use App\Core\Model;

class User extends Model {
    /**
     * Fetch user record by email address.
     * 
     * @param string $email
     * @return array|false
     */
    public function getByEmail($email) {
        $stmt = $this->db->prepare("
            SELECT e.*, r.name as role 
            FROM employees e 
            JOIN roles r ON e.role_id = r.id 
            WHERE e.email = :email 
            LIMIT 1
        ");
        $stmt->execute([':email' => $email]);
        return $stmt->fetch();
    }

    /**
     * Fetch user record by ID.
     * 
     * @param int $id
     * @return array|false
     */
    public function getById($id) {
        $stmt = $this->db->prepare("
            SELECT e.*, r.name as role 
            FROM employees e 
            JOIN roles r ON e.role_id = r.id 
            WHERE e.id = :id 
            LIMIT 1
        ");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Fetch all user records.
     * 
     * @return array
     */
    public function getAll() {
        $stmt = $this->db->prepare("
            SELECT e.*, r.name as role 
            FROM employees e 
            JOIN roles r ON e.role_id = r.id 
            ORDER BY e.name ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Add new user record.
     * 
     * @param string $name
     * @param string $email
     * @param string $passwordHash
     * @param string $role
     * @param string $status
     * @return int|false Last inserted ID or false on failure
     */
    public function create($name, $email, $passwordHash, $role, $status) {
        // Resolve Role ID from string name
        $roleStmt = $this->db->prepare("SELECT id FROM roles WHERE name = ?");
        $roleStmt->execute([$role]);
        $roleId = $roleStmt->fetchColumn() ?: 3; // Default to Staff role

        $stmt = $this->db->prepare("
            INSERT INTO employees (name, email, password_hash, role_id, status)
            VALUES (:name, :email, :password_hash, :role_id, :status)
        ");
        $success = $stmt->execute([
            ':name' => $name,
            ':email' => $email,
            ':password_hash' => $passwordHash,
            ':role_id' => $roleId,
            ':status' => $status
        ]);
        return $success ? $this->db->lastInsertId() : false;
    }

    /**
     * Update user details.
     * 
     * @param int $id
     * @param string $name
     * @param string $email
     * @param string $role
     * @param string $status
     * @return bool
     */
    public function update($id, $name, $email, $role, $status) {
        // Resolve Role ID from string name
        $roleStmt = $this->db->prepare("SELECT id FROM roles WHERE name = ?");
        $roleStmt->execute([$role]);
        $roleId = $roleStmt->fetchColumn() ?: 3;

        $stmt = $this->db->prepare("
            UPDATE employees 
            SET name = :name, email = :email, role_id = :role_id, status = :status 
            WHERE id = :id
        ");
        return $stmt->execute([
            ':id' => $id,
            ':name' => $name,
            ':email' => $email,
            ':role_id' => $roleId,
            ':status' => $status
        ]);
    }

    /**
     * Update user password.
     * 
     * @param int $id
     * @param string $passwordHash
     * @return bool
     */
    public function updatePassword($id, $passwordHash) {
        $stmt = $this->db->prepare("
            UPDATE employees 
            SET password_hash = :password_hash 
            WHERE id = :id
        ");
        return $stmt->execute([
            ':id' => $id,
            ':password_hash' => $passwordHash
        ]);
    }
}
