<div class="container-fluid py-4 print-container">
    <!-- Breadcrumbs -->
    <nav aria-label="breadcrumb" class="no-print">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/dashboard" class="text-decoration-none">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/reports" class="text-decoration-none">Reports</a></li>
            <li class="breadcrumb-item active" aria-current="page">Audit Stocktakes</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
        <div>
            <a href="<?php echo BASE_URL; ?>/reports" class="text-decoration-none text-muted"><i class="bi bi-arrow-left me-1"></i> Back to Analytics & Reports</a>
            <h1 class="h3 mt-2 mb-0 fw-bold">Stocktake Audit Summary Report</h1>
            <p class="text-muted mb-0">Overview of cycle check statuses, verified counts, and missing/damaged items logs.</p>
        </div>
        <div class="d-flex gap-2">
            <button onclick="window.print();" class="btn btn-outline-dark">
                <i class="bi bi-printer me-2"></i>Print Report
            </button>
            <a href="<?php echo BASE_URL; ?>/reports/export?type=audit" class="btn btn-primary">
                <i class="bi bi-download me-2"></i>Export CSV
            </a>
        </div>
    </div>

    <!-- Printable Header (Visible only when printed) -->
    <div class="print-header d-none d-print-block mb-4">
        <h2 class="fw-bold">AssetFlow Enterprise Resource Management System</h2>
        <h4 class="text-muted">Stocktake Verification Audit Summary Report</h4>
        <p class="small text-muted mb-0">Run Date: <?php echo date('M d, Y H:i A'); ?></p>
        <hr style="border-color: #000;">
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Audit Cycle Title</th>
                            <th>Cycle Status</th>
                            <th>Total Checked Assets</th>
                            <th>Verified (Match)</th>
                            <th>Missing (Lost)</th>
                            <th>Damaged (Repairs)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($records)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">No stocktake cycles recorded.</td>
                            </tr>
                        <?php else: ?>
                            <?php 
                            $grandTotalChecked = 0;
                            $grandVerified = 0;
                            $grandMissing = 0;
                            $grandDamaged = 0;
                            
                            foreach ($records as $row): 
                                $grandTotalChecked += $row['total_checked'];
                                $grandVerified += $row['verified_count'];
                                $grandMissing += $row['missing_count'];
                                $grandDamaged += $row['damaged_count'];
                            ?>
                                <tr>
                                    <td><span class="fw-bold text-dark"><?php echo htmlspecialchars($row['cycle_title']); ?></span></td>
                                    <td>
                                        <span class="status-badge <?php 
                                            echo $row['cycle_status'] === 'Completed' ? 'status-disposed' : 'status-available'; 
                                        ?>">
                                            <?php echo htmlspecialchars($row['cycle_status']); ?>
                                        </span>
                                    </td>
                                    <td><strong class="text-dark"><?php echo (int)$row['total_checked']; ?> assets</strong></td>
                                    <td><span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1"><?php echo (int)$row['verified_count']; ?> verified</span></td>
                                    <td>
                                         <?php if ($row['missing_count'] > 0): ?>
                                             <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2.5 py-1"><i class="bi bi-exclamation-triangle me-1"></i><?php echo (int)$row['missing_count']; ?> missing</span>
                                         <?php else: ?>
                                             <span class="badge bg-light text-dark border px-2.5 py-1">0 missing</span>
                                         <?php endif; ?>
                                    </td>
                                    <td>
                                         <?php if ($row['damaged_count'] > 0): ?>
                                             <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2.5 py-1"><?php echo (int)$row['damaged_count']; ?> damaged</span>
                                         <?php else: ?>
                                             <span class="badge bg-light text-dark border px-2.5 py-1">0 damaged</span>
                                         <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <!-- Totals Row -->
                            <tr class="table-light fw-bold" style="border-top: 2px solid var(--border-color);">
                                <td>TOTALS</td>
                                <td>-</td>
                                <td><?php echo $grandTotalChecked; ?> assets</td>
                                <td><?php echo $grandVerified; ?> verified</td>
                                <td class="<?php echo $grandMissing > 0 ? 'text-danger' : ''; ?>"><?php echo $grandMissing; ?> missing</td>
                                <td class="<?php echo $grandDamaged > 0 ? 'text-warning' : ''; ?>"><?php echo $grandDamaged; ?> damaged</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
