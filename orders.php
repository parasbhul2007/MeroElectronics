<?php
include 'connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$uid = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC");
$stmt->bind_param("i", $uid);
$stmt->execute();
$orders = $stmt->get_result();

include 'header.php';
?>

<section class="container">
    <h2 class="section-title">My Orders</h2>

    <?php if ($orders->num_rows === 0): ?>
        <p>You haven't placed any orders yet. <a href="products.php">Start shopping</a></p>
    <?php else: ?>
        <table class="admin-table">
            <tr><th>Order ID</th><th>Date</th><th>Total</th><th>Status</th><th>Details</th></tr>
            <?php while ($o = $orders->fetch_assoc()): ?>
                <tr>
                    <td>#<?php echo $o['id']; ?></td>
                    <td><?php echo date("d M Y", strtotime($o['order_date'])); ?></td>
                    <td>Rs. <?php echo number_format($o['total_amount'], 2); ?></td>
                    <td><span class="status-badge status-<?php echo $o['status']; ?>"><?php echo ucfirst($o['status']); ?></span></td>
                    <td><a href="order-details.php?id=<?php echo $o['id']; ?>">View</a></td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php endif; ?>
</section>

<?php include 'footer.php'; ?>
