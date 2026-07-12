<?php
namespace App\Middleware;

use App\Core\Session;

/**
 * Role-Based Access Control Middleware
 * Restricts route access to specified role groups.
 */
class RoleMiddleware {
    /**
     * Handle the incoming request.
     * Redirects to forbidden page if user does not possess correct permissions.
     * 
     * @param array $allowedRoles Array of allowed role strings
     * @return void
     */
    public static function handle(array $allowedRoles = []) {
        Session::init();
        
        // Ensure user is authenticated first
        AuthMiddleware::handle();

        $userRole = Session::getRole();
        if (!empty($allowedRoles) && !in_array($userRole, $allowedRoles)) {
            header("Location: " . BASE_URL . "/errors/403");
            exit;
        }
    }
}
