<?php
use App\Core\Session;
?>
<div class="container-fluid py-4">
    <div class="mb-4">
        <?php if ($is_profile): ?>
            <h1 class="h3 mb-0 fw-bold">My Account Profile</h1>
            <p class="text-muted">Manage your personal account details and update security password.</p>
        <?php else: ?>
            <a href="<?php echo BASE_URL; ?>/users" class="text-decoration-none text-muted"><i class="bi bi-arrow-left me-1"></i> Back to User List</a>
            <h1 class="h3 mt-2 mb-0 fw-bold">Edit User Account</h1>
            <p class="text-muted">Update contact details, permissions, and roles for <strong><?php echo htmlspecialchars($user['name']); ?></strong>.</p>
        <?php endif; ?>
    </div>

    <div class="row">
        <div class="col-lg-8 col-xl-6">
            <?php if (isset($error) && $error !== ''): ?>
                <div class="alert alert-danger border-0 shadow-sm mb-4">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <form action="<?php echo $is_profile ? BASE_URL . '/profile' : BASE_URL . '/users/edit?id=' . $user['id']; ?>" method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo Session::generateCSRFToken(); ?>">

                        <div class="mb-3">
                            <label for="name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="name" name="name" required placeholder="John Doe" value="<?php echo htmlspecialchars($user['name']); ?>" autofocus>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" required placeholder="john.doe@company.com" value="<?php echo htmlspecialchars($user['email']); ?>">
                        </div>

                        <hr class="my-4" style="background-color: var(--border-color);">
                        
                        <h5 class="fw-bold mb-3">Security & Password</h5>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label"><?php echo $is_profile ? 'New Password' : 'Reset Password'; ?></label>
                            <input type="password" class="form-control" id="password" name="password" placeholder="••••••••">
                            <div class="form-text">Leave blank to keep the current password.</div>
                        </div>

                        <?php if ($is_profile): ?>
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="••••••••">
                            </div>
                        <?php endif; ?>

                        <?php if (!$is_profile): ?>
                            <hr class="my-4" style="background-color: var(--border-color);">
                            <h5 class="fw-bold mb-3">Roles & Access Restrictions</h5>
                            
                            <div class="row mb-4">
                                <div class="col-md-6 mb-3 mb-md-0">
                                    <label for="role" class="form-label">Role</label>
                                    <select class="form-select" id="role" name="role">
                                        <option value="Staff" <?php echo $user['role'] === 'Staff' ? 'selected' : ''; ?>>Staff (Standard view & requests)</option>
                                        <option value="Manager" <?php echo $user['role'] === 'Manager' ? 'selected' : ''; ?>>Manager (Edit assets & allocate)</option>
                                        <option value="Admin" <?php echo $user['role'] === 'Admin' ? 'selected' : ''; ?>>Administrator (Full control)</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="status" class="form-label">Account Status</label>
                                    <select class="form-select" id="status" name="status">
                                        <option value="Active" <?php echo $user['status'] === 'Active' ? 'selected' : ''; ?>>Active</option>
                                        <option value="Inactive" <?php echo $user['status'] === 'Inactive' ? 'selected' : ''; ?>>Inactive / Suspended</option>
                                    </select>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <button type="submit" class="btn btn-primary px-4 py-2">
                                <?php echo $is_profile ? 'Update Profile' : 'Save Changes'; ?>
                            </button>
                            <?php if (!$is_profile && (int)$user['id'] !== (int)Session::getUserId()): ?>
                                <!-- Display visual hint that status deactivation suspends account -->
                                <span class="text-muted" style="font-size: 13px;"><i class="bi bi-info-circle me-1"></i>Deactivation revokes dashboard logins.</span>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
