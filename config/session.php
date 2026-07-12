<?php
/**
 * Secure Session Configuration Settings
 */

define('SESSION_LIFETIME', 3600); // 1 hour duration

// Apply secure session cookie configuration rules
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
    
    // Auto-detect secure HTTPS context to enable Secure flag
    $isSecure = false;
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        $isSecure = true;
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
        $isSecure = true;
    }
    
    if ($isSecure) {
        ini_set('session.cookie_secure', 1);
    }
}
