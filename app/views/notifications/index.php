<div class="container-fluid py-4">
    <!-- Breadcrumbs -->
    <nav aria-label="breadcrumb" class="no-print">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/dashboard" class="text-decoration-none">Dashboard</a></li>
            <li class="breadcrumb-item active" aria-current="page">Notifications</li>
        </ol>
    </nav>

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 fw-bold">Notifications Centre</h1>
            <p class="text-muted mb-0">Track system alerts, custody assignment updates, and scheduled maintenance approvals.</p>
        </div>
        <?php if (!empty($notifications)): ?>
            <a href="<?php echo BASE_URL; ?>/notifications/readAll" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-check2-all me-1"></i>Mark All as Read
            </a>
        <?php endif; ?>
    </div>

    <!-- Alert flash feedback -->
    <?php echo flash('success'); ?>
    <?php echo flash('danger'); ?>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="list-group list-group-flush">
                <?php if (empty($notifications)): ?>
                    <div class="text-center py-5">
                        <div class="d-inline-flex align-items-center justify-content-center bg-light text-muted rounded-circle p-3 mb-3" style="width: 60px; height: 60px;">
                            <i class="bi bi-bell-slash fs-3"></i>
                        </div>
                        <h5 class="fw-bold text-dark">All Clear!</h5>
                        <p class="text-muted small mb-0">You have no system alert notifications to process right now.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($notifications as $n): ?>
                        <?php
                        // Pick icon and color based on title text
                        $iconClass = 'bi-bell';
                        $iconBg = 'bg-secondary-subtle text-secondary';
                        
                        if (strpos($n['title'], 'Assigned') !== false) {
                            $iconClass = 'bi-box-seam';
                            $iconBg = 'bg-primary-subtle text-primary';
                        } elseif (strpos($n['title'], 'Transfer') !== false) {
                            $iconClass = 'bi-arrow-left-right';
                            $iconBg = 'bg-info-subtle text-info';
                        } elseif (strpos($n['title'], 'Reminder') !== false) {
                            $iconClass = 'bi-calendar-check';
                            $iconBg = 'bg-purple-subtle text-purple';
                        } elseif (strpos($n['title'], 'Approved') !== false) {
                            $iconClass = 'bi-check-circle';
                            $iconBg = 'bg-success-subtle text-success';
                        } elseif (strpos($n['title'], 'Overdue') !== false) {
                            $iconClass = 'bi-exclamation-triangle';
                            $iconBg = 'bg-danger-subtle text-danger';
                        }
                        ?>
                        <div class="list-group-item p-4 d-flex align-items-start gap-3 <?php echo $n['is_read'] == 0 ? 'bg-light-subtle border-start border-primary border-4' : ''; ?>" style="border-left: 1px solid var(--border-color);">
                            <div class="d-inline-flex align-items-center justify-content-center rounded-circle p-2.5 <?php echo $iconBg; ?>" style="width: 42px; height: 42px; flex-shrink: 0;">
                                <i class="bi <?php echo $iconClass; ?> fs-5"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
                                    <h6 class="fw-bold text-dark mb-0 d-inline-flex align-items-center">
                                        <?php echo htmlspecialchars($n['title']); ?>
                                        <?php if ($n['is_read'] == 0): ?>
                                            <span class="badge bg-success rounded-circle p-1 ms-2" style="width: 8px; height: 8px;" title="Unread Alert"></span>
                                        <?php endif; ?>
                                    </h6>
                                    <small class="text-muted font-monospace" style="font-size: 11.5px;"><?php echo htmlspecialchars(date('M d, Y H:i A', strtotime($n['created_at']))); ?></small>
                                </div>
                                <p class="text-muted small mb-0"><?php echo htmlspecialchars($n['message']); ?></p>
                            </div>
                            <?php if ($n['is_read'] == 0): ?>
                                <div class="flex-shrink-0 align-self-center">
                                    <a href="<?php echo BASE_URL; ?>/notifications/read?id=<?php echo $n['id']; ?>" class="btn btn-sm btn-outline-secondary border-0 py-1" title="Mark as Read">
                                        <i class="bi bi-check-lg fs-5"></i>
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
