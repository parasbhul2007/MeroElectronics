<?php
include 'connection.php';
include 'header.php';

$category_id = isset($_GET['category']) ? (int)$_GET['category'] : 0;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$sql = "SELECT p.*, c.name AS category_name FROM products p LEFT JOIN category c ON p.category_id = c.id WHERE 1=1";
$params = [];
$types = "";

if ($category_id > 0) {
    $sql .= " AND p.category_id = ?";
    $params[] = $category_id;
    $types .= "i";
}
if ($search !== '') {
    $sql .= " AND p.name LIKE ?";
    $params[] = "%$search%";
    $types .= "s";
}
$sql .= " ORDER BY p.created_at DESC";

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$products = $stmt->get_result();

$categories = $conn->query("SELECT * FROM category ORDER BY name ASC");
?>

<section class="container">
    <h2 class="section-title">Our Products</h2>

    <div class="products-layout">
        <aside class="filter-sidebar">
            <form method="GET" action="products.php" class="search-box">
                <input type="text" name="search" placeholder="Search products..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="btn-small">Search</button>
            </form>

            <h4>Categories</h4>
            <ul class="category-filter-list">
                <li><a href="products.php" class="<?php echo $category_id === 0 ? 'active' : ''; ?>">All</a></li>
                <?php while ($c = $categories->fetch_assoc()): ?>
                    <li>
                        <a href="products.php?category=<?php echo $c['id']; ?>" class="<?php echo $category_id === (int)$c['id'] ? 'active' : ''; ?>">
                            <?php echo htmlspecialchars($c['name']); ?>
                        </a>
                    </li>
                <?php endwhile; ?>
            </ul>
        </aside>

        <div class="product-grid">
            <?php if ($products->num_rows === 0): ?>
                <p>No products found.</p>
            <?php endif; ?>
            <?php while ($p = $products->fetch_assoc()): ?>
                <div class="product-card">
                    <a href="product_details.php?id=<?php echo $p['id']; ?>">
                        <img src="image/<?php echo !empty($p['image']) ? htmlspecialchars($p['image']) : 'no-image.png'; ?>" alt="<?php echo htmlspecialchars($p['name']); ?>">
                        <h3><?php echo htmlspecialchars($p['name']); ?></h3>
                    </a>
                    <p class="category-tag"><?php echo htmlspecialchars($p['category_name'] ?? 'Uncategorized'); ?></p>
                    <p class="price">Rs. <?php echo number_format($p['price'], 2); ?></p>
                    <a href="product_details.php?id=<?php echo $p['id']; ?>" class="btn-small">View Details</a>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>
