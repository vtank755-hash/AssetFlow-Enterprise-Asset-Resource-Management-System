<?php
namespace App\Core;

abstract class Controller {
    /**
     * Render a view file with data extracted to local scope variables.
     *
     * @param string $view View template path relative to app/views/ (e.g. 'auth/login')
     * @param array $data Data array to expose to the view
     * @return void
     */
    protected function view($view, $data = []) {
        View::render($view, $data);
    }

    /**
     * Redirect request to path within application domain.
     *
     * @param string $path Target path starting with a forward slash (e.g. '/dashboard')
     * @return void
     */
    protected function redirect($path) {
        header("Location: " . BASE_URL . $path);
        exit;
    }

    /**
     * Restrict page access based on roles. Redirects if unauthorized.
     *
     * @param array $allowedRoles Array of allowed role values ('Admin', 'Manager', 'Staff')
     * @return void
     */
    protected function checkAccess(array $allowedRoles = []) {
        Session::init();
        
        if (!Session::isAuthenticated()) {
            $this->redirect('/auth/login');
        }

        if (!empty($allowedRoles) && !in_array(Session::getRole(), $allowedRoles)) {
            $this->redirect('/errors/403');
        }
    }

    /**
     * Validate CSRF tokens for POST requests.
     *
     * @return void
     */
    protected function validateCSRF() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST['csrf_token'] ?? '';
            if (!Session::verifyCSRFToken($token)) {
                die("Security Error: CSRF token mismatch or expired. Please submit the form again.");
            }
        }
    }
}
