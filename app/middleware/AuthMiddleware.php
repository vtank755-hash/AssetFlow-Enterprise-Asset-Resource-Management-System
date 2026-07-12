<?php
namespace App\Middleware;

use App\Core\Session;

/**
 * Authentication Middleware
 * Enforces authentication validation.
 */
class AuthMiddleware {
    /**
     * Handle the incoming request.
     * Redirects to login page if user is not authenticated.
     * 
     * @return void
     */
    public static function handle() {
        Session::init();
        if (!Session::isAuthenticated()) {
            header("Location: " . BASE_URL . "/auth/login");
            exit;
        }
    }
}
