<?php
use App\Core\Session;
?>
<div class="container-fluid py-4">
    <!-- Breadcrumbs -->
    <nav aria-label="breadcrumb" class="no-print">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/dashboard" class="text-decoration-none">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Account Settings</li>
        </ol>
    </nav>

    <div class="mb-4">
        <h1 class="h3 mb-0 fw-bold">Account Settings</h1>
        <p class="text-muted">Manage your personal profile information, security password credentials, and system preferences.</p>
    </div>

    <!-- Alert Notifications -->
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger border-0 shadow-sm mb-4">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success border-0 shadow-sm mb-4">
            <i class="bi bi-check-circle-fill me-2"></i>
            <?php echo htmlspecialchars($success); ?>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- LEFT COLUMN: Profile Overview & Picture -->
        <div class="col-lg-4 col-md-12">
            <div class="card shadow-sm border-0 mb-4 text-center">
                <div class="card-body p-4">
                    <!-- Profile Avatar Display -->
                    <div class="mb-3 d-inline-block position-relative">
                        <?php if (!empty($user['profile_picture'])): ?>
                            <img src="<?php echo BASE_URL; ?>/uploads/profile_pics/<?php echo htmlspecialchars($user['profile_picture']); ?>" class="rounded-circle border shadow-sm object-fit-cover" style="width: 130px; height: 130px;" alt="Avatar">
                        <?php else: ?>
                            <div class="rounded-circle bg-indigo text-white d-flex align-items-center justify-content-center border shadow-sm mx-auto fw-bold" style="width: 130px; height: 130px; font-size: 42px;">
                                <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <h4 class="fw-bold text-dark mb-1"><?php echo htmlspecialchars($user['name']); ?></h4>
                    <p class="text-muted small mb-2"><?php echo htmlspecialchars($user['email']); ?></p>
                    
                    <div class="d-flex justify-content-center gap-1.5 mb-3">
                        <span class="badge bg-indigo-subtle text-indigo font-monospace px-2.5 py-1.5"><?php echo htmlspecialchars($user['role']); ?></span>
                        <span class="badge bg-secondary-subtle text-secondary px-2.5 py-1.5">ID: <?php echo $user['id']; ?></span>
                    </div>

                    <hr class="my-3" style="background-color: var(--border-color);">

                    <div class="text-start">
                        <span class="small d-block text-secondary"><strong>Account Status:</strong> <span class="text-success fw-bold">Active</span></span>
                        <span class="small d-block text-secondary"><strong>Member Since:</strong> <?php echo htmlspecialchars(date('M d, Y', strtotime($user['created_at']))); ?></span>
                    </div>
                </div>
            </div>

            <!-- Upload Avatar Form -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold text-dark mb-0"><i class="bi bi-image me-2"></i>Change Profile Picture</h6>
                </div>
                <div class="card-body p-4">
                    <form action="<?php echo BASE_URL; ?>/profile" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?php echo Session::generateCSRFToken(); ?>">
                        <input type="hidden" name="action" value="upload_avatar">

                        <div class="mb-3">
                            <label for="profile_pic" class="form-label small fw-bold">Select Image File</label>
                            <input class="form-control form-control-sm" type="file" id="profile_pic" name="profile_pic" accept="image/*" required>
                            <div class="form-text small">Accepted formats: JPG, PNG, GIF. Max size 2MB.</div>
                        </div>

                        <button type="submit" class="btn btn-sm btn-primary w-100 py-1.5">Upload Picture</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- RIGHT COLUMN: Update Forms -->
        <div class="col-lg-8 col-md-12">
            <!-- Update Profile Details Card -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold text-dark mb-0"><i class="bi bi-person-gear me-2"></i>Update Personal Details</h6>
                </div>
                <div class="card-body p-4">
                    <form action="<?php echo BASE_URL; ?>/profile" method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo Session::generateCSRFToken(); ?>">
                        <input type="hidden" name="action" value="update_profile">

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" required value="<?php echo htmlspecialchars($user['name']); ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email" name="email" required value="<?php echo htmlspecialchars($user['email']); ?>">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-sm btn-primary px-4 py-2 mt-4">Save Profile Details</button>
                    </form>
                </div>
            </div>

            <!-- Change Password Card -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold text-dark mb-0"><i class="bi bi-shield-lock me-2"></i>Change Security Password</h6>
                </div>
                <div class="card-body p-4">
                    <form action="<?php echo BASE_URL; ?>/profile" method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo Session::generateCSRFToken(); ?>">
                        <input type="hidden" name="action" value="change_password">

                        <div class="mb-3">
                            <label for="current_password" class="form-label fw-semibold">Current Password <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="current_password" name="current_password" required placeholder="Enter current login password">
                        </div>

                        <div class="row g-3 mb-2">
                            <div class="col-md-6">
                                <label for="new_password" class="form-label fw-semibold">New Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="new_password" name="new_password" required placeholder="Min 8 characters" minlength="8">
                            </div>
                            <div class="col-md-6">
                                <label for="confirm_password" class="form-label fw-semibold">Confirm New Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required placeholder="Repeat new password" minlength="8">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-sm btn-primary px-4 py-2 mt-4">Change Password</button>
                    </form>
                </div>
            </div>

            <!-- Preferences Card -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold text-dark mb-0"><i class="bi bi-sliders me-2"></i>System Preferences</h6>
                </div>
                <div class="card-body p-4">
                    <form action="<?php echo BASE_URL; ?>/profile" method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo Session::generateCSRFToken(); ?>">
                        <input type="hidden" name="action" value="update_preferences">

                        <div class="mb-3">
                            <label for="theme" class="form-label fw-semibold">Interface Theme Style</label>
                            <select class="form-select" id="theme" name="theme">
                                <option value="light" <?php echo ($preferences['theme'] ?? 'light') === 'light' ? 'selected' : ''; ?>>Light Theme (Classic)</option>
                                <option value="dark" <?php echo ($preferences['theme'] ?? 'light') === 'dark' ? 'selected' : ''; ?>>Dark Theme (Midnight)</option>
                            </select>
                            <div class="form-text small">Customize the color accents of the enterprise dashboard.</div>
                        </div>

                        <div class="mb-2">
                            <label class="form-label fw-semibold d-block">Notifications Alerts</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" role="switch" id="email_alerts" name="email_alerts" value="yes" <?php echo ($preferences['email_alerts'] ?? 'yes') === 'yes' ? 'checked' : ''; ?>>
                                <label class="form-check-input-label small text-muted" for="email_alerts">Receive email confirmation copies for system allocations and tickets.</label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-sm btn-primary px-4 py-2 mt-4">Save Preferences</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
