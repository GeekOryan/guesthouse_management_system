<?php

// Destroys session and redirects to login

require_once '../config/config.php';

session_start();
session_destroy();
header("Location: " . SITE_URL . "/admin/login.php");
exit();

?>