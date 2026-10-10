<?php
// This is the config.example.php
// Copy this file to config.php if needed. It now uses dynamic URLs and safe local defaults.

$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$script_dir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
$path_parts = explode('/', trim($script_dir, '/'));
$project_folder = $path_parts[0] ?? '';
define('SITE_URL', $protocol . '://' . $host . ($project_folder ? '/' . $project_folder : ''));

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'guesthouse');

define('SITE_NAME', 'Your Guesthouse Name');
define('SITE_EMAIL', 'info@yourgueshouse.com');
define('SITE_PHONE', '+27 11 222 3333');
define('SECRET_KEY', 'change-this-to-a-random-string');

define('ROOT_PATH', dirname(__DIR__));
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('UPLOAD_PATH', PUBLIC_PATH . '/assets/images/uploads');
?>