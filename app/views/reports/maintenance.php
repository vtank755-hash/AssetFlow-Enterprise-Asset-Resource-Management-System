<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="<?php echo BASE_URL; ?>/reports" class="text-decoration-none text-muted"><i class="bi bi-arrow-left me-1"></i> Back to Reports</a>
            <h1 class="h3 mt-2 mb-0 fw-bold">Maintenance Expenditures</h1>
            <p class="text-muted mb-0">Track cumulative repair costs and operational downtime per asset tag.</p>
        </div>
        <a href="<?php echo BASE_URL; ?>/reports/export?type=maintenance" class="btn btn-outline-success">
            <i class="bi bi-download me-2"></i>Export CSV
        </a>
    </div>

    <!-- Table Grid -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Asset Tag</th>
                            <th>Resource Name</th>
                            <th>Category</th>
                            <th>Completed Service Orders</th>
                            <th>Cumulative Cost</th>
                            <th>Total Downtime</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($records)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">No maintenance records found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($records as $row): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($row['asset_tag']); ?></strong></td>
                                    <td><span class="fw-semibold text-dark"><?php echo htmlspecialchars($row['asset_name']); ?></span></td>
                                    <td><?php echo htmlspecialchars($row['category_name']); ?></td>
                                    <td>
                                        <span class="badge bg-light text-dark border px-2.5 py-1">
                                            <?php echo htmlspecialchars($row['total_events']); ?> service logs
                                        </span>
                                    </td>
                                    <td class="fw-semibold text-danger">
                                        $<?php echo htmlspecialchars(number_format($row['total_cost'] ?: 0, 2)); ?>
                                    </td>
                                    <td>
                                        <span class="text-muted">
                                            <?php echo htmlspecialchars($row['total_downtime_days'] ?: 0); ?> days offline
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <a href="<?php echo BASE_URL; ?>/assets/view?id=<?php echo $row['id'] ?? '#'; ?>&tab=maintenance" class="btn btn-sm btn-outline-secondary border-0 px-2 py-1">
                                            <i class="bi bi-eye"></i> View Service History
                                        </a>
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
