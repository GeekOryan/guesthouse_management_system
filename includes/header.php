<?php

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/functions.php';

session_start();

$site_name = getSetting('site_name');
$site_phone = getSetting('site_phone');
$site_email = getSetting('site_email');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?=  isset($page_title) ? $page_title . ' | ' . $site_name : $site_name ?></title>

     <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500&display=swap" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="<?= SITE_URL ?>/public/assets/css/style.css" rel="stylesheet">

    <?php
    // Fetching the theme settings
    $theme_color = getSetting('theme_color') ?: '#8B6914';
    $theme_color_dark = getSetting('theme_color_dark') ?: '#6B5010';
    $theme_bg = getSetting('theme_bg') ?: '#faf9f7';
    $theme_font = getSetting('theme_font') ?: 'Inter';
    $dark_mode = getSetting('dark_mode') ?: '0';
    $site_logo = getSetting('site_logo') ?: '';

    // Load selected Google Font
    $font_url = str_replace(' ', '+', $theme_font);
    ?>

    <!-- Dynamic Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=<?= $font_url ?>:wght@300;400;500;700&family=Playfair+Display:wght@400;600;700&display=swap" rel="stylesheet">

    <!-- Dynamic Theme Variables -->
    <style>
:root {
    --primary:     <?= htmlspecialchars($theme_color) ?>;
    --primary-dark:<?= htmlspecialchars($theme_color_dark) ?>;
    --light-bg:    <?= htmlspecialchars($theme_bg) ?>;
}
body {
    font-family: '<?= htmlspecialchars($theme_font) ?>', sans-serif;
    <?= $dark_mode === '1' ? 'background:#0f0f1a; color:#e0e0e0;' : '' ?>
}
<?php if ($dark_mode === '1'): ?>
/* Dark mode overrides */
.navbar        { background: #1a1a2e !important; }
.nav-link      { color: #ccc !important; }
.card, .booking-step-card, .booking-summary-card,
.room-card, .contact-form-card { background: #1e1e30 !important; color: #e0e0e0; }
.card-title, h1, h2, h3, h4, h5, h6 { color: #ffffff !important; }
.text-muted    { color: #aaaaaa !important; }
.bg-white      { background: #1e1e30 !important; }
.form-control  { background: #2a2a40; color: #e0e0e0; border-color: #444; }
.form-select   { background: #2a2a40; color: #e0e0e0; border-color: #444; }
.table         { color: #e0e0e0; }
.top-bar       { background: #0f0f1a !important; }
section.bg-white { background: #141428 !important; }
.booking-bar   { background: #1e1e30 !important; }
<?php endif; ?>
</style>

<?php if (!empty($site_logo)): ?>
<style>
/* Hide text brand, show logo */
.brand-name { display: none !important; }
.navbar-brand::before {
    content: '';
    display: inline-block;
    background: url('<?= SITE_URL ?>/public/assets/images/uploads/<?= htmlspecialchars($site_logo) ?>') no-repeat center/contain;
    width: 120px;
    height: 40px;
    vertical-align: middle;
}
</style>
<?php endif; ?>
    
</head>
<body>

<div class="top-bar py-2 d-none d-md-block">
    <div class="container d-flex justify-content-between align-items-center">
        <div class="d-flex gap-4">
            <span><i class="bi bi-telephone-fill me-1"></i><?=  $site_phone ?></span>
            <span><i class="bi bi-envelope-fill me-1"></i><?=  $site_email ?></span>
        </div>
        <div>
            <a href="#" class="me-2"><i class="bi bi-facebook"></i></a>
            <a href="#" class="me-2"><i class="bi bi-instagram"></i></a>
            <a href="#"><i class="bi bi-twitter"></i></a>
        </div>
    </div>
</div>

<!-- Main Navigation Bar -->
<nav class="navbar navbar-expand-lg navbar-light sticky-top">
    <div class="container">
        <a class="navbar-brand" href="<?=  SITE_URL ?>">
            <span class="brand-name"><?=  $site_name ?></span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNav">
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>" href="<?= SITE_URL ?>">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'rooms.php' ? 'active' : '' ?>" href="<?= SITE_URL ?>/public/rooms.php">Rooms</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'gallery.php' ? 'active' : '' ?>" href="<?= SITE_URL ?>/public/gallery.php">Gallery</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'about.php' ? 'active' : '' ?>" href="<?= SITE_URL ?>/public/about.php">About</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'contact.php' ? 'active' : '' ?>" href="<?= SITE_URL ?>/public/contact.php">Contact</a>
                </li>
                <li class="nav-item ms-2">
                    <a class="btn btn-primary px-4" href="<?= SITE_URL ?>/public/booking.php">Book Now</a>
                </li>
            </ul>
        </div>
    </div>
</nav>
    
    
</body>
</html>
