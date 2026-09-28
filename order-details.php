<?php
include 'connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$uid = $_SESSION['user_id'];
$is_admin = ($_SESSION['role'] === 'admin');

// Admins can view any order, customers only their own
if ($is_admin) {
    $stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->bind_param("i", $order_id);
} else {
    $stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $order_id, $uid);
}
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    header("Location: orders.php");
    exit();
}

$item_stmt = $conn->prepare("SELECT oi.*, p.name FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
$item_stmt->bind_param("i", $order_id);
$item_stmt->execute();
$order_items = $item_stmt->get_result();

include 'header.php';
?>

<section class="container">
    <h2 class="section-title">Order #<?php echo $order['id']; ?></h2>

    <p><strong>Date:</strong> <?php echo date("d M Y, h:i A", strtotime($order['order_date'])); ?></p>
    <p><strong>Status:</strong> <span class="status-badge status-<?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span></p>
    <p><strong>Shipping Address:</strong> <?php echo htmlspecialchars($order['shipping_address']); ?></p>

    <table class="cart-table">
        <tr><th>Product</th><th>Quantity</th><th>Price</th><th>Subtotal</th></tr>
        <?php while ($item = $order_items->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($item['name']); ?></td>
                <td><?php echo $item['quantity']; ?></td>
                <td>Rs. <?php echo number_format($item['price'], 2); ?></td>
                <td>Rs. <?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
            </tr>
        <?php endwhile; ?>
    </table>

    <h3>Total: Rs. <?php echo number_format($order['total_amount'], 2); ?></h3>

    <?php if ($is_admin): ?>
        <form method="POST" action="manage-orders.php" class="form-box">
            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
            <label>Update Status</label>
            <select name="status">
                <?php foreach (['pending','processing','shipped','delivered','cancelled'] as $s): ?>
                    <option value="<?php echo $s; ?>" <?php echo $order['status'] === $s ? 'selected' : ''; ?>><?php echo ucfirst($s); ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn-primary">Update</button>
        </form>
    <?php endif; ?>

    <p><a href="orders.php">&larr; Back to Orders</a></p>
</section>

<?php include 'footer.php'; ?>
