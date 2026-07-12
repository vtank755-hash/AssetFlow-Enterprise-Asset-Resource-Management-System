<?php
namespace App\Models;

use App\Core\Model;

/**
 * Notification Model
 * Manages user alert notifications, unread counts, and creation hooks.
 */
class Notification extends Model {
    /**
     * Get all notifications for an employee.
     */
    public function getAllForEmployee($employeeId) {
        $stmt = $this->db->prepare("
            SELECT * FROM notifications 
            WHERE employee_id = ? 
            ORDER BY is_read ASC, created_at DESC
        ");
        $stmt->execute([$employeeId]);
        return $stmt->fetchAll();
    }

    /**
     * Get unread notifications count for an employee.
     */
    public function getUnreadCount($employeeId) {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM notifications 
            WHERE employee_id = ? AND is_read = 0
        ");
        $stmt->execute([$employeeId]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Create a notification.
     */
    public function create($employeeId, $title, $message) {
        $stmt = $this->db->prepare("
            INSERT INTO notifications (employee_id, title, message, is_read)
            VALUES (?, ?, ?, 0)
        ");
        return $stmt->execute([$employeeId, $title, $message]);
    }

    /**
     * Mark single notification as read.
     */
    public function markAsRead($id, $employeeId) {
        $stmt = $this->db->prepare("
            UPDATE notifications 
            SET is_read = 1 
            WHERE id = ? AND employee_id = ?
        ");
        return $stmt->execute([$id, $employeeId]);
    }

    /**
     * Mark all notifications as read for an employee.
     */
    public function markAllAsRead($employeeId) {
        $stmt = $this->db->prepare("
            UPDATE notifications 
            SET is_read = 1 
            WHERE employee_id = ? AND is_read = 0
        ");
        return $stmt->execute([$employeeId]);
    }
}
