<?php include 'connection.php'; ?>
<?php include 'header.php'; ?>

<section class="hero">
    <div class="container">
        <h1>Welcome to Mero Electronics</h1>
        <p>Genuine electronics, honest prices, real warranty — right here in Nepal.</p>
        <a href="products.php" class="btn-primary">Shop Now</a>
    </div>
</section>

<section class="container">
    <h2 class="section-title">Shop by Category</h2>
    <div class="category-grid">
        <?php
        $cat_result = $conn->query("SELECT * FROM category ORDER BY name ASC");
        while ($cat = $cat_result->fetch_assoc()) {
            echo '<a href="products.php?category=' . $cat['id'] . '" class="category-card">';
            echo '<span>' . htmlspecialchars($cat['name']) . '</span>';
            echo '</a>';
        }
        ?>
    </div>
</section>

<section class="container">
    <h2 class="section-title">Featured Products</h2>
    <div class="product-grid">
        <?php
        $prod_result = $conn->query("SELECT * FROM products ORDER BY created_at DESC LIMIT 8");
        while ($p = $prod_result->fetch_assoc()) {
            $img = !empty($p['image']) ? 'image/' . $p['image'] : 'image/no-image.png';
            echo '<div class="product-card">';
            echo '<a href="product_details.php?id=' . $p['id'] . '">';
            echo '<img src="' . htmlspecialchars($img) . '" alt="' . htmlspecialchars($p['name']) . '">';
            echo '<h3>' . htmlspecialchars($p['name']) . '</h3>';
            echo '</a>';
            echo '<p class="price">Rs. ' . number_format($p['price'], 2) . '</p>';
            echo '<a href="product_details.php?id=' . $p['id'] . '" class="btn-small">View Details</a>';
            echo '</div>';
        }
        ?>
    </div>
</section>

<?php include 'footer.php'; ?>
