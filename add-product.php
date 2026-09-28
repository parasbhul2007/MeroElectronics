<?php
include 'connection.php';

// Admin-only guard
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$error = "";
$success = "";

// Handle delete
if (isset($_GET['delete'])) {
    $del_id = (int)$_GET['delete'];
    $conn->query("DELETE FROM products WHERE id = $del_id");
    header("Location: add-product.php");
    exit();
}

// Handle add product
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = (float)$_POST['price'];
    $stock = (int)$_POST['stock'];
    $category_id = (int)$_POST['category_id'];
    $image_name = 'no-image.png';

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $image_name = 'product_' . time() . '.' . $ext;
        move_uploaded_file($_FILES['image']['tmp_name'], 'image/' . $image_name);
    }

    if ($name === "" || $price <= 0 || $category_id <= 0) {
        $error = "Please provide a valid title, price, and category.";
    } else {
        $stmt = $conn->prepare("INSERT INTO products (name, description, price, stock, image, category_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdisi", $name, $description, $price, $stock, $image_name, $category_id);
        if ($stmt->execute()) {
            $success = "Product added successfully.";
        } else {
            $error = "Failed to add product.";
        }
        $stmt->close();
    }
}

$categories = $conn->query("SELECT * FROM category ORDER BY name ASC");
$products = $conn->query("SELECT p.*, c.name AS category_name FROM products p LEFT JOIN category c ON p.category_id = c.id ORDER BY p.created_at DESC");

include 'header.php';
?>

<section class="container">
    <h2 class="section-title">Admin: Manage Products</h2>

    <div class="admin-links">
        <a href="add-product.php" class="btn-small">Add Product</a>
        <a href="manage-category.php" class="btn-small">Manage Categories</a>
        <a href="manage-orders.php" class="btn-small">Manage Orders</a>
    </div>

    <?php if ($error): ?><p class="alert alert-error"><?php echo $error; ?></p><?php endif; ?>
    <?php if ($success): ?><p class="alert alert-success"><?php echo $success; ?></p><?php endif; ?>

    <?php if ($categories->num_rows === 0): ?>
        <p class="empty-note">No categories yet — <a href="manage-category.php">add a category first</a> before adding products.</p>
    <?php else: ?>
        <form method="POST" action="add-product.php" enctype="multipart/form-data" class="simple-form">
            <div class="field-row">
                <label>Category:</label>
                <select name="category_id" required>
                    <option value="">Select Category</option>
                    <?php $categories->data_seek(0); while ($c = $categories->fetch_assoc()): ?>
                        <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['name']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="field-row">
                <label>Title:</label>
                <input type="text" name="name" required>
            </div>

            <div class="field-row">
                <label>Quantity:</label>
                <input type="number" name="stock" value="0">
            </div>

            <div class="field-row">
                <label>Price:</label>
                <input type="number" step="0.01" name="price" required>
            </div>

            <div class="field-row">
                <label>Image:</label>
                <input type="file" name="image" accept="image/*">
            </div>

            <div class="field-row">
                <label>Description:</label>
                <textarea name="description" rows="4"></textarea>
            </div>

            <button type="submit" class="btn-primary">Add Product</button>
        </form>
    <?php endif; ?>

    <h3 class="section-title">Existing Products</h3>
    <table class="admin-table">
        <tr>
            <th>Image</th><th>Name</th><th>Category</th><th>Price</th><th>Stock</th><th>Actions</th>
        </tr>
        <?php while ($p = $products->fetch_assoc()): ?>
            <tr>
                <td><img src="image/<?php echo !empty($p['image']) ? htmlspecialchars($p['image']) : 'no-image.png'; ?>" class="admin-thumb"></td>
                <td><?php echo htmlspecialchars($p['name']); ?></td>
                <td><?php echo htmlspecialchars($p['category_name'] ?? '-'); ?></td>
                <td>Rs. <?php echo number_format($p['price'], 2); ?></td>
                <td><?php echo $p['stock']; ?></td>
                <td>
                    <a href="edit-product.php?id=<?php echo $p['id']; ?>">Edit</a> |
                    <a href="add-product.php?delete=<?php echo $p['id']; ?>" class="link-delete">Delete</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
</section>

<?php include 'footer.php'; ?>
