<?php
use App\Core\Session;
$role = Session::getRole();
?>
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 fw-bold">Stocktake & Audits</h1>
            <p class="text-muted mb-0">Create verification schedules, track physical assets state, and extract discrepancy logs.</p>
        </div>
        <?php if ($role === 'Admin'): ?>
            <a href="<?php echo BASE_URL; ?>/audits/create" class="btn btn-primary">
                <i class="bi bi-clipboard-plus me-2"></i>Start Stocktake
            </a>
        <?php endif; ?>
    </div>

    <!-- Alerts -->
    <?php echo flash('success'); ?>
    <?php echo flash('danger'); ?>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Audit Cycle</th>
                            <th>Scope & Criteria</th>
                            <th>Assigned Auditor</th>
                            <th>Duration Period</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($cycles)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">No stocktake audit cycles recorded in the system.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($cycles as $c): ?>
                                <tr>
                                    <td>
                                        <span class="fw-bold text-indigo d-block"><?php echo htmlspecialchars($c['title']); ?></span>
                                        <span class="text-muted small">Started by: <?php echo htmlspecialchars($c['creator_name']); ?></span>
                                    </td>
                                    <td>
                                        <div>
                                            <span class="small d-block text-dark">
                                                <strong>Dept:</strong> <?php echo htmlspecialchars($c['department_name'] ?: 'All Departments'); ?>
                                            </span>
                                            <span class="small d-block text-muted">
                                                <strong>Location:</strong> <?php echo htmlspecialchars($c['location_scope'] ?: 'All Locations'); ?>
                                            </span>
                                        </div>
                                    </td>
                                    <td><span class="fw-semibold text-dark"><i class="bi bi-person me-1"></i><?php echo htmlspecialchars($c['auditor_name']); ?></span></td>
                                    <td>
                                        <span class="small text-muted font-monospace">
                                            <?php echo htmlspecialchars(date('M d, Y', strtotime($c['start_date']))); ?> - 
                                            <?php echo htmlspecialchars(date('M d, Y', strtotime($c['end_date']))); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php
                                        $badgeClass = ($c['status'] === 'Completed') ? 
                                            'bg-secondary-subtle text-secondary border border-secondary-subtle' : 
                                            'bg-success-subtle text-success border border-success-subtle';
                                        ?>
                                        <span class="status-badge <?php echo $badgeClass; ?>">
                                            <?php echo htmlspecialchars($c['status']); ?>
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-inline-flex gap-1">
                                            <a href="<?php echo BASE_URL; ?>/audits/view?id=<?php echo $c['id']; ?>" class="btn btn-sm btn-outline-primary border-0 px-2 py-1">
                                                <i class="bi bi-list-check me-1"></i>Checklist
                                            </a>
                                            <a href="<?php echo BASE_URL; ?>/audits/report?id=<?php echo $c['id']; ?>" class="btn btn-sm btn-outline-warning border-0 px-2 py-1 text-warning-emphasis">
                                                <i class="bi bi-exclamation-triangle me-1"></i>Discrepancies
                                            </a>
                                            <?php if ($c['status'] === 'In Progress' && $role === 'Admin'): ?>
                                                <a href="<?php echo BASE_URL; ?>/audits/close?id=<?php echo $c['id']; ?>" class="btn btn-sm btn-outline-danger border-0 px-2 py-1" onclick="return confirm('Are you sure you want to close this audit cycle? This will lock all checks.');">
                                                    <i class="bi bi-lock me-1"></i>Close
                                                </a>
                                            <?php endif; ?>
                                        </div>
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
