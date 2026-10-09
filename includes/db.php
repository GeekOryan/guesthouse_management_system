<?php
// Database connection using MySQLi with automatic SSL detection for TiDB Cloud

require_once dirname(__DIR__) . '/config/config.php';

// Initialize connection
$conn = mysqli_init();

// If we are connecting to TiDB Cloud, we MUST use SSL
if (strpos(DB_HOST, 'tidbcloud.com') !== false) {
    // Point to the public root certificate
    $ca_cert = dirname(__DIR__) . '/config/isrgrootx1.pem';
    mysqli_ssl_set($conn, NULL, NULL, $ca_cert, NULL, NULL);
}

// Connect to the database
$conn->real_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT, NULL, MYSQLI_CLIENT_SSL);

// Check connection
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Set character encoding to UTF-8
$conn->set_charset("utf8mb4");
?>