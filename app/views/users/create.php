<?php
use App\Core\Session;
?>
<div class="container-fluid py-4">
    <div class="mb-4">
        <a href="<?php echo BASE_URL; ?>/users" class="text-decoration-none text-muted"><i class="bi bi-arrow-left me-1"></i> Back to User List</a>
        <h1 class="h3 mt-2 mb-0 fw-bold">Create New User</h1>
        <p class="text-muted">Register a new user account with specific role-based permissions.</p>
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
                    <form action="<?php echo BASE_URL; ?>/users/create" method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo Session::generateCSRFToken(); ?>">

                        <div class="mb-3">
                            <label for="name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="name" name="name" required placeholder="John Doe" autofocus>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="email" name="email" required placeholder="john.doe@company.com">
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Initial Password</label>
                            <input type="password" class="form-control" id="password" name="password" required placeholder="••••••••">
                            <div class="form-text">Choose a secure initial password for the user.</div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <label for="role" class="form-label">Role</label>
                                <select class="form-select" id="role" name="role">
                                    <option value="Staff">Staff (Standard view & requests)</option>
                                    <option value="Manager">Manager (Edit assets & allocate)</option>
                                    <option value="Admin">Administrator (Full control)</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="status" class="form-label">Account Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="Active">Active</option>
                                    <option value="Inactive">Inactive / Suspended</option>
                                </select>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary px-4 py-2">Create User Account</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
