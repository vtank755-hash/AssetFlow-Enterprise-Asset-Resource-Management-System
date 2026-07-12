<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\User;

class UserController extends Controller {
    private $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    /**
     * Lists all users (Admin Only)
     */
    public function index() {
        $this->checkAccess(['Admin']);
        $users = $this->userModel->getAll();
        
        $this->view('users/index', [
            'title' => 'User Management',
            'users' => $users
        ]);
    }

    /**
     * Creates a new user (Admin Only)
     */
    public function create() {
        $this->checkAccess(['Admin']);
        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $role = $_POST['role'] ?? 'Staff';
            $status = $_POST['status'] ?? 'Active';

            if (empty($name) || empty($email) || empty($password)) {
                $error = 'Name, email, and password are required fields.';
            } elseif ($this->userModel->getByEmail($email)) {
                $error = 'The email address is already in use.';
            } else {
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                $newId = $this->userModel->create($name, $email, $passwordHash, $role, $status);
                
                if ($newId) {
                    $adminId = Session::getUserId();
                    $this->userModel->logAction($adminId, 'CREATE_USER', 'users', $newId, "Created user: $name <$email>, Role: $role");

                    // Trigger Employee Registration Notification
                    $db = \App\Core\Database::getConnection();
                    $stmtNotif = $db->prepare("INSERT INTO notifications (employee_id, title, message) VALUES (?, 'Employee Registration', ?)");
                    $stmtNotif->execute([$newId, "Welcome to AssetFlow! Your employee account has been created successfully."]);

                    Session::setFlash('success', "User account for '{$name}' created successfully.");
                    $this->redirect('/users');
                } else {
                    $error = 'System error occurred while creating user. Please try again.';
                }
            }
        }

        $this->view('users/create', [
            'title' => 'Create New User',
            'error' => $error
        ]);
    }

    /**
     * Edits an existing user (Admin Only)
     */
    public function edit() {
        $this->checkAccess(['Admin']);
        $id = $_GET['id'] ?? null;
        
        if (!$id) {
            $this->redirect('/users');
        }

        $user = $this->userModel->getById($id);
        if (!$user) {
            Session::setFlash('danger', 'The requested user could not be found.');
            $this->redirect('/users');
        }

        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $role = $_POST['role'] ?? 'Staff';
            $status = $_POST['status'] ?? 'Active';
            $password = $_POST['password'] ?? '';

            if (empty($name) || empty($email)) {
                $error = 'Name and email are required fields.';
            } else {
                $duplicate = $this->userModel->getByEmail($email);
                if ($duplicate && (int)$duplicate['id'] !== (int)$id) {
                    $error = 'The email address is already in use by another user.';
                } else {
                    $success = $this->userModel->update($id, $name, $email, $role, $status);
                    
                    // Update password if a new one is provided
                    if (!empty($password)) {
                        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                        $this->userModel->updatePassword($id, $passwordHash);
                    }

                    if ($success) {
                        $adminId = Session::getUserId();
                        $this->userModel->logAction($adminId, 'UPDATE_USER', 'users', $id, "Updated user: $name <$email>, Role: $role, Status: $status");
                        Session::setFlash('success', "User account for '{$name}' updated successfully.");
                        $this->redirect('/users');
                    } else {
                        $error = 'Failed to update user details.';
                    }
                }
            }
        }

        $this->view('users/edit', [
            'title' => 'Edit User - ' . $user['name'],
            'user' => $user,
            'error' => $error,
            'is_profile' => false
        ]);
    }

    /**
     * Manage logged-in user profile details, password changes, avatars, and preferences (All Roles)
     */
    public function profile() {
        $this->checkAccess();
        $id = Session::getUserId();
        
        $user = $this->userModel->getById($id);
        $error = '';
        $successMsg = '';

        // Decode preferences
        $preferences = json_decode($user['preferences'] ?? '', true) ?: [
            'theme' => 'light',
            'email_alerts' => 'yes'
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();
            $action = $_POST['action'] ?? '';

            if ($action === 'update_profile') {
                $name = trim($_POST['name'] ?? '');
                $email = trim($_POST['email'] ?? '');

                if (empty($name) || empty($email)) {
                    $error = 'Name and email are required fields.';
                } else {
                    $duplicate = $this->userModel->getByEmail($email);
                    if ($duplicate && (int)$duplicate['id'] !== (int)$id) {
                        $error = 'The email address is already in use by another account.';
                    } else {
                        if ($this->userModel->update($id, $name, $email, $user['role'], $user['status'])) {
                            Session::set('user_name', $name);
                            Session::set('user_email', $email);
                            $user['name'] = $name;
                            $user['email'] = $email;
                            $successMsg = 'Profile details updated successfully.';
                            $this->userModel->logAction($id, 'UPDATE_PROFILE', 'employees', $id, "Updated profile details.");
                        } else {
                            $error = 'Failed to update profile details.';
                        }
                    }
                }
            } elseif ($action === 'change_password') {
                $currentPassword = $_POST['current_password'] ?? '';
                $newPassword = $_POST['new_password'] ?? '';
                $confirmPassword = $_POST['confirm_password'] ?? '';

                if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
                    $error = 'All password fields are required.';
                } elseif (!password_verify($currentPassword, $user['password_hash'])) {
                    $error = 'Your current password is incorrect.';
                } elseif (strlen($newPassword) < 8) {
                    $error = 'New password must be at least 8 characters long.';
                } elseif ($newPassword !== $confirmPassword) {
                    $error = 'New password and confirmation do not match.';
                } else {
                    $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
                    if ($this->userModel->updatePassword($id, $passwordHash)) {
                        $successMsg = 'Password changed successfully.';
                        $this->userModel->logAction($id, 'CHANGE_PASSWORD', 'employees', $id, "Changed account password.");
                        
                        $db = \App\Core\Database::getConnection();
                        $stmtNotif = $db->prepare("INSERT INTO notifications (employee_id, title, message) VALUES (?, 'Password Reset', 'Your account password has been changed successfully.')");
                        $stmtNotif->execute([$id]);
                    } else {
                        $error = 'Failed to update account password.';
                    }
                }
            } elseif ($action === 'upload_avatar') {
                if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
                    $fileTmpPath = $_FILES['profile_pic']['tmp_name'];
                    $fileName = $_FILES['profile_pic']['name'];
                    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
                    
                    if (in_array($fileExtension, $allowedExtensions)) {
                        // Create upload dir if not exists
                        $uploadDir = dirname(dirname(__DIR__)) . '/public/uploads/profile_pics/';
                        if (!is_dir($uploadDir)) {
                            mkdir($uploadDir, 0755, true);
                        }

                        $newFileName = 'avatar_' . $id . '_' . time() . '.' . $fileExtension;
                        $destPath = $uploadDir . $newFileName;

                        if (move_uploaded_file($fileTmpPath, $destPath)) {
                            // Delete old picture if exists
                            if ($user['profile_picture'] && file_exists($uploadDir . $user['profile_picture'])) {
                                @unlink($uploadDir . $user['profile_picture']);
                            }
                            
                            $this->userModel->updateProfilePicture($id, $newFileName);
                            $user['profile_picture'] = $newFileName;
                            Session::set('user_avatar', $newFileName);
                            $successMsg = 'Profile picture updated successfully.';
                            $this->userModel->logAction($id, 'UPLOAD_AVATAR', 'employees', $id, "Uploaded profile avatar.");
                        } else {
                            $error = 'Failed to save uploaded image file.';
                        }
                    } else {
                        $error = 'Invalid image file format. Allowed extensions: JPG, JPEG, PNG, GIF.';
                    }
                } else {
                    $error = 'No file uploaded or error during upload.';
                }
            } elseif ($action === 'update_preferences') {
                $theme = $_POST['theme'] ?? 'light';
                $emailAlerts = $_POST['email_alerts'] ?? 'no';

                $preferences = [
                    'theme' => $theme,
                    'email_alerts' => $emailAlerts
                ];

                $prefJson = json_encode($preferences);
                if ($this->userModel->updatePreferences($id, $prefJson)) {
                    Session::set('user_theme', $theme);
                    $successMsg = 'Preferences saved successfully.';
                    $this->userModel->logAction($id, 'UPDATE_PREFERENCES', 'employees', $id, "Updated preferences.");
                } else {
                    $error = 'Failed to save preferences settings.';
                }
            }
        }

        $this->view('users/profile', [
            'title' => 'My Account Settings',
            'user' => $user,
            'preferences' => $preferences,
            'error' => $error,
            'success' => $successMsg
        ]);
    }
}
