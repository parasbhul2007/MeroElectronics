<?php
include 'connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$error = "";
$success = "";

// Delete category
if (isset($_GET['delete'])) {
    $del_id = (int)$_GET['delete'];
    $conn->query("DELETE FROM category WHERE id = $del_id");
    header("Location: manage-category.php");
    exit();
}

// Add category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'])) {
    $name = trim($_POST['name']);
    if ($name === "") {
        $error = "Category name cannot be empty.";
    } else {
        $stmt = $conn->prepare("INSERT INTO category (name) VALUES (?)");
        $stmt->bind_param("s", $name);
        if ($stmt->execute()) {
            $success = "Category added.";
        } else {
            $error = "Failed to add category.";
        }
        $stmt->close();
    }
}

$categories = $conn->query("SELECT c.*, COUNT(p.id) AS product_count FROM category c LEFT JOIN products p ON p.category_id = c.id GROUP BY c.id ORDER BY c.name ASC");

include 'header.php';
?>

<section class="container">
    <h2 class="section-title">Admin: Manage Categories</h2>

    <div class="admin-links">
        <a href="add-product.php" class="btn-small">Manage Products</a>
        <a href="manage-orders.php" class="btn-small">Manage Orders</a>
    </div>

    <?php if ($error): ?><p class="alert alert-error"><?php echo $error; ?></p><?php endif; ?>
    <?php if ($success): ?><p class="alert alert-success"><?php echo $success; ?></p><?php endif; ?>

    <form method="POST" action="manage-category.php" class="simple-form">
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
        <ul class="category-list-plain">
            <?php while ($c = $categories->fetch_assoc()): ?>
                <li>
                    <span><?php echo htmlspecialchars($c['name']); ?> <small>(<?php echo $c['product_count']; ?> products)</small></span>
                    <a href="manage-category.php?delete=<?php echo $c['id']; ?>" class="link-delete">Delete</a>
                </li>
            <?php endwhile; ?>
        </ul>
    <?php endif; ?>
</section>

<?php include 'footer.php'; ?>
