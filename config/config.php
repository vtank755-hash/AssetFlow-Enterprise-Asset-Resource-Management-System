<?php
/**
 * Global Configuration Settings
 */

// Define Environment
define('ENVIRONMENT', 'development'); // Options: 'development', 'production'

// Error Handling Configuration based on Environment
if (ENVIRONMENT === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', dirname(__DIR__) . '/logs/app.log');
}

// Timezone Settings
date_default_timezone_set('Asia/Kolkata'); // Indian Standard Time (IST)

// Application settings
define('BASE_URL', '/AssetFlow-Enterprise-Asset-Resource-Management-System');
define('APP_NAME', 'AssetFlow');
define('APP_VERSION', '1.0.0');
