<?php


// Reusable helper functions for the entire project.

// Sanitize user input
// Like Python's strip() but also removes harmful HTML

function sanitize($data){
    global $conn;
    $data = trim($data); // remove whitespace
    $data = stripslashes($data); // remove backlashes
    $data = htmlspecialchars($data); // convert < > & to safe versions
    return $conn->real_escape_string($data); // make safe for SQL
}

// This redirects to another page.
function redirect($url){
    header("Location: " . SITE_URL . "/" . $url);
    exit();
}

// Check if admin is logged in
function requireAdmin(){
    if (!isset($_SESSION['admin_id'])){
        header("Location: " . SITE_URL . "/admin/login.php");
        exit();
    }
}

// Format price in Rands
// Converts 650.00 to R650.00
function formatPrice($amount){
    return "R " . number_format($amount, 2);
}

// Calculate number of nights between two dates
function calcNights($check_in, $check_out){
    $in = new DateTime($check_in);
    $out = new DateTime($check_out);
    return $in->diff($out)->days;
}

// Calculate total booking price
function calcTotalPrice($price_per_night, $check_in, $check_out){
    $nights = calcNights($check_in, $check_out);
    return $nights * $price_per_night;
}

// Get a setting value from the settings table
function getSetting($key){
    global $conn;
    $key = $conn->real_escape_string($key);
    $result = $conn->query("SELECT setting_value FROM settings WHERE setting_key = '$key'");
    if ($result && $result->num_rows > 0){
        return $result->fetch_assoc()['setting_value'];
    }
    return '';
}


// CSRF Token Generation
// Creates a unique secret token per session
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// CSRF Token Verification
// Checks that the token from the form matches the session token
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) &&
           hash_equals($_SESSION['csrf_token'], $token);
}
?>
