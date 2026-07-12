<?php
use App\Core\Session;
?>
<div class="container-fluid py-4">
    <div class="mb-4">
        <a href="<?php echo BASE_URL; ?>/bookings" class="text-decoration-none text-muted"><i class="bi bi-arrow-left me-1"></i> Back to Reservations Directory</a>
        <h1 class="h3 mt-2 mb-0 fw-bold">Reserve Resource</h1>
        <p class="text-muted">Book business facilities, project vehicles, or hardware sandbox equipment.</p>
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
                    <form action="<?php echo BASE_URL; ?>/bookings/create" method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo Session::generateCSRFToken(); ?>">

                        <div class="mb-3">
                            <label for="asset_id" class="form-label fw-semibold">Resource / Asset <span class="text-danger">*</span></label>
                            <select class="form-select" id="asset_id" name="asset_id" required>
                                <option value="" disabled selected>Select Resource Room / Vehicle / Equipment...</option>
                                <?php foreach ($assets as $asset): ?>
                                    <option value="<?php echo $asset['id']; ?>">
                                        [<?php echo htmlspecialchars($asset['category_name']); ?>] <?php echo htmlspecialchars($asset['name']); ?> (<?php echo htmlspecialchars($asset['asset_tag']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">Listing only active catalog assets representing rooms, vehicles, and tools.</div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <label for="start_time" class="form-label fw-semibold">Reservation Start <span class="text-danger">*</span></label>
                                <input type="datetime-local" class="form-control" id="start_time" name="start_time" required min="<?php echo date('Y-m-d\TH:i'); ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="end_time" class="form-label fw-semibold">Reservation End <span class="text-danger">*</span></label>
                                <input type="datetime-local" class="form-control" id="end_time" name="end_time" required min="<?php echo date('Y-m-d\TH:i'); ?>">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="purpose" class="form-label fw-semibold">Reservation Purpose <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="purpose" name="purpose" rows="4" required placeholder="Specify booking details (e.g. Regional Project Site visit, Q3 Staff Meeting)"></textarea>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary px-4">Confirm Reservation</button>
                            <a href="<?php echo BASE_URL; ?>/bookings" class="btn btn-light px-3">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
