<?php

//  public/rooms.php
//  Rooms & Rates page — lists all active rooms from the DB

$page_title = "Rooms & Rates";
require_once '../includes/header.php';

// Fetch all active rooms from the database
$rooms_result = $conn->query("SELECT * FROM rooms WHERE is_active = 1 ORDER BY price_per_night ASC");
?>

<!-- PAGE HEADER -->
<section class="page-header text-white text-center py-5">
    <div class="container">
        <p class="section-subtitle text-white mb-2">Accommodation</p>
        <h1 class="mb-3">Our Rooms & Rates</h1>
        <p class="opacity-75 mx-auto" style="max-width:500px;">
            Choose from our selection of comfortable, well-appointed rooms — 
            each designed with your comfort in mind.
        </p>
    </div>
</section>

<!--ROOMS LISTING -->
<section class="py-6">
    <div class="container">

        <?php if ($rooms_result->num_rows === 0): ?>
        <!-- No rooms found -->
        <div class="text-center py-5">
            <i class="bi bi-house-x" style="font-size:3rem; color:var(--primary);"></i>
            <h4 class="mt-3">No rooms available at the moment</h4>
            <p class="text-muted">Please check back soon or contact us directly.</p>
        </div>

        <?php else: ?>
        <div class="row g-4">
            <?php while ($room = $rooms_result->fetch_assoc()): 
                $amenities = explode(',', $room['amenities']);
            ?>
            <div class="col-lg-12">
                <div class="card room-card-horizontal">
                    <div class="row g-0">

                        <!-- Room Image -->
                        <div class="col-md-4">
                            <img src="<?= !empty($room['image'])
                                ? SITE_URL . '/public/assets/images/uploads/' . $room['image']
                                : SITE_URL . '/public/assets/images/room-placeholder.jpg' ?>"
                                alt="<?= $room['name'] ?>"
                                class="img-fluid h-100 w-100"
                                style="object-fit:cover; min-height:250px;">
                        </div>

                        <!-- Room Details -->
                        <div class="col-md-8">
                            <div class="card-body p-4 h-100 d-flex flex-column">

                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h3 class="card-title mb-0"><?= $room['name'] ?></h3>
                                    <div class="text-end">
                                        <div class="room-price"><?= formatPrice($room['price_per_night']) ?></div>
                                        <small class="text-muted">per night</small>
                                    </div>
                                </div>

                                <!-- Capacity -->
                                <p class="text-muted small mb-3">
                                    <i class="bi bi-people me-1"></i>
                                    Up to <?= $room['capacity'] ?> guest<?= $room['capacity'] > 1 ? 's' : '' ?>
                                </p>

                                <!-- Description -->
                                <p class="text-muted mb-4"><?= $room['description'] ?></p>

                                <!-- Amenities -->
                                <div class="mb-4">
                                    <p class="fw-semibold small mb-2">Room Amenities:</p>
                                    <div class="d-flex flex-wrap gap-2">
                                        <?php foreach ($amenities as $amenity): ?>
                                        <span class="amenity-badge">
                                            <i class="bi bi-check-circle me-1"></i><?= trim($amenity) ?>
                                        </span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                                <!-- Book Button -->
                                <div class="mt-auto">
                                    <a href="<?= SITE_URL ?>/public/booking.php?room_id=<?= $room['id'] ?>"
                                       class="btn btn-primary px-5">
                                        <i class="bi bi-calendar-check me-2"></i>Book This Room
                                    </a>
                                    <span class="text-muted small ms-3">
                                        <i class="bi bi-shield-check me-1"></i>Free cancellation within 24hrs
                                    </span>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
        <?php endif; ?>

    </div>
</section>

<!--CALL TO ACTION -->
<section class="cta-section py-5 text-white text-center">
    <div class="container">
        <h3 class="mb-3">Not Sure Which Room to Choose?</h3>
        <p class="mb-4 opacity-75">Contact us and we'll help you find the perfect room for your stay.</p>
        <a href="<?= SITE_URL ?>/public/contact.php" class="btn btn-light px-5 text-primary fw-semibold">
            <i class="bi bi-headset me-2"></i>Contact Us
        </a>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>