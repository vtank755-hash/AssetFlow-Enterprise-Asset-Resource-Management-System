<?php
use App\Core\Session;
?>
<div class="container-fluid py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="no-print">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/dashboard" class="text-decoration-none">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/assets" class="text-decoration-none">Assets</a></li>
            <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/assets/view?id=<?php echo $asset['id']; ?>" class="text-decoration-none"><?php echo htmlspecialchars($asset['asset_tag']); ?></a></li>
            <li class="breadcrumb-item active" aria-current="page">Edit Asset</li>
        </ol>
    </nav>

    <div class="mb-4">
        <a href="<?php echo BASE_URL; ?>/assets/view?id=<?php echo $asset['id']; ?>" class="text-decoration-none text-muted"><i class="bi bi-arrow-left me-1"></i> Back to Asset Profile</a>
        <h1 class="h3 mt-2 mb-0 fw-bold">Edit Asset Details</h1>
        <p class="text-muted">Modifying records for asset tag <strong><?php echo htmlspecialchars($asset['asset_tag']); ?></strong>.</p>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <?php if (isset($error) && $error !== ''): ?>
                <div class="alert alert-danger border-0 shadow-sm mb-4">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <form action="<?php echo BASE_URL; ?>/assets/edit?id=<?php echo $asset['id']; ?>" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?php echo Session::generateCSRFToken(); ?>">

                        <div class="row mb-3">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <label class="form-label fw-semibold text-muted">Asset Tag</label>
                                <input type="text" class="form-control bg-light" value="<?php echo htmlspecialchars($asset['asset_tag']); ?>" readonly>
                                <div class="form-text">Asset tag IDs are locked once provisioned.</div>
                            </div>
                            <div class="col-md-6">
                                <label for="category_id" class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                                <select class="form-select mb-2" id="category_id" name="category_id">
                                    <option value="" disabled>Select Category...</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo $cat['id']; ?>" <?php echo $asset['category_id'] == $cat['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($cat['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="text" class="form-control form-control-sm" name="new_category_name" placeholder="Or change to a new category name...">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <label for="name" class="form-label fw-semibold">Asset Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" required placeholder="e.g. ThinkPad L14" value="<?php echo htmlspecialchars($asset['name']); ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="model" class="form-label fw-semibold">Model / Specs</label>
                                <input type="text" class="form-control" id="model" name="model" placeholder="e.g. 16GB RAM" value="<?php echo htmlspecialchars($asset['model'] ?? ''); ?>">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <label for="serial_number" class="form-label fw-semibold">Serial Number / VIN <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="serial_number" name="serial_number" required placeholder="e.g. L3-23984A" value="<?php echo htmlspecialchars($asset['serial_number']); ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="location" class="form-label fw-semibold">Physical Location <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="location" name="location" required placeholder="e.g. HQ - Closet" value="<?php echo htmlspecialchars($asset['location']); ?>">
                            </div>
                        </div>

                        <hr class="my-4" style="background-color: var(--border-color);">
                        <h5 class="fw-bold mb-3">Financials & Depreciation</h5>

                        <div class="row mb-4">
                            <div class="col-md-4 mb-3 mb-md-0">
                                <label for="purchase_date" class="form-label fw-semibold">Purchase Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="purchase_date" name="purchase_date" required value="<?php echo htmlspecialchars($asset['purchase_date']); ?>">
                            </div>
                            <div class="col-md-4 mb-3 mb-md-0">
                                <label for="purchase_cost" class="form-label fw-semibold">Purchase Cost (₹) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0" class="form-control" id="purchase_cost" name="purchase_cost" required value="<?php echo htmlspecialchars($asset['purchase_cost']); ?>">
                            </div>
                            <div class="col-md-4">
                                <label for="depreciation_rate" class="form-label fw-semibold">Annual Depreciation Rate (%) <span class="text-danger">*</span></label>
                                <input type="number" step="0.1" min="0" max="100" class="form-control" id="depreciation_rate" name="depreciation_rate" required value="<?php echo htmlspecialchars($asset['depreciation_rate']); ?>">
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-4 mb-3 mb-md-0">
                                <label for="status" class="form-label fw-semibold">Lifecycle Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="Available" <?php echo $asset['status'] === 'Available' ? 'selected' : ''; ?>>Available</option>
                                    <option value="Allocated" <?php echo $asset['status'] === 'Allocated' ? 'selected' : ''; ?>>Allocated</option>
                                    <option value="Reserved" <?php echo $asset['status'] === 'Reserved' ? 'selected' : ''; ?>>Reserved</option>
                                    <option value="Maintenance" <?php echo $asset['status'] === 'Maintenance' ? 'selected' : ''; ?>>Maintenance</option>
                                    <option value="Lost" <?php echo $asset['status'] === 'Lost' ? 'selected' : ''; ?>>Lost</option>
                                    <option value="Retired" <?php echo $asset['status'] === 'Retired' ? 'selected' : ''; ?>>Retired</option>
                                    <option value="Disposed" <?php echo $asset['status'] === 'Disposed' ? 'selected' : ''; ?>>Disposed</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3 mb-md-0">
                                <label for="photo" class="form-label fw-semibold">Update Photo</label>
                                <input type="file" class="form-control" id="photo" name="photo" accept="image/*">
                            </div>
                            <div class="col-md-4">
                                <label for="documents" class="form-label fw-semibold">Upload Documents</label>
                                <input type="file" class="form-control" id="documents" name="documents[]" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary px-4 py-2">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
