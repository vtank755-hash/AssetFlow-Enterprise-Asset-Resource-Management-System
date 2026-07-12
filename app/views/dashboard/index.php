<?php
use App\Core\Session;
?>
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 fw-bold">Dashboard</h1>
            <p class="text-muted mb-0">Overview of enterprise assets, allocations, bookings, and operations.</p>
        </div>
        <div class="text-muted small">
            <i class="bi bi-clock me-1"></i> IST: <?php echo date('h:i A, d M Y'); ?>
        </div>
    </div>

    <?php if ($role === 'Staff'): ?>
        <!-- STAFF DASHBOARD VIEW -->
        <div class="row g-4 mb-4">
            <div class="col-md-8">
                <!-- My Possessions Card -->
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title mb-0 fw-bold"><i class="bi bi-person-badge text-indigo me-2"></i>My Possessions (<?php echo $totalAssets; ?> items)</h5>
                    </div>
                    <div class="card-body <?php echo empty($staffAssignedAssets) ? 'p-5' : 'p-0'; ?>">
                        <?php if (empty($staffAssignedAssets)): ?>
                            <div class="text-center py-4 text-muted">
                                <div class="bg-light d-inline-flex align-items-center justify-content-center rounded-circle p-3 mb-3" style="width: 60px; height: 60px; color: var(--text-muted); background-color: #f1f5f9 !important;">
                                    <i class="bi bi-box-seam fs-3"></i>
                                </div>
                                <h6 class="fw-bold text-dark">No Resources Issued</h6>
                                <p class="small mb-0 text-muted">You do not have any company equipment registered under your custody.</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Asset Tag</th>
                                            <th>Resource Name</th>
                                            <th>Checkout Date</th>
                                            <th>Return Due Date</th>
                                            <th>Location</th>
                                            <th>Issued By</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($staffAssignedAssets as $alloc): ?>
                                            <tr>
                                                <td><strong class="text-indigo"><?php echo htmlspecialchars($alloc['asset_tag']); ?></strong></td>
                                                <td><span class="fw-semibold text-dark"><?php echo htmlspecialchars($alloc['asset_name']); ?></span></td>
                                                <td><?php echo htmlspecialchars(date('M d, Y', strtotime($alloc['allocated_date']))); ?></td>
                                                <td>
                                                     <?php 
                                                     $isOverdue = ($alloc['due_date'] < date('Y-m-d'));
                                                     echo $isOverdue ? 
                                                         '<span class="text-danger fw-bold"><i class="bi bi-exclamation-triangle-fill me-1"></i>' . htmlspecialchars(date('M d, Y', strtotime($alloc['due_date']))) . '</span>' : 
                                                         htmlspecialchars(date('M d, Y', strtotime($alloc['due_date'])));
                                                     ?>
                                                </td>
                                                <td><?php echo htmlspecialchars($alloc['location']); ?></td>
                                                <td><?php echo htmlspecialchars($alloc['allocator_name']); ?></td>
                                                <td>
                                                     <span class="status-badge <?php echo $isOverdue ? 'status-overdue' : 'status-allocated'; ?>">
                                                         <?php echo $isOverdue ? 'Overdue' : 'Active'; ?>
                                                     </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Notifications Pane (Staff) -->
            <div class="col-md-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title mb-0 fw-bold"><i class="bi bi-bell text-indigo me-2"></i>Recent Notifications</h5>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($recentNotifications)): ?>
                            <div class="text-center py-5 text-muted">
                                <i class="bi bi-bell-slash fs-2 d-block mb-2"></i>
                                No notifications found.
                            </div>
                        <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($recentNotifications as $notif): ?>
                                    <div class="list-group-item p-3 border-0 border-bottom">
                                        <div class="d-flex justify-content-between mb-1">
                                            <strong class="text-dark small"><?php echo htmlspecialchars($notif['title']); ?></strong>
                                            <span class="text-muted" style="font-size: 11px;"><?php echo date('M d, H:i', strtotime($notif['created_at'])); ?></span>
                                        </div>
                                        <p class="small text-muted mb-0"><?php echo htmlspecialchars($notif['message']); ?></p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

    <?php else: ?>
        <!-- ADMIN/MANAGER DASHBOARD VIEW -->
        <!-- Row 1: Core Metrics -->
        <div class="row g-4 mb-4">
            <!-- Total Assets -->
            <div class="col-md-3 col-sm-6">
                <div class="card card-hover shadow-sm border-0">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="text-muted small fw-semibold d-block mb-1">TOTAL ASSETS</span>
                                <span class="fw-bold fs-3 text-dark"><?php echo $stats['total_assets']; ?></span>
                            </div>
                            <div class="bg-primary-subtle text-primary rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                <i class="bi bi-box fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Available Assets -->
            <div class="col-md-3 col-sm-6">
                <div class="card card-hover shadow-sm border-0">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="text-muted small fw-semibold d-block mb-1">AVAILABLE ASSETS</span>
                                <span class="fw-bold fs-3 text-success"><?php echo $stats['available_assets']; ?></span>
                            </div>
                            <div class="bg-success-subtle text-success rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                <i class="bi bi-check-circle fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Allocated Assets -->
            <div class="col-md-3 col-sm-6">
                <div class="card card-hover shadow-sm border-0">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="text-muted small fw-semibold d-block mb-1">ALLOCATED ASSETS</span>
                                <span class="fw-bold fs-3 text-info"><?php echo $stats['allocated_assets']; ?></span>
                            </div>
                            <div class="bg-info-subtle text-info rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                <i class="bi bi-person-check fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Maintenance -->
            <div class="col-md-3 col-sm-6">
                <div class="card card-hover shadow-sm border-0">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="text-muted small fw-semibold d-block mb-1">IN MAINTENANCE</span>
                                <span class="fw-bold fs-3 text-warning"><?php echo $stats['maintenance_assets']; ?></span>
                            </div>
                            <div class="bg-warning-subtle text-warning rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                <i class="bi bi-tools fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Row 2: Secondary Metrics -->
        <div class="row g-4 mb-4">
            <!-- Bookings -->
            <div class="col-md-3 col-sm-6">
                <div class="card card-hover shadow-sm border-0">
                    <div class="card-body p-3.5">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="text-muted small fw-semibold d-block mb-0.5">ACTIVE BOOKINGS</span>
                                <span class="fw-bold fs-4 text-purple" style="color: #8b5cf6;"><?php echo $stats['active_bookings']; ?></span>
                            </div>
                            <div class="text-purple bg-purple-subtle rounded-circle p-2.5 d-flex align-items-center justify-content-center" style="width: 42px; height: 42px; background-color: #f5f3ff; color: #8b5cf6;">
                                <i class="bi bi-calendar-event fs-5"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Transfers -->
            <div class="col-md-3 col-sm-6">
                <div class="card card-hover shadow-sm border-0">
                    <div class="card-body p-3.5">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="text-muted small fw-semibold d-block mb-0.5">PENDING TRANSFERS</span>
                                <span class="fw-bold fs-4 text-cyan" style="color: #06b6d4;"><?php echo $stats['pending_transfers']; ?></span>
                            </div>
                            <div class="text-cyan bg-cyan-subtle rounded-circle p-2.5 d-flex align-items-center justify-content-center" style="width: 42px; height: 42px; background-color: #ecfeff; color: #06b6d4;">
                                <i class="bi bi-arrow-left-right fs-5"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Upcoming Returns -->
            <div class="col-md-3 col-sm-6">
                <div class="card card-hover shadow-sm border-0">
                    <div class="card-body p-3.5">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="text-muted small fw-semibold d-block mb-0.5">RETURNS IN 7 DAYS</span>
                                <span class="fw-bold fs-4 text-orange" style="color: #f97316;"><?php echo $stats['upcoming_returns']; ?></span>
                            </div>
                            <div class="text-orange bg-orange-subtle rounded-circle p-2.5 d-flex align-items-center justify-content-center" style="width: 42px; height: 42px; background-color: #fff7ed; color: #f97316;">
                                <i class="bi bi-clock-history fs-5"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Overdue Assets -->
            <div class="col-md-3 col-sm-6">
                <div class="card card-hover shadow-sm border-0">
                    <div class="card-body p-3.5">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="text-muted small fw-semibold d-block mb-0.5">OVERDUE ASSETS</span>
                                <span class="fw-bold fs-4 text-danger"><?php echo $stats['overdue_assets']; ?></span>
                            </div>
                            <div class="text-danger bg-danger-subtle rounded-circle p-2.5 d-flex align-items-center justify-content-center" style="width: 42px; height: 42px; background-color: #fef2f2;">
                                <i class="bi bi-exclamation-octagon fs-5"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Row 2.5: Dynamic Charts (No-JS HTML/SVG fallback) -->
        <div class="row g-4 mb-4 animate-fade-in" style="animation-delay: 0.15s;">
            <!-- Department Utilization Bar Chart -->
            <div class="col-md-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white py-3 border-0">
                        <h5 class="card-title mb-0 fw-bold"><i class="bi bi-bar-chart-fill text-indigo me-2"></i>Utilization by Department</h5>
                    </div>
                    <div class="card-body d-flex flex-column justify-content-between p-4">
                        <div class="d-flex align-items-end justify-content-around bg-light rounded p-3 mb-3 border border-secondary-subtle" style="height: 180px;">
                            <?php 
                            $maxVal = 1;
                            foreach ($chartData['utilization'] as $item) {
                                if ($item['value'] > $maxVal) {
                                    $maxVal = $item['value'];
                                }
                            }
                            foreach ($chartData['utilization'] as $item): 
                                $pct = ($item['value'] / $maxVal) * 100;
                            ?>
                                <div class="d-flex flex-column align-items-center" style="height: 100%; width: 60px;">
                                    <!-- Dynamic bar height from value -->
                                    <div class="bg-indigo rounded-top shadow-sm w-50 position-relative bar-hover" 
                                         style="height: <?php echo max(10, $pct * 0.8); ?>%; background: linear-gradient(180deg, #6366f1 0%, #4f46e5 100%) !important;"
                                         title="Allocations: <?php echo $item['value']; ?>">
                                        <span class="position-absolute top-0 start-50 translate-middle-x bg-dark text-white rounded px-1.5 py-0.5 small shadow-sm d-none bar-tooltip" style="margin-top: -30px; font-size: 10px; z-index: 10;">
                                            <?php echo $item['value']; ?>
                                        </span>
                                    </div>
                                    <span class="text-muted text-center text-truncate small mt-2 w-100" style="font-size: 10.5px; font-weight: 500;" title="<?php echo htmlspecialchars($item['label']); ?>">
                                        <?php echo htmlspecialchars($item['label']); ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <p class="text-muted small mb-0"><i class="bi bi-info-circle me-1.5"></i>Live checked out allocations count per department.</p>
                    </div>
                </div>
            </div>

            <!-- Maintenance Frequency Line Chart -->
            <div class="col-md-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white py-3 border-0">
                        <h5 class="card-title mb-0 fw-bold"><i class="bi bi-graph-up text-indigo me-2"></i>Maintenance Frequency</h5>
                    </div>
                    <div class="card-body d-flex flex-column justify-content-between p-4">
                        <div class="bg-light rounded p-3 mb-3 border border-secondary-subtle d-flex align-items-center justify-content-center" style="height: 180px;">
                            <?php
                            $maintData = $chartData['maintenance'];
                            $maxMaint = 1;
                            foreach ($maintData as $item) {
                                if ($item['value'] > $maxMaint) {
                                    $maxMaint = $item['value'];
                                }
                            }
                            $points = [];
                            $width = 440;
                            $height = 140;
                            $paddingX = 40;
                            $paddingY = 25;
                            $count = count($maintData);
                            $stepX = $count > 1 ? ($width - 2 * $paddingX) / ($count - 1) : 0;
                            
                            for ($i = 0; $i < $count; $i++) {
                                $x = $paddingX + $i * $stepX;
                                $ratio = $maintData[$i]['value'] / $maxMaint;
                                $y = $height - $paddingY - $ratio * ($height - 2 * $paddingY);
                                $points[] = "$x,$y";
                            }
                            $pointsString = implode(' ', $points);
                            ?>
                            <svg viewBox="0 0 <?php echo $width; ?> <?php echo $height; ?>" class="w-100 h-100" style="overflow: visible;">
                                <!-- Grid line bottom baseline -->
                                <line x1="<?php echo $paddingX; ?>" y1="<?php echo $height - $paddingY; ?>" x2="<?php echo $width - $paddingX; ?>" y2="<?php echo $height - $paddingY; ?>" stroke="#cbd5e1" stroke-width="1.5" stroke-dasharray="3,3" />
                                
                                <!-- Trend Line -->
                                <polyline fill="none" stroke="#f43f5e" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round" points="<?php echo $pointsString; ?>" />
                                
                                <!-- Markers -->
                                <?php foreach ($points as $idx => $pt): 
                                    list($px, $py) = explode(',', $pt);
                                ?>
                                    <circle cx="<?php echo $px; ?>" cy="<?php echo $py; ?>" r="5" fill="#f43f5e" stroke="#ffffff" stroke-width="1.5" class="chart-marker" />
                                    <!-- Value indicator -->
                                    <text x="<?php echo $px; ?>" y="<?php echo $py - 12; ?>" text-anchor="middle" font-size="11" font-weight="bold" fill="#0f172a" font-family="'Outfit', sans-serif">
                                        <?php echo $maintData[$idx]['value']; ?>
                                    </text>
                                    <!-- X-axis Label -->
                                    <text x="<?php echo $px; ?>" y="<?php echo $height - 5; ?>" text-anchor="middle" font-size="10.5" font-weight="600" fill="#64748b" font-family="'Outfit', sans-serif">
                                        <?php echo htmlspecialchars($maintData[$idx]['label']); ?>
                                    </text>
                                <?php endforeach; ?>
                            </svg>
                        </div>
                        <p class="text-muted small mb-0"><i class="bi bi-info-circle me-1.5"></i>Number of work orders logged per month for the last 6 months.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Row 3: Actionable Content columns -->
        <div class="row g-4">
            <!-- Left Side Actions & Audit -->
            <div class="col-lg-8">
                <!-- Quick Actions Grid -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title mb-0 fw-bold"><i class="bi bi-lightning-charge-fill text-warning me-2"></i>Quick Operations</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <a href="<?php echo BASE_URL; ?>/assets/create" class="btn btn-outline-primary w-100 py-3 d-flex flex-column align-items-center gap-2">
                                    <i class="bi bi-plus-square-fill fs-3 text-indigo"></i>
                                    <span class="fw-bold small text-dark">Register New Asset</span>
                                </a>
                            </div>
                            <div class="col-md-4">
                                <a href="<?php echo BASE_URL; ?>/allocations/create" class="btn btn-outline-primary w-100 py-3 d-flex flex-column align-items-center gap-2">
                                    <i class="bi bi-person-plus-fill fs-3 text-indigo"></i>
                                    <span class="fw-bold small text-dark">Issue Resource / Asset</span>
                                </a>
                            </div>
                            <div class="col-md-4">
                                <a href="<?php echo BASE_URL; ?>/maintenance/create" class="btn btn-outline-primary w-100 py-3 d-flex flex-column align-items-center gap-2">
                                    <i class="bi bi-wrench-adjustable-circle-fill fs-3 text-indigo"></i>
                                    <span class="fw-bold small text-dark">Request Maintenance</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Audit Cycle Summary -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title mb-0 fw-bold"><i class="bi bi-clipboard-check text-indigo me-2"></i>Stocktake & Audits Status</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="fw-bold mb-1">Periodic Audit Checking</h6>
                                <p class="text-muted small mb-0">Total active in-progress stocktake cycles: <strong><?php echo $stats['active_audits']; ?></strong></p>
                            </div>
                            <span class="badge <?php echo $stats['active_audits'] > 0 ? 'bg-success' : 'bg-light text-dark border'; ?> px-3 py-2">
                                <?php echo $stats['active_audits'] > 0 ? 'Active Cycle running' : 'System in sync'; ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Portfolio Valuation Card -->
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="text-muted small fw-semibold d-block mb-1">TOTAL PORTFOLIO ACQUISITION COST</span>
                                <h4 class="fw-bold text-dark mb-0">₹<?php echo htmlspecialchars(number_format($stats['total_valuation'], 2)); ?></h4>
                            </div>
                            <div class="bg-success-subtle text-success rounded-circle p-3" style="width: 55px; height: 55px; display:flex; align-items:center; justify-content:center;">
                                <i class="bi bi-currency-rupee fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side logs and notifications -->
            <div class="col-lg-4">
                <!-- Notifications Card -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title mb-0 fw-bold"><i class="bi bi-bell text-indigo me-2"></i>System Alerts</h5>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($recentNotifications)): ?>
                            <div class="text-center py-5 text-muted">
                                <i class="bi bi-bell-slash fs-3 d-block mb-2"></i>
                                No new notification alerts.
                            </div>
                        <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($recentNotifications as $notif): ?>
                                    <div class="list-group-item p-3 border-0 border-bottom">
                                        <div class="d-flex justify-content-between mb-1">
                                            <strong class="text-dark small"><?php echo htmlspecialchars($notif['title']); ?></strong>
                                            <span class="text-muted" style="font-size: 11px;"><?php echo date('M d, H:i', strtotime($notif['created_at'])); ?></span>
                                        </div>
                                        <p class="small text-muted mb-0"><?php echo htmlspecialchars($notif['message']); ?></p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Recent Activities -->
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title mb-0 fw-bold"><i class="bi bi-journal-text text-indigo me-2"></i>Audit Logs</h5>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($recentActivities)): ?>
                            <div class="text-center py-5 text-muted">
                                <i class="bi bi-journal-x fs-3 d-block mb-2"></i>
                                No recent logs recorded.
                            </div>
                        <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($recentActivities as $act): ?>
                                    <div class="list-group-item p-3 border-0 border-bottom">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span class="badge bg-secondary-subtle text-secondary-emphasis" style="font-size: 10px;"><?php echo htmlspecialchars($act['action']); ?></span>
                                            <span class="text-muted" style="font-size: 11px;"><?php echo date('M d, H:i', strtotime($act['created_at'])); ?></span>
                                        </div>
                                        <p class="small text-dark fw-semibold mb-0"><?php echo htmlspecialchars($act['employee_name'] ?: 'System'); ?></p>
                                        <p class="small text-muted mb-0" style="font-size: 12px;"><?php echo htmlspecialchars($act['details']); ?></p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
