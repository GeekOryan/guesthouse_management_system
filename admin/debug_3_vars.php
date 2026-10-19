<?php
require_once dirname(__DIR__) . '/vendor/autoload.php';
use Cloudinary\Configuration\Configuration;
use Cloudinary\Cloudinary;

// Aggressively trim any invisible spaces
$cn = trim(getenv('CLOUDINARY_CLOUD_NAME') ?: '');
$ak = trim(getenv('CLOUDINARY_API_KEY') ?: '');
$as = trim(getenv('CLOUDINARY_API_SECRET') ?: '');

echo "<h2>☁️ 3-Variable Cloudinary Debugger</h2>";
echo "<hr>";
echo "<h3>What PHP is seeing from Render:</h3>";
echo "<ul style='font-family: monospace; font-size: 1.1rem;'>";
echo "<li><strong>CLOUDINARY_CLOUD_NAME:</strong> '$cn' (Length: " . strlen($cn) . ")</li>";
echo "<li><strong>CLOUDINARY_API_KEY:</strong> '$ak' (Length: " . strlen($ak) . ")</li>";
echo "<li><strong>CLOUDINARY_API_SECRET:</strong> '***HIDDEN***' (Length: " . strlen($as) . ")</li>";
echo "</ul>";
echo "<hr>";

if (!$cn || !$ak || !$as) {
    echo "<h3 style='color:red;'>❌ FAILED: One or more variables are empty.</h3>";
    echo "<p>Go to Render > Environment and check for typos or empty fields.</p>";
    exit;
}

echo "<h3>Attempting to initialize Cloudinary...</h3>";
try {
    Configuration::instance([
        'cloud' => [
            'cloud_name' => $cn,
            'api_key'    => $ak,
            'api_secret' => $as,
        ],
    ]);
    $c = new Cloudinary();
    echo "<h3 style='color:green;'>🎉 SUCCESS! Cloudinary initialized perfectly.</h3>";
} catch (Exception $e) {
    echo "<h3 style='color:red;'>❌ FAILED: " . $e->getMessage() . "</h3>";
    echo "<p><strong>How to fix based on lengths above:</strong></p>";
    echo "<ul>";
    echo "<li>If Cloud Name length is > 10, you probably pasted the URL or Key into the wrong box.</li>";
    echo "<li>If API Key length is not 15 digits, you pasted the wrong value.</li>";
    echo "<li>If API Secret length is < 20, you pasted the wrong value.</li>";
    echo "</ul>";
}
?>