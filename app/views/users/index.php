<?php
use App\Core\Session;
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 fw-bold">User Management</h1>
            <p class="text-muted mb-0">Manage system user access, credentials, and roles.</p>
        </div>
        <a href="<?php echo BASE_URL; ?>/users/create" class="btn btn-primary">
            <i class="bi bi-person-plus me-2"></i>Create New User
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email Address</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Created Date</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($users)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">No users found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-circle bg-light text-primary me-3 d-flex align-items-center justify-content-center rounded-circle fw-bold" style="width: 40px; height: 40px; border: 1px solid var(--border-color);">
                                                <?php 
                                                    $parts = explode(' ', $user['name']);
                                                    $initials = '';
                                                    foreach ($parts as $p) {
                                                        $initials .= strtoupper($p[0] ?? '');
                                                    }
                                                    echo htmlspecialchars(substr($initials, 0, 2));
                                                ?>
                                            </div>
                                            <div>
                                                <span class="fw-semibold text-dark"><?php echo htmlspecialchars($user['name']); ?></span>
                                                <?php if ((int)$user['id'] === (int)Session::getUserId()): ?>
                                                    <span class="badge bg-light text-dark border ms-1" style="font-size:10px;">You</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td>
                                        <span class="badge <?php 
                                            echo $user['role'] === 'Admin' ? 'bg-danger-subtle text-danger border border-danger-subtle' : 
                                                ($user['role'] === 'Manager' ? 'bg-primary-subtle text-primary border border-primary-subtle' : 'bg-secondary-subtle text-secondary border border-secondary-subtle'); 
                                        ?> px-2.5 py-1">
                                            <?php echo htmlspecialchars($user['role']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="status-badge <?php echo $user['status'] === 'Active' ? 'status-active' : 'status-disposed'; ?>">
                                            <?php echo htmlspecialchars($user['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="text-muted" style="font-size: 14px;">
                                            <?php echo htmlspecialchars(date('M d, Y', strtotime($user['created_at']))); ?>
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <a href="<?php echo BASE_URL; ?>/users/edit?id=<?php echo $user['id']; ?>" class="btn btn-sm btn-outline-secondary border-0 px-2 py-1" title="Edit User">
                                            <i class="bi bi-pencil-fill" style="font-size: 14px;"></i> Edit
                                        </a>
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
