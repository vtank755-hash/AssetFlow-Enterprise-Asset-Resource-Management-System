<?php
use App\Core\Session;
$role = Session::getRole();
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 fw-bold">Maintenance Registry</h1>
            <p class="text-muted mb-0">Monitor service events, downtime scheduling, and maintenance expenditures.</p>
        </div>
        <?php if ($role === 'Admin' || $role === 'Manager'): ?>
            <a href="<?php echo BASE_URL; ?>/maintenance/create" class="btn btn-primary">
                <i class="bi bi-calendar-plus me-2"></i>Schedule Service
            </a>
        <?php endif; ?>
    </div>

    <!-- Registry Grid Card -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Asset Tag</th>
                            <th>Resource Name</th>
                            <th>Work Order</th>
                            <th>Scheduled Date</th>
                            <th>Completion Date</th>
                            <th>Cost</th>
                            <th>Status</th>
                            <th>Service Provider</th>
                            <?php if ($role === 'Admin' || $role === 'Manager'): ?>
                                <th class="text-end">Actions</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($orders)): ?>
                            <tr>
                                <td colspan="9" class="text-center py-4 text-muted">No maintenance orders registered.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($orders as $order): ?>
                                <tr>
                                    <td>
                                        <a href="<?php echo BASE_URL; ?>/assets/view?id=<?php echo $order['asset_id']; ?>" class="fw-bold text-decoration-none">
                                            <?php echo htmlspecialchars($order['asset_tag']); ?>
                                        </a>
                                    </td>
                                    <td>
                                        <span class="fw-semibold text-dark"><?php echo htmlspecialchars($order['asset_name']); ?></span>
                                    </td>
                                    <td>
                                        <div>
                                            <span class="fw-semibold text-dark d-block" style="font-size: 14px;"><?php echo htmlspecialchars($order['title']); ?></span>
                                            <span class="text-muted small"><?php echo htmlspecialchars($order['description']); ?></span>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars(date('M d, Y', strtotime($order['scheduled_date']))); ?></td>
                                    <td>
                                        <?php echo $order['completion_date'] ? htmlspecialchars(date('M d, Y', strtotime($order['completion_date']))) : '<span class="text-muted italic small">Incomplete</span>'; ?>
                                    </td>
                                    <td class="fw-semibold text-dark">₹<?php echo htmlspecialchars(number_format($order['cost'], 2)); ?></td>
                                    <td>
                                        <?php
                                        $badgeClass = 'bg-secondary text-white';
                                        switch ($order['status']) {
                                            case 'Pending': $badgeClass = 'status-pending'; break;
                                            case 'Approved': $badgeClass = 'bg-info-subtle text-info border border-info-subtle'; break;
                                            case 'Rejected': $badgeClass = 'bg-danger-subtle text-danger border border-danger-subtle'; break;
                                            case 'Technician Assigned': $badgeClass = 'bg-primary-subtle text-primary border border-primary-subtle'; break;
                                            case 'In Progress': $badgeClass = 'status-inprogress'; break;
                                            case 'Resolved': $badgeClass = 'status-completed'; break;
                                        }
                                        ?>
                                        <span class="status-badge <?php echo $badgeClass; ?>">
                                            <?php echo htmlspecialchars($order['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($order['performed_by'] ?: 'N/A'); ?></td>
                                    
                                    <?php if ($role === 'Admin' || $role === 'Manager'): ?>
                                        <td class="text-end">
                                            <a href="<?php echo BASE_URL; ?>/maintenance/edit?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-outline-secondary border-0 px-2 py-1">
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
