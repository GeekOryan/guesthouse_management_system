<?php
//  public/booking.php
//  Booking page — availability check + booking form

$page_title = "Book a Room";
require_once '../includes/header.php';

// --- Pre-fill from URL if coming from homepage form or "Book Room" button
$selected_room_id = isset($_GET['room_id']) ? (int)$_GET['room_id'] : 0;
$check_in         = isset($_GET['check_in'])  ? $_GET['check_in']  : '';
$check_out        = isset($_GET['check_out']) ? $_GET['check_out'] : '';
$num_guests       = isset($_GET['guests'])    ? (int)$_GET['guests'] : 2;

// --- Fetch all active rooms for the dropdown
$rooms_result = $conn->query("SELECT * FROM rooms WHERE is_active = 1 ORDER BY price_per_night ASC");
$rooms = [];
while ($r = $rooms_result->fetch_assoc()) {
    $rooms[] = $r;
}

// --- Handle booking form submission (POST request)
$booking_success = false;
$booking_error   = '';
$available_rooms = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // --- Step 1: Check Availability (AJAX or form submit)
    if (isset($_POST['action']) && $_POST['action'] === 'check_availability') {

        $check_in   = sanitize($_POST['check_in']);
        $check_out  = sanitize($_POST['check_out']);
        $num_guests = (int)$_POST['num_guests'];
        $room_id    = (int)$_POST['room_id'];

        // Build query — find rooms not booked in the selected date range
        $query = "SELECT * FROM rooms WHERE is_active = 1 AND capacity >= $num_guests";

        // Filter by specific room if selected
        if ($room_id > 0) {
            $query .= " AND id = $room_id";
        }

        $all_rooms = $conn->query($query);

        while ($room = $all_rooms->fetch_assoc()) {
            // Check if room has any CONFIRMED booking overlapping with dates
            $rid = $room['id'];
            $overlap = $conn->query("
                SELECT id FROM bookings 
                WHERE room_id = $rid 
                AND status != 'cancelled'
                AND check_in  < '$check_out' 
                AND check_out > '$check_in'
            ");

            // Also check blocked dates
            $blocked = $conn->query("
                SELECT id FROM blocked_dates
                WHERE room_id = $rid
                AND blocked_date >= '$check_in'
                AND blocked_date <  '$check_out'
            ");

            // Room is available if no overlapping bookings AND no blocked dates
            if ($overlap->num_rows === 0 && $blocked->num_rows === 0) {
                $room['nights']      = calcNights($check_in, $check_out);
                $room['total_price'] = calcTotalPrice($room['price_per_night'], $check_in, $check_out);
                $available_rooms[]   = $room;
            }
        }
    }

    // --- Step 2: Confirm Booking
    if (isset($_POST['action']) && $_POST['action'] === 'confirm_booking') {

        $room_id         = (int)$_POST['room_id'];
        $check_in        = sanitize($_POST['check_in']);
        $check_out       = sanitize($_POST['check_out']);
        $num_guests      = (int)$_POST['num_guests'];
        $guest_name      = sanitize($_POST['guest_name']);
        $guest_email     = sanitize($_POST['guest_email']);
        $guest_phone     = sanitize($_POST['guest_phone']);
        $special_requests = sanitize($_POST['special_requests']);
        $total_price     = calcTotalPrice(
            $conn->query("SELECT price_per_night FROM rooms WHERE id = $room_id")
                 ->fetch_assoc()['price_per_night'],
            $check_in, $check_out
        );

        // Basic validation
        if (empty($guest_name) || empty($guest_email) || empty($check_in) || empty($check_out)) {
            $booking_error = "Please fill in all required fields.";
        } elseif (strtotime($check_out) <= strtotime($check_in)) {
            $booking_error = "Check-out date must be after check-in date.";
        } else {
            // Insert booking into DB
            $stmt = $conn->prepare("
                INSERT INTO bookings 
                (room_id, guest_name, guest_email, guest_phone, check_in, check_out, num_guests, total_price, special_requests)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param(
                "isssssids",
                $room_id, $guest_name, $guest_email, $guest_phone,
                $check_in, $check_out, $num_guests, $total_price, $special_requests
            );

            if ($stmt->execute()) {
                $booking_success = true;
                $booking_ref = "GH-" . str_pad($stmt->insert_id, 5, "0", STR_PAD_LEFT);
            } else {
                $booking_error = "Something went wrong. Please try again.";
            }
            $stmt->close();
        }
    }
}
?>

<!--PAGE HEADER -->
<section class="page-header text-white text-center py-5">
    <div class="container">
        <p class="section-subtitle text-white mb-2">Reservations</p>
        <h1 class="mb-3">Book Your Stay</h1>
        <p class="opacity-75">Check availability and reserve your room in minutes.</p>
    </div>
</section>

<section class="py-6">
<div class="container">

<?php if ($booking_success): ?>
<!--SUCCESS MESSAGE -->
<div class="row justify-content-center">
    <div class="col-md-7 text-center">
        <div class="booking-success-card p-5">
            <div class="success-icon mb-4">
                <i class="bi bi-check-circle-fill"></i>
            </div>
            <h2 class="mb-3">Booking Received!</h2>
            <p class="text-muted mb-2">Thank you, <strong><?= $guest_name ?></strong>!</p>
            <p class="text-muted mb-4">
                Your booking reference is: 
                <strong class="text-primary fs-5"><?= $booking_ref ?></strong>
            </p>
            <div class="booking-summary p-4 mb-4 text-start">
                <div class="row g-2">
                    <div class="col-6">
                        <small class="text-muted d-block">Check-in</small>
                        <strong><?= date('D, d M Y', strtotime($check_in)) ?></strong>
                    </div>
                    <div class="col-6">
                        <small class="text-muted d-block">Check-out</small>
                        <strong><?= date('D, d M Y', strtotime($check_out)) ?></strong>
                    </div>
                    <div class="col-6 mt-2">
                        <small class="text-muted d-block">Guests</small>
                        <strong><?= $num_guests ?></strong>
                    </div>
                    <div class="col-6 mt-2">
                        <small class="text-muted d-block">Total Price</small>
                        <strong class="text-primary"><?= formatPrice($total_price) ?></strong>
                    </div>
                </div>
            </div>
            <p class="text-muted small mb-4">
                A confirmation email will be sent to <strong><?= $guest_email ?></strong>. 
                We will confirm your booking within 24 hours.
            </p>
            <a href="<?= SITE_URL ?>" class="btn btn-primary px-5">Back to Home</a>
        </div>
    </div>
</div>

<?php else: ?>

<div class="row g-5">

    <!--LEFT: BOOKING FORM -->
    <div class="col-lg-8">

        <!-- STEP 1: Availability Check -->
        <div class="booking-step-card p-4 mb-4">
            <h5 class="mb-4">
                <span class="step-number">1</span> Check Availability
            </h5>
            <form method="POST" id="availabilityForm">
                <input type="hidden" name="action" value="check_availability">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Check-in Date <span class="text-danger">*</span></label>
                        <input type="date" name="check_in" id="check_in" class="form-control"
                               min="<?= date('Y-m-d') ?>"
                               value="<?= htmlspecialchars($check_in) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Check-out Date <span class="text-danger">*</span></label>
                        <input type="date" name="check_out" id="check_out" class="form-control"
                               min="<?= date('Y-m-d', strtotime('+1 day')) ?>"
                               value="<?= htmlspecialchars($check_out) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Number of Guests</label>
                        <select name="num_guests" class="form-select">
                            <?php for ($i = 1; $i <= 4; $i++): ?>
                            <option value="<?= $i ?>" <?= $num_guests == $i ? 'selected' : '' ?>>
                                <?= $i ?> Guest<?= $i > 1 ? 's' : '' ?>
                            </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Room Preference</label>
                        <select name="room_id" class="form-select">
                            <option value="0">Any Available Room</option>
                            <?php foreach ($rooms as $r): ?>
                            <option value="<?= $r['id'] ?>" <?= $selected_room_id == $r['id'] ? 'selected' : '' ?>>
                                <?= $r['name'] ?> — <?= formatPrice($r['price_per_night']) ?>/night
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary px-5">
                            <i class="bi bi-search me-2"></i>Check Availability
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- STEP 2: Available Rooms (shown after search) -->
        <?php if (!empty($available_rooms)): ?>
        <div class="booking-step-card p-4 mb-4" id="availableRooms">
            <h5 class="mb-4">
                <span class="step-number">2</span> 
                Select a Room
                <small class="text-muted fw-normal ms-2">
                    <?= count($available_rooms) ?> room<?= count($available_rooms) > 1 ? 's' : '' ?> available
                </small>
            </h5>
            <?php foreach ($available_rooms as $room): ?>
            <div class="available-room-card p-3 mb-3">
                <div class="row align-items-center">
                    <div class="col-md-5">
                        <h6 class="mb-1"><?= $room['name'] ?></h6>
                        <small class="text-muted">
                            <i class="bi bi-people me-1"></i>Up to <?= $room['capacity'] ?> guests
                        </small>
                        <div class="d-flex flex-wrap gap-1 mt-2">
                            <?php foreach (array_slice(explode(',', $room['amenities']), 0, 3) as $a): ?>
                            <span class="amenity-badge"><?= trim($a) ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="col-md-4 text-center">
                        <div class="room-price"><?= formatPrice($room['total_price']) ?></div>
                        <small class="text-muted">
                            <?= $room['nights'] ?> night<?= $room['nights'] > 1 ? 's' : '' ?> × 
                            <?= formatPrice($room['price_per_night']) ?>
                        </small>
                    </div>
                    <div class="col-md-3 text-end">
                        <button class="btn btn-primary btn-sm px-3 select-room-btn"
                                data-room-id="<?= $room['id'] ?>"
                                data-room-name="<?= $room['name'] ?>"
                                data-total="<?= $room['total_price'] ?>"
                                data-nights="<?= $room['nights'] ?>">
                            Select Room
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- STEP 3: Guest Details Form -->
        <div class="booking-step-card p-4" id="guestDetailsForm" style="display:none;">
            <h5 class="mb-4">
                <span class="step-number">3</span> Your Details
            </h5>
            <?php if (!empty($booking_error)): ?>
            <div class="alert alert-danger"><?= $booking_error ?></div>
            <?php endif; ?>
            <form method="POST">
                <input type="hidden" name="action"     value="confirm_booking">
                <input type="hidden" name="room_id"    id="selected_room_id" value="">
                <input type="hidden" name="check_in"   value="<?= htmlspecialchars($check_in) ?>">
                <input type="hidden" name="check_out"  value="<?= htmlspecialchars($check_out) ?>">
                <input type="hidden" name="num_guests" value="<?= $num_guests ?>">

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="guest_name" class="form-control" 
                               placeholder="John Doe" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
                        <input type="email" name="guest_email" class="form-control" 
                               placeholder="john@example.com" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Phone Number</label>
                        <input type="tel" name="guest_phone" class="form-control" 
                               placeholder="+27 12 345 6789">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Special Requests</label>
                        <textarea name="special_requests" class="form-control" rows="3"
                                  placeholder="Any special requirements? Early check-in, dietary needs, etc."></textarea>
                    </div>
                    <div class="col-12 mt-2">
                        <button type="submit" class="btn btn-primary btn-lg px-5">
                            <i class="bi bi-calendar-check me-2"></i>Confirm Booking
                        </button>
                        <p class="text-muted small mt-2">
                            <i class="bi bi-lock me-1"></i>Your information is safe and secure.
                        </p>
                    </div>
                </div>
            </form>
        </div>

        <?php elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($available_rooms)): ?>
        <!-- No rooms available -->
        <div class="booking-step-card p-4 text-center">
            <i class="bi bi-calendar-x" style="font-size:2.5rem; color:var(--primary);"></i>
            <h5 class="mt-3">No Rooms Available</h5>
            <p class="text-muted">
                Sorry, no rooms are available for your selected dates and guest count. 
                Try different dates or contact us directly.
            </p>
            <a href="<?= SITE_URL ?>/public/contact.php" class="btn btn-outline-primary">
                Contact Us
            </a>
        </div>
        <?php endif; ?>

    </div>

    <!-- RIGHT: BOOKING SUMMARY -->
    <div class="col-lg-4">
        <div class="booking-summary-card p-4 sticky-top" style="top: 100px;">
            <h6 class="fw-bold mb-4">Booking Summary</h6>

            <div class="summary-row">
                <span class="text-muted">Check-in</span>
                <strong id="summary-checkin">
                    <?= !empty($check_in) ? date('d M Y', strtotime($check_in)) : '—' ?>
                </strong>
            </div>
            <div class="summary-row">
                <span class="text-muted">Check-out</span>
                <strong id="summary-checkout">
                    <?= !empty($check_out) ? date('d M Y', strtotime($check_out)) : '—' ?>
                </strong>
            </div>
            <div class="summary-row">
                <span class="text-muted">Guests</span>
                <strong><?= $num_guests ?></strong>
            </div>
            <div class="summary-row">
                <span class="text-muted">Room</span>
                <strong id="summary-room">—</strong>
            </div>
            <div class="summary-row">
                <span class="text-muted">Nights</span>
                <strong id="summary-nights">—</strong>
            </div>
            <hr>
            <div class="summary-row">
                <span class="fw-bold">Total</span>
                <strong class="text-primary fs-5" id="summary-total">—</strong>
            </div>

            <div class="mt-4">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <i class="bi bi-shield-check text-success"></i>
                    <small>Secure booking</small>
                </div>
                <div class="d-flex align-items-center gap-2 mb-2">
                    <i class="bi bi-arrow-counterclockwise text-success"></i>
                    <small>Free cancellation within 24hrs</small>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-headset text-success"></i>
                    <small>24/7 guest support</small>
                </div>
            </div>
        </div>
    </div>

</div>
<?php endif; ?>
</div>
</section>

<?php require_once '../includes/footer.php'; ?>

<!-- JavaScript for room selection -->
<script>
// When guest clicks "Select Room" update the summary panel
// and show the guest details form
document.querySelectorAll('.select-room-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {

        // Get data from button attributes
        const roomId   = this.dataset.roomId;
        const roomName = this.dataset.roomName;
        const total    = parseFloat(this.dataset.total);
        const nights   = this.dataset.nights;

        // Update hidden input
        document.getElementById('selected_room_id').value = roomId;

        // Update summary panel
        document.getElementById('summary-room').textContent  = roomName;
        document.getElementById('summary-nights').textContent = nights + ' night' + (nights > 1 ? 's' : '');
        document.getElementById('summary-total').textContent  = 'R ' + total.toFixed(2);

        // Show guest details form
        document.getElementById('guestDetailsForm').style.display = 'block';

        // Scroll down to the form smoothly
        document.getElementById('guestDetailsForm').scrollIntoView({ behavior: 'smooth' });

        // Highlight selected room
        document.querySelectorAll('.available-room-card').forEach(c => c.classList.remove('selected'));
        this.closest('.available-room-card').classList.add('selected');
    });
});
</script>