<?php
namespace App\Core;

class Session {
    /**
     * Start secure session if it hasn't been started yet
     * @return void
     */
    public static function init() {
        if (session_status() === PHP_SESSION_NONE) {
            // Set secure session cookie parameters
            ini_set('session.cookie_httponly', 1);
            ini_set('session.use_only_cookies', 1);
            
            // If running on HTTPS, set secure cookie flag
            $isSecure = false;
            if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
                $isSecure = true;
            } elseif (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
                $isSecure = true;
            }
            
            if ($isSecure) {
                ini_set('session.cookie_secure', 1);
            }
            
            session_start();
        }
    }

    /**
     * Set session key/value pair
     * @param string $key
     * @param mixed $value
     */
    public static function set($key, $value) {
        self::init();
        $_SESSION[$key] = $value;
    }

    /**
     * Retrieve session value by key
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function get($key, $default = null) {
        self::init();
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Delete session key
     * @param string $key
     */
    public static function delete($key) {
        self::init();
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }

    /**
     * Destroy current session
     */
    public static function destroy() {
        self::init();
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(), 
                '', 
                time() - 42000,
                $params["path"], 
                $params["domain"],
                $params["secure"], 
                $params["httponly"]
            );
        }
        session_destroy();
    }

    /**
     * Set a temporary flash message
     * @param string $type ('success', 'danger', 'info', 'warning')
     * @param string $message
     */
    public static function setFlash($type, $message) {
        self::init();
        $_SESSION['flash'][$type] = $message;
    }

    /**
     * Retrieve flash message and clear it from session
     * @param string $type
     * @return string|null
     */
    public static function getFlash($type) {
        self::init();
        if (isset($_SESSION['flash'][$type])) {
            $msg = $_SESSION['flash'][$type];
            unset($_SESSION['flash'][$type]);
            return $msg;
        }
        return null;
    }

    /**
     * Check if a specific type of flash message exists
     * @param string $type
     * @return bool
     */
    public static function hasFlash($type) {
        self::init();
        return isset($_SESSION['flash'][$type]);
    }

    /**
     * Returns true if user is logged in
     * @return bool
     */
    public static function isAuthenticated() {
        return self::get('user_id') !== null;
    }

    /**
     * Retrieve active user ID
     * @return int|null
     */
    public static function getUserId() {
        return self::get('user_id');
    }

    /**
     * Retrieve active user role
     * @return string|null
     */
    public static function getRole() {
        return self::get('role');
    }

    /**
     * Retrieve active user name
     * @return string|null
     */
    public static function getUserName() {
        return self::get('user_name');
    }

    /**
     * Generate or return current CSRF token
     * @return string
     */
    public static function generateCSRFToken() {
        self::init();
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    /**
     * Verify CSRF token correctness
     * @param string $token
     * @return bool
     */
    public static function verifyCSRFToken($token) {
        self::init();
        if (empty($_SESSION['csrf_token']) || empty($token)) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }
}
