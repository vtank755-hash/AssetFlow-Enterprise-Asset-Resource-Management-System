<?php
use App\Core\Session;
$role = Session::getRole();
?>
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 fw-bold">Consumables Inventory</h1>
            <p class="text-muted mb-0">Track office supplies, repair parts, and consumable materials.</p>
        </div>
        <?php if ($role === 'Admin' || $role === 'Manager'): ?>
            <a href="<?php echo BASE_URL; ?>/inventory/create" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i>Add Stock Item
            </a>
        <?php endif; ?>
    </div>

    <!-- Alert details if any items are low on stock -->
    <?php
    $lowStockItems = [];
    foreach ($items as $item) {
        if ($item['quantity'] < $item['min_threshold']) {
            $lowStockItems[] = $item;
        }
    }
    ?>
    <?php if (!empty($lowStockItems)): ?>
        <div class="alert alert-warning border-0 shadow-sm mb-4">
            <h5 class="alert-heading fw-bold"><i class="bi bi-exclamation-triangle-fill me-2"></i>Low Stock Warning</h5>
            <p class="mb-0">The following consumables are below their set minimum thresholds: 
                <strong><?php 
                    $names = array_map(function($i) { return htmlspecialchars($i['name']) . ' (Qty: ' . $i['quantity'] . ')'; }, $lowStockItems);
                    echo implode(', ', $names);
                ?></strong>. Please adjust stock levels or issue purchase orders.
            </p>
        </div>
    <?php endif; ?>

    <!-- Table Grid -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Item Details</th>
                            <th>SKU</th>
                            <th>Storage Location</th>
                            <th>Stock Quantity</th>
                            <th>Threshold Level</th>
                            <th>Unit Value</th>
                            <th>Total Inventory Value</th>
                            <?php if ($role === 'Admin' || $role === 'Manager'): ?>
                                <th class="text-end">Actions</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($items)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">No inventory items found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($items as $item): ?>
                                <tr class="<?php echo ($item['quantity'] < $item['min_threshold']) ? 'table-danger-subtle' : ''; ?>">
                                    <td class="text-wrap">
                                        <span class="fw-semibold text-dark d-block"><?php echo htmlspecialchars($item['name']); ?></span>
                                    </td>
                                    <td><strong class="font-monospace text-muted"><?php echo htmlspecialchars($item['sku']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($item['location']); ?></td>
                                    <td>
                                        <span class="badge <?php echo ($item['quantity'] < $item['min_threshold']) ? 'bg-danger text-white' : 'bg-light text-dark border'; ?> fs-6 px-2.5 py-1">
                                            <?php echo htmlspecialchars($item['quantity']); ?> units
                                        </span>
                                    </td>
                                    <td><span class="text-muted"><?php echo htmlspecialchars($item['min_threshold']); ?> units (Min)</span></td>
                                    <td>₹<?php echo htmlspecialchars(number_format($item['unit_price'], 2)); ?></td>
                                    <td class="fw-semibold">₹<?php echo htmlspecialchars(number_format($item['unit_price'] * $item['quantity'], 2)); ?></td>
                                    
                                    <?php if ($role === 'Admin' || $role === 'Manager'): ?>
                                        <td class="text-end">
                                            <div class="d-inline-flex align-items-center">
                                                <!-- Direct link to quantity adjustment form -->
                                                <a href="<?php echo BASE_URL; ?>/inventory/edit?id=<?php echo $item['id']; ?>&action=adjust" class="btn btn-sm btn-outline-success py-1 px-2.5 me-1">
                                                    <i class="bi bi-arrow-down-up"></i> Adjust
                                                </a>

                                                <!-- Metadata edit link -->
                                                <a href="<?php echo BASE_URL; ?>/inventory/edit?id=<?php echo $item['id']; ?>&action=edit" class="btn btn-sm btn-outline-secondary border-0 px-2 py-1">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                            </div>
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
