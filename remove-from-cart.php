<?php
include 'connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (isset($_GET['cart_id'])) {
    $cart_id = (int)$_GET['cart_id'];
    $uid = $_SESSION['user_id'];

    $stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $cart_id, $uid);
    $stmt->execute();
}

header("Location: cart.php");
exit();
?>
