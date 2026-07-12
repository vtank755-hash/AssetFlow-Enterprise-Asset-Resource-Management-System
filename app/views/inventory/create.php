<?php
use App\Core\Session;
?>
<div class="container-fluid py-4">
    <div class="mb-4">
        <a href="<?php echo BASE_URL; ?>/inventory" class="text-decoration-none text-muted"><i class="bi bi-arrow-left me-1"></i> Back to Inventory</a>
        <h1 class="h3 mt-2 mb-0 fw-bold">Register Consumable Stock</h1>
        <p class="text-muted">Register a consumable supply, part, or resource SKU into tracking.</p>
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
                    <form action="<?php echo BASE_URL; ?>/inventory/create" method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo Session::generateCSRFToken(); ?>">

                        <div class="mb-3">
                            <label for="name" class="form-label fw-semibold">Item Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" required placeholder="e.g. AA Batteries 24-pack" autofocus>
                        </div>

                        <div class="mb-3">
                            <label for="sku" class="form-label fw-semibold">SKU Identifier <span class="text-danger">*</span></label>
                            <input type="text" class="form-control font-monospace" id="sku" name="sku" required placeholder="e.g. BAT-AA-24">
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <label for="quantity" class="form-label fw-semibold">Initial Quantity</label>
                                <input type="number" min="0" class="form-control" id="quantity" name="quantity" value="0">
                            </div>
                            <div class="col-md-6">
                                <label for="min_threshold" class="form-label fw-semibold">Minimum Warning Threshold</label>
                                <input type="number" min="0" class="form-control" id="min_threshold" name="min_threshold" value="5">
                                <div class="form-text">Triggers automated alert emails when stock drops below this.</div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <label for="unit_price" class="form-label fw-semibold">Unit Price (₹) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="0" class="form-control" id="unit_price" name="unit_price" required placeholder="0.00">
                            </div>
                            <div class="col-md-6">
                                <label for="location" class="form-label fw-semibold">Storage Location <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="location" name="location" required placeholder="e.g. HQ - Supply Room Closet B">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary px-4 py-2 mt-3">Register Stock Item</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
