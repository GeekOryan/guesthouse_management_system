<?php
// This file ensures that anyone visiting the root of the site 
// is automatically redirected to the public folder.

require_once __DIR__ . '/config/config.php';

// Redirect to the public folder using the dynamically defined SITE_URL
header("Location: " . SITE_URL . "/public/");
exit();
?>