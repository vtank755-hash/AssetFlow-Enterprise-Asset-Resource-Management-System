<?php
use App\Core\Session;
$role = Session::getRole();

// Calculate Monthly Calendar Grid
$firstDayOfMonth = strtotime("$year-$month-01");
$daysInMonth = (int)date('t', $firstDayOfMonth);
$startDayOfWeek = (int)date('w', $firstDayOfMonth); // 0 = Sun, 6 = Sat

$monthName = date('F', $firstDayOfMonth);

// Map bookings to day numbers
$dayBookings = [];
for ($d = 1; $d <= $daysInMonth; $d++) {
    $dayBookings[$d] = [];
}
foreach ($bookings as $b) {
    if ($b['status'] !== 'Cancelled') {
        $bStart = strtotime($b['start_time']);
        $bStartYear = (int)date('Y', $bStart);
        $bStartMonth = (int)date('m', $bStart);
        $bStartDay = (int)date('d', $bStart);
        
        if ($bStartYear === $year && $bStartMonth === $month) {
            $dayBookings[$bStartDay][] = $b;
        }
    }
}

// Navigation helpers
$prevMonth = $month - 1;
$prevYear = $year;
if ($prevMonth < 1) {
    $prevMonth = 12;
    $prevYear--;
}

$nextMonth = $month + 1;
$nextYear = $year;
if ($nextMonth > 12) {
    $nextMonth = 1;
    $nextYear++;
}

$activeViewTab = $_GET['view'] ?? 'calendar';
?>
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 fw-bold">Resource Bookings</h1>
            <p class="text-muted mb-0">Reserve conference rooms, project vehicles, or staging hardware equipment.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?php echo BASE_URL; ?>/bookings/create" class="btn btn-primary">
                <i class="bi bi-calendar-plus me-2"></i>Reserve Resource
            </a>
        </div>
    </div>

    <!-- Alert Messages -->
    <?php echo flash('success'); ?>
    <?php echo flash('danger'); ?>

    <!-- View Nav Header -->
    <div class="details-tab-nav mb-4">
        <a href="<?php echo BASE_URL; ?>/bookings?view=calendar&month=<?php echo $month; ?>&year=<?php echo $year; ?>" class="details-tab-link <?php echo $activeViewTab === 'calendar' ? 'active' : ''; ?>">
            <i class="bi bi-calendar3 me-1"></i> Calendar Grid View
        </a>
        <a href="<?php echo BASE_URL; ?>/bookings?view=list" class="details-tab-link <?php echo $activeViewTab === 'list' ? 'active' : ''; ?>">
            <i class="bi bi-list-ul me-1"></i> Bookings List Directory
        </a>
    </div>

    <?php if ($activeViewTab === 'calendar'): ?>
        <!-- CALENDAR TABLE LAYOUT -->
        <div class="card shadow-sm border-0">
            <!-- Calendar Navigation Header -->
            <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                <div class="d-flex align-items-center gap-2">
                    <h5 class="fw-bold mb-0 text-dark"><?php echo $monthName . ' ' . $year; ?></h5>
                </div>
                <div class="d-flex gap-1">
                    <a href="<?php echo BASE_URL; ?>/bookings?view=calendar&month=<?php echo $prevMonth; ?>&year=<?php echo $prevYear; ?>" class="btn btn-sm btn-outline-secondary px-2.5 py-1">
                        <i class="bi bi-chevron-left"></i> Prev Month
                    </a>
                    <a href="<?php echo BASE_URL; ?>/bookings?view=calendar&month=<?php echo date('m'); ?>&year=<?php echo date('Y'); ?>" class="btn btn-sm btn-outline-secondary px-3 py-1">
                        Today
                    </a>
                    <a href="<?php echo BASE_URL; ?>/bookings?view=calendar&month=<?php echo $nextMonth; ?>&year=<?php echo $nextYear; ?>" class="btn btn-sm btn-outline-secondary px-2.5 py-1">
                        Next Month <i class="bi bi-chevron-right"></i>
                    </a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered mb-0 calendar-grid-layout" style="min-width: 800px; border-collapse: collapse;">
                        <thead>
                            <tr class="text-center bg-light text-secondary small uppercase fw-bold" style="border-bottom: 2px solid var(--border-color);">
                                <th class="py-2.5" style="width: 14.28%;">Sun</th>
                                <th class="py-2.5" style="width: 14.28%;">Mon</th>
                                <th class="py-2.5" style="width: 14.28%;">Tue</th>
                                <th class="py-2.5" style="width: 14.28%;">Wed</th>
                                <th class="py-2.5" style="width: 14.28%;">Thu</th>
                                <th class="py-2.5" style="width: 14.28%;">Fri</th>
                                <th class="py-2.5" style="width: 14.28%;">Sat</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                            <?php
                            // Print empty padding cells before first day of month
                            for ($i = 0; $i < $startDayOfWeek; $i++) {
                                echo '<td class="bg-light-subtle" style="height: 125px; border: 1px solid var(--border-color); background-color: #f8fafc !important;"></td>';
                            }
                            
                            $currentDayOfWeek = $startDayOfWeek;
                            for ($day = 1; $day <= $daysInMonth; $day++) {
                                if ($currentDayOfWeek === 7) {
                                    echo '</tr><tr style="border-top: 1px solid var(--border-color);">';
                                    $currentDayOfWeek = 0;
                                }
                                
                                $isToday = (date('Y-m-d') === sprintf('%04d-%02d-%02d', $year, $month, $day));
                                ?>
                                <td class="<?php echo $isToday ? 'bg-indigo-subtle' : ''; ?>" style="height: 125px; vertical-align: top; position: relative; border: 1px solid var(--border-color); padding: 8px;">
                                    <span class="fw-bold <?php echo $isToday ? 'text-indigo fs-6' : 'text-secondary'; ?> d-block mb-1.5"><?php echo $day; ?></span>
                                    
                                    <div class="d-flex flex-column gap-1 overflow-auto" style="max-height: 90px;">
                                        <?php foreach ($dayBookings[$day] as $ev): ?>
                                            <?php
                                            $badgeClass = 'bg-primary-subtle text-primary border border-primary-subtle';
                                            if ($ev['status'] === 'Ongoing') {
                                                $badgeClass = 'bg-success-subtle text-success border border-success-subtle';
                                            } elseif ($ev['status'] === 'Completed') {
                                                $badgeClass = 'bg-secondary-subtle text-secondary border border-secondary-subtle';
                                            }
                                            ?>
                                            <div class="p-1 px-1.5 rounded text-truncate text-start <?php echo $badgeClass; ?>" style="font-size: 10.5px; line-height: 1.2;" title="<?php echo htmlspecialchars($ev['purpose'] . ' (' . $ev['asset_name'] . ')'); ?>">
                                                <strong><?php echo date('H:i', strtotime($ev['start_time'])); ?></strong>: <?php echo htmlspecialchars($ev['asset_name']); ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </td>
                                <?php
                                $currentDayOfWeek++;
                            }
                            
                            // Print trailing empty cells
                            while ($currentDayOfWeek < 7) {
                                echo '<td class="bg-light-subtle" style="height: 125px; border: 1px solid var(--border-color); background-color: #f8fafc !important;"></td>';
                                $currentDayOfWeek++;
                            }
                            ?>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    <?php else: ?>
        <!-- BOOKINGS LIST DIRECTORY -->
        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Resource Class</th>
                                <th>Resource / Asset</th>
                                <th>Reserved Custodian</th>
                                <th>Start Time</th>
                                <th>End Time</th>
                                <th>Purpose</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($bookings)): ?>
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">No reservations found matching your account permissions.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($bookings as $b): ?>
                                    <tr>
                                        <td>
                                            <span class="badge bg-secondary-subtle text-secondary-emphasis font-monospace px-2 py-1">
                                                <?php echo htmlspecialchars($b['category_name']); ?>
                                            </span>
                                        </td>
                                        <td><span class="fw-bold text-dark"><?php echo htmlspecialchars($b['asset_name']); ?></span> <span class="text-muted small">(<?php echo htmlspecialchars($b['asset_tag']); ?>)</span></td>
                                        <td><span class="fw-semibold"><?php echo htmlspecialchars($b['employee_name']); ?></span></td>
                                        <td><?php echo htmlspecialchars(date('M d, H:i A', strtotime($b['start_time']))); ?></td>
                                        <td><?php echo htmlspecialchars(date('M d, H:i A', strtotime($b['end_time']))); ?></td>
                                        <td class="text-wrap small text-muted" style="max-width: 200px;"><?php echo htmlspecialchars($b['purpose']); ?></td>
                                        <td>
                                            <?php
                                            $badgeClass = 'bg-secondary';
                                            if ($b['status'] === 'Upcoming') {
                                                $badgeClass = 'bg-primary-subtle text-primary border border-primary-subtle';
                                            } elseif ($b['status'] === 'Ongoing') {
                                                $badgeClass = 'bg-success-subtle text-success border border-success-subtle';
                                            } elseif ($b['status'] === 'Cancelled') {
                                                $badgeClass = 'bg-danger-subtle text-danger border border-danger-subtle';
                                            } elseif ($b['status'] === 'Completed') {
                                                $badgeClass = 'bg-secondary-subtle text-secondary border border-secondary-subtle';
                                            }
                                            ?>
                                            <span class="status-badge <?php echo $badgeClass; ?>">
                                                <?php echo htmlspecialchars($b['status']); ?>
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <?php if ($b['status'] === 'Upcoming' && ($role === 'Admin' || $role === 'Manager' || Session::getUserId() === $b['employee_id'])): ?>
                                                <a href="<?php echo BASE_URL; ?>/bookings/cancel?id=<?php echo $b['id']; ?>" class="btn btn-sm btn-outline-danger border-0 px-2 py-1" onclick="return confirm('Are you sure you want to cancel this booking reservation?');">
                                                    <i class="bi bi-x-circle me-1"></i>Cancel
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted small">-</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
