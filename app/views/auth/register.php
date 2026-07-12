<?php
use App\Core\Session;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account - <?php echo APP_NAME; ?></title>
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
            <h3 class="fw-bold mb-1 text-dark">AssetFlow – register</h3>
            <p class="text-muted small">Create an employee account to request checkouts.</p>
        </div>

        <?php if (isset($error) && $error !== ''): ?>
            <div class="alert alert-danger border-0 shadow-sm mb-4">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> 
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form action="<?php echo BASE_URL; ?>/auth/register" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo Session::generateCSRFToken(); ?>">
            
            <div class="mb-3">
                <label for="name" class="form-label text-secondary fw-semibold" style="font-size: 14px;">Full Name</label>
                <input type="text" class="form-control border-secondary-subtle" id="name" name="name" required placeholder="e.g. John Doe" value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>" autofocus>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label text-secondary fw-semibold" style="font-size: 14px;">Email Address</label>
                <input type="email" class="form-control border-secondary-subtle" id="email" name="email" required placeholder="name@company.com" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
            </div>
            
            <div class="mb-3">
                <label for="password" class="form-label text-secondary fw-semibold" style="font-size: 14px;">Password</label>
                <input type="password" class="form-control border-secondary-subtle" id="password" name="password" required placeholder="At least 8 characters">
            </div>

            <div class="mb-4">
                <label for="confirm_password" class="form-label text-secondary fw-semibold" style="font-size: 14px;">Confirm Password</label>
                <input type="password" class="form-control border-secondary-subtle" id="confirm_password" name="confirm_password" required placeholder="**********">
            </div>

            <button type="submit" class="btn btn-primary w-100 py-2.5 fw-semibold mb-3">Sign Up</button>
        </form>

        <hr style="border-top: 1px solid #e2e8f0; margin: 1.5rem 0;">

        <div class="text-center mt-3">
            <span class="text-muted small">Already have an account?</span>
            <a href="<?php echo BASE_URL; ?>/auth/login" class="text-decoration-none small ms-1 text-primary fw-semibold">Sign in instead</a>
        </div>
    </div>
</body>
</html>
