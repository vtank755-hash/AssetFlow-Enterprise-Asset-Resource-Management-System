<div class="container-fluid py-4 print-container">
    <!-- Breadcrumbs -->
    <nav aria-label="breadcrumb" class="no-print">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/dashboard" class="text-decoration-none">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/reports" class="text-decoration-none">Reports</a></li>
            <li class="breadcrumb-item active" aria-current="page">Department Allocations</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
        <div>
            <a href="<?php echo BASE_URL; ?>/reports" class="text-decoration-none text-muted"><i class="bi bi-arrow-left me-1"></i> Back to Analytics & Reports</a>
            <h1 class="h3 mt-2 mb-0 fw-bold">Department Allocations Report</h1>
            <p class="text-muted mb-0">Summary of assets held and total valuation per business division.</p>
        </div>
        <div class="d-flex gap-2">
            <button onclick="window.print();" class="btn btn-outline-primary">
                <i class="bi bi-printer me-2"></i>Print PDF
            </button>
            <a href="<?php echo BASE_URL; ?>/reports/export?type=department" class="btn btn-outline-success">
                <i class="bi bi-download me-2"></i>Export CSV
            </a>
        </div>
    </div>

    <!-- Printable Header (Visible only when printed) -->
    <div class="print-header d-none d-print-block mb-4">
        <h2 class="fw-bold">AssetFlow Enterprise Resource Management System</h2>
        <h4 class="text-muted">Department Asset Allocation Summary Report</h4>
        <p class="small text-muted mb-0">Run Date: <?php echo date('M d, Y H:i A'); ?></p>
        <hr style="border-color: #000;">
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Department Name</th>
                            <th>Department Code</th>
                            <th>Active Allocated Assets</th>
                            <th>Current Allocated Valuation</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($records)): ?>
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">No department allocations logged.</td>
                            </tr>
                        <?php else: ?>
                            <?php 
                            $grandTotalAssets = 0;
                            $grandTotalValuation = 0.00;
                            foreach ($records as $row): 
                                $grandTotalAssets += $row['total_assets'];
                                $grandTotalValuation += $row['total_valuation'] ?: 0.00;
                            ?>
                                <tr>
                                    <td><span class="fw-bold text-dark"><?php echo htmlspecialchars($row['department_name']); ?></span></td>
                                    <td><span class="badge bg-secondary text-secondary-emphasis font-monospace px-2.5 py-1.5"><?php echo htmlspecialchars($row['department_code']); ?></span></td>
                                    <td>
                                        <span class="badge bg-light text-dark border px-2.5 py-1">
                                            <i class="bi bi-box-seam me-1"></i> <?php echo (int)$row['total_assets']; ?> items
                                        </span>
                                    </td>
                                    <td class="fw-bold text-dark">₹<?php echo htmlspecialchars(number_format($row['total_valuation'] ?: 0.00, 2)); ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <!-- Totals Row -->
                            <tr class="table-light fw-bold" style="border-top: 2px solid var(--border-color);">
                                <td>TOTALS</td>
                                <td>-</td>
                                <td><?php echo $grandTotalAssets; ?> items</td>
                                <td class="text-indigo">₹<?php echo htmlspecialchars(number_format($grandTotalValuation, 2)); ?></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
