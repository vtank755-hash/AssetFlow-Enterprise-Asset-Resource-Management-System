<div class="container-fluid py-4 print-container">
    <!-- Breadcrumb / Header -->
    <div class="mb-4 d-flex justify-content-between align-items-center no-print">
        <div>
            <a href="<?php echo BASE_URL; ?>/audits/view?id=<?php echo $cycle['id']; ?>" class="text-decoration-none text-muted"><i class="bi bi-arrow-left me-1"></i> Back to Audit Checklist</a>
            <h1 class="h3 mt-2 mb-0 fw-bold">Discrepancy Report</h1>
            <p class="text-muted mb-0">Discrepancy items for: <strong><?php echo htmlspecialchars($cycle['title']); ?></strong></p>
        </div>
        <div class="d-flex gap-2">
            <button onclick="window.print();" class="btn btn-outline-dark">
                <i class="bi bi-printer me-2"></i>Print Report
            </button>
        </div>
    </div>

    <!-- Printable Header Section (Only visible during print) -->
    <div class="print-header d-none d-print-block mb-4">
        <h2 class="fw-bold">AssetFlow Enterprise Resource Management System</h2>
        <h4 class="text-muted">Stocktake Discrepancy Report - <?php echo htmlspecialchars($cycle['title']); ?></h4>
        <p class="small text-muted mb-0">Run Date: <?php echo date('M d, Y H:i A'); ?> | Primary Auditor: <?php echo htmlspecialchars($cycle['auditor_name']); ?></p>
        <hr>
    </div>

    <div class="row g-4 mb-4">
        <!-- Discrepancy summary counts -->
        <div class="col-md-6 col-sm-12">
            <div class="card shadow-sm border-0 bg-danger-subtle text-danger p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="fw-bold small d-block mb-1">TOTAL MISSING ASSETS</span>
                        <?php 
                        $missingCount = count(array_filter($discrepancies, function($d) { return $d['status'] === 'Missing'; }));
                        ?>
                        <span class="fw-extrabold fs-3"><?php echo $missingCount; ?> items</span>
                    </div>
                    <i class="bi bi-exclamation-octagon fs-1"></i>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-sm-12">
            <div class="card shadow-sm border-0 bg-warning-subtle text-warning-emphasis p-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <span class="fw-bold small d-block mb-1">TOTAL DAMAGED ASSETS</span>
                        <?php 
                        $damagedCount = count(array_filter($discrepancies, function($d) { return $d['status'] === 'Damaged'; }));
                        ?>
                        <span class="fw-extrabold fs-3"><?php echo $damagedCount; ?> items</span>
                    </div>
                    <i class="bi bi-tools fs-1"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Asset Tag</th>
                            <th>Asset Description</th>
                            <th>Serial / VIN</th>
                            <th>Physical Location</th>
                            <th>Discrepancy State</th>
                            <th>Audited By</th>
                            <th>Audit Check Date</th>
                            <th>Auditor Findings / Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($discrepancies)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">Congratulations! No missing or damaged discrepancies recorded for this cycle.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($discrepancies as $disc): ?>
                                <tr>
                                    <td><strong class="text-indigo"><?php echo htmlspecialchars($disc['asset_tag']); ?></strong></td>
                                    <td><span class="fw-bold text-dark"><?php echo htmlspecialchars($disc['asset_name']); ?></span></td>
                                    <td class="font-monospace small text-muted"><?php echo htmlspecialchars($disc['serial_number']); ?></td>
                                    <td><span class="small text-muted"><?php echo htmlspecialchars($disc['location']); ?></span></td>
                                    <td>
                                        <span class="status-badge <?php 
                                            echo $disc['status'] === 'Missing' ? 'bg-danger-subtle text-danger border border-danger-subtle' : 
                                                'bg-warning-subtle text-warning border border-warning-subtle'; 
                                        ?>">
                                            <?php echo htmlspecialchars($disc['status']); ?>
                                        </span>
                                    </td>
                                    <td><span class="fw-semibold text-dark"><?php echo htmlspecialchars($disc['auditor_name']); ?></span></td>
                                    <td class="small text-muted"><?php echo htmlspecialchars(date('M d, H:i A', strtotime($disc['audit_date']))); ?></td>
                                    <td class="text-wrap small text-dark" style="max-width: 250px;"><?php echo htmlspecialchars($disc['notes'] ?: '-'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
