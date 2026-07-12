<?php
use App\Core\Session;
?>
<div class="container-fluid py-4">
    <div class="mb-4">
        <a href="<?php echo BASE_URL; ?>/maintenance" class="text-decoration-none text-muted"><i class="bi bi-arrow-left me-1"></i> Back to Maintenance Registry</a>
        <h1 class="h3 mt-2 mb-0 fw-bold">Schedule Service Order</h1>
        <p class="text-muted">Put an asset tag into maintenance state and record servicing details.</p>
    </div>

    <div class="row">
        <div class="col-lg-8 col-xl-6">
            <?php if (isset($error) && $error !== ''): ?>
                <div class="alert alert-danger border-0 shadow-sm mb-4">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <form action="<?php echo BASE_URL; ?>/maintenance/create" method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo Session::generateCSRFToken(); ?>">

                        <div class="mb-3">
                            <label for="asset_id" class="form-label fw-semibold">Target Asset <span class="text-danger">*</span></label>
                            <select class="form-select" id="asset_id" name="asset_id" required>
                                <option value="" disabled selected>Select Asset to Service...</option>
                                <?php foreach ($assets as $asset): ?>
                                    <option value="<?php echo $asset['id']; ?>" <?php echo $preSelectedAssetId == $asset['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($asset['name'] . ' (' . $asset['asset_tag'] . ')'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">System will change asset lifecycle state to 'Maintenance' upon saving.</div>
                        </div>

                        <div class="mb-3">
                            <label for="title" class="form-label fw-semibold">Service Title / Reason <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="title" name="title" required placeholder="e.g. Broken Display Screen, annual servicing">
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label fw-semibold">Detailed Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3" placeholder="Provide description of damage, errors or standard parts required..."></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="scheduled_date" class="form-label fw-semibold">Scheduled Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="scheduled_date" name="scheduled_date" required value="<?php echo date('Y-m-d'); ?>">
                        </div>

                        <div class="mb-3">
                            <label for="performed_by" class="form-label fw-semibold">Service Provider / Technician</label>
                            <input type="text" class="form-control" id="performed_by" name="performed_by" placeholder="e.g. IT Department, Apple Store HQ, local garage">
                        </div>

                        <div class="mb-4">
                            <label for="notes" class="form-label fw-semibold font-monospace">Planning Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="2" placeholder="Internal remarks..."></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary px-4 py-2">Schedule Maintenance</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
