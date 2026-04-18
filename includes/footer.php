<?php

$site_address = getSetting('site_address');

?>




<!-- Footer -->

<footer class="footer mt-auto pt-5 pb-3">
    <div class="container">
        <div class="row g-4">
            <!-- Brand Column -->
             <div class="col-lg-4">
                <h5 class="footer-brand mb-3"><?= $site_name ?></h5>
                <p class="text-muted">Experience comfort and warmth in the heart of Johannesburg. Your home away from home.</p>
                <div class="d-flex gap-3 mt-3">
                    <a href="#" class="footer-social"><i class="bi bi-facebook"></i></a>
                    <a href="#" class="footer-social"><i class="bi bi-instagram"></i></a>
                    <a href="#" class="footer-social"><i class="bi bi-twitter"></i></a>
                </div>
             </div>

            <!-- Quick Links -->
            <div class="col-lg-2 col-md-4">
                <h6 class="footer-heading mb-3">Quick Links</h6>
                <ul class="list-unstyled footer-links">
                    <li><a href="<?= SITE_URL ?>">Home</a></li>
                    <li><a href="<?= SITE_URL ?>/public/rooms.php">Rooms</a></li>
                    <li><a href="<?= SITE_URL ?>/public/gallery.php">Gallery</a></li>
                    <li><a href="<?= SITE_URL ?>/public/about.php">About</a></li>
                    <li><a href="<?= SITE_URL ?>/public/contact.php">Contact</a></li>
                </ul> 
              </div>

            <!-- Contact Info -->
             <div class="col-lg-3 col-md-4">
                <h6 class="footer-heading mb-3">Contact Us</h6>
                <ul class="list-unstyled footer-links">
                    <li><i class="bi bi-geo-alt me-2"></i><?= $site_address ?></li>
                    <li><i class="bi bi-telephone me-2"></i><?= $site_phone ?></li>
                    <li><i class="bi bi-envelope me-2"></i><?= $site_email ?></li>
                </ul>
            </div>

            <!-- Check-in info -->
             <div class="col-lg-3 col-md-4">
                <h6 class="footer-heading mb-3">Guest Information</h6>
                <ul class="list-unstyled footer-links">
                    <li><i class="bi bi-clock me-2"></i>Check-in: <?= getSetting('check_in_time') ?></li>
                    <li><i class="bi bi-clock me-2"></i>Check-out: <?= getSetting('check_out_time') ?></li>
                    <li><i class="bi bi-shield-check me-2"></i>Secure Booking</li>
                    <li><i class="bi bi-headset me-2"></i>24/7 Support</li>
                </ul>
            </div>


        </div>

        <hr class="mt-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center">
            <p class="text-muted small mb-0">&copy; <?= date('Y') ?> <?= $site_name ?>. All rights reserved.</p>
            <p class="text-muted small mb-0">Built with ❤️ in Johannesburg</p>
        </div>

    </div>
</footer>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<!-- Custom JS -->
<script src="<?= SITE_URL ?>/public/assets/js/main.js"></script>
</body>
</html>
