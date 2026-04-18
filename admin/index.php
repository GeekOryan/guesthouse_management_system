<?php
//  admin/index.php
//  Admin Dashboard — overview of the entire guesthouse
$page_title = "Dashboard";
require_once 'includes/admin_header.php';

// Stats Cards Data

// Total bookings
$total_bookings = $conn->query("SELECT COUNT(*) as total FROM bookings")
                       ->fetch_assoc()['total'];

// Pending bookings
$pending = $conn->query("SELECT COUNT(*) as total FROM bookings WHERE status = 'pending'")
                ->fetch_assoc()['total'];

// Confirmed bookings
$confirmed = $conn->query("SELECT COUNT(*) as total FROM bookings WHERE status = 'confirmed'")
                  ->fetch_assoc()['total'];

// Total revenue from confirmed bookings only
$revenue = $conn->query("SELECT SUM(total_price) as total FROM bookings WHERE status = 'confirmed'")
                ->fetch_assoc()['total'] ?? 0;

// Total rooms
$total_rooms = $conn->query("SELECT COUNT(*) as total FROM rooms WHERE is_active = 1")
                    ->fetch_assoc()['total'];

// Unread messages
$unread_messages = $conn->query("SELECT COUNT(*) as total FROM contact_messages WHERE is_read = 0")
                        ->fetch_assoc()['total'];

// Recent Bookings
$recent_bookings = $conn->query("
    SELECT b.*, r.name as room_name 
    FROM bookings b
    JOIN rooms r ON b.room_id = r.id
    ORDER BY b.created_at DESC
    LIMIT 5
");

// Upcoming Bookings (check_in is in the future)
$upcoming = $conn->query("
    SELECT b.*, r.name as room_name
    FROM bookings b
    JOIN rooms r ON b.room_id = r.id
    WHERE b.check_in >= CURDATE()
    AND b.status != 'cancelled'
    ORDER BY b.check_in ASC
    LIMIT 5
");
?>

<!--STATS CARDS-->
<div class="row g-4 mb-4">

    <div class="col-md-4 col-sm-6">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <p class="text-muted small mb-1">Total Bookings</p>
                    <h3 class="fw-bold mb-0"><?= $total_bookings ?></h3>
                </div>
                <div class="stat-icon" style="background:#e8f4fd;">
                    <i class="bi bi-calendar-check" style="color:#0d6efd;"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4 col-sm-6">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <p class="text-muted small mb-1">Pending Bookings</p>
                    <h3 class="fw-bold mb-0"><?= $pending ?></h3>
                </div>
                <div class="stat-icon" style="background:#fff8e1;">
                    <i class="bi bi-hourglass-split" style="color:#f59e0b;"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4 col-sm-6">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <p class="text-muted small mb-1">Confirmed Bookings</p>
                    <h3 class="fw-bold mb-0"><?= $confirmed ?></h3>
                </div>
                <div class="stat-icon" style="background:#e8f5e9;">
                    <i class="bi bi-check-circle" style="color:#28a745;"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4 col-sm-6">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <p class="text-muted small mb-1">Total Revenue</p>
                    <h3 class="fw-bold mb-0"><?= formatPrice($revenue) ?></h3>
                </div>
                <div class="stat-icon" style="background:#f3e8ff;">
                    <i class="bi bi-cash-coin" style="color:#8b5cf6;"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4 col-sm-6">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <p class="text-muted small mb-1">Active Rooms</p>
                    <h3 class="fw-bold mb-0"><?= $total_rooms ?></h3>
                </div>
                <div class="stat-icon" style="background:#fce8e8;">
                    <i class="bi bi-door-open" style="color:#ef4444;"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4 col-sm-6">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <p class="text-muted small mb-1">Unread Messages</p>
                    <h3 class="fw-bold mb-0"><?= $unread_messages ?></h3>
                </div>
                <div class="stat-icon" style="background:#e8f4fd;">
                    <i class="bi bi-envelope" style="color:#0d6efd;"></i>
                </div>
            </div>
        </div>
    </div>

</div>

<!--TABLES ROW-->
<div class="row g-4">

    <!-- Recent Bookings -->
    <div class="col-lg-7">
        <div class="admin-card card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-clock-history me-2"></i>Recent Bookings</span>
                <a href="<?= SITE_URL ?>/admin/bookings.php" class="btn btn-sm btn-outline-primary">
                    View All
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table admin-table mb-0">
                        <thead>
                            <tr>
                                <th class="ps-3">Guest</th>
                                <th>Room</th>
                                <th>Check-in</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($recent_bookings->num_rows === 0): ?>
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">
                                    No bookings yet
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php while ($b = $recent_bookings->fetch_assoc()): ?>
                            <tr>
                                <td class="ps-3">
                                    <div class="fw-semibold small"><?= $b['guest_name'] ?></div>
                                    <div class="text-muted" style="font-size:0.75rem;">
                                        <?= $b['guest_email'] ?>
                                    </div>
                                </td>
                                <td class="small"><?= $b['room_name'] ?></td>
                                <td class="small">
                                    <?= date('d M Y', strtotime($b['check_in'])) ?>
                                </td>
                                <td>
                                    <span class="status-badge status-<?= $b['status'] ?>">
                                        <?= ucfirst($b['status']) ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Upcoming Bookings -->
    <div class="col-lg-5">
        <div class="admin-card card h-100">
            <div class="card-header">
                <i class="bi bi-calendar-event me-2"></i>Upcoming Check-ins
            </div>
            <div class="card-body p-0">
                <?php if ($upcoming->num_rows === 0): ?>
                <div class="text-center text-muted py-5">
                    <i class="bi bi-calendar-x d-block mb-2" style="font-size:2rem;"></i>
                    No upcoming check-ins
                </div>
                <?php else: ?>
                <ul class="list-group list-group-flush">
                    <?php while ($u = $upcoming->fetch_assoc()): ?>
                    <li class="list-group-item px-3 py-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="fw-semibold small"><?= $u['guest_name'] ?></div>
                                <div class="text-muted" style="font-size:0.78rem;">
                                    <?= $u['room_name'] ?>
                                </div>
                            </div>
                            <div class="text-end">
                                <div class="small fw-semibold text-primary">
                                    <?= date('d M', strtotime($u['check_in'])) ?>
                                </div>
                                <div class="text-muted" style="font-size:0.75rem;">
                                    <?= calcNights($u['check_in'], $u['check_out']) ?> nights
                                </div>
                            </div>
                        </div>
                    </li>
                    <?php endwhile; ?>
                </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>

</div>

<?php require_once 'includes/admin_footer.php'; ?>