<?php
use App\Core\Session;
$role = Session::getRole();
?>
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 fw-bold">Asset Categories Setup</h1>
            <p class="text-muted mb-0">Organize assets into custom classification groups (hardware, software, tooling).</p>
        </div>
        <?php if ($role === 'Admin' || $role === 'Manager'): ?>
            <a href="<?php echo BASE_URL; ?>/categories/create" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i>Add Category
            </a>
        <?php endif; ?>
    </div>

    <!-- Alert Messages -->
    <?php echo flash('success'); ?>
    <?php echo flash('danger'); ?>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3">
            <form action="<?php echo BASE_URL; ?>/categories" method="GET" class="d-flex gap-2" style="max-width: 400px;">
                <input type="text" class="form-control form-control-sm" name="search" placeholder="Search categories..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="btn btn-sm btn-primary px-3"><i class="bi bi-search"></i></button>
            </form>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Category Name</th>
                            <th>Description</th>
                            <th>Active Assets Tracked</th>
                            <?php if ($role === 'Admin' || $role === 'Manager'): ?>
                                <th class="text-end">Actions</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($categories)): ?>
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">No asset categories found matching your search.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($categories as $cat): ?>
                                <tr>
                                    <td><span class="fw-bold text-indigo"><?php echo htmlspecialchars($cat['name']); ?></span></td>
                                    <td class="text-wrap text-muted small" style="min-width: 250px;"><?php echo htmlspecialchars($cat['description'] ?: 'No description provided.'); ?></td>
                                    <td>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1">
                                            <i class="bi bi-box-seam me-1"></i> <?php echo (int)$cat['asset_count']; ?> assets
                                        </span>
                                    </td>
                                    <?php if ($role === 'Admin' || $role === 'Manager'): ?>
                                        <td class="text-end">
                                            <a href="<?php echo BASE_URL; ?>/categories/edit?id=<?php echo $cat['id']; ?>" class="btn btn-sm btn-outline-secondary border-0 px-2 py-1 me-1"><i class="bi bi-pencil"></i></a>
                                            <?php if ($role === 'Admin'): ?>
                                                <a href="<?php echo BASE_URL; ?>/categories/delete?id=<?php echo $cat['id']; ?>" class="btn btn-sm btn-outline-danger border-0 px-2 py-1" onclick="return confirm('Are you sure you want to delete this category?');"><i class="bi bi-trash"></i></a>
                                            <?php endif; ?>
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
