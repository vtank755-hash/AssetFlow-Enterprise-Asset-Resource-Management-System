<?php
/**
 * Global Configuration Settings
 */

// Application URL & Info
define('BASE_URL', '/AssetFlow-Enterprise-Asset-Resource-Management-System');
define('APP_NAME', 'AssetFlow');
define('APP_VERSION', '1.0.0');

// Session Settings
define('SESSION_LIFETIME', 3600); // 1 hour

// PHPMailer Settings (Mailtrap or local test SMTP configuration)
define('SMTP_HOST', 'sandbox.smtp.mailtrap.io');
define('SMTP_PORT', 2525);
define('SMTP_USER', 'test_user'); // To be customized by user if needed
define('SMTP_PASS', 'test_pass'); // To be customized by user if needed
define('SMTP_SECURE', 'tls');
define('SMTP_FROM', 'noreply@assetflow.com');
define('SMTP_FROM_NAME', 'AssetFlow Notification');
