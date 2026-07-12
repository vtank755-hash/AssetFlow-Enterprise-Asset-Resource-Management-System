<?php
/**
 * AssetFlow Front Controller and Entrypoint
 */

// Load configurations
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/mail.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/autoload.php';
require_once __DIR__ . '/../includes/helpers.php';

// Load Composer Autoloader
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

use App\Core\Router;
use App\Core\Session;

// Initialize Session
Session::init();

// Initialize Router
$router = new Router();

// Register Authentication Routes
$router->add('auth/login',           'AuthController@login');
$router->add('auth/register',        'AuthController@register');
$router->add('auth/logout',          'AuthController@logout');
$router->add('auth/forgot-password', 'AuthController@forgotPassword');
$router->add('auth/reset-password',  'AuthController@resetPassword');

// Register Dashboard & Error Routes
$router->add('dashboard',            'DashboardController@index');
$router->add('errors/403',           'DashboardController@forbidden');

// Register Asset Management Routes
$router->add('assets',               'AssetController@index');
$router->add('assets/create',        'AssetController@create');
$router->add('assets/edit',          'AssetController@edit');
$router->add('assets/view',          'AssetController@show');
$router->add('assets/delete',        'AssetController@delete');

// Register Allocation Management Routes
$router->add('allocations',          'AllocationController@index');
$router->add('allocations/create',   'AllocationController@create');
$router->add('allocations/return',   'AllocationController@return');

// Register Maintenance Log Routes
$router->add('maintenance',          'MaintenanceController@index');
$router->add('maintenance/create',   'MaintenanceController@create');
$router->add('maintenance/edit',     'MaintenanceController@edit');

// Register Consumable Inventory Routes
$router->add('inventory',            'InventoryController@index');
$router->add('inventory/create',     'InventoryController@create');
$router->add('inventory/edit',       'InventoryController@edit');

// Register Report & Analytics Routes
$router->add('reports',              'ReportController@index');
$router->add('reports/valuation',    'ReportController@valuation');
$router->add('reports/maintenance',  'ReportController@maintenance');
$router->add('reports/utilization',  'ReportController@utilization');
$router->add('reports/department',   'ReportController@department');
$router->add('reports/booking',      'ReportController@booking');
$router->add('reports/audit',        'ReportController@audit');
$router->add('reports/export',       'ReportController@export');

// Register User Settings & Administration Routes
$router->add('users',                'UserController@index');
$router->add('users/create',         'UserController@create');
$router->add('users/edit',           'UserController@edit');
$router->add('profile',              'UserController@profile');

// Register Department Administration Routes
$router->add('departments',          'DepartmentController@index');
$router->add('departments/create',   'DepartmentController@create');
$router->add('departments/edit',     'DepartmentController@edit');
$router->add('departments/delete',   'DepartmentController@delete');

// Register Category Administration Routes
$router->add('categories',           'CategoryController@index');
$router->add('categories/create',    'CategoryController@create');
$router->add('categories/edit',      'CategoryController@edit');
$router->add('categories/delete',    'CategoryController@delete');

// Register Resource Booking Routes
$router->add('bookings',             'BookingController@index');
$router->add('bookings/create',      'BookingController@create');
$router->add('bookings/cancel',      'BookingController@cancel');

// Register Stocktake Audit Routes
$router->add('audits',               'AuditController@index');
$router->add('audits/create',        'AuditController@create');
$router->add('audits/view',          'AuditController@details');
$router->add('audits/verify',        'AuditController@verify');
$router->add('audits/close',         'AuditController@close');
$router->add('audits/report',        'AuditController@report');

// Register Notifications Routes
$router->add('notifications',         'NotificationController@index');
$router->add('notifications/read',    'NotificationController@read');
$router->add('notifications/readAll', 'NotificationController@readAll');

// Dispatch Router request
$router->dispatch($_SERVER['REQUEST_URI']);
