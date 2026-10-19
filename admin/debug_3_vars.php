<?php
require_once dirname(__DIR__) . '/vendor/autoload.php';
use Cloudinary\Cloudinary;

// Read and TRIM the 3 separate environment variables
$cloudName = trim(getenv('CLOUDINARY_CLOUD_NAME') ?: '');
$apiKey    = trim(getenv('CLOUDINARY_API_KEY') ?: '');
$apiSecret = trim(getenv('CLOUDINARY_API_SECRET') ?: '');

echo "<h2>☁️ Cloudinary Constructor Debugger</h2>";
echo "<hr>";
echo "<h3>What PHP is seeing from Render:</h3>";
echo "<ul style='font-family: monospace; font-size: 1.1rem;'>";
echo "<li><strong>CLOUDINARY_CLOUD_NAME:</strong> '$cloudName' (Length: " . strlen($cloudName) . ")</li>";
echo "<li><strong>CLOUDINARY_API_KEY:</strong> '$apiKey' (Length: " . strlen($apiKey) . ")</li>";
echo "<li><strong>CLOUDINARY_API_SECRET:</strong> '***HIDDEN***' (Length: " . strlen($apiSecret) . ")</li>";
echo "</ul>";
echo "<hr>";

if (!$cloudName || !$apiKey || !$apiSecret) {
    echo "<h3 style='color:red;'>❌ FAILED: One or more variables are empty.</h3>";
    exit;
}

echo "<h3>Attempting to initialize Cloudinary via Constructor...</h3>";
try {
    // THIS IS THE MAGIC FIX: Pass config directly to the constructor
    $c = new Cloudinary([
        'cloud' => [
            'cloud_name' => $cloudName,
            'api_key'    => $apiKey,
            'api_secret' => $apiSecret,
        ]
    ]);
    echo "<h3 style='color:green;'>🎉 SUCCESS! Cloudinary initialized perfectly via constructor.</h3>";
    echo "<p>The SDK is now ready to upload images. The issue was the Configuration::instance() singleton.</p>";
} catch (Exception $e) {
    echo "<h3 style='color:red;'>❌ FAILED: " . $e->getMessage() . "</h3>";
}
?>