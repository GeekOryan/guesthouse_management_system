<?php
require_once '../config/config.php';
require_once '../includes/db.php';

echo "<h3>Adding missing 'phone' column to contact_messages table...</h3>";

$sql = "ALTER TABLE contact_messages ADD COLUMN phone VARCHAR(50) AFTER email;";

if ($conn->query($sql) === TRUE) {
    echo "<h2 style='color:green;'>✅ SUCCESS! The 'phone' column has been added.</h2>";
    echo "<p><strong style='color:red;'>IMPORTANT:</strong> Delete this file (fix_contact_db.php) immediately!</p>";
    echo "<p><a href='contact.php'>← Go back and test the contact form</a></p>";
} else {
    echo "<h2 style='color:red;'>❌ Error: " . $conn->error . "</h2>";
}

$conn->close();
?>