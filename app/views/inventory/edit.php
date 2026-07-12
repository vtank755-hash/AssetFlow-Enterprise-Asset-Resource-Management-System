<?php
use App\Core\Session;
?>
<div class="container-fluid py-4">
    <div class="mb-4">
        <a href="<?php echo BASE_URL; ?>/inventory" class="text-decoration-none text-muted"><i class="bi bi-arrow-left me-1"></i> Back to Inventory</a>
        <?php if ($action === 'adjust'): ?>
            <h1 class="h3 mt-2 mb-0 fw-bold">Stock Quantity Adjustment</h1>
            <p class="text-muted">Increment or decrement physical stock levels for item: <strong><?php echo htmlspecialchars($item['name']); ?></strong>.</p>
        <?php else: ?>
            <h1 class="h3 mt-2 mb-0 fw-bold">Edit Consumable Details</h1>
            <p class="text-muted">Modifying SKU tracking parameters for <strong><?php echo htmlspecialchars($item['name']); ?></strong>.</p>
        <?php endif; ?>
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
                    <?php if ($action === 'adjust'): ?>
                        <!-- Dedicated Responsive Stock Adjustment Form -->
                        <form action="<?php echo BASE_URL; ?>/inventory/edit?id=<?php echo $item['id']; ?>&action=adjust" method="POST">
                            <input type="hidden" name="csrf_token" value="<?php echo Session::generateCSRFToken(); ?>">

                            <div class="mb-3">
                                <label class="form-label fw-semibold text-muted font-monospace small">SKU IDENTIFIER</label>
                                <input type="text" class="form-control bg-light font-monospace" value="<?php echo htmlspecialchars($item['sku']); ?>" readonly>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold text-muted font-monospace small">CURRENT STOCK</label>
                                <input type="text" class="form-control bg-light fw-bold text-indigo" value="<?php echo htmlspecialchars($item['quantity']); ?> units" readonly>
                            </div>

                            <div class="mb-3">
                                <label for="quantity_change" class="form-label fw-semibold">Adjustment Quantity <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="quantity_change" name="quantity_change" placeholder="e.g. +10 or -5" required autofocus>
                                <div class="form-text">Input positive integers to add units, negative integers to subtract.</div>
                            </div>

                            <div class="mb-4">
                                <label for="reason" class="form-label fw-semibold">Reason for Adjustment</label>
                                <input type="text" class="form-control" id="reason" name="reason" placeholder="e.g. replenishment shipment, project release">
                            </div>

                            <button type="submit" class="btn btn-success px-4 py-2">Save Stock Level</button>
                        </form>
                    <?php else: ?>
                        <!-- Standard Item Metadata Editing Form -->
                        <form action="<?php echo BASE_URL; ?>/inventory/edit?id=<?php echo $item['id']; ?>&action=edit" method="POST">
                            <input type="hidden" name="csrf_token" value="<?php echo Session::generateCSRFToken(); ?>">

                            <div class="mb-3">
                                <label for="name" class="form-label fw-semibold">Item Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="name" name="name" required value="<?php echo htmlspecialchars($item['name']); ?>">
                            </div>

                            <div class="mb-3">
                                <label for="sku" class="form-label fw-semibold">SKU Identifier <span class="text-danger">*</span></label>
                                <input type="text" class="form-control font-monospace" id="sku" name="sku" required value="<?php echo htmlspecialchars($item['sku']); ?>">
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6 mb-3 mb-md-0">
                                    <label class="form-label fw-semibold text-muted">Current Quantity</label>
                                    <input type="text" class="form-control bg-light" value="<?php echo htmlspecialchars($item['quantity']); ?> units" readonly>
                                    <div class="form-text">To adjust inventory quantities, use the 'Adjust' button on the registry screen.</div>
                                </div>
                                <div class="col-md-6">
                                    <label for="min_threshold" class="form-label fw-semibold">Minimum Warning Threshold</label>
                                    <input type="number" min="0" class="form-control" id="min_threshold" name="min_threshold" value="<?php echo htmlspecialchars($item['min_threshold']); ?>">
                                    <div class="form-text">Triggers automated alert emails when stock drops below this.</div>
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-md-6 mb-3 mb-md-0">
                                    <label for="unit_price" class="form-label fw-semibold">Unit Price ($) <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" min="0" class="form-control" id="unit_price" name="unit_price" required value="<?php echo htmlspecialchars($item['unit_price']); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="location" class="form-label fw-semibold">Storage Location <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="location" name="location" required value="<?php echo htmlspecialchars($item['location']); ?>">
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary px-4 py-2">Save Changes</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
