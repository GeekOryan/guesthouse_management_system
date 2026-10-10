   <?php
   echo "<h3>Environment Variables Check:</h3>";
   echo "<ul>";
   echo "<li>CLOUDINARY_CLOUD_NAME: " . (getenv('CLOUDINARY_CLOUD_NAME') ?: '<span style="color:red">NOT FOUND</span>') . "</li>";
   echo "<li>CLOUDINARY_API_KEY: " . (getenv('CLOUDINARY_API_KEY') ?: '<span style="color:red">NOT FOUND</span>') . "</li>";
   echo "<li>CLOUDINARY_API_SECRET: " . (getenv('CLOUDINARY_API_SECRET') ? '***HIDDEN***' : '<span style="color:red">NOT FOUND</span>') . "</li>";
   echo "</ul>";
   ?>