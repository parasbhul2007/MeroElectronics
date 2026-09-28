<?php
include 'connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: cart.php");
    exit();
}

$uid = $_SESSION['user_id'];
$shipping_address = trim($_POST['shipping_address']);

// Get cart items
$stmt = $conn->prepare("SELECT cart.product_id, cart.quantity, products.price, products.stock
                         FROM cart JOIN products ON cart.product_id = products.id
                         WHERE cart.user_id = ?");
$stmt->bind_param("i", $uid);
$stmt->execute();
$cart_items = $stmt->get_result();

$items = [];
$total = 0;
while ($row = $cart_items->fetch_assoc()) {
    $items[] = $row;
    $total += $row['price'] * $row['quantity'];
}

if (count($items) === 0) {
    header("Location: cart.php");
    exit();
}

// Start transaction so order + items + stock update all succeed or all fail together
$conn->begin_transaction();

try {
    $order_stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, shipping_address, status) VALUES (?, ?, ?, 'pending')");
    $order_stmt->bind_param("ids", $uid, $total, $shipping_address);
    $order_stmt->execute();
    $order_id = $conn->insert_id;

    $item_stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
    $stock_stmt = $conn->prepare("UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?");

    foreach ($items as $item) {
        $item_stmt->bind_param("iiid", $order_id, $item['product_id'], $item['quantity'], $item['price']);
        $item_stmt->execute();

        $stock_stmt->bind_param("iii", $item['quantity'], $item['product_id'], $item['quantity']);
        $stock_stmt->execute();
    }

    // Clear the cart
    $clear_stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
    $clear_stmt->bind_param("i", $uid);
    $clear_stmt->execute();

    $conn->commit();
    header("Location: order-details.php?id=" . $order_id);
    exit();

} catch (Exception $e) {
    $conn->rollback();
    header("Location: checkout.php?error=1");
    exit();
}
?>
