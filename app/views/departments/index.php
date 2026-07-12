<?php
use App\Core\Session;
$role = Session::getRole();
?>
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 fw-bold">Departments Setup</h1>
            <p class="text-muted mb-0">Manage organizational departments, structural hierarchies, and staff assignments.</p>
        </div>
        <?php if ($role === 'Admin'): ?>
            <a href="<?php echo BASE_URL; ?>/departments/create" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i>Add Department
            </a>
        <?php endif; ?>
    </div>

    <!-- Alert Messages -->
    <?php echo flash('success'); ?>
    <?php echo flash('danger'); ?>

    <div class="row g-4">
        <!-- Departments Directory (Left side) -->
        <div class="col-lg-7">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <form action="<?php echo BASE_URL; ?>/departments" method="GET" class="d-flex gap-2">
                        <input type="text" class="form-control form-control-sm" name="search" placeholder="Search name or code..." value="<?php echo htmlspecialchars($search); ?>">
                        <button type="submit" class="btn btn-sm btn-primary px-3"><i class="bi bi-search"></i></button>
                    </form>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Dept Code</th>
                                    <th>Department Name</th>
                                    <th>Active Staff</th>
                                    <?php if ($role === 'Admin'): ?>
                                        <th class="text-end">Actions</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($departments)): ?>
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">No departments found matching your criteria.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($departments as $dept): ?>
                                        <tr>
                                            <td><span class="badge bg-secondary text-secondary-emphasis font-monospace px-2.5 py-1.5"><?php echo htmlspecialchars($dept['code']); ?></span></td>
                                            <td><span class="fw-semibold text-dark"><?php echo htmlspecialchars($dept['name']); ?></span></td>
                                            <td>
                                                <span class="badge bg-light text-dark border px-2.5 py-1">
                                                    <i class="bi bi-people me-1"></i> <?php echo (int)$dept['employee_count']; ?> employees
                                                </span>
                                            </td>
                                            <?php if ($role === 'Admin'): ?>
                                                <td class="text-end">
                                                    <a href="<?php echo BASE_URL; ?>/departments/edit?id=<?php echo $dept['id']; ?>" class="btn btn-sm btn-outline-secondary border-0 px-2 py-1 me-1"><i class="bi bi-pencil"></i></a>
                                                    <a href="<?php echo BASE_URL; ?>/departments/delete?id=<?php echo $dept['id']; ?>" class="btn btn-sm btn-outline-danger border-0 px-2 py-1" onclick="return confirm('Are you sure you want to delete this department?');"><i class="bi bi-trash"></i></a>
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

        <!-- Hierarchy Map Pane (Right side) -->
        <div class="col-lg-5">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0 fw-bold"><i class="bi bi-diagram-3 text-indigo me-2"></i>Department Hierarchy</h5>
                </div>
                <div class="card-body p-4">
                    <p class="text-muted small mb-4">Visual representation of corporate structure reporting routes:</p>
                    
                    <!-- Pure HTML/CSS Branch Tree -->
                    <div class="list-group">
                        <div class="list-group-item d-flex align-items-center p-3 mb-2 rounded border border-light shadow-2xs">
                            <div class="bg-primary-subtle text-primary rounded-circle p-2.5 me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="bi bi-diagram-3-fill fs-5"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-0 text-dark">Executive Office (EXEC)</h6>
                                <span class="text-muted small">Organization Root Authority</span>
                            </div>
                        </div>
                        
                        <div class="list-group-item d-flex align-items-center p-3 mb-2 ms-5 border-start" style="border-left: 3px solid var(--accent-color) !important; border-top: none; border-right: none; border-bottom: none;">
                            <i class="bi bi-arrow-return-right text-muted me-3 fs-5"></i>
                            <div class="bg-indigo-subtle text-indigo rounded-circle p-2 me-3 d-flex align-items-center justify-content-center" style="width: 35px; height: 35px; background-color: #e0e7ff; color: #4f46e5;">
                                <i class="bi bi-laptop-fill"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-0 text-dark">Information Technology (IT)</h6>
                                <span class="text-muted small">Reporting to: Executive Office</span>
                            </div>
                        </div>

                        <div class="list-group-item d-flex align-items-center p-3 ms-5 border-start" style="border-left: 3px solid var(--accent-color) !important; border-top: none; border-right: none; border-bottom: none;">
                            <i class="bi bi-arrow-return-right text-muted me-3 fs-5"></i>
                            <div class="bg-indigo-subtle text-indigo rounded-circle p-2 me-3 d-flex align-items-center justify-content-center" style="width: 35px; height: 35px; background-color: #e0e7ff; color: #4f46e5;">
                                <i class="bi bi-truck"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-0 text-dark">Operations & Warehouse (OPS)</h6>
                                <span class="text-muted small">Reporting to: Executive Office</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
