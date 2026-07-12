<?php
use App\Core\Session;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - <?php echo APP_NAME; ?></title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Custom Style Sheet -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/style.css">
</head>
<body class="auth-bg">
    <div class="auth-card">
        <div class="text-center mb-4">
            <div class="d-inline-flex align-items-center justify-content-center bg-primary text-white rounded-circle p-3 mb-3" style="width: 60px; height: 60px; background-color: var(--accent-color) !important;">
                <i class="bi bi-key-fill" style="font-size: 28px;"></i>
            </div>
            <h3 class="fw-bold mb-1">Reset Password</h3>
            <p class="text-muted">Create a secure new password</p>
        </div>

        <?php if (isset($error) && $error !== ''): ?>
            <div class="alert alert-danger border-0 shadow-sm mb-4">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> 
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form action="<?php echo BASE_URL; ?>/auth/reset-password" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo Session::generateCSRFToken(); ?>">
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token ?? ''); ?>">
            
            <div class="mb-3">
                <label for="password" class="form-label">New Password</label>
                <input type="password" class="form-control" id="password" name="password" required placeholder="••••••••" autofocus>
                <div class="form-text">Must be at least 8 characters long.</div>
            </div>

            <div class="mb-4">
                <label for="confirm_password" class="form-label">Confirm Password</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required placeholder="••••••••">
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2.5">Update Password</button>
        </form>
    </div>
</body>
</html>
