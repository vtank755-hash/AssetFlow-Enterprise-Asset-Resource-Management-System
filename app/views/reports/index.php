<div class="container-fluid py-4">
    <div class="mb-4">
        <h1 class="h3 mb-0 fw-bold">Analytics & Reports</h1>
        <p class="text-muted">Generate lifecycle audits, financial valuations, and service cost reports.</p>
    </div>

    <div class="row g-4">
        <!-- Valuation Card -->
        <div class="col-md-4">
            <div class="card card-hover shadow-sm border-0 h-100">
                <div class="card-body p-4 d-flex flex-column">
                    <div class="d-inline-flex align-items-center justify-content-center bg-success-subtle text-success rounded-circle p-3 mb-3" style="width: 50px; height: 50px;">
                        <i class="bi bi-currency-dollar fs-4"></i>
                    </div>
                    <h5 class="fw-bold text-dark">Asset Valuation</h5>
                    <p class="text-muted small flex-grow-1">Track financial depreciation rates, accumulated loss, and current asset book values using straight-line calculations.</p>
                    <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                        <a href="<?php echo BASE_URL; ?>/reports/valuation" class="btn btn-sm btn-primary">Open Report</a>
                        <a href="<?php echo BASE_URL; ?>/reports/export?type=valuation" class="text-decoration-none text-muted small"><i class="bi bi-download me-1"></i>Export CSV</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Maintenance Card -->
        <div class="col-md-4">
            <div class="card card-hover shadow-sm border-0 h-100">
                <div class="card-body p-4 d-flex flex-column">
                    <div class="d-inline-flex align-items-center justify-content-center bg-danger-subtle text-danger rounded-circle p-3 mb-3" style="width: 50px; height: 50px;">
                        <i class="bi bi-wrench-adjustable fs-4"></i>
                    </div>
                    <h5 class="fw-bold text-dark">Maintenance Expenses</h5>
                    <p class="text-muted small flex-grow-1">Analyze servicing costs, cumulative work orders completed, and total downtime days registered across equipment inventories.</p>
                    <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                        <a href="<?php echo BASE_URL; ?>/reports/maintenance" class="btn btn-sm btn-primary">Open Report</a>
                        <a href="<?php echo BASE_URL; ?>/reports/export?type=maintenance" class="text-decoration-none text-muted small"><i class="bi bi-download me-1"></i>Export CSV</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Utilization Card -->
        <div class="col-md-4">
            <div class="card card-hover shadow-sm border-0 h-100">
                <div class="card-body p-4 d-flex flex-column">
                    <div class="d-inline-flex align-items-center justify-content-center bg-indigo-subtle text-primary rounded-circle p-3 mb-3" style="width: 50px; height: 50px; background-color: #e0e7ff; color: #4f46e5;">
                        <i class="bi bi-pie-chart fs-4"></i>
                    </div>
                    <h5 class="fw-bold text-dark">Resource Utilization</h5>
                    <p class="text-muted small flex-grow-1">Monitor active check-out quantities, historical allocation frequencies, and overall demand rates grouped by asset categories.</p>
                    <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                        <a href="<?php echo BASE_URL; ?>/reports/utilization" class="btn btn-sm btn-primary">Open Report</a>
                        <a href="<?php echo BASE_URL; ?>/reports/export?type=utilization" class="text-decoration-none text-muted small"><i class="bi bi-download me-1"></i>Export CSV</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
