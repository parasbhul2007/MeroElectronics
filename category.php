<?php
include 'connection.php';
include 'header.php';

$categories = $conn->query("
    SELECT c.*, COUNT(p.id) AS product_count
    FROM category c
    LEFT JOIN products p ON p.category_id = c.id
    GROUP BY c.id
    ORDER BY c.name ASC
");
?>

<section class="container">
    <h2 class="section-title">All Categories</h2>
    <div class="category-grid">
        <?php while ($c = $categories->fetch_assoc()): ?>
            <a href="products.php?category=<?php echo $c['id']; ?>" class="category-card">
                <span><?php echo htmlspecialchars($c['name']); ?></span>
                <small><?php echo $c['product_count']; ?> products</small>
            </a>
        <?php endwhile; ?>
    </div>
</section>

<?php include 'footer.php'; ?>
