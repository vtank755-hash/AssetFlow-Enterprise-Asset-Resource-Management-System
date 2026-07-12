<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\User;
use App\Core\Mailer;

class AuthController extends Controller {
    private $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    /**
     * Handles sign-in process
     */
    public function login() {
        if (Session::isAuthenticated()) {
            $this->redirect('/dashboard');
        }

        $error = '';
        $email = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            if (empty($email) || empty($password)) {
                $error = 'Please fill in all fields.';
            } else {
                $user = $this->userModel->getByEmail($email);
                
                if ($user && $user['status'] === 'Active' && password_verify($password, $user['password_hash'])) {
                    // Populate Session
                    Session::set('user_id', $user['id']);
                    Session::set('user_name', $user['name']);
                    Session::set('user_email', $user['email']);
                    Session::set('role', $user['role']);
                    
                    // Log Audit Trail
                    $this->userModel->logAction($user['id'], 'LOGIN', 'users', $user['id'], 'User logged in successfully.');
                    
                    Session::setFlash('success', "Welcome back, {$user['name']}!");
                    $this->redirect('/dashboard');
                } else {
                    $error = 'Invalid email or password, or account is deactivated.';
                }
            }
        }

        $this->view('auth/login', [
            'no_layout' => true,
            'error' => $error,
            'email' => $email
        ]);
    }

    /**
     * Handles logout
     */
    public function logout() {
        $userId = Session::getUserId();
        if ($userId) {
            $this->userModel->logAction($userId, 'LOGOUT', 'users', $userId, 'User logged out.');
        }
        Session::destroy();
        
        // Restart session to store flash
        Session::init();
        Session::setFlash('success', 'You have been logged out successfully.');
        $this->redirect('/auth/login');
    }

    /**
     * Handles password recovery request
     */
    public function forgotPassword() {
        if (Session::isAuthenticated()) {
            $this->redirect('/dashboard');
        }

        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();
            $email = trim($_POST['email'] ?? '');

            if (empty($email)) {
                $error = 'Email address is required.';
            } else {
                $user = $this->userModel->getByEmail($email);
                
                if ($user) {
                    // Generate reset token
                    $token = bin2hex(random_bytes(16));
                    
                    // Store token in session (valid for 15 minutes)
                    Session::init();
                    $tokens = Session::get('reset_tokens', []);
                    $tokens[$token] = [
                        'email' => $email,
                        'expires' => time() + 900
                    ];
                    Session::set('reset_tokens', $tokens);

                    // Build Reset Link
                    $resetLink = 'http://' . $_SERVER['HTTP_HOST'] . BASE_URL . '/auth/reset-password?token=' . $token;

                    // Email body
                    $subject = 'Password Reset Request - ' . APP_NAME;
                    $body = "
                        <h2>Password Reset Request</h2>
                        <p>Hi {$user['name']},</p>
                        <p>You requested a password reset for your account on AssetFlow.</p>
                        <p>Click the link below to reset your password. This link is valid for 15 minutes:</p>
                        <p><a href='{$resetLink}' style='background:#6366f1;color:#fff;padding:10px 20px;text-decoration:none;border-radius:5px;display:inline-block;'>Reset Password</a></p>
                        <p>Or copy and paste this URL into your browser:</p>
                        <p>{$resetLink}</p>
                    ";

                    // Send email
                    $mailSent = Mailer::send($email, $subject, $body);

                    if ($mailSent) {
                        Session::setFlash('success', 'A password recovery link has been sent to your email.');
                    } else {
                        // Dev assistance: if SMTP failed, output link as flash message for easy sandbox testing!
                        Session::setFlash('success', 'Recovery simulated! Click here to reset: <a href="' . $resetLink . '">Reset Password Link</a>');
                    }
                    $this->redirect('/auth/login');
                } else {
                    $error = 'No account was found with that email address.';
                }
            }
        }

        $this->view('auth/forgot-password', [
            'no_layout' => true,
            'error' => $error
        ]);
    }

    /**
     * Handles password reset processing
     */
    public function resetPassword() {
        if (Session::isAuthenticated()) {
            $this->redirect('/dashboard');
        }

        Session::init();
        $tokens = Session::get('reset_tokens', []);
        $error = '';
        $token = $_GET['token'] ?? $_POST['token'] ?? '';

        // Validate Token
        if (empty($token) || !isset($tokens[$token])) {
            Session::setFlash('danger', 'Invalid or expired password reset token.');
            $this->redirect('/auth/login');
        }

        $tokenData = $tokens[$token];
        if (time() > $tokenData['expires']) {
            unset($tokens[$token]);
            Session::set('reset_tokens', $tokens);
            Session::setFlash('danger', 'Password reset token has expired.');
            $this->redirect('/auth/login');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->validateCSRF();
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            if (strlen($password) < 8) {
                $error = 'Password must be at least 8 characters long.';
            } elseif ($password !== $confirmPassword) {
                $error = 'Passwords do not match.';
            } else {
                $user = $this->userModel->getByEmail($tokenData['email']);
                if ($user) {
                    $newHash = password_hash($password, PASSWORD_DEFAULT);
                    $this->userModel->updatePassword($user['id'], $newHash);

                    // Clear reset token
                    unset($tokens[$token]);
                    Session::set('reset_tokens', $tokens);

                    // Log audit trail
                    $this->userModel->logAction($user['id'], 'PASSWORD_RESET', 'users', $user['id'], 'User reset their password.');

                    Session::setFlash('success', 'Your password has been reset successfully. You can now log in.');
                    $this->redirect('/auth/login');
                } else {
                    $error = 'User account not found.';
                }
            }
        }

        $this->view('auth/reset-password', [
            'no_layout' => true,
            'error' => $error,
            'token' => $token
        ]);
    }
}
