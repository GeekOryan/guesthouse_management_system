<?php
// This file must be in the /public/ folder to be accessible
echo "<h2>Render Environment Variables Check</h2>";
echo "<ul style='font-family: monospace; font-size: 1.2rem;'>";

$cloud_name = getenv('CLOUDINARY_CLOUD_NAME');
$api_key = getenv('CLOUDINARY_API_KEY');
$api_secret = getenv('CLOUDINARY_API_SECRET');

echo "<li>CLOUDINARY_CLOUD_NAME: " . ($cloud_name ? "<span style='color:green'>$cloud_name</span>" : "<span style='color:red'>NOT FOUND</span>") . "</li>";
echo "<li>CLOUDINARY_API_KEY: " . ($api_key ? "<span style='color:green'>$api_key</span>" : "<span style='color:red'>NOT FOUND</span>") . "</li>";
echo "<li>CLOUDINARY_API_SECRET: " . ($api_secret ? "<span style='color:green'>*** EXISTS ***</span>" : "<span style='color:red'>NOT FOUND</span>") . "</li>";
echo "</ul>";

if ($cloud_name && $api_key && $api_secret) {
    echo "<h3 style='color:green'>✅ SUCCESS! Cloudinary credentials are loaded.</h3>";
} else {
    echo "<h3 style='color:red'>❌ FAILED! Render is not passing the variables to PHP.</h3>";
    echo "<p>Double-check the exact spelling in your Render Dashboard Environment tab.</p>";
}
?>