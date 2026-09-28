<?php
include 'connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cart_id = (int)$_POST['cart_id'];
    $quantity = max(1, (int)$_POST['quantity']);
    $uid = $_SESSION['user_id'];

    $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
    $stmt->bind_param("iii", $quantity, $cart_id, $uid);
    $stmt->execute();
}

header("Location: cart.php");
exit();
?>
