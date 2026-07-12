<?php
use App\Core\Session;
$role = Session::getRole();
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 fw-bold">Assets Directory</h1>
            <p class="text-muted mb-0">Track and manage lifecycle, depreciation, and custody of hardware, software, and tools.</p>
        </div>
        <?php if ($role === 'Admin' || $role === 'Manager'): ?>
            <a href="<?php echo BASE_URL; ?>/assets/create" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i>Register New Asset
            </a>
        <?php endif; ?>
    </div>

    <!-- Filters Section -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-3">
            <form action="<?php echo BASE_URL; ?>/assets" method="GET" class="row g-3">
                <div class="col-md-3">
                    <label for="search" class="form-label d-none">Search Query</label>
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control border-start-0" id="search" name="search" placeholder="Search name, model, tag, serial..." value="<?php echo htmlspecialchars($filters['search']); ?>">
                    </div>
                </div>
                
                <div class="col-md-3">
                    <label for="category" class="form-label d-none">Category</label>
                    <select class="form-select" id="category" name="category">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo $filters['category'] == $cat['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="status" class="form-label d-none">Status</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">All Statuses</option>
                        <option value="Available" <?php echo $filters['status'] === 'Available' ? 'selected' : ''; ?>>Available</option>
                        <option value="Allocated" <?php echo $filters['status'] === 'Allocated' ? 'selected' : ''; ?>>Allocated</option>
                        <option value="Maintenance" <?php echo $filters['status'] === 'Maintenance' ? 'selected' : ''; ?>>Maintenance</option>
                        <option value="Disposed" <?php echo $filters['status'] === 'Disposed' ? 'selected' : ''; ?>>Disposed</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label for="location" class="form-label d-none">Location</label>
                    <input type="text" class="form-control" id="location" name="location" placeholder="Location..." value="<?php echo htmlspecialchars($filters['location']); ?>">
                </div>

                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-outline-primary w-100 py-2"><i class="bi bi-funnel"></i></button>
                </div>
            </form>
        </div>
    </div>

    <!-- Table Registry -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Asset Tag</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Location</th>
                            <th>Status</th>
                            <th>Purchase Cost</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($assets)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">No assets match the search criteria.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($assets as $asset): ?>
                                <tr>
                                    <td>
                                        <a href="<?php echo BASE_URL; ?>/assets/view?id=<?php echo $asset['id']; ?>" class="fw-bold text-decoration-none">
                                            <?php echo htmlspecialchars($asset['asset_tag']); ?>
                                        </a>
                                    </td>
                                    <td>
                                        <div>
                                            <span class="fw-semibold text-dark d-block"><?php echo htmlspecialchars($asset['name']); ?></span>
                                            <span class="text-muted" style="font-size: 13px;"><?php echo htmlspecialchars($asset['model'] ?: 'N/A'); ?></span>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($asset['category_name']); ?></td>
                                    <td><?php echo htmlspecialchars($asset['location']); ?></td>
                                    <td>
                                        <span class="status-badge <?php 
                                            echo $asset['status'] === 'Available' ? 'status-available' : 
                                                ($asset['status'] === 'Allocated' ? 'status-allocated' : 
                                                ($asset['status'] === 'Maintenance' ? 'status-maintenance' : 'status-disposed')); 
                                        ?>">
                                            <?php echo htmlspecialchars($asset['status']); ?>
                                        </span>
                                    </td>
                                    <td>₹<?php echo htmlspecialchars(number_format($asset['purchase_cost'], 2)); ?></td>
                                    <td class="text-end">
                                        <div class="d-inline-flex align-items-center">
                                            <a href="<?php echo BASE_URL; ?>/assets/view?id=<?php echo $asset['id']; ?>" class="btn btn-sm btn-outline-secondary border-0 px-2 py-1 me-1">
                                                <i class="bi bi-eye"></i> View
                                            </a>
                                            <?php if ($role === 'Admin' || $role === 'Manager'): ?>
                                                <a href="<?php echo BASE_URL; ?>/assets/edit?id=<?php echo $asset['id']; ?>" class="btn btn-sm btn-outline-primary border-0 px-2 py-1 me-1">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <?php if ($asset['status'] !== 'Disposed'): ?>
                                                    <details class="css-dropdown">
                                                        <summary class="btn btn-sm btn-outline-danger border-0 px-2 py-1">
                                                            <i class="bi bi-trash"></i>
                                                        </summary>
                                                        <div class="dropdown-menu-css p-3 text-center" style="right:0; width: 220px; font-size:13px;">
                                                            <p class="mb-2 text-wrap text-dark">Are you sure you want to decommission this asset?</p>
                                                            <form action="<?php echo BASE_URL; ?>/assets/delete" method="POST">
                                                                <input type="hidden" name="csrf_token" value="<?php echo Session::generateCSRFToken(); ?>">
                                                                <input type="hidden" name="id" value="<?php echo $asset['id']; ?>">
                                                                <button type="submit" class="btn btn-sm btn-danger w-100 py-1">Decommission</button>
                                                            </form>
                                                        </div>
                                                    </details>
                                                <?php endif; ?>
                                            <?php endif; ?>
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
