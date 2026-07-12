<?php
use App\Core\Session;
?>
<div class="container-fluid py-4">
    <div class="mb-4">
        <a href="<?php echo BASE_URL; ?>/departments" class="text-decoration-none text-muted"><i class="bi bi-arrow-left me-1"></i> Back to Departments Configuration</a>
        <h1 class="h3 mt-2 mb-0 fw-bold">Edit Department</h1>
        <p class="text-muted">Modify department specifications, code mapping, and profile properties.</p>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <?php if (isset($error) && $error !== ''): ?>
                <div class="alert alert-danger border-0 shadow-sm mb-4">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <form action="<?php echo BASE_URL; ?>/departments/edit?id=<?php echo $dept['id']; ?>" method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo Session::generateCSRFToken(); ?>">

                        <div class="mb-3">
                            <label for="name" class="form-label fw-semibold">Department Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" required placeholder="e.g. Human Resources" value="<?php echo htmlspecialchars($dept['name']); ?>">
                        </div>

                        <div class="mb-4">
                            <label for="code" class="form-label fw-semibold">Department Code <span class="text-danger">*</span></label>
                            <input type="text" class="form-control font-monospace" id="code" name="code" required placeholder="e.g. HR" maxlength="10" value="<?php echo htmlspecialchars($dept['code']); ?>">
                            <div class="form-text">Unique identification code up to 10 letters.</div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary px-4">Save Changes</button>
                            <a href="<?php echo BASE_URL; ?>/departments" class="btn btn-light px-3">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
