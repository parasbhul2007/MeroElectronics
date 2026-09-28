<?php
include 'connection.php';

if (!isset($_SESSION['seller_id'])) {
    header("Location: seller-login.php");
    exit();
}

$seller_id = (int)$_SESSION['seller_id'];
$product_count = 0;
$category_count = 0;

$stmt = $conn->prepare("SELECT COUNT(*) AS total FROM products WHERE seller_id = ?");
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$product_count = (int)$stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

$stmt = $conn->prepare("SELECT COUNT(*) AS total FROM category WHERE seller_id = ?");
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$category_count = (int)$stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

include 'header.php';
?>

<section class="container seller-page">
    <h2 class="section-title">Seller Dashboard</h2>
    <div class="seller-welcome">
        <h3>Welcome, <?php echo htmlspecialchars($_SESSION['seller_name']); ?></h3>
        <p>Manage your categories and products from here.</p>
    </div>

    <div class="seller-actions">
        <a href="seller-add-category.php" class="seller-action-card">
            <strong>Add Category</strong>
            <span><?php echo $category_count; ?> categories</span>
        </a>
        <a href="seller-add-product.php" class="seller-action-card">
            <strong>Add Product</strong>
            <span><?php echo $product_count; ?> products</span>
        </a>
        <a href="seller-manage.php" class="seller-action-card">
            <strong>Manage Store</strong>
            <span>Edit or delete your products and categories</span>
        </a>
        <a href="seller-logout.php" class="seller-action-card seller-logout-card">
            <strong>Logout</strong>
            <span>Exit seller panel</span>
        </a>
    </div>
</section>

<?php include 'footer.php'; ?>
