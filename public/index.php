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

// Register Dashboard Routes
$router->add('dashboard',            'DashboardController@index');

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
$router->add('reports/export',       'ReportController@export');

// Register User Settings & Administration Routes
$router->add('users',                'UserController@index');
$router->add('users/create',         'UserController@create');
$router->add('users/edit',           'UserController@edit');
$router->add('profile',              'UserController@profile');

// Dispatch Router request
$router->dispatch($_SERVER['REQUEST_URI']);
