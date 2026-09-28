<?php
include 'connection.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $conn->prepare("SELECT p.*, c.name AS category_name FROM products p LEFT JOIN category c ON p.category_id = c.id WHERE p.id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    include 'header.php';
    echo '<div class="container"><p>Product not found.</p></div>';
    include 'footer.php';
    exit();
}
$product = $result->fetch_assoc();

include 'header.php';
?>

<section class="container product-detail">
    <div class="product-detail-image">
        <img src="image/<?php echo !empty($product['image']) ? htmlspecialchars($product['image']) : 'no-image.png'; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
    </div>
    <div class="product-detail-info">
        <h2><?php echo htmlspecialchars($product['name']); ?></h2>
        <p class="category-tag"><?php echo htmlspecialchars($product['category_name'] ?? 'Uncategorized'); ?></p>
        <p class="price"><strong>Rs. <?php echo number_format($product['price'], 2); ?></strong></p>
        <p class="stock-status">
            <?php echo $product['stock'] > 0 ? "In Stock ({$product['stock']} available)" : "Out of Stock"; ?>
        </p>
        <p class="description"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>

        <?php if ($product['stock'] > 0): ?>
            <?php if (isset($_SESSION['user_id'])): ?>
                <form method="POST" action="add-to-cart.php" class="add-cart-form">
                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                    <label>Quantity</label>
                    <input type="number" name="quantity" value="1" min="1" max="<?php echo $product['stock']; ?>">
                    <button type="submit" class="btn-primary">Add to Cart</button>
                </form>
            <?php else: ?>
                <p><a href="login.php" class="btn-primary">Login to Add to Cart</a></p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>

<?php include 'footer.php'; ?>
