<?php
include 'connection.php';
unset($_SESSION['seller_id'], $_SESSION['seller_name']);
header("Location: index.php");
exit();
?>
