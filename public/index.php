<?php

$page_title = "Welcome";
require_once '../includes/header.php';

// Fetching rooms from DB for the "Our Rooms" preview section
$rooms_result = $conn->query("SELECT * FROM rooms WHERE is_active = 1 LIMIT 3");
?>

<!-- HERO SECTION -->
<section class="hero">
    <div class="container text-center">
        <p class="section-subtitle text-white mb-3">Welcome to</p>
        <h1 class="mb-4"><?= $site_name ?></h1>
        <p class="mb-5 mx-auto" style="max-width:600px;">
            Experience comfort, elegance, and warm hospitality in the heart of Johannesburg.
            Your perfect home away from home awaits.
        </p>
        <div class="d-flex gap-3 justify-content-center flex-wrap">
            <a href="<?= SITE_URL ?>/public/booking.php" class="btn btn-primary btn-lg px-5">
                Book Now
            </a>
            <a href="<?= SITE_URL ?>/public/rooms.php" class="btn btn-outline-light btn-lg px-5">
                View Rooms
            </a>
        </div>
    </div>
</section>

<!-- BOOKING BAR -->
<section class="booking-bar py-4 shadow">
    <div class="container">
        <form action="<?= SITE_URL ?>/public/booking.php" method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label fw-semibold">Check-in Date</label>
                <input type="date" name="check_in" class="form-control" min="<?= date('Y-m-d') ?>" required>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Check-out Date</label>
                <input type="date" name="check_in" class="form-control" min="<?= date('Y-m-d', strtotime('+1 day')) ?>" required>
            </div>

            <div class="col-md-2">
                <label class="form-label fw-semibold">Guests</label>
                <select name="guests" class="form-select">
                    <option value="1">1 Guest</option>
                    <option value="2" selected>2 Guest</option>
                    <option value="3">3 Guest</option>
                    <option value="4">4 Guest</option>
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label fw-semibold">Room Type</label>
                <select name="room_id" class="form-select">
                    <option value="">Any Room</option>
                    <?php
                    $all_rooms = $conn->query("SELECT id, name FROM rooms WHERE is_active = 1");
                    while ($r = $all_rooms->fetch_assoc()):
                    ?>
                    <option value="<?= $r['id'] ?>"><?= $r['name'] ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100 py-2">
                    <i class="bi bi-search me-1"></i>Check Availability
                </button>
            </div>
        </form>
    </div>
</section>

<!-- The "Why Choose Us" Section -->

<section class="py-6 bg-white">
    <div class="container text-center">
        <p class="section-subtitle">Why Choose Us</p>
        <h2 class="section-title mb-2">A Stay You Will Remember</h2>
        <div class="title-divider mx-auto mb-5"></div>
        <div class="row g-4">
            <div class="col-md-3">
                <div class="feature-box p-4">
                    <div class="feature-icon mb-3">
                        <i class="bi bi-house-heart"></i>
                    </div>
                    <h5>Homely Comfort</h5>
                    <p class="text-muted small">Warm, welcoming rooms designed to make you feel right at home.</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="feature-box p-4">
                    <div class="feature-icon mb-3">
                        <i class="bi bi-wifi"></i>
                    </div>
                    <h5>Free WiFi</h5>
                    <p class="text-muted small">Stay connected with high-speed internet throughout the property.</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="feature-box p-4">
                    <div class="feature-icon mb-3">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <h5>Safe & Secure</h5>
                    <p class="text-muted small">24/7 security so you can relax and enjoy your stay with peace of mind.</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="feature-box p-4">
                    <div class="feature-icon mb-3">
                        <i class="bi bi-geo-alt"></i>
                    </div>
                    <h5>Great Location</h5>
                    <p class="text-muted small">Centrally located with easy access to restaurants, shops and attractions.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ROOMS PREVIEW -->
<section class="py-6" style="background: var(--light-bg);">
    <div class="container text-center">
        <p class="section-subtitle">Accommodation</p>
        <h2 class="section-title mb-2">Our Rooms</h2>
        <div class="title-divider mx-auto mb-5"></div>
        <div class="row g-4">
            <?php while ($room = $rooms_result->fetch_assoc()): ?>
            <div class="col-md-4">
                <div class="card room-card h-100">
                    <!-- Room Image -->
                    <img src="<?= !empty($room['image'])
                        ? SITE_URL . '/public/assets/images/uploads/' . $room['image']
                        : SITE_URL . '/public/assets/images/room-placeholder.jpg' ?>"
                        alt="<?= $room['name'] ?>"
                        class="card-img-top">
                    <div class="card-body text-start p-4">
                        <h5 class="card-title"><?= $room['name'] ?></h5>
                        <p class="text-muted small mb-3"><?= $room['description'] ?></p>
                        <!-- Amenities -->
                        <div class="d-flex flex-wrap gap-1 mb-3">
                            <?php
                            $amenities = explode(',', $room['amenities']);
                            foreach (array_slice($amenities, 0, 3) as $amenity):
                            ?>
                            <span class="amenity-badge"><?= trim($amenity) ?></span>
                            <?php endforeach; ?>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="room-price"><?= formatPrice($room['price_per_night']) ?></span>
                                <span class="text-muted small"> / night</span>
                            </div>
                            <a href="<?= SITE_URL ?>/public/booking.php?room_id=<?= $room['id'] ?>" 
                               class="btn btn-primary btn-sm px-3">
                                Book Room
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
        <div class="mt-5">
            <a href="<?= SITE_URL ?>/public/rooms.php" class="btn btn-outline-primary px-5">
                View All Rooms
            </a>
        </div>
    </div>
</section>

<!-- Call to Action -->
<section class="cta-section py-6 text-white text-center">
    <div class="container">
        <h2 class="mb-3">Ready for an Unforgettable Stay?</h2>
        <p class="mb-4 opacity-75">Book directly with us for the best rates - no hidden fees, no booking charges.</p>
        <a href="<?= SITE_URL ?>/public/booking.php" class="btn btn-light btn-lg px-5 text-primary fw-semibold">
            Book Your Stay Today
        </a>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>