<?php
/**
 * Global Helper Functions
 */

use App\Core\Session;

if (!function_exists('escape')) {
    /**
     * Escape HTML special characters for safe output.
     * 
     * @param string $string
     * @return string
     */
    function escape($string) {
        return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('url')) {
    /**
     * Generate an absolute URL within the application context.
     * 
     * @param string $path
     * @return string
     */
    function url($path) {
        return BASE_URL . '/' . ltrim($path, '/');
    }
}

if (!function_exists('csrf_token')) {
    /**
     * Retrieve or generate the current session CSRF token.
     * 
     * @return string
     */
    function csrf_token() {
        return Session::generateCSRFToken();
    }
}

if (!function_exists('csrf_field')) {
    /**
     * Render the hidden input tag containing the CSRF token.
     * 
     * @return string
     */
    function csrf_field() {
        $token = csrf_token();
        return '<input type="hidden" name="csrf_token" value="' . $token . '">';
    }
}

if (!function_exists('formatCurrency')) {
    /**
     * Format a decimal amount as Indian Rupees (₹).
     * 
     * @param float|int $amount
     * @return string
     */
    function formatCurrency($amount) {
        return '₹' . number_format((float)$amount, 2);
    }
}

if (!function_exists('flash')) {
    /**
     * Render a dismissible flash alert banner if message exists.
     * 
     * @param string $type ('success', 'danger', 'info', 'warning')
     * @return string
     */
    function flash($type) {
        if (Session::hasFlash($type)) {
            $msg = Session::getFlash($type);
            $icon = 'bi-info-circle-fill';
            if ($type === 'success') $icon = 'bi-check-circle-fill';
            if ($type === 'danger') $icon = 'bi-exclamation-triangle-fill';
            if ($type === 'warning') $icon = 'bi-exclamation-circle-fill';
            
            return '
            <div class="alert alert-' . $type . ' alert-dismissible fade show border-0 shadow-sm" role="alert">
                <i class="bi ' . $icon . ' me-2"></i>
                ' . escape($msg) . '
                <a href="?" class="btn-close" aria-label="Close" style="text-decoration:none;"></a>
            </div>';
        }
        return '';
    }
}
