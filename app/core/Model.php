<?php
namespace App\Core;

abstract class Model {
    /**
     * @var \PDO
     */
    protected $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /**
     * Create an entry in the system audit logs.
     * 
     * @param int|null $employeeId Employee performing the action
     * @param string $action Action description (e.g., 'CREATE', 'UPDATE', 'DELETE')
     * @param string $tableName Associated table name
     * @param int|null $recordId Mutated record ID
     * @param string $details JSON or text detail block of changed fields
     * @return bool
     */
    public function logAction($employeeId, $action, $tableName, $recordId, $details) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO activity_logs (employee_id, action, table_name, record_id, details)
                VALUES (:employee_id, :action, :table_name, :record_id, :details)
            ");
            return $stmt->execute([
                ':employee_id' => $employeeId,
                ':action' => $action,
                ':table_name' => $tableName,
                ':record_id' => $recordId,
                ':details' => $details
            ]);
        } catch (\PDOException $e) {
            error_log("Failed to write activity log: " . $e->getMessage());
            return false;
        }
    }
}
