<?php
/**
 * Global Application Constants
 */

// User Roles Constants
define('ROLE_ADMIN', 'Admin');
define('ROLE_MANAGER', 'Manager');
define('ROLE_STAFF', 'Staff');

// Asset Status Constants
define('STATUS_AVAILABLE', 'Available');
define('STATUS_ALLOCATED', 'Allocated');
define('STATUS_MAINTENANCE', 'Maintenance');
define('STATUS_DISPOSED', 'Disposed');

// Currency Configuration
define('CURRENCY_SYMBOL', '₹');

// Upload Parameters
define('MAX_UPLOAD_SIZE', 10485760); // 10MB in bytes
define('ALLOWED_DOC_EXTENSIONS', ['pdf', 'jpg', 'jpeg', 'png', 'doc', 'docx']);
define('UPLOAD_PATH', dirname(__DIR__) . '/public/uploads');
