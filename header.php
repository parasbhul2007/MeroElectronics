<?php
$cart_count = 0;
if (isset($_SESSION['user_id'])) {
    $uid = $_SESSION['user_id'];
    $res = $conn->query("SELECT SUM(quantity) AS total FROM cart WHERE user_id = $uid");
    if ($row = $res->fetch_assoc()) {
        $cart_count = $row['total'] ? $row['total'] : 0;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Mero Electronics</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/style.css">
</head>
<body>

<header class="site-header">
    <div class="topbar">
        <div class="container topbar-inner">
            <span>Genuine electronics, honest prices — delivered across Nepal</span>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <span class="admin-flag">Admin Mode</span>
            <?php elseif (isset($_SESSION['seller_id'])): ?>
                <span class="seller-flag">Seller Mode</span>
            <?php endif; ?>
        </div>
    </div>

    <div class="container header-inner">
        <a href="index.php" class="logo">Mero<span>Electronics</span></a>

        <form method="GET" action="products.php" class="navbar-search">
            <input type="text" name="search" placeholder="Search for phones, laptops, accessories...">
            <button type="submit">Search</button>
        </form>

        <div class="header-actions">
            <a href="cart.php" class="cart-link">🛒 Cart <span class="cart-count"><?php echo $cart_count; ?></span></a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <span class="welcome-text">Hi, <?php echo htmlspecialchars($_SESSION['name']); ?></span>
                <a href="logout.php" class="btn-small btn-outline">Logout</a>
            <?php else: ?>
                <a href="login.php" class="btn-small btn-outline">Login</a>
                <a href="register.php" class="btn-small">Register</a>
            <?php endif; ?>
        </div>
    </div>

    <nav class="main-nav">
        <div class="container main-nav-inner">
            <a href="index.php">Home</a>
            <a href="products.php">Products</a>
            <a href="category.php">Categories</a>
            <a href="about.php">About</a>
            <a href="contact.php">Contact</a>
            <?php if (isset($_SESSION['user_id']) && $_SESSION['role'] !== 'admin'): ?>
                <a href="orders.php">My Orders</a>
            <?php endif; ?>
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <a href="add-product.php" class="admin-nav-link">Manage Products</a>
                <a href="manage-category.php" class="admin-nav-link">Manage Categories</a>
                <a href="manage-orders.php" class="admin-nav-link">Manage Orders</a>
            <?php endif; ?>
            <?php if (isset($_SESSION['seller_id'])): ?>
                <a href="seller-dashboard.php" class="seller-nav-link">Seller Dashboard</a>
                <a href="seller-add-product.php" class="seller-nav-link">Add Product</a>
                <a href="seller-add-category.php" class="seller-nav-link">Add Category</a>
                <a href="seller-manage.php" class="seller-nav-link">Manage Store</a>
                <a href="seller-logout.php" class="seller-nav-link">Seller Logout</a>
            <?php else: ?>
                <a href="seller-register.php" class="seller-nav-link">Become a Seller</a>
            <?php endif; ?>
        </div>
    </nav>
</header>
