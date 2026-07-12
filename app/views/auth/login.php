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
    <div class="auth-card" style="background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 2.5rem; max-width: 440px; box-shadow: 0 10px 30px rgba(0,0,0,0.04);">
        <div class="text-center mb-4">
            <div class="d-inline-flex align-items-center justify-content-center bg-dark text-white rounded-circle p-3 mb-3" style="width: 60px; height: 60px; border: 1px solid #334155; background-color: #0f172a !important;">
                <span class="fw-bold font-monospace" style="font-size: 18px; letter-spacing: 0.5px;">AF</span>
            </div>
            <h3 class="fw-bold mb-1 text-dark">AssetFlow – login</h3>
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
                <label for="email" class="form-label text-secondary fw-semibold" style="font-size: 14px;">Email</label>
                <input type="email" class="form-control border-secondary-subtle" id="email" name="email" required placeholder="name@company.com" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>" autofocus>
            </div>
            
            <div class="mb-4">
                <label for="password" class="form-label text-secondary fw-semibold" style="font-size: 14px;">Password</label>
                <input type="password" class="form-control border-secondary-subtle" id="password" name="password" required placeholder="**********">
                <div class="text-end mt-1.5">
                    <a href="<?php echo BASE_URL; ?>/auth/forgot-password" class="text-decoration-none text-muted small">Forgot password</a>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2.5 fw-semibold mb-3">Sign In</button>
        </form>

        <hr style="border-top: 1px solid #e2e8f0; margin: 1.5rem 0;">

        <div class="mt-3">
            <span class="text-dark d-block fw-semibold mb-2" style="font-size: 14px;">New here?</span>
            <div class="p-3 mb-3 text-start" style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; color: #475569;">
                <span class="small d-block text-dark fw-semibold">Sign up creates an employee account</span>
                <span class="small d-block text-muted mt-0.5" style="font-size: 11.5px;">admin roles assigned later</span>
            </div>
            <a href="<?php echo BASE_URL; ?>/auth/register" class="btn btn-outline-dark w-100 py-2.5 fw-semibold">Create Account</a>
        </div>
    </div>
</body>
</html>
