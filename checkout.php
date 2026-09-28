<?php
include 'connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$uid = $_SESSION['user_id'];

$sql = "SELECT cart.quantity, products.name, products.price
        FROM cart JOIN products ON cart.product_id = products.id
        WHERE cart.user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $uid);
$stmt->execute();
$cart_items = $stmt->get_result();

$total = 0;
$items = [];
while ($row = $cart_items->fetch_assoc()) {
    $row['subtotal'] = $row['price'] * $row['quantity'];
    $total += $row['subtotal'];
    $items[] = $row;
}

if (count($items) === 0) {
    header("Location: cart.php");
    exit();
}

// Fetch user's saved address as default
$user_stmt = $conn->prepare("SELECT address, phone FROM users WHERE id = ?");
$user_stmt->bind_param("i", $uid);
$user_stmt->execute();
$user_info = $user_stmt->get_result()->fetch_assoc();

include 'header.php';
?>

<section class="container">
    <h2 class="section-title">Checkout</h2>

    <div class="checkout-layout">
        <form method="POST" action="place-order.php" class="form-box">
            <label>Full Shipping Address *</label>
            <textarea name="shipping_address" rows="3" required><?php echo htmlspecialchars($user_info['address'] ?? ''); ?></textarea>

            <label>Contact Phone *</label>
            <input type="text" name="phone" value="<?php echo htmlspecialchars($user_info['phone'] ?? ''); ?>" required>

            <label>Payment Method</label>
            <select name="payment_method">
                <option value="cod">Cash on Delivery</option>
                <option value="esewa">eSewa</option>
                <option value="khalti">Khalti</option>
            </select>

            <button type="submit" class="btn-primary">Place Order</button>
        </form>

        <div class="order-summary">
            <h3>Order Summary</h3>
            <table class="cart-table">
                <?php foreach ($items as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['name']); ?> x <?php echo $item['quantity']; ?></td>
                        <td>Rs. <?php echo number_format($item['subtotal'], 2); ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
            <h3>Total: Rs. <?php echo number_format($total, 2); ?></h3>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>
