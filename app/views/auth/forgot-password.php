<?php
use App\Core\Session;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - <?php echo APP_NAME; ?></title>
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
                <i class="bi bi-shield-lock" style="font-size: 28px;"></i>
            </div>
            <h3 class="fw-bold mb-1">Recover Password</h3>
            <p class="text-muted">Enter email to receive recovery link</p>
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

        <form action="<?php echo BASE_URL; ?>/auth/forgot-password" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo Session::generateCSRFToken(); ?>">
            
            <div class="mb-4">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" class="form-control" id="email" name="email" required placeholder="name@company.com" autofocus>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2.5 mb-3">Send Recovery Link</button>
            
            <div class="text-center">
                <a href="<?php echo BASE_URL; ?>/auth/login" class="text-decoration-none text-muted" style="font-size: 14px;"><i class="bi bi-arrow-left me-1"></i> Back to Login</a>
            </div>
        </form>
    </div>
</body>
</html>
