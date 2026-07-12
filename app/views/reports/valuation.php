<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="<?php echo BASE_URL; ?>/reports" class="text-decoration-none text-muted"><i class="bi bi-arrow-left me-1"></i> Back to Reports</a>
            <h1 class="h3 mt-2 mb-0 fw-bold">Asset Valuation & Depreciation</h1>
            <p class="text-muted mb-0">Financial valuation statement incorporating straight-line depreciation calculations.</p>
        </div>
        <a href="<?php echo BASE_URL; ?>/reports/export?type=valuation" class="btn btn-outline-success">
            <i class="bi bi-download me-2"></i>Export CSV
        </a>
    </div>

    <!-- Summary Metrics Card -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <span class="text-muted small fw-semibold uppercase d-block mb-1">TOTAL ACQUISITION VALUE</span>
                    <span class="fw-bold fs-3 text-dark">₹<?php echo htmlspecialchars(number_format($totals['purchase_cost'], 2)); ?></span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <span class="text-muted small fw-semibold uppercase d-block mb-1">CUMULATIVE DEPRECIATION</span>
                    <span class="fw-bold fs-3 text-danger">-₹<?php echo htmlspecialchars(number_format($totals['accumulated_depreciation'], 2)); ?></span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <span class="text-muted small fw-semibold uppercase d-block mb-1">CURRENT NET BOOK VALUE</span>
                    <span class="fw-bold fs-3 text-success">₹<?php echo htmlspecialchars(number_format($totals['book_value'], 2)); ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Registry Grid Card -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Asset Tag</th>
                            <th>Resource Name</th>
                            <th>Category</th>
                            <th>Acquisition Date</th>
                            <th>Purchase Cost</th>
                            <th>Accum. Depreciation</th>
                            <th>Net Book Value</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($assets)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">No assets found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($assets as $asset): ?>
                                <tr>
                                    <td>
                                        <a href="<?php echo BASE_URL; ?>/assets/view?id=<?php echo $asset['id'] ?? '#'; ?>" class="fw-bold text-decoration-none">
                                            <?php echo htmlspecialchars($asset['asset_tag']); ?>
                                        </a>
                                    </td>
                                    <td><span class="fw-semibold text-dark"><?php echo htmlspecialchars($asset['name']); ?></span></td>
                                    <td><?php echo htmlspecialchars($asset['category_name']); ?></td>
                                    <td><?php echo htmlspecialchars(date('M d, Y', strtotime($asset['purchase_date']))); ?></td>
                                    <td class="text-dark">₹<?php echo htmlspecialchars(number_format($asset['purchase_cost'], 2)); ?></td>
                                    <td class="text-danger">-₹<?php echo htmlspecialchars(number_format($asset['accumulated_depreciation'], 2)); ?></td>
                                    <td class="fw-bold text-success">₹<?php echo htmlspecialchars(number_format($asset['book_value'], 2)); ?></td>
                                    <td>
                                        <span class="status-badge <?php 
                                            echo $asset['status'] === 'Available' ? 'status-available' : 
                                                ($asset['status'] === 'Allocated' ? 'status-allocated' : 
                                                ($asset['status'] === 'Maintenance' ? 'status-maintenance' : 'status-disposed')); 
                                        ?>">
                                            <?php echo htmlspecialchars($asset['status']); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
