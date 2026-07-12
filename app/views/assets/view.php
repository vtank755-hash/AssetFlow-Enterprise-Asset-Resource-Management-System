<?php
use App\Core\Session;
$role = Session::getRole();
?>
<div class="container-fluid py-4">
    <div class="mb-4 d-flex justify-content-between align-items-start">
        <div>
            <a href="<?php echo BASE_URL; ?>/assets" class="text-decoration-none text-muted"><i class="bi bi-arrow-left me-1"></i> Back to Assets Directory</a>
            <h1 class="h3 mt-2 mb-0 fw-bold"><?php echo htmlspecialchars($asset['name']); ?></h1>
            <p class="text-muted mb-0">Tag ID: <strong class="text-dark"><?php echo htmlspecialchars($asset['asset_tag']); ?></strong></p>
        </div>
        
        <div>
            <?php if ($role === 'Admin' || $role === 'Manager'): ?>
                <a href="<?php echo BASE_URL; ?>/assets/edit?id=<?php echo $asset['id']; ?>" class="btn btn-outline-primary me-2">
                    <i class="bi bi-pencil-square me-1"></i> Edit Details
                </a>
            <?php endif; ?>
            
            <span class="status-badge px-3 py-1.5 fs-6 <?php 
                echo $asset['status'] === 'Available' ? 'status-available' : 
                    ($asset['status'] === 'Allocated' ? 'status-allocated' : 
                    ($asset['status'] === 'Maintenance' ? 'status-maintenance' : 'status-disposed')); 
            ?>">
                <i class="bi bi-circle-fill me-1" style="font-size: 8px; vertical-align: middle;"></i>
                <?php echo htmlspecialchars($asset['status']); ?>
            </span>
        </div>
    </div>

    <!-- Details Tabs Header -->
    <div class="details-tab-nav">
        <a href="<?php echo BASE_URL; ?>/assets/view?id=<?php echo $asset['id']; ?>&tab=info" class="details-tab-link <?php echo $activeTab === 'info' ? 'active' : ''; ?>">
            <i class="bi bi-info-circle me-1"></i> Information
        </a>
        <a href="<?php echo BASE_URL; ?>/assets/view?id=<?php echo $asset['id']; ?>&tab=allocations" class="details-tab-link <?php echo $activeTab === 'allocations' ? 'active' : ''; ?>">
            <i class="bi bi-arrow-left-right me-1"></i> Allocations History
        </a>
        <a href="<?php echo BASE_URL; ?>/assets/view?id=<?php echo $asset['id']; ?>&tab=maintenance" class="details-tab-link <?php echo $activeTab === 'maintenance' ? 'active' : ''; ?>">
            <i class="bi bi-wrench me-1"></i> Service Logs
        </a>
    </div>

    <!-- Content Sections -->
    <div class="row">
        <?php if ($activeTab === 'info'): ?>
            <!-- Main Information Tab -->
            <div class="col-lg-7 mb-4">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title mb-0 fw-bold">Specifications & Location</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <span class="text-muted d-block small">CATEGORY</span>
                                <span class="fw-semibold text-dark"><?php echo htmlspecialchars($asset['category_name']); ?></span>
                            </div>
                            <div class="col-sm-6">
                                <span class="text-muted d-block small">PHYSICAL LOCATION</span>
                                <span class="fw-semibold text-dark"><?php echo htmlspecialchars($asset['location']); ?></span>
                            </div>
                            <div class="col-sm-6">
                                <span class="text-muted d-block small">MODEL / SPECS</span>
                                <span class="fw-semibold text-dark"><?php echo htmlspecialchars($asset['model'] ?: 'N/A'); ?></span>
                            </div>
                            <div class="col-sm-6">
                                <span class="text-muted d-block small">SERIAL NUMBER / VIN</span>
                                <span class="fw-semibold text-dark"><?php echo htmlspecialchars($asset['serial_number']); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Financial Valuation Card -->
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3">
                        <h5 class="card-title mb-0 fw-bold">Depreciation & Valuation (Straight-Line)</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3 mb-4">
                            <div class="col-sm-4">
                                <span class="text-muted d-block small">PURCHASE COST</span>
                                <span class="fw-bold fs-5 text-dark">₹<?php echo htmlspecialchars(number_format($depreciation['purchase_cost'], 2)); ?></span>
                            </div>
                            <div class="col-sm-4">
                                <span class="text-muted d-block small">ACCUMULATED DEPRECIATION</span>
                                <span class="fw-semibold text-danger">-₹<?php echo htmlspecialchars(number_format($depreciation['accumulated_depreciation'], 2)); ?></span>
                            </div>
                            <div class="col-sm-4">
                                <span class="text-muted d-block small">CURRENT BOOK VALUE</span>
                                <span class="fw-bold fs-5 text-success">₹<?php echo htmlspecialchars(number_format($depreciation['book_value'], 2)); ?></span>
                            </div>
                        </div>

                        <!-- Graphical Asset Value Visualizer using pure CSS -->
                        <div>
                            <?php 
                            $pct = ($depreciation['purchase_cost'] > 0) ? ($depreciation['book_value'] / $depreciation['purchase_cost']) * 100 : 0;
                            ?>
                            <div class="d-flex justify-content-between mb-1 small">
                                <span class="text-muted">Asset Value Remaining</span>
                                <span class="fw-bold"><?php echo round($pct); ?>%</span>
                            </div>
                            <div class="css-chart-bar-container" style="height: 10px;">
                                <div class="css-chart-bar" style="width: <?php echo $pct; ?>%; background-color: var(--accent-color);"></div>
                            </div>
                            <div class="d-flex justify-content-between mt-1 text-muted" style="font-size: 11px;">
                                <span>Purchase (<?php echo htmlspecialchars(date('M Y', strtotime($asset['purchase_date']))); ?>)</span>
                                <span>Fully Depreciated</span>
                            </div>
                        </div>

                        <div class="row mt-4 pt-3 border-top g-2 text-muted" style="font-size: 13px;">
                            <div class="col-6">
                                <span>Annual Rate:</span> <strong class="text-dark"><?php echo htmlspecialchars($asset['depreciation_rate']); ?>%</strong>
                            </div>
                            <div class="col-6">
                                <span>Annual Loss:</span> <strong class="text-dark">₹<?php echo htmlspecialchars(number_format($depreciation['annual_depreciation'], 2)); ?></strong>
                            </div>
                            <div class="col-6">
                                <span>Years Held:</span> <strong class="text-dark"><?php echo htmlspecialchars($depreciation['years_held']); ?> years</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Context Info Sidebar -->
            <div class="col-lg-5">
                <div class="card shadow-sm border-0 mb-4 bg-light">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-3"><i class="bi bi-clock-history me-1 text-indigo"></i> Quick Actions</h5>
                        <p class="text-muted small">Perform common lifecycle tasks for this resource tag.</p>
                        
                        <div class="d-grid gap-2">
                            <?php if ($asset['status'] === 'Available' && ($role === 'Admin' || $role === 'Manager')): ?>
                                <a href="<?php echo BASE_URL; ?>/allocations/create?asset_id=<?php echo $asset['id']; ?>" class="btn btn-primary text-start">
                                    <i class="bi bi-arrow-right-short me-1"></i> Check-Out Asset
                                </a>
                            <?php endif; ?>
                            
                            <?php if ($asset['status'] === 'Allocated' && ($role === 'Admin' || $role === 'Manager')): ?>
                                <!-- Find active allocation to perform check-in directly -->
                                <a href="<?php echo BASE_URL; ?>/allocations" class="btn btn-secondary text-start">
                                    <i class="bi bi-arrow-left-short me-1"></i> Manage Active Allocation
                                </a>
                            <?php endif; ?>

                            <?php if ($role === 'Admin' || $role === 'Manager'): ?>
                                <a href="<?php echo BASE_URL; ?>/maintenance/create?asset_id=<?php echo $asset['id']; ?>" class="btn btn-outline-dark text-start">
                                    <i class="bi bi-calendar-plus me-1"></i> Schedule Maintenance
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

        <?php elseif ($activeTab === 'allocations'): ?>
            <!-- Allocations History Tab -->
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Custodian</th>
                                        <th>Checked Out</th>
                                        <th>Due Date</th>
                                        <th>Returned Date</th>
                                        <th>Status</th>
                                        <th>Issued By</th>
                                        <th>Notes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($allocations)): ?>
                                        <tr>
                                            <td colspan="7" class="text-center py-4 text-muted">This asset has never been allocated.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($allocations as $alloc): ?>
                                            <tr>
                                                <td class="fw-semibold"><?php echo htmlspecialchars($alloc['user_name']); ?></td>
                                                <td><?php echo htmlspecialchars(date('M d, Y', strtotime($alloc['allocated_date']))); ?></td>
                                                <td><?php echo htmlspecialchars(date('M d, Y', strtotime($alloc['due_date']))); ?></td>
                                                <td>
                                                    <?php echo $alloc['returned_date'] ? htmlspecialchars(date('M d, Y', strtotime($alloc['returned_date']))) : '<span class="text-muted italic">In Possession</span>'; ?>
                                                </td>
                                                <td>
                                                    <span class="status-badge <?php 
                                                        echo $alloc['status'] === 'Active' ? 'status-allocated' : 
                                                            ($alloc['status'] === 'Returned' ? 'status-returned' : 'status-overdue'); 
                                                    ?>">
                                                        <?php echo htmlspecialchars($alloc['status']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo htmlspecialchars($alloc['allocator_name']); ?></td>
                                                <td class="text-muted small" style="max-width: 250px;"><?php echo htmlspecialchars($alloc['notes'] ?? ''); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        <?php elseif ($activeTab === 'maintenance'): ?>
            <!-- Service logs Tab -->
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Work Order</th>
                                        <th>Service Date</th>
                                        <th>Completion Date</th>
                                        <th>Cost</th>
                                        <th>Status</th>
                                        <th>Technician / Shop</th>
                                        <th>Notes</th>
                                        <?php if ($role === 'Admin' || $role === 'Manager'): ?>
                                            <th class="text-end">Actions</th>
                                        <?php endif; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($maintenances)): ?>
                                        <tr>
                                            <td colspan="8" class="text-center py-4 text-muted">No maintenance orders registered for this asset.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($maintenances as $main): ?>
                                            <tr>
                                                <td>
                                                    <span class="fw-semibold text-dark d-block"><?php echo htmlspecialchars($main['title']); ?></span>
                                                    <span class="text-muted small"><?php echo htmlspecialchars($main['description']); ?></span>
                                                </td>
                                                <td><?php echo htmlspecialchars(date('M d, Y', strtotime($main['scheduled_date']))); ?></td>
                                                <td>
                                                    <?php echo $main['completion_date'] ? htmlspecialchars(date('M d, Y', strtotime($main['completion_date']))) : '<span class="text-muted italic">Scheduled</span>'; ?>
                                                </td>
                                                <td>₹<?php echo htmlspecialchars(number_format($main['cost'], 2)); ?></td>
                                                <td>
                                                    <span class="status-badge <?php 
                                                        echo $main['status'] === 'Pending' ? 'status-pending' : 
                                                            ($main['status'] === 'In Progress' ? 'status-inprogress' : 
                                                            ($main['status'] === 'Completed' ? 'status-completed' : 'status-cancelled')); 
                                                    ?>">
                                                        <?php echo htmlspecialchars($main['status']); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo htmlspecialchars($main['performed_by'] ?: 'N/A'); ?></td>
                                                <td class="text-muted small" style="max-width: 200px;"><?php echo htmlspecialchars($main['notes'] ?? ''); ?></td>
                                                <?php if ($role === 'Admin' || $role === 'Manager'): ?>
                                                    <td class="text-end">
                                                        <a href="<?php echo BASE_URL; ?>/maintenance/edit?id=<?php echo $main['id']; ?>" class="btn btn-sm btn-outline-secondary border-0 px-2 py-1">
                                                            <i class="bi bi-pencil-square"></i> Update
                                                        </a>
                                                    </td>
                                                <?php endif; ?>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
