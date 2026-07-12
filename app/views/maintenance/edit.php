<?php
use App\Core\Session;
?>
<div class="container-fluid py-4">
    <div class="mb-4">
        <a href="<?php echo BASE_URL; ?>/maintenance" class="text-decoration-none text-muted"><i class="bi bi-arrow-left me-1"></i> Back to Maintenance Registry</a>
        <h1 class="h3 mt-2 mb-0 fw-bold">Update Work Order</h1>
        <p class="text-muted">Edit parameters, log expenditure and close service order for asset <strong><?php echo htmlspecialchars($order['asset_tag']); ?></strong>.</p>
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
                    <form action="<?php echo BASE_URL; ?>/maintenance/edit?id=<?php echo $order['id']; ?>" method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo Session::generateCSRFToken(); ?>">

                        <div class="row mb-3">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <label class="form-label fw-semibold text-muted">Asset Tag</label>
                                <input type="text" class="form-control bg-light" value="<?php echo htmlspecialchars($order['asset_tag']); ?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-muted">Asset Name</label>
                                <input type="text" class="form-control bg-light" value="<?php echo htmlspecialchars($order['asset_name']); ?>" readonly>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="title" class="form-label fw-semibold">Service Title / Reason <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="title" name="title" required value="<?php echo htmlspecialchars($order['title']); ?>">
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label fw-semibold">Detailed Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"><?php echo htmlspecialchars($order['description'] ?? ''); ?></textarea>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <label for="scheduled_date" class="form-label fw-semibold">Scheduled Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="scheduled_date" name="scheduled_date" required value="<?php echo htmlspecialchars($order['scheduled_date']); ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="completion_date" class="form-label fw-semibold">Completion Date</label>
                                <input type="date" class="form-control" id="completion_date" name="completion_date" value="<?php echo htmlspecialchars($order['completion_date'] ?? ''); ?>">
                                <div class="form-text">Fill in once work order is resolved.</div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <label for="cost" class="form-label fw-semibold">Service Cost (₹)</label>
                                <input type="number" step="0.01" min="0" class="form-control" id="cost" name="cost" required value="<?php echo htmlspecialchars($order['cost']); ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="status" class="form-label fw-semibold">Work Order Status <span class="text-danger">*</span></label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="Pending" <?php echo $order['status'] === 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                    <option value="Approved" <?php echo $order['status'] === 'Approved' ? 'selected' : ''; ?>>Approved</option>
                                    <option value="Rejected" <?php echo $order['status'] === 'Rejected' ? 'selected' : ''; ?>>Rejected</option>
                                    <option value="Technician Assigned" <?php echo $order['status'] === 'Technician Assigned' ? 'selected' : ''; ?>>Technician Assigned</option>
                                    <option value="In Progress" <?php echo $order['status'] === 'In Progress' ? 'selected' : ''; ?>>In Progress</option>
                                    <option value="Resolved" <?php echo $order['status'] === 'Resolved' ? 'selected' : ''; ?>>Resolved</option>
                                </select>
                                <div class="form-text">Resolving/rejecting the order sets the asset state back to 'Available'.</div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="performed_by" class="form-label fw-semibold">Service Provider / Technician</label>
                            <input type="text" class="form-control" id="performed_by" name="performed_by" value="<?php echo htmlspecialchars($order['performed_by'] ?? ''); ?>">
                        </div>

                        <div class="mb-4">
                            <label for="notes" class="form-label fw-semibold">Service Notes & Resolution Remarks</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Describe the resolution steps, parts replaced, warranty updates..."><?php echo htmlspecialchars($order['notes'] ?? ''); ?></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary px-4 py-2">Update Work Order</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
