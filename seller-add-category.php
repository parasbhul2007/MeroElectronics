<?php
include 'connection.php';

if (!isset($_SESSION['seller_id'])) {
    header("Location: seller-login.php");
    exit();
}

$seller_id = (int)$_SESSION['seller_id'];
$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'])) {
    $name = trim($_POST['name']);

    if ($name === "") {
        $error = "Category name cannot be empty.";
    } else {
        $stmt = $conn->prepare("SELECT id FROM category WHERE name = ? AND seller_id = ?");
        $stmt->bind_param("si", $name, $seller_id);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "You already have a category with this name.";
        } else {
            $stmt2 = $conn->prepare("INSERT INTO category (name, seller_id) VALUES (?, ?)");
            $stmt2->bind_param("si", $name, $seller_id);
            if ($stmt2->execute()) {
                $success = "Category added successfully.";
            } else {
                $error = "Failed to add category.";
            }
            $stmt2->close();
        }
        $stmt->close();
    }
}

$stmt = $conn->prepare("SELECT c.*, COUNT(p.id) AS product_count FROM category c LEFT JOIN products p ON p.category_id = c.id WHERE c.seller_id = ? GROUP BY c.id ORDER BY c.name ASC");
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$categories = $stmt->get_result();

include 'header.php';
?>

<section class="container">
    <h2 class="section-title">Add Category</h2>

    <div class="seller-links">
        <a href="seller-dashboard.php" class="btn-small">Seller Dashboard</a>
        <a href="seller-add-product.php" class="btn-small">Add Product</a>
        <a href="seller-logout.php" class="btn-small btn-danger-small">Logout</a>
    </div>

    <?php if ($error): ?><p class="alert alert-error"><?php echo htmlspecialchars($error); ?></p><?php endif; ?>
    <?php if ($success): ?><p class="alert alert-success"><?php echo htmlspecialchars($success); ?></p><?php endif; ?>

    <form method="POST" action="seller-add-category.php" class="simple-form seller-form">
        <div class="field-column">
            <label>Category Name:</label>
            <input type="text" name="name" required>
        </div>
        <button type="submit" class="btn-primary">Add Category</button>
    </form>

    <h3 class="section-title">Category List</h3>
    <?php if ($categories->num_rows === 0): ?>
        <p class="empty-note">No categories added yet.</p>
    <?php else: ?>
        <ul class="category-list-plain seller-category-list">
            <?php while ($c = $categories->fetch_assoc()): ?>
                <li>
                    <span><?php echo htmlspecialchars($c['name']); ?> <small>(<?php echo $c['product_count']; ?> products)</small></span>
                </li>
            <?php endwhile; ?>
        </ul>
    <?php endif; ?>
</section>

<?php include 'footer.php'; ?>
