<?php
echo "<h2>☁️ Cloudinary Configuration Debugger</h2>";
echo "<hr>";

$url = getenv('CLOUDINARY_URL');

if (!$url) {
    echo "<h3 style='color:red;'>❌ FAILED: CLOUDINARY_URL is completely missing from Render.</h3>";
    echo "<p>Go to Render > Environment and add it exactly as:</p>";
    echo "<code>cloudinary://YOUR_API_KEY:YOUR_API_SECRET@YOUR_CLOUD_NAME</code>";
    exit;
}

echo "<h3 style='color:green;'>✅ Variable Found!</h3>";
echo "<p><strong>Raw Value:</strong> <code>" . htmlspecialchars($url) . "</code></p>";
echo "<p><strong>Length:</strong> " . strlen($url) . " characters</p>";
echo "<hr>";

// Try to parse the URL
$parsed = parse_url($url);

echo "<h3>URL Parsing Results:</h3>";
echo "<ul>";
echo "<li><strong>Scheme:</strong> " . ($parsed['scheme'] ?? '<span style="color:red">MISSING (Should be "cloudinary")</span>') . "</li>";
echo "<li><strong>Host (Cloud Name):</strong> " . ($parsed['host'] ?? '<span style="color:red">MISSING</span>') . "</li>";
echo "<li><strong>User (API Key):</strong> " . ($parsed['user'] ?? '<span style="color:red">MISSING</span>') . "</li>";
echo "<li><strong>Pass (API Secret):</strong> " . ($parsed['pass'] ?? '<span style="color:red">MISSING</span>') . "</li>";
echo "</ul>";

echo "<hr>";

// Check for common pitfalls
$has_issues = false;

if (!isset($parsed['scheme']) || $parsed['scheme'] !== 'cloudinary') {
    echo "<p style='color:red;'><strong>⚠️ ISSUE 1:</strong> The URL must start with <code>cloudinary://</code>. Yours starts with <code>" . ($parsed['scheme'] ?? 'nothing') . "://</code></p>";
    $has_issues = true;
}

if (isset($parsed['pass']) && (strpos($parsed['pass'], '+') !== false || strpos($parsed['pass'], '/') !== false || strpos($parsed['pass'], '=') !== false)) {
    echo "<p style='color:red;'><strong>⚠️ ISSUE 2:</strong> Your API Secret contains special characters (+, /, or =) that are breaking the URL parser!</p>";
    echo "<p><strong>THE FIX:</strong> You must URL-encode your API Secret in the Render dashboard. For example, change <code>+</code> to <code>%2B</code> and <code>/</code> to <code>%2F</code>. Or, better yet, use the 3 separate environment variables method, which we will do next if this is the case.</p>";
    $has_issues = true;
}

if (!$has_issues && isset($parsed['host']) && isset($parsed['user']) && isset($parsed['pass'])) {
    echo "<h3 style='color:green;'>🎉 SUCCESS: The URL is perfectly formatted!</h3>";
    echo "<p>If the URL is perfect but the SDK still fails, the issue is a Render caching glitch. We will force a clean rebuild.</p>";
}

echo "<hr>";
echo "<p><a href='/admin/gallery.php'>← Back to Gallery</a></p>";
?>