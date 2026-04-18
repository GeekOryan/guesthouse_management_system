<?php
// ============================================================
//  public/about.php
//  About page
// ============================================================
$page_title = "About Us";
require_once '../includes/header.php';
?>

<!-- ===================== PAGE HEADER ===================== -->
<section class="page-header text-white text-center py-5">
    <div class="container">
        <p class="section-subtitle text-white mb-2">Our Story</p>
        <h1 class="mb-3">About Us</h1>
        <p class="opacity-75">A little bit about who we are and what we stand for.</p>
    </div>
</section>

<!-- ===================== OUR STORY ======================= -->
<section class="py-6 bg-white">
    <div class="container">
        <div class="row g-5 align-items-center">
            <div class="col-lg-6">
                <p class="section-subtitle">Who We Are</p>
                <h2 class="section-title mb-3">A Place That Feels Like Home</h2>
                <div class="title-divider mb-4"></div>
                <p class="text-muted mb-3">
                    Welcome to <?= $site_name ?> — a warm, family-run guesthouse 
                    nestled in the heart of Johannesburg. We opened our doors with one 
                    simple goal: to offer every guest a comfortable, affordable, and 
                    memorable stay.
                </p>
                <p class="text-muted mb-3">
                    Whether you're travelling for business or leisure, we pride ourselves 
                    on personal service, spotless rooms, and an atmosphere that makes you 
                    feel truly welcome from the moment you arrive.
                </p>
                <p class="text-muted mb-4">
                    Our team is available around the clock to ensure your stay is nothing 
                    short of perfect. We believe that great hospitality is about the little 
                    things — a warm smile, a helpful recommendation, and a room that exceeds 
                    your expectations.
                </p>
                <a href="<?= SITE_URL ?>/public/booking.php" class="btn btn-primary px-5">
                    Book a Stay
                </a>
            </div>
            <div class="col-lg-6">
                <img src="<?= SITE_URL ?>/public/assets/images/about.jpg"
                     onerror="this.src='https://placehold.co/600x400/8B6914/fff?text=Our+Guesthouse'"
                     alt="About our guesthouse"
                     class="img-fluid rounded shadow">
            </div>
        </div>
    </div>
</section>

<!-- ===================== STATS =========================== -->
<section class="py-5" style="background: var(--light-bg);">
    <div class="container">
        <div class="row g-4 text-center">
            <div class="col-md-3">
                <div class="stat-box p-4">
                    <h2 class="stat-number">10+</h2>
                    <p class="text-muted mb-0">Years of Hospitality</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-box p-4">
                    <h2 class="stat-number">500+</h2>
                    <p class="text-muted mb-0">Happy Guests</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-box p-4">
                    <h2 class="stat-number">
                        <?php
                        $room_count = $conn->query("SELECT COUNT(*) as total FROM rooms WHERE is_active = 1");
                        echo $room_count->fetch_assoc()['total'];
                        ?>
                    </h2>
                    <p class="text-muted mb-0">Rooms Available</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-box p-4">
                    <h2 class="stat-number">24/7</h2>
                    <p class="text-muted mb-0">Guest Support</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===================== VALUES ========================== -->
<section class="py-6 bg-white">
    <div class="container text-center">
        <p class="section-subtitle">What We Stand For</p>
        <h2 class="section-title mb-2">Our Values</h2>
        <div class="title-divider mx-auto mb-5"></div>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="value-card p-4">
                    <div class="feature-icon mb-3">
                        <i class="bi bi-heart"></i>
                    </div>
                    <h5>Genuine Hospitality</h5>
                    <p class="text-muted small">
                        We treat every guest like family. Your comfort and happiness 
                        is our top priority from check-in to check-out.
                    </p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="value-card p-4">
                    <div class="feature-icon mb-3">
                        <i class="bi bi-star"></i>
                    </div>
                    <h5>Quality & Cleanliness</h5>
                    <p class="text-muted small">
                        Our rooms are cleaned and inspected daily to the highest 
                        standards. We never compromise on quality.
                    </p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="value-card p-4">
                    <div class="feature-icon mb-3">
                        <i class="bi bi-cash-coin"></i>
                    </div>
                    <h5>Honest Pricing</h5>
                    <p class="text-muted small">
                        What you see is what you pay. No hidden fees, no surprise 
                        charges — just transparent, fair pricing.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===================== CTA ============================= -->
<section class="cta-section py-5 text-white text-center">
    <div class="container">
        <h3 class="mb-3">Come Stay With Us</h3>
        <p class="mb-4 opacity-75">We would love to welcome you to <?= $site_name ?>.</p>
        <a href="<?= SITE_URL ?>/public/booking.php" class="btn btn-light px-5 text-primary fw-semibold">
            <i class="bi bi-calendar-check me-2"></i>Book Your Stay
        </a>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>