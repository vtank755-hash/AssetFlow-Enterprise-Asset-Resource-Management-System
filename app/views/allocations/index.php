<?php
use App\Core\Session;
$role = Session::getRole();
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 fw-bold">Resource Allocations</h1>
            <p class="text-muted mb-0">Monitor custody, timelines, and return schedules of organizational resources.</p>
        </div>
        <?php if ($role === 'Admin' || $role === 'Manager'): ?>
            <a href="<?php echo BASE_URL; ?>/allocations/create" class="btn btn-primary">
                <i class="bi bi-box-arrow-up me-2"></i>New Check-Out
            </a>
        <?php endif; ?>
    </div>

    <!-- Allocations Grid Card -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Asset Tag</th>
                            <th>Resource Name</th>
                            <th>Custodian</th>
                            <th>Allocated Date</th>
                            <th>Due Date</th>
                            <th>Returned Date</th>
                            <th>Status</th>
                            <th>Issued By</th>
                            <?php if ($role === 'Admin' || $role === 'Manager'): ?>
                                <th class="text-end">Actions</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($allocations)): ?>
                            <tr>
                                <td colspan="9" class="text-center py-4 text-muted">No allocations recorded.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($allocations as $alloc): ?>
                                <tr>
                                    <td>
                                        <a href="<?php echo BASE_URL; ?>/assets/view?id=<?php echo $alloc['asset_id']; ?>" class="fw-bold text-decoration-none">
                                            <?php echo htmlspecialchars($alloc['asset_tag']); ?>
                                        </a>
                                    </td>
                                    <td>
                                        <span class="fw-semibold text-dark"><?php echo htmlspecialchars($alloc['asset_name']); ?></span>
                                    </td>
                                    <td><?php echo htmlspecialchars($alloc['user_name']); ?></td>
                                    <td><?php echo htmlspecialchars(date('M d, Y', strtotime($alloc['allocated_date']))); ?></td>
                                    <td><?php echo htmlspecialchars(date('M d, Y', strtotime($alloc['due_date']))); ?></td>
                                    <td>
                                        <?php echo $alloc['returned_date'] ? htmlspecialchars(date('M d, Y', strtotime($alloc['returned_date']))) : '<span class="text-muted italic small">In Possession</span>'; ?>
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
                                    
                                    <?php if ($role === 'Admin' || $role === 'Manager'): ?>
                                        <td class="text-end">
                                            <?php if ($alloc['status'] !== 'Returned'): ?>
                                                <a href="<?php echo BASE_URL; ?>/allocations/return?id=<?php echo $alloc['id']; ?>" class="btn btn-sm btn-outline-success py-1 px-2.5">
                                                    <i class="bi bi-box-arrow-in-down me-1"></i> Check-in
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted small"><i class="bi bi-check2-all text-success me-1"></i>Archived</span>
                                            <?php endif; ?>
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
