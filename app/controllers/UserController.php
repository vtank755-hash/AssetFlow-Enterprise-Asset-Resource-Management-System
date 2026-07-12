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
     * Manage logged-in user profile details (All Roles)
     */
    public function profile() {
        $this->checkAccess();
        $id = Session::getUserId();
        
        $user = $this->userModel->getById($id);
        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            if (empty($name) || empty($email)) {
                $error = 'Name and email are required fields.';
            } else {
                $duplicate = $this->userModel->getByEmail($email);
                if ($duplicate && (int)$duplicate['id'] !== (int)$id) {
                    $error = 'The email address is already in use by another user.';
                } else {
                    // Update profile details (role and status cannot be updated from user side)
                    $success = $this->userModel->update($id, $name, $email, $user['role'], $user['status']);
                    
                    if (!empty($password)) {
                        if (strlen($password) < 8) {
                            $error = 'New password must be at least 8 characters long.';
                        } elseif ($password !== $confirmPassword) {
                            $error = 'Password confirmation does not match.';
                        } else {
                            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                            $this->userModel->updatePassword($id, $passwordHash);
                        }
                    }

                    if ($success && empty($error)) {
                        Session::set('user_name', $name);
                        Session::set('user_email', $email);
                        $this->userModel->logAction($id, 'UPDATE_PROFILE', 'users', $id, "User updated their own profile.");
                        Session::setFlash('success', 'Your profile has been updated successfully.');
                        $this->redirect('/profile');
                    } elseif (empty($error)) {
                        $error = 'Failed to update profile details.';
                    }
                }
            }
        }

        $this->view('users/edit', [
            'title' => 'My Account Profile',
            'user' => $user,
            'error' => $error,
            'is_profile' => true
        ]);
    }
}
