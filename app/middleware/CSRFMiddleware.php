<?php
namespace App\Middleware;

use App\Core\Session;

/**
 * CSRF Protection Middleware
 * Verifies CSRF token matching for POST requests.
 */
class CSRFMiddleware {
    /**
     * Handle the incoming request.
     * Rejects request with security error if CSRF check fails.
     * 
     * @return void
     */
    public static function handle() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Session::init();
            $token = $_POST['csrf_token'] ?? '';
            
            if (!Session::verifyCSRFToken($token)) {
                header($_SERVER["SERVER_PROTOCOL"] . " 400 Bad Request");
                die("Security Error: CSRF token mismatch or expired. Please submit the form again.");
            }
        }
    }
}
