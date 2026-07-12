<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 Forbidden - AssetFlow</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Custom Style Sheet -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/css/style.css">
</head>
<body class="auth-bg">
    <div class="auth-card text-center" style="max-width: 500px;">
        <div class="d-inline-flex align-items-center justify-content-center bg-danger-subtle text-danger rounded-circle p-4 mb-3" style="width: 80px; height: 80px;">
            <i class="bi bi-shield-slash fs-1"></i>
        </div>
        <h1 class="fw-bold mb-2 display-6">403</h1>
        <h3 class="fw-semibold mb-3">Access Forbidden</h3>
        <p class="text-muted mb-4">You do not have the necessary permission levels or administrative role privileges required to access this resource or execute this action.</p>
        
        <div class="d-grid gap-2">
            <a href="<?php echo BASE_URL; ?>/dashboard" class="btn btn-primary py-2.5">
                <i class="bi bi-house me-1"></i> Back to Dashboard
            </a>
        </div>
    </div>
</body>
</html>
