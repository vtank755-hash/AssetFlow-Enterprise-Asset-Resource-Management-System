<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\Booking;

/**
 * Booking Controller
 * Manages resource reservation requests, calendar table generation, and conflicts checking.
 */
class BookingController extends Controller {
    private $bookingModel;

    public function __construct() {
        $this->bookingModel = new Booking();
    }

    /**
     * Display bookings calendar and directory list.
     * 
     * @return void
     */
    public function index() {
        $this->checkAccess();

        $role = Session::getRole();
        $userId = Session::getUserId();

        // Staff members are restricted to viewing/managing only their bookings
        $filterUserId = ($role === 'Staff') ? $userId : null;
        $bookings = $this->bookingModel->getAll($filterUserId);

        // Process status checks dynamically based on system time
        $now = date('Y-m-d H:i:s');
        $db = \App\Core\Database::getConnection();
        
        foreach ($bookings as &$book) {
            if ($book['status'] !== 'Cancelled') {
                if ($book['end_time'] < $now) {
                    $newStatus = 'Completed';
                } elseif ($book['start_time'] <= $now && $book['end_time'] >= $now) {
                    $newStatus = 'Ongoing';
                } else {
                    $newStatus = 'Upcoming';
                }

                if ($book['status'] !== $newStatus) {
                    $book['status'] = $newStatus;
                    $stmt = $db->prepare("UPDATE resource_bookings SET status = ? WHERE id = ?");
                    $stmt->execute([$newStatus, $book['id']]);
                }
            }
        }

        // Calendar Parameters
        $month = isset($_GET['month']) ? max(1, min(12, (int)$_GET['month'])) : (int)date('m');
        $year = isset($_GET['year']) ? max(2020, min(2030, (int)$_GET['year'])) : (int)date('Y');

        $this->view('bookings/index', [
            'title' => 'Resource Bookings Calendar',
            'bookings' => $bookings,
            'role' => $role,
            'month' => $month,
            'year' => $year
        ]);
    }

    /**
     * Create booking reservation.
     * 
     * @return void
     */
    public function create() {
        $this->checkAccess();
        $error = '';
        
        $assets = $this->bookingModel->getBookableAssets();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();
            $assetId = $_POST['asset_id'] ?? '';
            $startTime = $_POST['start_time'] ?? '';
            $endTime = $_POST['end_time'] ?? '';
            $purpose = trim($_POST['purpose'] ?? '');

            if (empty($assets)) {
                $error = 'No active rooms, vehicles, or equipment are available. Please contact the Asset Manager or create a resource before making a booking.';
            } elseif (empty($assetId) || empty($startTime) || empty($endTime) || empty($purpose)) {
                $error = 'All fields are required.';
            } elseif ($startTime >= $endTime) {
                $error = 'End time must be after start time.';
            } elseif (strtotime($startTime) < time() - 300) { // Allow 5 minutes clock drift
                $error = 'Cannot schedule reservations in the past.';
            } else {
                $employeeId = Session::getUserId();
                try {
                    $newId = $this->bookingModel->create($assetId, $employeeId, $startTime, $endTime, $purpose);
                    if ($newId) {
                        Session::setFlash('success', 'Resource reserved successfully.');
                        $this->redirect('/bookings');
                    } else {
                        $error = 'Failed to record reservation details.';
                    }
                } catch (\Exception $e) {
                    $error = $e->getMessage();
                }
            }
        }

        $this->view('bookings/create', [
            'title' => 'Reserve Resource',
            'assets' => $assets,
            'error' => $error
        ]);
    }

    /**
     * Cancel reservation.
     * 
     * @return void
     */
    public function cancel() {
        $this->checkAccess();
        $id = $_GET['id'] ?? null;
        if (!$id) {
            $this->redirect('/bookings');
        }

        $role = Session::getRole();
        $userId = Session::getUserId();

        // Staff can only cancel their own bookings, Admin/Manager can cancel any
        $filterUserId = ($role === 'Staff') ? $userId : null;

        if ($this->bookingModel->cancel($id, $filterUserId)) {
            Session::setFlash('success', 'Booking cancelled successfully.');
        } else {
            Session::setFlash('danger', 'Failed to cancel booking reservation.');
        }

        $this->redirect('/bookings');
    }
}
