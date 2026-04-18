<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';

echo "<h3>Theme Values From Database:</h3>";
echo "theme_color: "      . getSetting('theme_color')      . "<br>";
echo "theme_color_dark: " . getSetting('theme_color_dark') . "<br>";
echo "theme_bg: "         . getSetting('theme_bg')         . "<br>";
echo "theme_font: "       . getSetting('theme_font')       . "<br>";
echo "dark_mode: "        . getSetting('dark_mode')        . "<br>";
echo "<br>";
echo "<h3>Config Values:</h3>";
echo "SITE_URL: " . SITE_URL . "<br>";
?>

