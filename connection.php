<?php
// ===================================================
// Database Connection
// ===================================================
$host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "meroelectronics";

$conn = new mysqli($host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Start session on every page that includes this file
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
