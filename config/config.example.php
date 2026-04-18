<?php

// This is the config.example.php
// So you will copy this file to the config.php file and then fill in YOUR details

define('DB_HOST', 'localhost');
define('DB_USER', 'your_database_username');
define('DB_PASS', 'your_database_password');
define('DB_NAME', 'guesthouse');

define('SITE_NAME', 'Your Guesthouse Name');
define('SITE_EMAIL', 'info@yourgueshouse.com');
define('SITE_PHONE', '+27 11 222 3333');
define('SITE_URL', 'http://localhost/guesthouse');

define('SECRET_KEY', 'change-this-to-a-random-string');
define('ROOT_PATH', dirname(__DIR__));
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('UPLOAD_PATH', PUBLIC_PATH . '/assets/images/uploads');
?>

