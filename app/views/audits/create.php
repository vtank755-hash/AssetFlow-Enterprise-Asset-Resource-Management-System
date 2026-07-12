<?php
use App\Core\Session;
?>
<div class="container-fluid py-4">
    <div class="mb-4">
        <a href="<?php echo BASE_URL; ?>/audits" class="text-decoration-none text-muted"><i class="bi bi-arrow-left me-1"></i> Back to Audits Directory</a>
        <h1 class="h3 mt-2 mb-0 fw-bold">Start Stocktake Audit</h1>
        <p class="text-muted">Start a periodic verification cycle mapping physical inventory states (Verified, Missing, Damaged).</p>
    </div>

    <div class="row">
        <div class="col-lg-7">
            <?php if (isset($error) && $error !== ''): ?>
                <div class="alert alert-danger border-0 shadow-sm mb-4">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <form action="<?php echo BASE_URL; ?>/audits/create" method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo Session::generateCSRFToken(); ?>">

                        <div class="mb-3">
                            <label for="title" class="form-label fw-semibold">Audit Title / Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="title" name="title" required placeholder="e.g. Q3 2026 IT Hardware Stocktake">
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <label for="start_date" class="form-label fw-semibold">Start Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="start_date" name="start_date" required value="<?php echo date('Y-m-d'); ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="end_date" class="form-label fw-semibold">End Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="end_date" name="end_date" required value="<?php echo date('Y-m-d', strtotime('+7 days')); ?>">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="auditor_id" class="form-label fw-semibold">Assigned Auditor <span class="text-danger">*</span></label>
                            <select class="form-select" id="auditor_id" name="auditor_id" required>
                                <option value="" disabled selected>Select Auditor...</option>
                                <?php foreach ($auditors as $aud): ?>
                                    <option value="<?php echo $aud['id']; ?>"><?php echo htmlspecialchars($aud['name']); ?> (<?php echo htmlspecialchars($aud['role']); ?>)</option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">Choose the primary staff custodian responsible for physical tag matching.</div>
                        </div>

                        <hr class="my-4" style="background-color: var(--border-color);">
                        <h5 class="fw-bold mb-3 text-secondary">Optional Audit Scoping</h5>

                        <div class="row mb-4">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <label for="department_id" class="form-label fw-semibold">Department Scope</label>
                                <select class="form-select" id="department_id" name="department_id">
                                    <option value="">All Departments (No Filter)</option>
                                    <?php foreach ($departments as $dept): ?>
                                        <option value="<?php echo $dept['id']; ?>"><?php echo htmlspecialchars($dept['name']); ?> (<?php echo htmlspecialchars($dept['code']); ?>)</option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="location_scope" class="form-label fw-semibold">Location Scope</label>
                                <input type="text" class="form-control" id="location_scope" name="location_scope" placeholder="e.g. HQ - IT Storage Room">
                                <div class="form-text">Matching text snippet in asset location address.</div>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary px-4">Start Verification Cycle</button>
                            <a href="<?php echo BASE_URL; ?>/audits" class="btn btn-light px-3">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
