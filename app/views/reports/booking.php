<div class="container-fluid py-4 print-container">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
        <div>
            <a href="<?php echo BASE_URL; ?>/reports" class="text-decoration-none text-muted"><i class="bi bi-arrow-left me-1"></i> Back to Analytics & Reports</a>
            <h1 class="h3 mt-2 mb-0 fw-bold">Booking Frequency Report</h1>
            <p class="text-muted mb-0">Analysis of resource reservation schedules and booking state metrics.</p>
        </div>
        <div class="d-flex gap-2">
            <button onclick="window.print();" class="btn btn-outline-dark">
                <i class="bi bi-printer me-2"></i>Print Report
            </button>
            <a href="<?php echo BASE_URL; ?>/reports/export?type=booking" class="btn btn-primary">
                <i class="bi bi-download me-2"></i>Export CSV
            </a>
        </div>
    </div>

    <!-- Printable Header (Visible only when printed) -->
    <div class="print-header d-none d-print-block mb-4">
        <h2 class="fw-bold">AssetFlow Enterprise Resource Management System</h2>
        <h4 class="text-muted">Resource Bookings Frequency Summary Report</h4>
        <p class="small text-muted mb-0">Run Date: <?php echo date('M d, Y H:i A'); ?></p>
        <hr style="border-color: #000;">
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Resource / Asset Class</th>
                            <th>Total Bookings</th>
                            <th>Upcoming Reservations</th>
                            <th>Ongoing Sessions</th>
                            <th>Completed Sessions</th>
                            <th>Cancelled Reservations</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($records)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">No resource bookings recorded.</td>
                            </tr>
                        <?php else: ?>
                            <?php 
                            $grandTotalBookings = 0;
                            $grandUpcoming = 0;
                            $grandOngoing = 0;
                            $grandCompleted = 0;
                            $grandCancelled = 0;
                            
                            foreach ($records as $row): 
                                $grandTotalBookings += $row['total_bookings'];
                                $grandUpcoming += $row['upcoming_bookings'];
                                $grandOngoing += $row['ongoing_bookings'];
                                $grandCompleted += $row['completed_bookings'];
                                $grandCancelled += $row['cancelled_bookings'];
                            ?>
                                <tr>
                                    <td><span class="fw-bold text-dark"><?php echo htmlspecialchars($row['category_name']); ?></span></td>
                                    <td><strong class="text-indigo"><?php echo (int)$row['total_bookings']; ?></strong></td>
                                    <td><span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1"><?php echo (int)$row['upcoming_bookings']; ?> upcoming</span></td>
                                    <td><span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1"><?php echo (int)$row['ongoing_bookings']; ?> ongoing</span></td>
                                    <td><span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2.5 py-1"><?php echo (int)$row['completed_bookings']; ?> completed</span></td>
                                    <td><span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2.5 py-1"><?php echo (int)$row['cancelled_bookings']; ?> cancelled</span></td>
                                </tr>
                            <?php endforeach; ?>
                            <!-- Totals Row -->
                            <tr class="table-light fw-bold" style="border-top: 2px solid var(--border-color);">
                                <td>TOTALS</td>
                                <td><?php echo $grandTotalBookings; ?></td>
                                <td><?php echo $grandUpcoming; ?></td>
                                <td><?php echo $grandOngoing; ?></td>
                                <td><?php echo $grandCompleted; ?></td>
                                <td><?php echo $grandCancelled; ?></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
