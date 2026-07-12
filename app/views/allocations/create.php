<?php
use App\Core\Session;
?>
<div class="container-fluid py-4">
    <div class="mb-4">
        <a href="<?php echo BASE_URL; ?>/allocations" class="text-decoration-none text-muted"><i class="bi bi-arrow-left me-1"></i> Back to Allocations</a>
        <h1 class="h3 mt-2 mb-0 fw-bold">Check-Out Asset Resource</h1>
        <p class="text-muted">Assign custody of an available equipment item to a team member.</p>
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
                    <form action="<?php echo BASE_URL; ?>/allocations/create" method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo Session::generateCSRFToken(); ?>">

                        <div class="mb-3">
                            <label for="asset_id" class="form-label fw-semibold">Asset to Check-Out <span class="text-danger">*</span></label>
                            <select class="form-select" id="asset_id" name="asset_id" required>
                                <option value="" disabled selected>Select Available Asset...</option>
                                <?php foreach ($assets as $asset): ?>
                                    <option value="<?php echo $asset['id']; ?>" <?php echo $preSelectedAssetId == $asset['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($asset['name'] . ' (' . $asset['asset_tag'] . ')'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">Only assets with status 'Available' are shown.</div>
                        </div>

                        <div class="mb-3">
                            <label for="user_id" class="form-label fw-semibold">Assign Custody To (User) <span class="text-danger">*</span></label>
                            <select class="form-select" id="user_id" name="user_id" required>
                                <option value="" disabled selected>Select User...</option>
                                <?php foreach ($users as $user): ?>
                                    <option value="<?php echo $user['id']; ?>">
                                        <?php echo htmlspecialchars($user['name'] . ' (' . $user['email'] . ')'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="due_date" class="form-label fw-semibold">Expected Return Due Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="due_date" name="due_date" required value="<?php echo date('Y-m-d', strtotime('+3 months')); ?>">
                            <div class="form-text">System defaults to 3 months checkout window. Adjust as needed.</div>
                        </div>

                        <div class="mb-4">
                            <label for="notes" class="form-label fw-semibold">Checkout Notes / Details</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Describe the reason for assignment, asset physical status, or specific guidelines..."></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary px-4 py-2">Check-Out Resource</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
