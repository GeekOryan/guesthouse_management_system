<?php

// This creates one reuwsable database connection using MySQLi.
// Every other file that needs the DB will just require this file.

// includes/db.php
// Database connection using MySQLi
// Usage: require_once '../includes/db.php';
// Then use $conn everywhere

require_once dirname(__DIR__) . '/config/config.php';

// Create connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Checking the connection, stop everything if it fails.
if ($conn->connect_error){
    // In production you would log this, and not display it.
    die("Database connection failed: " . $conn->connect_error);
}

// Set character encoding to UTF-8 (handles special characters)
$conn->set_charset("utf8mb4");
?>