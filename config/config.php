<?php
// This is the brain of the app. SAFE TO COMMIT TO GITHUB.

// ==========================================================
// 1. DYNAMIC SITE URL
// ==========================================================
$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];

if (in_array($host, ['localhost', '127.0.0.1'])) {
    $script_dir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
    $path_parts = explode('/', trim($script_dir, '/'));
    $project_folder = $path_parts[0] ?? '';
    define('SITE_URL', $protocol . '://' . $host . ($project_folder ? '/' . $project_folder : ''));
} else {
    define('SITE_URL', $protocol . '://' . $host);
}

// ==========================================================
// 2. DATABASE SETTINGS (Reads from Render Environment Variables)
// ==========================================================
define('DB_HOST', getenv('DB_HOST') ?: 'gateway01.eu-central-1.prod.aws.tidbcloud.com');
define('DB_USER', getenv('DB_USER') ?: '4XbDHSaMVmT7v4L.root');
define('DB_PASS', getenv('DB_PASS') ?: 'ZxXGyqb8qthRzwY6'); // <-- PUT YOUR PASSWORD HERE
define('DB_NAME', getenv('DB_NAME') ?: 'guesthouse');
define('DB_PORT', getenv('DB_PORT') ?: '4000');

// ==========================================================
// 3. SITE & PATH SETTINGS
// ==========================================================
define('SITE_NAME', 'Yves Saint Oryan Guesthouse');
define('SITE_EMAIL', 'info@grandguesthouse.com');
define('SITE_PHONE', '+27 12 345 6789');
define('SECRET_KEY', 'change-this-to-a-random-string-in-production');

define('ROOT_PATH', dirname(__DIR__));
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('UPLOAD_PATH', PUBLIC_PATH . '/assets/images/uploads');
?>