<?php
use App\Core\Session;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - <?php echo APP_NAME; ?></title>
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
                <i class="bi bi-cpu" style="font-size: 28px;"></i>
            </div>
            <h3 class="fw-bold mb-1"><?php echo APP_NAME; ?></h3>
            <p class="text-muted">Enterprise Resource Management</p>
        </div>

        <?php if (isset($error) && $error !== ''): ?>
            <div class="alert alert-danger border-0 shadow-sm mb-4">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> 
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if (Session::hasFlash('success')): ?>
            <div class="alert alert-success border-0 shadow-sm mb-4">
                <i class="bi bi-check-circle-fill me-2"></i> 
                <?php echo Session::getFlash('success'); ?>
            </div>
        <?php endif; ?>

        <form action="<?php echo BASE_URL; ?>/auth/login" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo Session::generateCSRFToken(); ?>">
            
            <div class="mb-3">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" class="form-control" id="email" name="email" required placeholder="name@company.com" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" autofocus>
            </div>
            
            <div class="mb-4">
                <div class="d-flex justify-content-between mb-1">
                    <label for="password" class="form-label mb-0">Password</label>
                    <a href="<?php echo BASE_URL; ?>/auth/forgot-password" class="text-decoration-none text-muted" style="font-size: 13px;">Forgot password?</a>
                </div>
                <input type="password" class="form-control" id="password" name="password" required placeholder="••••••••">
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2.5">Sign In</button>
        </form>

        <div class="text-center mt-4 pt-3 border-top">
            <span class="text-muted small">New to AssetFlow?</span>
            <a href="<?php echo BASE_URL; ?>/auth/register" class="text-decoration-none small ms-1" style="color: var(--accent-color); font-weight: 500;">Create an account</a>
        </div>
    </div>
</body>
</html>
