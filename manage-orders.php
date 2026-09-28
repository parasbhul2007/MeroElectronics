<?php
include 'connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id'])) {
    $order_id = (int)$_POST['order_id'];
    $status = $_POST['status'];
    $allowed = ['pending','processing','shipped','delivered','cancelled'];
    if (in_array($status, $allowed)) {
        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $order_id);
        $stmt->execute();
    }
    header("Location: order-details.php?id=" . $order_id);
    exit();
}

$orders = $conn->query("SELECT o.*, u.name AS customer_name FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.order_date DESC");

include 'header.php';
?>

<section class="container">
    <h2 class="section-title">Admin: All Orders</h2>

    <div class="admin-links">
        <a href="add-product.php" class="btn-small">Manage Products</a>
        <a href="manage-category.php" class="btn-small">Manage Categories</a>
    </div>

    <table class="admin-table">
        <tr><th>Order ID</th><th>Customer</th><th>Date</th><th>Total</th><th>Status</th><th>Details</th></tr>
        <?php while ($o = $orders->fetch_assoc()): ?>
            <tr>
                <td>#<?php echo $o['id']; ?></td>
                <td><?php echo htmlspecialchars($o['customer_name']); ?></td>
                <td><?php echo date("d M Y", strtotime($o['order_date'])); ?></td>
                <td>Rs. <?php echo number_format($o['total_amount'], 2); ?></td>
                <td><span class="status-badge status-<?php echo $o['status']; ?>"><?php echo ucfirst($o['status']); ?></span></td>
                <td><a href="order-details.php?id=<?php echo $o['id']; ?>">View / Update</a></td>
            </tr>
        <?php endwhile; ?>
    </table>
</section>

<?php include 'footer.php'; ?>
