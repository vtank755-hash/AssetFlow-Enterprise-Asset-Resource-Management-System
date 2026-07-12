<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($title) ? htmlspecialchars($title) . ' - ' . APP_NAME : APP_NAME; ?></title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Custom Style Sheet -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/style.css">
</head>
<body>
    <!-- Fixed Top Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white fixed-top no-print" style="z-index: 1040; height: 60px; border-bottom: 1px solid #e2e8f0; box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05);">
        <div class="container-fluid px-3">
            <!-- Mobile Toggle Sidebar Trigger -->
            <label for="sidebar-toggle" class="btn btn-light border-secondary-subtle me-2 d-md-none text-dark"><i class="bi bi-list fs-4"></i></label>
            
            <a class="navbar-brand d-flex align-items-center fw-bold text-dark" href="<?php echo BASE_URL; ?>/dashboard">
                <i class="bi bi-cpu text-indigo me-2 fs-4"></i>
                <span>AssetFlow</span>
            </a>
            
            <div class="d-flex align-items-center ms-auto gap-3">
                <!-- Topbar Search form -->
                <form class="d-none d-sm-flex" action="<?php echo BASE_URL; ?>/assets" method="GET">
                    <div class="input-group input-group-sm">
                        <input type="text" name="q" class="form-control bg-light text-dark border border-secondary-subtle" placeholder="Search assets..." style="width: 180px;">
                        <button class="btn btn-outline-secondary border border-secondary-subtle" type="submit"><i class="bi bi-search"></i></button>
                    </div>
                </form>
                
                <!-- Print PDF Button -->
                <button onclick="window.print();" class="btn btn-sm btn-outline-primary no-print d-flex align-items-center gap-1">
                    <i class="bi bi-printer"></i>
                    <span class="d-none d-md-inline">Print PDF</span>
                </button>
                
                <!-- Notifications Badge Link -->
                <a href="<?php echo BASE_URL; ?>/notifications" class="text-dark position-relative px-2">
                    <i class="bi bi-bell fs-5"></i>
                    <?php 
                    $userId = \App\Core\Session::getUserId();
                    $unreadCount = 0;
                    if ($userId) {
                        $notifModel = new \App\Models\Notification();
                        $unreadCount = $notifModel->getUnreadCount($userId);
                    }
                    if ($unreadCount > 0): 
                    ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 9px; padding: 2px 5px;">
                            <?php echo $unreadCount; ?>
                        </span>
                    <?php endif; ?>
                </a>

                <!-- User Profile Dropdown using Details Hack -->
                <details class="css-dropdown">
                    <summary class="btn btn-sm btn-outline-dark border-0 d-flex align-items-center gap-2">
                        <i class="bi bi-person-circle fs-5"></i>
                        <span class="d-none d-md-inline"><?php echo htmlspecialchars(\App\Core\Session::getUserName() ?? ''); ?></span>
                    </summary>
                    <div class="dropdown-menu-css" style="top: 100%; right: 0;">
                        <a href="<?php echo BASE_URL; ?>/profile"><i class="bi bi-person me-2"></i>Profile</a>
                        <a href="<?php echo BASE_URL; ?>/profile"><i class="bi bi-sliders me-2"></i>Settings</a>
                        <hr class="dropdown-divider my-1" style="background-color: var(--border-color);">
                        <a href="<?php echo BASE_URL; ?>/auth/logout" class="text-danger"><i class="bi bi-box-arrow-right me-2"></i>Logout</a>
                    </div>
                </details>
            </div>
        </div>
    </nav>

    <div class="wrapper" style="margin-top: 60px;">
        <!-- Pure CSS Sidebar Toggle Checkbox -->
        <input type="checkbox" id="sidebar-toggle" class="d-none">
