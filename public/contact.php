<?php
//  public/contact.php
//  Contact page with working contact form
$page_title = "Contact Us";
require_once '../includes/header.php';

$success = false;
$error   = '';

// Handle contact form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name    = sanitize($_POST['name']);
    $email   = sanitize($_POST['email']);
    $phone   = sanitize($_POST['phone']);
    $subject = sanitize($_POST['subject']);
    $message = sanitize($_POST['message']);

    if (empty($name) || empty($email) || empty($message)) {
        $error = "Please fill in all required fields.";
    } elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        // Save message to DB — we'll add a messages table
        $stmt = $conn->prepare("
            INSERT INTO contact_messages (name, email, phone, subject, message)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("sssss", $name, $email, $phone, $subject, $message);

        if ($stmt->execute()) {
            $success = true;
        } else {
            $error = "Something went wrong. Please try again.";
        }
        $stmt->close();
    }
}
?>

<!--PAGE HEADER -->
<section class="page-header text-white text-center py-5">
    <div class="container">
        <p class="section-subtitle text-white mb-2">Get In Touch</p>
        <h1 class="mb-3">Contact Us</h1>
        <p class="opacity-75">We'd love to hear from you. Reach out anytime.</p>
    </div>
</section>

<!-- CONTACT SECTION -->
<section class="py-6">
    <div class="container">
        <div class="row g-5">

            <!-- Contact Info -->
            <div class="col-lg-4">
                <h4 class="mb-4">Get In Touch</h4>

                <div class="contact-info-item d-flex gap-3 mb-4">
                    <div class="contact-icon">
                        <i class="bi bi-geo-alt-fill"></i>
                    </div>
                    <div>
                        <h6 class="mb-1">Our Address</h6>
                        <p class="text-muted small mb-0"><?= getSetting('site_address') ?></p>
                    </div>
                </div>

                <div class="contact-info-item d-flex gap-3 mb-4">
                    <div class="contact-icon">
                        <i class="bi bi-telephone-fill"></i>
                    </div>
                    <div>
                        <h6 class="mb-1">Phone</h6>
                        <p class="text-muted small mb-0"><?= $site_phone ?></p>
                    </div>
                </div>

                <div class="contact-info-item d-flex gap-3 mb-4">
                    <div class="contact-icon">
                        <i class="bi bi-envelope-fill"></i>
                    </div>
                    <div>
                        <h6 class="mb-1">Email</h6>
                        <p class="text-muted small mb-0"><?= $site_email ?></p>
                    </div>
                </div>

                <div class="contact-info-item d-flex gap-3 mb-4">
                    <div class="contact-icon">
                        <i class="bi bi-clock-fill"></i>
                    </div>
                    <div>
                        <h6 class="mb-1">Reception Hours</h6>
                        <p class="text-muted small mb-0">Monday – Sunday: 06:00 – 22:00</p>
                    </div>
                </div>

                <div class="contact-info-item d-flex gap-3">
                    <div class="contact-icon">
                        <i class="bi bi-door-open-fill"></i>
                    </div>
                    <div>
                        <h6 class="mb-1">Check-in / Check-out</h6>
                        <p class="text-muted small mb-0">
                            Check-in: <?= getSetting('check_in_time') ?><br>
                            Check-out: <?= getSetting('check_out_time') ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="col-lg-8">
                <div class="contact-form-card p-4 p-md-5">

                    <?php if ($success): ?>
                    <div class="text-center py-4">
                        <i class="bi bi-check-circle-fill text-success" style="font-size:3rem;"></i>
                        <h4 class="mt-3">Message Sent!</h4>
                        <p class="text-muted">
                            Thank you for reaching out. We'll get back to you within 24 hours.
                        </p>
                        <a href="<?= SITE_URL ?>/public/contact.php" class="btn btn-primary px-4">
                            Send Another Message
                        </a>
                    </div>

                    <?php else: ?>

                    <h5 class="mb-4">Send Us a Message</h5>

                    <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    Full Name <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="name" class="form-control"
                                       placeholder="John Doe" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">
                                    Email Address <span class="text-danger">*</span>
                                </label>
                                <input type="email" name="email" class="form-control"
                                       placeholder="john@example.com" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Phone Number</label>
                                <input type="tel" name="phone" class="form-control"
                                       placeholder="+27 12 345 6789">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Subject</label>
                                <select name="subject" class="form-select">
                                    <option value="General Enquiry">General Enquiry</option>
                                    <option value="Booking Enquiry">Booking Enquiry</option>
                                    <option value="Rates & Availability">Rates & Availability</option>
                                    <option value="Complaint">Complaint</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">
                                    Message <span class="text-danger">*</span>
                                </label>
                                <textarea name="message" class="form-control" rows="5"
                                          placeholder="How can we help you?" required></textarea>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary px-5">
                                    <i class="bi bi-send me-2"></i>Send Message
                                </button>
                            </div>
                        </div>
                    </form>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
</section>

<!--CTA -->
<section class="cta-section py-5 text-white text-center">
    <div class="container">
        <h3 class="mb-3">Ready to Book?</h3>
        <p class="mb-4 opacity-75">Skip the queue and book your room directly online.</p>
        <a href="<?= SITE_URL ?>/public/booking.php" class="btn btn-light px-5 text-primary fw-semibold">
            <i class="bi bi-calendar-check me-2"></i>Book Now
        </a>
    </div>
</section>

<?php require_once '../includes/footer.php'; ?>