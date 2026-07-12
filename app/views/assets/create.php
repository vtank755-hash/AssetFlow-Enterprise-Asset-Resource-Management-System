<?php
use App\Core\Session;
?>
<div class="container-fluid py-4">
    <div class="mb-4">
        <a href="<?php echo BASE_URL; ?>/assets" class="text-decoration-none text-muted"><i class="bi bi-arrow-left me-1"></i> Back to Assets Directory</a>
        <h1 class="h3 mt-2 mb-0 fw-bold">Register New Asset</h1>
        <p class="text-muted">Register a hardware machine, tooling, vehicle, or licensed soft asset into system tracking.</p>
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
                    <form action="<?php echo BASE_URL; ?>/assets/create" method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo Session::generateCSRFToken(); ?>">

                        <div class="row mb-3">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <label for="asset_tag" class="form-label fw-semibold">Asset Tag <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="asset_tag" name="asset_tag" required placeholder="e.g. AST-2026-0001" value="<?php echo htmlspecialchars($suggestedTag); ?>">
                                <div class="form-text">System generated a suggested unique tag pattern.</div>
                            </div>
                            <div class="col-md-6">
                                <label for="category_id" class="form-label fw-semibold">Category <span class="text-danger">*</span></label>
                                <select class="form-select" id="category_id" name="category_id" required>
                                    <option value="" disabled selected>Select Category...</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <label for="name" class="form-label fw-semibold">Asset Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" required placeholder="e.g. ThinkPad L14 Gen 4">
                            </div>
                            <div class="col-md-6">
                                <label for="model" class="form-label fw-semibold">Model / Specs</label>
                                <input type="text" class="form-control" id="model" name="model" placeholder="e.g. Ryzen 7, 16GB, 512GB SSD">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <label for="serial_number" class="form-label fw-semibold">Serial Number / VIN <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="serial_number" name="serial_number" required placeholder="e.g. L3-23984A">
                            </div>
                            <div class="col-md-6">
                                <label for="location" class="form-label fw-semibold">Physical Location <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="location" name="location" required placeholder="e.g. HQ - IT Storage Closet">
                            </div>
                        </div>

                        <hr class="my-4" style="background-color: var(--border-color);">
                        <h5 class="fw-bold mb-3">Financials & Depreciation</h5>

                        <div class="row mb-4">
                            <div class="col-md-4 mb-3 mb-md-0">
                                <label for="purchase_date" class="form-label fw-semibold">Purchase Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="purchase_date" name="purchase_date" required value="<?php echo date('Y-m-d'); ?>">
                            </div>
                            <div class="col-md-4 mb-3 mb-md-0">
                                <label for="purchase_cost" class="form-label fw-semibold">Purchase Cost ($) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0" class="form-control" id="purchase_cost" name="purchase_cost" required placeholder="0.00">
                            </div>
                            <div class="col-md-4">
                                <label for="depreciation_rate" class="form-label fw-semibold">Annual Depreciation Rate (%) <span class="text-danger">*</span></label>
                                <input type="number" step="0.1" min="0" max="100" class="form-control" id="depreciation_rate" name="depreciation_rate" required placeholder="e.g. 20.0">
                                <div class="form-text">Used for straight-line calculations.</div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <label for="status" class="form-label fw-semibold">Initial Status</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="Available" selected>Available</option>
                                    <option value="Allocated">Allocated</option>
                                    <option value="Maintenance">Maintenance</option>
                                </select>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary px-4 py-2">Register Asset</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
