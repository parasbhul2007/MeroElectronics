<?php
include 'connection.php';

if (!isset($_SESSION['seller_id'])) {
    header("Location: seller-login.php");
    exit();
}

$seller_id = (int)$_SESSION['seller_id'];
$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = (float)$_POST['price'];
    $stock = (int)$_POST['stock'];
    $category_id = (int)$_POST['category_id'];
    $image_name = 'no-image.png';

    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $image_name = 'seller_product_' . time() . '_' . $seller_id . '.' . $ext;
        move_uploaded_file($_FILES['image']['tmp_name'], 'image/' . $image_name);
    }

    $cat_stmt = $conn->prepare("SELECT id FROM category WHERE id = ? AND seller_id = ?");
    $cat_stmt->bind_param("ii", $category_id, $seller_id);
    $cat_stmt->execute();
    $cat_result = $cat_stmt->get_result();

    if ($name === "" || $price <= 0 || $category_id <= 0 || $cat_result->num_rows !== 1) {
        $error = "Please provide a valid title, price, and your category.";
    } else {
        $stmt = $conn->prepare("INSERT INTO products (name, description, price, stock, image, category_id, seller_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdisii", $name, $description, $price, $stock, $image_name, $category_id, $seller_id);
        if ($stmt->execute()) {
            $success = "Product added successfully.";
        } else {
            $error = "Failed to add product.";
        }
        $stmt->close();
    }
    $cat_stmt->close();
}

$stmt = $conn->prepare("SELECT id, name FROM category WHERE seller_id = ? ORDER BY name ASC");
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$categories = $stmt->get_result();

$stmt2 = $conn->prepare("SELECT p.*, c.name AS category_name FROM products p LEFT JOIN category c ON p.category_id = c.id WHERE p.seller_id = ? ORDER BY p.created_at DESC");
$stmt2->bind_param("i", $seller_id);
$stmt2->execute();
$products = $stmt2->get_result();

include 'header.php';
?>

<section class="container">
    <h2 class="section-title">Add Product</h2>

    <div class="seller-links">
        <a href="seller-dashboard.php" class="btn-small">Seller Dashboard</a>
        <a href="seller-add-category.php" class="btn-small">Add Category</a>
        <a href="seller-logout.php" class="btn-small btn-danger-small">Logout</a>
    </div>

    <?php if ($error): ?><p class="alert alert-error"><?php echo htmlspecialchars($error); ?></p><?php endif; ?>
    <?php if ($success): ?><p class="alert alert-success"><?php echo htmlspecialchars($success); ?></p><?php endif; ?>

    <?php if ($categories->num_rows === 0): ?>
        <p class="empty-note">No categories yet — <a href="seller-add-category.php">add a category first</a> before adding products.</p>
    <?php else: ?>
        <form method="POST" action="seller-add-product.php" enctype="multipart/form-data" class="simple-form seller-form">
            <div class="field-row">
                <label>Category:</label>
                <select name="category_id" required>
                    <option value="">Select Category</option>
                    <?php while ($c = $categories->fetch_assoc()): ?>
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
                <input type="number" name="stock" value="0" min="0">
            </div>

            <div class="field-row">
                <label>Price:</label>
                <input type="number" step="0.01" name="price" min="0.01" required>
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

    <h3 class="section-title">My Products</h3>
    <?php if ($products->num_rows === 0): ?>
        <p class="empty-note">No products added yet.</p>
    <?php else: ?>
        <table class="admin-table">
            <tr>
                <th>Image</th><th>Name</th><th>Category</th><th>Price</th><th>Stock</th>
            </tr>
            <?php while ($p = $products->fetch_assoc()): ?>
                <tr>
                    <td><img src="image/<?php echo !empty($p['image']) ? htmlspecialchars($p['image']) : 'no-image.png'; ?>" class="admin-thumb"></td>
                    <td><?php echo htmlspecialchars($p['name']); ?></td>
                    <td><?php echo htmlspecialchars($p['category_name'] ?? '-'); ?></td>
                    <td>Rs. <?php echo number_format($p['price'], 2); ?></td>
                    <td><?php echo $p['stock']; ?></td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php endif; ?>
</section>

<?php include 'footer.php'; ?>
