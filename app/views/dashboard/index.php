<?php
use App\Core\Session;
?>
<div class="container-fluid py-4">
    <div class="mb-4">
        <h1 class="h3 mb-0 fw-bold">Dashboard</h1>
        <p class="text-muted">Welcome back, <strong><?php echo htmlspecialchars(Session::getUserName()); ?></strong>. Here is the operational status overview.</p>
    </div>

    <?php if ($role === 'Staff'): ?>
        <!-- STAFF DASHBOARD VIEW -->
        <div class="row">
            <div class="col-12 mb-4">
                <div class="card shadow-sm border-0 bg-primary text-white p-4" style="background: linear-gradient(135deg, var(--accent-color) 0%, #312e81 100%) !important;">
                    <h3 class="fw-bold mb-2">My Assigned Resources</h3>
                    <p class="mb-0 text-white-50">Below is a list of all company equipment currently issued under your custody and responsibility.</p>
                </div>
            </div>

            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title mb-0 fw-bold"><i class="bi bi-person-badge text-indigo me-2"></i>My Possessions (<?php echo $totalAssets; ?> items)</h5>
                    </div>
                    <div class="card-body p-0">
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
                                    <?php if (empty($staffAssignedAssets)): ?>
                                        <tr>
                                            <td colspan="7" class="text-center py-5 text-muted">
                                                <i class="bi bi-box-seam fs-1 d-block mb-3"></i>
                                                No resources are currently registered under your name.
                                            </td>
                                        </tr>
                                    <?php else: ?>
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
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <?php else: ?>
        <!-- ADMIN/MANAGER DASHBOARD VIEW -->
        <!-- Metrics Deck -->
        <div class="row g-4 mb-4">
            <!-- Active Assets -->
            <div class="col-md-3">
                <div class="card card-hover shadow-sm border-0">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="text-muted small fw-semibold d-block mb-1">ACTIVE ASSETS</span>
                                <span class="fw-bold fs-3 text-dark"><?php echo htmlspecialchars($totalAssets); ?></span>
                            </div>
                            <div class="bg-indigo-subtle text-primary rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; background-color: #e0e7ff; color: #4f46e5;">
                                <i class="bi bi-box-seam fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Valuation -->
            <div class="col-md-3">
                <div class="card card-hover shadow-sm border-0">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="text-muted small fw-semibold d-block mb-1">TOTAL VALUATION</span>
                                <span class="fw-bold fs-3 text-dark">₹<?php echo htmlspecialchars(number_format($totalValuation, 2)); ?></span>
                            </div>
                            <div class="bg-success-subtle text-success rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                <i class="bi bi-currency-rupee fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Active Maintenance -->
            <div class="col-md-3">
                <div class="card card-hover shadow-sm border-0">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="text-muted small fw-semibold d-block mb-1">IN MAINTENANCE</span>
                                <span class="fw-bold fs-3 text-dark"><?php echo htmlspecialchars($activeMaintenance); ?></span>
                            </div>
                            <div class="bg-warning-subtle text-warning rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                <i class="bi bi-wrench fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stock warnings -->
            <div class="col-md-3">
                <div class="card card-hover shadow-sm border-0 <?php echo $lowStockCount > 0 ? 'border-start border-danger border-4' : ''; ?>">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="text-muted small fw-semibold d-block mb-1">LOW STOCK SKUS</span>
                                <span class="fw-bold fs-3 <?php echo $lowStockCount > 0 ? 'text-danger' : 'text-dark'; ?>"><?php echo htmlspecialchars($lowStockCount); ?></span>
                            </div>
                            <div class="<?php echo $lowStockCount > 0 ? 'bg-danger-subtle text-danger' : 'bg-light text-muted'; ?> rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                <i class="bi bi-journal-text fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Recent Custody Allocations -->
            <div class="col-lg-6 mb-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title mb-0 fw-bold"><i class="bi bi-clock-history text-indigo me-2"></i>Recent Checked-Out Resources</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Tag ID</th>
                                        <th>Custodian</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($recentAllocations)): ?>
                                        <tr>
                                            <td colspan="4" class="text-center py-4 text-muted">No recent allocations.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($recentAllocations as $alloc): ?>
                                            <tr>
                                                <td>
                                                    <a href="<?php echo BASE_URL; ?>/assets/view?id=<?php echo $alloc['asset_id']; ?>" class="fw-semibold text-decoration-none">
                                                        <?php echo htmlspecialchars($alloc['asset_tag']); ?>
                                                    </a>
                                                </td>
                                                <td><?php echo htmlspecialchars($alloc['user_name']); ?></td>
                                                <td><?php echo htmlspecialchars(date('M d, Y', strtotime($alloc['allocated_date']))); ?></td>
                                                <td>
                                                    <span class="status-badge <?php echo $alloc['status'] === 'Active' ? 'status-allocated' : 'status-returned'; ?>" style="font-size: 11px; padding: 4px 8px;">
                                                        <?php echo htmlspecialchars($alloc['status']); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Active Service Orders -->
            <div class="col-lg-6 mb-4">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title mb-0 fw-bold"><i class="bi bi-wrench-adjustable text-indigo me-2"></i>Active Maintenance Orders</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Tag ID</th>
                                        <th>Work Order</th>
                                        <th>Scheduled</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($recentWorkOrders)): ?>
                                        <tr>
                                            <td colspan="4" class="text-center py-4 text-muted">No active maintenance work orders.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($recentWorkOrders as $work): ?>
                                            <tr>
                                                <td><strong><?php echo htmlspecialchars($work['asset_tag']); ?></strong></td>
                                                <td>
                                                    <span class="fw-semibold text-dark d-block text-truncate" style="max-width: 150px; font-size:14px;"><?php echo htmlspecialchars($work['title']); ?></span>
                                                </td>
                                                <td><?php echo htmlspecialchars(date('M d, Y', strtotime($work['scheduled_date']))); ?></td>
                                                <td>
                                                    <span class="status-badge <?php 
                                                        echo $work['status'] === 'Pending' ? 'status-pending' : 
                                                            ($work['status'] === 'In Progress' ? 'status-inprogress' : 
                                                            ($work['status'] === 'Completed' ? 'status-completed' : 'status-cancelled')); 
                                                    ?>" style="font-size: 11px; padding: 4px 8px;">
                                                        <?php echo htmlspecialchars($work['status']); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
