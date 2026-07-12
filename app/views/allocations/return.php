<?php
use App\Core\Session;
?>
<div class="container-fluid py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="no-print">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/dashboard" class="text-decoration-none">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/allocations" class="text-decoration-none">Allocations</a></li>
            <li class="breadcrumb-item active" aria-current="page">Confirm Check-in</li>
        </ol>
    </nav>

    <div class="mb-4">
        <a href="<?php echo BASE_URL; ?>/allocations" class="text-decoration-none text-muted"><i class="bi bi-arrow-left me-1"></i> Back to Allocations</a>
        <h1 class="h3 mt-2 mb-0 fw-bold">Confirm Asset Return (Check-in)</h1>
        <p class="text-muted">Perform physical condition check and return this resource back to the available pool.</p>
    </div>

    <!-- Alert Notification errors -->
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger border-0 shadow-sm mb-4" style="max-width: 600px;">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <?php echo htmlspecialchars($error); ?>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-lg-6">
            <!-- Allocation Info Summary -->
            <div class="card shadow-sm border-0 mb-4 bg-light">
                <div class="card-body p-4">
                    <h6 class="fw-bold text-indigo mb-3"><i class="bi bi-info-circle me-1.5"></i>Allocation Summary Details</h6>
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <span class="small d-block text-secondary">Asset Resource</span>
                            <span class="fw-semibold text-dark"><?php echo htmlspecialchars($allocation['asset_name']); ?></span>
                        </div>
                        <div class="col-sm-6">
                            <span class="small d-block text-secondary">Asset Tag Code</span>
                            <span class="font-monospace fw-semibold text-dark"><?php echo htmlspecialchars($allocation['asset_tag']); ?></span>
                        </div>
                        <div class="col-sm-6">
                            <span class="small d-block text-secondary">Assigned Custodian</span>
                            <span class="fw-semibold text-dark"><?php echo htmlspecialchars($allocation['user_name']); ?></span>
                        </div>
                        <div class="col-sm-6">
                            <span class="small d-block text-secondary">Check-out Date</span>
                            <span class="fw-semibold text-dark"><?php echo htmlspecialchars(date('M d, Y', strtotime($allocation['allocated_date']))); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Return Submission Form -->
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <form action="<?php echo BASE_URL; ?>/allocations/return" method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo Session::generateCSRFToken(); ?>">
                        <input type="hidden" name="id" value="<?php echo $allocation['id']; ?>">

                        <div class="mb-4">
                            <label for="return_notes" class="form-label fw-semibold text-dark">Return Notes / Condition Remarks</label>
                            <textarea class="form-control" id="return_notes" name="return_notes" rows="4" placeholder="Specify physical item condition (e.g. Good condition, normal screen scratches, keyboard wear)..." required></textarea>
                            <div class="form-text">Notes will be recorded in the permanent allocation and audit history logs.</div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success px-4 py-2"><i class="bi bi-box-arrow-in-down me-1.5"></i>Confirm Return Check-in</button>
                            <a href="<?php echo BASE_URL; ?>/allocations" class="btn btn-light px-3 py-2">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
