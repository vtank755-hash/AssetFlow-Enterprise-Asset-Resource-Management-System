<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\Notification;

/**
 * Notification Controller
 * Manages user alert notifications list, read flags updates, and bulk clear commands.
 */
class NotificationController extends Controller {
    private $notifModel;

    public function __construct() {
        $this->notifModel = new Notification();
    }

    /**
     * Display all notifications for the active user.
     * 
     * @return void
     */
    public function index() {
        $this->checkAccess();

        $userId = Session::getUserId();
        $notifications = $this->notifModel->getAllForEmployee($userId);

        $this->view('notifications/index', [
            'title' => 'Notifications Centre',
            'notifications' => $notifications
        ]);
    }

    /**
     * Mark single notification as read.
     * 
     * @return void
     */
    public function read() {
        $this->checkAccess();
        $id = $_GET['id'] ?? null;
        if ($id) {
            $userId = Session::getUserId();
            $this->notifModel->markAsRead($id, $userId);
        }
        $this->redirect('/notifications');
    }

    /**
     * Mark all notifications as read for current user.
     * 
     * @return void
     */
    public function readAll() {
        $this->checkAccess();
        $userId = Session::getUserId();
        $this->notifModel->markAllAsRead($userId);

        Session::setFlash('success', 'All notifications marked as read.');
        $this->redirect('/notifications');
    }
}
