<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="<?php echo BASE_URL; ?>/reports" class="text-decoration-none text-muted"><i class="bi bi-arrow-left me-1"></i> Back to Reports</a>
            <h1 class="h3 mt-2 mb-0 fw-bold">Resource Utilization Analytics</h1>
            <p class="text-muted mb-0">Understand allocation velocities and current inventory utilization rates grouped by categories.</p>
        </div>
        <a href="<?php echo BASE_URL; ?>/reports/export?type=utilization" class="btn btn-outline-success">
            <i class="bi bi-download me-2"></i>Export CSV
        </a>
    </div>

    <!-- Table Grid -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0" data-report="utilization">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Total Registered Resources</th>
                            <th>Currently Checked Out</th>
                            <th>Historical Checkout Frequency</th>
                            <th>Current Utilization Rate</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($records)): ?>
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">No utilization data aggregated.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($records as $row): ?>
                                <tr>
                                    <td><span class="fw-bold text-dark"><?php echo htmlspecialchars($row['category_name']); ?></span></td>
                                    <td><?php echo htmlspecialchars($row['total_assets']); ?> assets</td>
                                    <td>
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1">
                                            <?php echo htmlspecialchars($row['currently_allocated'] ?: 0); ?> active
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['total_historical_allocations'] ?: 0); ?> events</td>
                                    <td>
                                        <?php 
                                        $rate = $row['utilization_rate'] ?: 0.0;
                                        ?>
                                        <div class="d-flex align-items-center" style="width: 250px;">
                                            <span class="fw-bold text-dark me-2" style="width: 45px; text-align: right;"><?php echo $rate; ?>%</span>
                                            <div class="css-chart-bar-container flex-grow-1" style="height: 8px;">
                                                <div class="css-chart-bar" style="width: <?php echo $rate; ?>%; background-color: var(--accent-color);"></div>
                                            </div>
                                        </div>
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
