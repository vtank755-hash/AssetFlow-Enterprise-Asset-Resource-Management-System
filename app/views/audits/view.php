<?php
use App\Core\Session;
$role = Session::getRole();
$userId = Session::getUserId();
$isAssignedAuditor = ($cycle['assigned_auditor_id'] == $userId);
$canAudit = ($role === 'Admin' || $role === 'Manager' || $isAssignedAuditor);
?>
<div class="container-fluid py-4">
    <!-- Breadcrumb -->
    <div class="mb-4 d-flex justify-content-between align-items-start">
        <div>
            <a href="<?php echo BASE_URL; ?>/audits" class="text-decoration-none text-muted"><i class="bi bi-arrow-left me-1"></i> Back to Audits Directory</a>
            <h1 class="h3 mt-2 mb-0 fw-bold"><?php echo htmlspecialchars($cycle['title']); ?></h1>
            <p class="text-muted mb-0">Assigned Auditor: <strong class="text-dark"><?php echo htmlspecialchars($cycle['auditor_name']); ?></strong></p>
        </div>
        <div>
            <span class="status-badge px-3 py-1.5 fs-6 <?php 
                echo $cycle['status'] === 'Completed' ? 'status-disposed' : 'status-available'; 
            ?>">
                <i class="bi bi-circle-fill me-1" style="font-size: 8px; vertical-align: middle;"></i>
                <?php echo htmlspecialchars($cycle['status']); ?>
            </span>
        </div>
    </div>

    <!-- Alert messages -->
    <?php echo flash('success'); ?>
    <?php echo flash('danger'); ?>

    <div class="row g-4">
        <!-- Target Assets Verification List -->
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 fw-bold"><i class="bi bi-clipboard-check text-indigo me-2"></i>Assets Verification Checklist</h5>
                    <a href="<?php echo BASE_URL; ?>/audits/report?id=<?php echo $cycle['id']; ?>" class="btn btn-sm btn-outline-warning text-warning-emphasis">
                        <i class="bi bi-exclamation-triangle me-1"></i>View Discrepancies
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Asset Tag</th>
                                    <th>Asset Details</th>
                                    <th>Serial Number</th>
                                    <th>Assigned Custodian</th>
                                    <th>Location Scope</th>
                                    <th>Verification Check</th>
                                    <?php if ($cycle['status'] === 'In Progress' && $canAudit): ?>
                                        <th class="text-end">Verification Actions</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($assets)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">No assets found within the configured department or location scope.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($assets as $asset): ?>
                                        <?php 
                                        $isChecked = isset($verifiedMap[$asset['id']]);
                                        $checkData = $isChecked ? $verifiedMap[$asset['id']] : null;
                                        ?>
                                        <tr class="<?php echo $isChecked ? 'bg-light-subtle' : ''; ?>">
                                            <td><strong class="text-indigo"><?php echo htmlspecialchars($asset['asset_tag']); ?></strong></td>
                                            <td>
                                                <span class="fw-bold text-dark d-block"><?php echo htmlspecialchars($asset['name']); ?></span>
                                                <span class="text-muted small"><?php echo htmlspecialchars($asset['category_name']); ?></span>
                                            </td>
                                            <td class="font-monospace text-muted small"><?php echo htmlspecialchars($asset['serial_number']); ?></td>
                                            <td><span class="fw-semibold text-dark"><?php echo htmlspecialchars($asset['custodian_name'] ?: 'In IT Storage'); ?></span></td>
                                            <td><span class="small text-muted"><?php echo htmlspecialchars($asset['location']); ?></span></td>
                                            <td>
                                                <?php if ($isChecked): ?>
                                                    <?php
                                                    $badgeClass = 'bg-secondary';
                                                    if ($checkData['status'] === 'Verified') {
                                                        $badgeClass = 'bg-success-subtle text-success border border-success-subtle';
                                                    } elseif ($checkData['status'] === 'Missing') {
                                                        $badgeClass = 'bg-danger-subtle text-danger border border-danger-subtle';
                                                    } elseif ($checkData['status'] === 'Damaged') {
                                                        $badgeClass = 'bg-warning-subtle text-warning border border-warning-subtle';
                                                    }
                                                    ?>
                                                    <span class="status-badge <?php echo $badgeClass; ?>">
                                                        <?php echo htmlspecialchars($checkData['status']); ?>
                                                    </span>
                                                    <?php if (!empty($checkData['notes'])): ?>
                                                        <div class="form-text text-truncate" style="max-width: 150px;" title="<?php echo htmlspecialchars($checkData['notes']); ?>">
                                                            Note: <?php echo htmlspecialchars($checkData['notes']); ?>
                                                        </div>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="badge bg-light text-dark border px-2.5 py-1">
                                                        <i class="bi bi-question-circle me-1"></i>Pending Check
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            
                                            <?php if ($cycle['status'] === 'In Progress' && $canAudit): ?>
                                                <td class="text-end">
                                                    <details class="css-dropdown">
                                                        <summary class="btn btn-sm btn-outline-primary py-1 px-2.5">
                                                            <i class="bi bi-check2-square me-1"></i>Verify
                                                        </summary>
                                                        <div class="dropdown-menu-css p-3 text-start" style="right:0; width: 260px; font-size:13px;">
                                                            <h6 class="fw-bold mb-2">Record Verification Check</h6>
                                                            <form action="<?php echo BASE_URL; ?>/audits/verify" method="POST">
                                                                <input type="hidden" name="csrf_token" value="<?php echo Session::generateCSRFToken(); ?>">
                                                                <input type="hidden" name="cycle_id" value="<?php echo $cycle['id']; ?>">
                                                                <input type="hidden" name="asset_id" value="<?php echo $asset['id']; ?>">
                                                                
                                                                <div class="mb-2">
                                                                    <label class="form-label fw-bold mb-1">State Checked</label>
                                                                    <select class="form-select form-select-sm" name="status" required>
                                                                        <option value="Verified" <?php echo ($isChecked && $checkData['status'] === 'Verified') ? 'selected' : ''; ?>>Verified (Match)</option>
                                                                        <option value="Missing" <?php echo ($isChecked && $checkData['status'] === 'Missing') ? 'selected' : ''; ?>>Missing (Lost)</option>
                                                                        <option value="Damaged" <?php echo ($isChecked && $checkData['status'] === 'Damaged') ? 'selected' : ''; ?>>Damaged (Repairs)</option>
                                                                    </select>
                                                                </div>
                                                                
                                                                <div class="mb-2">
                                                                    <label class="form-label fw-bold mb-1">Condition Notes</label>
                                                                    <textarea class="form-control form-control-sm" name="notes" rows="2" placeholder="Condition details or location..."><?php echo $isChecked ? htmlspecialchars($checkData['notes']) : ''; ?></textarea>
                                                                </div>
                                                                
                                                                <button type="submit" class="btn btn-sm btn-primary w-100 py-1">Submit Verification</button>
                                                            </form>
                                                        </div>
                                                    </details>
                                                </td>
                                            <?php endif; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
