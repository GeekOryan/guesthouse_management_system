<?php
// All logic first before any HTML
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/functions.php';

//Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $booking_id = (int)$_POST['booking_id'];
    $action     = sanitize($_POST['action']);

    if (in_array($action, ['confirmed', 'cancelled', 'pending'])) {
        $stmt = $conn->prepare("UPDATE bookings SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $action, $booking_id);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: " . SITE_URL . "/admin/bookings.php?updated=1");
    exit();
}

//Filter by status
$filter = isset($_GET['status']) ? sanitize($_GET['status']) : 'all';
$where  = $filter !== 'all' ? "WHERE b.status = '$filter'" : "";

// NOW load the header — all redirects are done
$page_title = "Bookings";
require_once 'includes/admin_header.php';

//Fetch bookings
$bookings = $conn->query("
    SELECT b.*, r.name as room_name, r.price_per_night
    FROM bookings b
    JOIN rooms r ON b.room_id = r.id
    $where
    ORDER BY b.created_at DESC
");

//Count per status
$counts = [];
foreach (['all', 'pending', 'confirmed', 'cancelled'] as $s) {
    $w = $s !== 'all' ? "WHERE status = '$s'" : "";
    $counts[$s] = $conn->query("SELECT COUNT(*) as total FROM bookings $w")
                       ->fetch_assoc()['total'];
}
?>

<!-- Success message -->
<?php if (isset($_GET['updated'])): ?>
<div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
    <i class="bi bi-check-circle me-2"></i>Booking status updated successfully.
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!--PAGE TITLE + FILTER-->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Bookings</h4>
        <p class="text-muted small mb-0">Manage all guest reservations</p>
    </div>
</div>

<!-- Filter Tabs -->
<div class="mb-4">
    <ul class="nav nav-pills gap-2">
        <?php foreach (['all' => 'All', 'pending' => 'Pending', 'confirmed' => 'Confirmed', 'cancelled' => 'Cancelled'] as $key => $label): ?>
        <li class="nav-item">
            <a class="nav-link <?= $filter === $key ? 'active' : '' ?>"
               href="<?= SITE_URL ?>/admin/bookings.php?status=<?= $key ?>"
               style="<?= $filter === $key ? 'background:var(--primary);' : 'color:#555;' ?>">
                <?= $label ?>
                <span class="badge bg-white ms-1"
                      style="color:<?= $filter === $key ? 'var(--primary)' : '#555' ?>">
                    <?= $counts[$key] ?>
                </span>
            </a>
        </li>
        <?php endforeach; ?>
    </ul>
</div>

<!--BOOKINGS TABLE -->
<div class="admin-card card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table admin-table mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">Ref</th>
                        <th>Guest</th>
                        <th>Room</th>
                        <th>Check-in</th>
                        <th>Check-out</th>
                        <th>Nights</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($bookings->num_rows === 0): ?>
                    <tr>
                        <td colspan="9" class="text-center text-muted py-5">
                            <i class="bi bi-calendar-x d-block mb-2" style="font-size:2rem;"></i>
                            No bookings found
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php while ($b = $bookings->fetch_assoc()):
                        $nights = calcNights($b['check_in'], $b['check_out']);
                    ?>
                    <tr>
                        <!-- Reference number -->
                        <td class="ps-3">
                            <span class="fw-semibold text-primary">
                                GH-<?= str_pad($b['id'], 5, "0", STR_PAD_LEFT) ?>
                            </span>
                        </td>

                        <!-- Guest details -->
                        <td>
                            <div class="fw-semibold small"><?= $b['guest_name'] ?></div>
                            <div class="text-muted" style="font-size:0.75rem;">
                                <?= $b['guest_email'] ?>
                            </div>
                            <?php if (!empty($b['guest_phone'])): ?>
                            <div class="text-muted" style="font-size:0.75rem;">
                                <?= $b['guest_phone'] ?>
                            </div>
                            <?php endif; ?>
                        </td>

                        <!-- Room -->
                        <td class="small"><?= $b['room_name'] ?></td>

                        <!-- Dates -->
                        <td class="small"><?= date('d M Y', strtotime($b['check_in'])) ?></td>
                        <td class="small"><?= date('d M Y', strtotime($b['check_out'])) ?></td>

                        <!-- Nights -->
                        <td class="small"><?= $nights ?></td>

                        <!-- Total price -->
                        <td class="small fw-semibold"><?= formatPrice($b['total_price']) ?></td>

                        <!-- Status badge -->
                        <td>
                            <span class="status-badge status-<?= $b['status'] ?>">
                                <?= ucfirst($b['status']) ?>
                            </span>
                        </td>

                        <!-- Action buttons -->
                        <td>
                            <div class="d-flex gap-1 flex-wrap">

                                <!-- View details button -->
                                <button class="btn btn-sm btn-outline-secondary"
                                        data-bs-toggle="modal"
                                        data-bs-target="#detailsModal"
                                        data-id="<?= $b['id'] ?>"
                                        data-name="<?= htmlspecialchars($b['guest_name']) ?>"
                                        data-email="<?= $b['guest_email'] ?>"
                                        data-phone="<?= $b['guest_phone'] ?>"
                                        data-room="<?= $b['room_name'] ?>"
                                        data-checkin="<?= date('d M Y', strtotime($b['check_in'])) ?>"
                                        data-checkout="<?= date('d M Y', strtotime($b['check_out'])) ?>"
                                        data-nights="<?= $nights ?>"
                                        data-guests="<?= $b['num_guests'] ?>"
                                        data-total="<?= formatPrice($b['total_price']) ?>"
                                        data-requests="<?= htmlspecialchars($b['special_requests'] ?? '') ?>"
                                        data-status="<?= $b['status'] ?>"
                                        data-created="<?= date('d M Y H:i', strtotime($b['created_at'])) ?>">
                                    <i class="bi bi-eye"></i>
                                </button>

                                <!-- Confirm button (only if pending) -->
                                <?php if ($b['status'] === 'pending'): ?>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                                    <input type="hidden" name="action" value="confirmed">
                                    <button type="submit" class="btn btn-sm btn-success"
                                            title="Confirm booking">
                                        <i class="bi bi-check-lg"></i>
                                    </button>
                                </form>
                                <?php endif; ?>

                                <!-- Cancel button (only if not already cancelled) -->
                                <?php if ($b['status'] !== 'cancelled'): ?>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="booking_id" value="<?= $b['id'] ?>">
                                    <input type="hidden" name="action" value="cancelled">
                                    <button type="submit" class="btn btn-sm btn-danger"
                                            title="Cancel booking"
                                            onclick="return confirm('Cancel this booking?')">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </form>
                                <?php endif; ?>

                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!--DETAILS MODAL -->
<div class="modal fade" id="detailsModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-calendar-check me-2"></i>
                    Booking Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted small">Reference</span>
                    <strong class="text-primary" id="modal-ref"></strong>
                </div>
                <hr class="my-2">
                <div class="row g-3">
                    <div class="col-6">
                        <small class="text-muted d-block">Guest Name</small>
                        <strong id="modal-name"></strong>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">Email</small>
                        <strong id="modal-email" style="font-size:0.85rem;"></strong>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">Phone</small>
                        <strong id="modal-phone"></strong>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">Room</small>
                        <strong id="modal-room"></strong>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">Check-in</small>
                        <strong id="modal-checkin"></strong>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">Check-out</small>
                        <strong id="modal-checkout"></strong>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">Nights</small>
                        <strong id="modal-nights"></strong>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">Guests</small>
                        <strong id="modal-guests"></strong>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">Total Price</small>
                        <strong class="text-primary" id="modal-total"></strong>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">Status</small>
                        <strong id="modal-status"></strong>
                    </div>
                    <div class="col-12">
                        <small class="text-muted d-block">Booked On</small>
                        <strong id="modal-created"></strong>
                    </div>
                    <div class="col-12" id="modal-requests-row">
                        <small class="text-muted d-block">Special Requests</small>
                        <strong id="modal-requests"></strong>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- JS to populate modal with booking details -->
<script>
document.querySelectorAll('[data-bs-target="#detailsModal"]').forEach(function(btn) {
    btn.addEventListener('click', function() {
        const id = this.dataset.id;
        document.getElementById('modal-ref').textContent      = 'GH-' + id.padStart(5, '0');
        document.getElementById('modal-name').textContent     = this.dataset.name;
        document.getElementById('modal-email').textContent    = this.dataset.email;
        document.getElementById('modal-phone').textContent    = this.dataset.phone || 'N/A';
        document.getElementById('modal-room').textContent     = this.dataset.room;
        document.getElementById('modal-checkin').textContent  = this.dataset.checkin;
        document.getElementById('modal-checkout').textContent = this.dataset.checkout;
        document.getElementById('modal-nights').textContent   = this.dataset.nights + ' night(s)';
        document.getElementById('modal-guests').textContent   = this.dataset.guests;
        document.getElementById('modal-total').textContent    = this.dataset.total;
        document.getElementById('modal-status').textContent   = this.dataset.status;
        document.getElementById('modal-created').textContent  = this.dataset.created;

        const requests = this.dataset.requests;
        const reqRow   = document.getElementById('modal-requests-row');
        if (requests && requests.trim() !== '') {
            document.getElementById('modal-requests').textContent = requests;
            reqRow.style.display = 'block';
        } else {
            reqRow.style.display = 'none';
        }
    });
});
</script>

<?php require_once 'includes/admin_footer.php'; ?>
