<?php
use App\Core\Session;

// Determine current request path for setting the active state on sidebar links
$currentUri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
if (BASE_URL !== '') {
    $currentUri = trim(str_replace(trim(BASE_URL, '/'), '', $currentUri), '/');
}
// Default to dashboard
if ($currentUri === '') {
    $currentUri = 'dashboard';
}

$role = Session::getRole();
$userName = Session::getUserName();

// Fetch Unread Notifications Count
$unreadNotifCount = 0;
$userId = Session::getUserId();
if ($userId) {
    $notifModel = new \App\Models\Notification();
    $unreadNotifCount = $notifModel->getUnreadCount($userId);
}
?>
<nav id="sidebar">
    <div class="sidebar-header">
        <div class="d-flex align-items-center justify-content-between">
            <h3 class="mb-0"><i class="bi bi-cpu text-indigo me-2"></i>AssetFlow</h3>
            <label for="sidebar-toggle" class="btn btn-sm btn-outline-light border-0 d-md-none"><i class="bi bi-x-lg"></i></label>
        </div>
    </div>

    <ul class="list-unstyled components">
        <li class="<?php echo $currentUri === 'dashboard' ? 'active' : ''; ?>">
            <a href="<?php echo BASE_URL; ?>/dashboard">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
        </li>
        <li class="<?php echo (strpos($currentUri, 'assets') === 0) ? 'active' : ''; ?>">
            <a href="<?php echo BASE_URL; ?>/assets">
                <i class="bi bi-box-seam"></i> Assets
            </a>
        </li>
        <li class="<?php echo (strpos($currentUri, 'allocations') === 0) ? 'active' : ''; ?>">
            <a href="<?php echo BASE_URL; ?>/allocations">
                <i class="bi bi-arrow-left-right"></i> Allocations
            </a>
        </li>
        <li class="<?php echo (strpos($currentUri, 'maintenance') === 0) ? 'active' : ''; ?>">
            <a href="<?php echo BASE_URL; ?>/maintenance">
                <i class="bi bi-wrench"></i> Maintenance
            </a>
        </li>
        <li class="<?php echo (strpos($currentUri, 'inventory') === 0) ? 'active' : ''; ?>">
            <a href="<?php echo BASE_URL; ?>/inventory">
                <i class="bi bi-journal-text"></i> Inventory
            </a>
        </li>
        <li class="<?php echo (strpos($currentUri, 'bookings') === 0) ? 'active' : ''; ?>">
            <a href="<?php echo BASE_URL; ?>/bookings">
                <i class="bi bi-calendar-event"></i> Bookings
            </a>
        </li>
        <li class="<?php echo ($currentUri === 'notifications') ? 'active' : ''; ?>">
            <a href="<?php echo BASE_URL; ?>/notifications" class="d-flex align-items-center justify-content-between">
                <span><i class="bi bi-bell"></i> Notifications</span>
                <?php if ($unreadNotifCount > 0): ?>
                    <span class="badge bg-danger rounded-pill px-2 py-0.5" style="font-size: 10px;"><?php echo $unreadNotifCount; ?></span>
                <?php endif; ?>
            </a>
        </li>
        <li class="<?php echo ($currentUri === 'profile') ? 'active' : ''; ?>">
            <a href="<?php echo BASE_URL; ?>/profile">
                <i class="bi bi-person-gear"></i> Profile & Settings
            </a>
        </li>

        <?php if ($role === 'Admin' || $role === 'Manager'): ?>
            <li class="sidebar-separator py-2 px-3 text-muted uppercase font-monospace fw-bold" style="font-size: 10px; letter-spacing: 0.5px;">Administration</li>
            
            <li class="<?php echo ($currentUri === 'departments') ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>/departments">
                    <i class="bi bi-building"></i> Departments Setup
                </a>
            </li>
            <li class="<?php echo ($currentUri === 'categories') ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>/categories">
                    <i class="bi bi-tags"></i> Categories Setup
                </a>
            </li>
            <li class="<?php echo (strpos($currentUri, 'audits') === 0) ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>/audits">
                    <i class="bi bi-clipboard-check"></i> Audits & Stocktakes
                </a>
            </li>
            <li class="<?php echo (strpos($currentUri, 'reports') === 0) ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>/reports">
                    <i class="bi bi-bar-chart-line"></i> Reports Overview
                </a>
            </li>
        <?php endif; ?>

        <?php if ($role === 'Admin'): ?>
            <li class="<?php echo (strpos($currentUri, 'users') === 0) ? 'active' : ''; ?>">
                <a href="<?php echo BASE_URL; ?>/users">
                    <i class="bi bi-people"></i> Employees Directory
                </a>
            </li>
        <?php endif; ?>

        <li class="sidebar-separator py-2 px-3 text-muted uppercase font-monospace fw-bold" style="font-size: 10px; letter-spacing: 0.5px;">Shortcuts</li>
        <li>
            <a href="<?php echo BASE_URL; ?>/dashboard#activity-logs">
                <i class="bi bi-clock-history"></i> Activity Logs
            </a>
        </li>
        <li>
            <a href="<?php echo BASE_URL; ?>/auth/logout" class="text-danger-emphasis">
                <i class="bi bi-box-arrow-right"></i> Logout System
            </a>
        </li>
    </ul>

    <!-- Bottom User Bar -->
    <div style="position: absolute; bottom: 0; width: 100%; border-top: 1px solid rgba(255, 255, 255, 0.05); padding: 15px 20px; background: #0b0f19;">
        <div class="d-flex align-items-center justify-content-between">
            <div class="overflow-hidden me-2">
                <p class="mb-0 text-white fw-bold text-truncate" style="font-size: 14px;"><?php echo htmlspecialchars($userName); ?></p>
                <span class="badge bg-secondary" style="font-size: 10px;"><?php echo htmlspecialchars($role); ?></span>
            </div>
            
            <details class="css-dropdown">
                <summary class="btn btn-sm btn-outline-light border-0 px-2 py-1">
                    <i class="bi bi-three-dots-vertical"></i>
                </summary>
                <div class="dropdown-menu-css" style="bottom: 100%; right: 0; top: auto; margin-bottom: 8px;">
                    <a href="<?php echo BASE_URL; ?>/profile"><i class="bi bi-person me-2"></i>Profile</a>
                    <hr class="dropdown-divider my-1" style="background-color: var(--border-color);">
                    <a href="<?php echo BASE_URL; ?>/auth/logout" class="text-danger"><i class="bi bi-box-arrow-right me-2"></i>Logout</a>
                </div>
            </details>
        </div>
    </div>
</nav>

<!-- Page Content Area -->
<div id="content">
    <!-- Responsive Mobile Header (Pure CSS Sidebar Toggle Trigger) -->
    <div class="d-md-none bg-dark text-white p-3 d-flex align-items-center justify-content-between mb-4 rounded-3 shadow-sm">
        <h4 class="mb-0 fw-bold"><i class="bi bi-cpu text-indigo me-2"></i>AssetFlow</h4>
        <label for="sidebar-toggle" class="btn btn-outline-light border-0"><i class="bi bi-list fs-4"></i></label>
    </div>
    <!-- Flash Messages Container -->
    <div class="container-fluid px-0">
        <?php if (Session::hasFlash('success')): ?>
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                <?php echo Session::getFlash('success'); ?>
                <a href="?" class="btn-close" aria-label="Close" style="text-decoration:none;"></a>
            </div>
        <?php endif; ?>
        <?php if (Session::hasFlash('danger')): ?>
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <?php echo Session::getFlash('danger'); ?>
                <a href="?" class="btn-close" aria-label="Close" style="text-decoration:none;"></a>
            </div>
        <?php endif; ?>
        <?php if (Session::hasFlash('warning')): ?>
            <div class="alert alert-warning alert-dismissible fade show border-0 shadow-sm" role="alert">
                <i class="bi bi-exclamation-circle-fill me-2"></i>
                <?php echo Session::getFlash('warning'); ?>
                <a href="?" class="btn-close" aria-label="Close" style="text-decoration:none;"></a>
            </div>
        <?php endif; ?>
    </div>
