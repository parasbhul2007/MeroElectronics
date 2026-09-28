<?php
include 'connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$uid = $_SESSION['user_id'];

$sql = "SELECT cart.id AS cart_id, cart.quantity, products.id AS product_id, products.name, products.price, products.image, products.stock
        FROM cart
        JOIN products ON cart.product_id = products.id
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

include 'header.php';
?>

<section class="container">
    <h2 class="section-title">Your Cart</h2>

    <?php if (count($items) === 0): ?>
        <p>Your cart is empty. <a href="products.php">Browse products</a></p>
    <?php else: ?>
        <table class="cart-table">
            <tr>
                <th>Product</th><th>Price</th><th>Quantity</th><th>Subtotal</th><th>Action</th>
            </tr>
            <?php foreach ($items as $item): ?>
                <tr>
                    <td class="cart-product-cell">
                        <img src="image/<?php echo !empty($item['image']) ? htmlspecialchars($item['image']) : 'no-image.png'; ?>" class="admin-thumb">
                        <?php echo htmlspecialchars($item['name']); ?>
                    </td>
                    <td>Rs. <?php echo number_format($item['price'], 2); ?></td>
                    <td>
                        <form method="POST" action="update-cart.php" class="qty-form">
                            <input type="hidden" name="cart_id" value="<?php echo $item['cart_id']; ?>">
                            <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" min="1" max="<?php echo $item['stock']; ?>">
                            <button type="submit" class="btn-small">Update</button>
                        </form>
                    </td>
                    <td>Rs. <?php echo number_format($item['subtotal'], 2); ?></td>
                    <td><a href="remove-from-cart.php?cart_id=<?php echo $item['cart_id']; ?>" class="link-delete">Remove</a></td>
                </tr>
            <?php endforeach; ?>
        </table>

        <div class="cart-summary">
            <h3>Total: Rs. <?php echo number_format($total, 2); ?></h3>
            <a href="checkout.php" class="btn-primary">Proceed to Checkout</a>
        </div>
    <?php endif; ?>
</section>

<?php include 'footer.php'; ?>
