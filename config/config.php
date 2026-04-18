<?php

// This is the brain of the app, all the global settings in one place.
// If there needs to be a change in password or site name, it can only be changed here.

// config/config.php
// Global configuration - DB credentials + site settings
// Edit this file when deploying to a live server

// DATABASE SETTINGS

define('DB_HOST', 'localhost'); // XAMPP default
define('DB_USER', 'root'); // XAMPP default username
define('DB_PASS', ''); // XAMPP default = no password
define('DB_NAME', 'guesthouse'); // DB will created later on.

// Site Settings
define('SITE_NAME', 'The Grand Guesthouse');
define('SITE_EMAIL', 'info@grandguesthouse.com');
define('SITE_PHONE', '+27 12 345 6789');
// define('SITE_URL', 'http://localhost/guesthouse');
define('SITE_URL', 'http://192.168.0.2/guesthouse');

// Security
define('SECRET_KEY', 'change-this-to-a-random-string-in-production');

// Paths
define('ROOT_PATH', dirname(__DIR__)); // Project root
define('PUBLIC_PATH', ROOT_PATH. '/pubic');
define('UPLOAD_PATH', PUBLIC_PATH . '/assets/images/upload');
?>