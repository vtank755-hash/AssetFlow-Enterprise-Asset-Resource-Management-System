<?php
namespace App\Models;

use App\Core\Model;
use Exception;

/**
 * Booking Model
 * Manages resource bookings (Rooms, Vehicles, Equipment), validation checks, and overlap detections.
 */
class Booking extends Model {
    /**
     * Get all bookings.
     * 
     * @param int|null $employeeId Filter by specific custodian (for Staff role)
     * @return array
     */
    public function getAll($employeeId = null) {
        $sql = "
            SELECT rb.*, a.name as asset_name, a.asset_tag, c.name as category_name, e.name as employee_name
            FROM resource_bookings rb
            JOIN assets a ON rb.asset_id = a.id
            JOIN asset_categories c ON a.category_id = c.id
            JOIN employees e ON rb.employee_id = e.id
        ";
        $params = [];

        if ($employeeId !== null) {
            $sql .= " WHERE rb.employee_id = :employee_id";
            $params[':employee_id'] = (int)$employeeId;
        }

        $sql .= " ORDER BY rb.start_time DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Check if a time slot overlaps with an existing booking for a given asset.
     * 
     * @param int $assetId
     * @param string $startTime Y-m-d H:i:s
     * @param string $endTime Y-m-d H:i:s
     * @param int|null $excludeBookingId Exclude specific booking ID (used during edits)
     * @return bool True if overlap exists, false otherwise
     */
    public function hasOverlap($assetId, $startTime, $endTime, $excludeBookingId = null) {
        $sql = "
            SELECT COUNT(*) 
            FROM resource_bookings 
            WHERE asset_id = :asset_id 
              AND status != 'Cancelled'
              AND (
                (start_time < :end_time AND end_time > :start_time)
              )
        ";
        $params = [
            ':asset_id' => $assetId,
            ':start_time' => $startTime,
            ':end_time' => $endTime
        ];

        if ($excludeBookingId !== null) {
            $sql .= " AND id != :exclude_id";
            $params[':exclude_id'] = (int)$excludeBookingId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn() > 0;
    }

    /**
     * Create a new booking with overlap prevention.
     */
    public function create($assetId, $employeeId, $startTime, $endTime, $purpose) {
        if ($this->hasOverlap($assetId, $startTime, $endTime)) {
            throw new Exception("The selected asset is already booked during this time period.");
        }

        // Determine status based on dates
        $now = date('Y-m-d H:i:s');
        $status = 'Upcoming';
        if ($startTime <= $now && $endTime >= $now) {
            $status = 'Ongoing';
        } elseif ($endTime < $now) {
            $status = 'Completed';
        }

        $stmt = $this->db->prepare("
            INSERT INTO resource_bookings (asset_id, employee_id, start_time, end_time, purpose, status)
            VALUES (:asset_id, :employee_id, :start_time, :end_time, :purpose, :status)
        ");
        
        $success = $stmt->execute([
            ':asset_id' => $assetId,
            ':employee_id' => $employeeId,
            ':start_time' => $startTime,
            ':end_time' => $endTime,
            ':purpose' => $purpose,
            ':status' => $status
        ]);

        return $success ? $this->db->lastInsertId() : false;
    }

    /**
     * Cancel an active booking.
     */
    public function cancel($bookingId, $employeeId = null) {
        if ($employeeId !== null) {
            $stmt = $this->db->prepare("UPDATE resource_bookings SET status = 'Cancelled' WHERE id = ? AND employee_id = ?");
            return $stmt->execute([$bookingId, $employeeId]);
        } else {
            $stmt = $this->db->prepare("UPDATE resource_bookings SET status = 'Cancelled' WHERE id = ?");
            return $stmt->execute([$bookingId]);
        }
    }

    /**
     * Fetch assets that belong to Rooms, Vehicles, or Equipment categories.
     */
    public function getBookableAssets() {
        $stmt = $this->db->prepare("
            SELECT a.id, a.name, a.asset_tag, c.name as category_name 
            FROM assets a
            JOIN asset_categories c ON a.category_id = c.id
            WHERE c.name LIKE '%Room%' 
               OR c.name LIKE '%Vehicle%' 
               OR c.name LIKE '%Equipment%'
               OR c.name LIKE '%Car%'
               OR c.name LIKE '%Conference%'
            ORDER BY c.name ASC, a.name ASC
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
