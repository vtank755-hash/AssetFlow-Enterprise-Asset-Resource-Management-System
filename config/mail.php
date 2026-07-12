<?php
/**
 * SMTP Mail Server Configuration Settings
 */

// SMTP server parameters (supports environment configuration with local defaults)
define('SMTP_HOST', getenv('SMTP_HOST') ?: 'sandbox.smtp.mailtrap.io');
define('SMTP_PORT', getenv('SMTP_PORT') ?: 2525);
define('SMTP_USER', getenv('SMTP_USER') ?: 'test_user');
define('SMTP_PASS', getenv('SMTP_PASS') ?: 'test_pass');
define('SMTP_SECURE', getenv('SMTP_SECURE') ?: 'tls');
define('SMTP_FROM', getenv('SMTP_FROM') ?: 'noreply@assetflow.com');
define('SMTP_FROM_NAME', getenv('SMTP_FROM_NAME') ?: 'AssetFlow Notification');
