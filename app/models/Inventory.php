<?php
namespace App\Models;

use App\Core\Model;
use App\Core\Mailer;

class Inventory extends Model {
    /**
     * Fetch all inventory items.
     */
    public function getAll() {
        $stmt = $this->db->prepare("SELECT * FROM inventory ORDER BY name ASC");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Fetch single item by ID.
     */
    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM inventory WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    /**
     * Create consumable stock parameter.
     */
    public function create($name, $sku, $quantity, $minThreshold, $unitPrice, $location) {
        $stmt = $this->db->prepare("
            INSERT INTO inventory (name, sku, quantity, min_threshold, unit_price, location)
            VALUES (:name, :sku, :quantity, :min_threshold, :unit_price, :location)
        ");
        $success = $stmt->execute([
            ':name' => $name,
            ':sku' => $sku,
            ':quantity' => (int)$quantity,
            ':min_threshold' => (int)$minThreshold,
            ':unit_price' => (float)$unitPrice,
            ':location' => $location
        ]);
        return $success ? $this->db->lastInsertId() : false;
    }

    /**
     * Update stock parameters.
     */
    public function update($id, $name, $sku, $minThreshold, $unitPrice, $location) {
        $stmt = $this->db->prepare("
            UPDATE inventory 
            SET name = :name, sku = :sku, min_threshold = :min_threshold, 
                unit_price = :unit_price, location = :location
            WHERE id = :id
        ");
        return $stmt->execute([
            ':name' => $name,
            ':sku' => $sku,
            ':min_threshold' => (int)$minThreshold,
            ':unit_price' => (float)$unitPrice,
            ':location' => $location,
            ':id' => $id
        ]);
    }

    /**
     * Adjust stock level directly (adding or consuming stock) and check thresholds.
     */
    public function adjustQuantity($id, $adjustment, $userId, $reason = '') {
        $stmt = $this->db->prepare("SELECT * FROM inventory WHERE id = ? FOR UPDATE");
        $stmt->execute([$id]);
        $item = $stmt->fetch();

        if (!$item) {
            return false;
        }

        $newQty = $item['quantity'] + (int)$adjustment;
        if ($newQty < 0) {
            return false; // Can't go below zero stock
        }

        // Update quantity
        $stmt = $this->db->prepare("UPDATE inventory SET quantity = ? WHERE id = ?");
        $success = $stmt->execute([$newQty, $id]);

        if ($success) {
            // Log to Audit Trail
            $actionText = ($adjustment >= 0) ? 'STOCK_ADD' : 'STOCK_CONSUME';
            $details = "Adjusted qty by {$adjustment} (Old Qty: {$item['quantity']}, New Qty: {$newQty}). Reason: {$reason}";
            $this->logAction($userId, $actionText, 'inventory', $id, $details);

            // Check if stock has dipped below threshold and trigger warning emails
            if ($newQty < $item['min_threshold'] && $item['quantity'] >= $item['min_threshold']) {
                $this->triggerLowStockNotification($item, $newQty);
            }
            return true;
        }
        return false;
    }

    /**
     * Send low stock email notices to administrative users.
     */
    private function triggerLowStockNotification($item, $currentQty) {
        // Fetch all Admins and Managers emails
        $stmt = $this->db->prepare("SELECT email, name FROM employees WHERE role_id IN (1, 2) AND status = 'Active'");
        $stmt->execute();
        $recipients = $stmt->fetchAll();

        $subject = "ALERT: Low Stock Notification for SKU " . $item['sku'];
        $body = "
            <h2>Inventory Stock Alert</h2>
            <p>The stock level for the following consumable item has dipped below its designated minimum threshold:</p>
            <table border='1' cellpadding='10' cellspacing='0' style='border-collapse:collapse; border-color:#eee;'>
                <tr><td><strong>Item Name:</strong></td><td>{$item['name']}</td></tr>
                <tr><td><strong>SKU:</strong></td><td>{$item['sku']}</td></tr>
                <tr><td><strong>Current Stock:</strong></td><td style='color:red; font-weight:bold;'>{$currentQty} items</td></tr>
                <tr><td><strong>Minimum Threshold:</strong></td><td>{$item['min_threshold']} items</td></tr>
                <tr><td><strong>Storage Location:</strong></td><td>{$item['location']}</td></tr>
            </table>
            <p>Please reorder stock as soon as possible.</p>
        ";

        foreach ($recipients as $recipient) {
            Mailer::send($recipient['email'], $subject, $body);
        }
    }
}
