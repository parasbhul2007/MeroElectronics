<?php
include 'connection.php';

if (!isset($_SESSION['seller_id'])) {
    header("Location: seller-login.php");
    exit();
}

$seller_id = (int)$_SESSION['seller_id'];
$message = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /* Delete category */
    if (isset($_POST['delete_category'])) {
        $category_id = (int)$_POST['category_id'];

        $check = $conn->prepare("SELECT COUNT(*) AS total FROM products WHERE category_id = ? AND seller_id = ?");
        $check->bind_param("ii", $category_id, $seller_id);
        $check->execute();
        $total = (int)$check->get_result()->fetch_assoc()['total'];
        $check->close();

        if ($total > 0) {
            $error = "This category cannot be deleted because it contains your products. Delete or move those products first.";
        } else {
            $stmt = $conn->prepare("DELETE FROM category WHERE id = ? AND seller_id = ?");
            $stmt->bind_param("ii", $category_id, $seller_id);
            $message = $stmt->execute() && $stmt->affected_rows > 0 ? "Category deleted successfully." : "Category could not be deleted.";
            if ($message !== "Category deleted successfully.") $error = $message;
            else $message = "Category deleted successfully.";
            $stmt->close();
        }
    }

    /* Edit category */
    if (isset($_POST['edit_category'])) {
        $category_id = (int)$_POST['category_id'];
        $category_name = trim($_POST['category_name']);

        if ($category_name === "") {
            $error = "Category name cannot be empty.";
        } else {
            $check = $conn->prepare("SELECT id FROM category WHERE name = ? AND seller_id = ? AND id <> ?");
            $check->bind_param("sii", $category_name, $seller_id, $category_id);
            $check->execute();
            $check->store_result();

            if ($check->num_rows > 0) {
                $error = "You already have a category with this name.";
            } else {
                $stmt = $conn->prepare("UPDATE category SET name = ? WHERE id = ? AND seller_id = ?");
                $stmt->bind_param("sii", $category_name, $category_id, $seller_id);
                if ($stmt->execute() && $stmt->affected_rows >= 0) {
                    $message = "Category updated successfully.";
                } else {
                    $error = "Category could not be updated.";
                }
                $stmt->close();
            }
            $check->close();
        }
    }

    /* Delete product */
    if (isset($_POST['delete_product'])) {
        $product_id = (int)$_POST['product_id'];

        $stmt = $conn->prepare("SELECT image FROM products WHERE id = ? AND seller_id = ?");
        $stmt->bind_param("ii", $product_id, $seller_id);
        $stmt->execute();
        $product = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$product) {
            $error = "Product not found.";
        } else {
            $stmt = $conn->prepare("DELETE FROM products WHERE id = ? AND seller_id = ?");
            $stmt->bind_param("ii", $product_id, $seller_id);

            if ($stmt->execute() && $stmt->affected_rows > 0) {
                if (!empty($product['image']) && $product['image'] !== 'no-image.png') {
                    $image_path = 'image/' . basename($product['image']);
                    if (is_file($image_path)) unlink($image_path);
                }
                $message = "Product deleted successfully.";
            } else {
                $error = "Product could not be deleted.";
            }
            $stmt->close();
        }
    }

    /* Edit product */
    if (isset($_POST['edit_product'])) {
        $product_id = (int)$_POST['product_id'];
        $name = trim($_POST['name']);
        $description = trim($_POST['description']);
        $price = (float)$_POST['price'];
        $stock = (int)$_POST['stock'];
        $category_id = (int)$_POST['category_id'];

        if ($name === "" || $price <= 0 || $stock < 0 || $category_id <= 0) {
            $error = "Please enter valid product information.";
        } else {
            $cat = $conn->prepare("SELECT id FROM category WHERE id = ? AND seller_id = ?");
            $cat->bind_param("ii", $category_id, $seller_id);
            $cat->execute();
            $valid_category = $cat->get_result()->num_rows === 1;
            $cat->close();

            if (!$valid_category) {
                $error = "Invalid category.";
            } else {
                $old = $conn->prepare("SELECT image FROM products WHERE id = ? AND seller_id = ?");
                $old->bind_param("ii", $product_id, $seller_id);
                $old->execute();
                $old_product = $old->get_result()->fetch_assoc();
                $old->close();

                if (!$old_product) {
                    $error = "Product not found.";
                } else {
                    $image_name = $old_product['image'];
                    $new_image_path = "";

                    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
                        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

                        if (!in_array($ext, $allowed, true)) {
                            $error = "Invalid image format. Use JPG, JPEG, PNG, WEBP, or GIF.";
                        } else {
                            $image_name = 'seller_product_' . time() . '_' . $seller_id . '.' . $ext;
                            $new_image_path = 'image/' . $image_name;
                            if (!move_uploaded_file($_FILES['image']['tmp_name'], $new_image_path)) {
                                $error = "The new image could not be uploaded.";
                            }
                        }
                    }

                    if ($error === "") {
                        $stmt = $conn->prepare("UPDATE products SET name = ?, description = ?, price = ?, stock = ?, image = ?, category_id = ? WHERE id = ? AND seller_id = ?");
                        $stmt->bind_param("ssdisiii", $name, $description, $price, $stock, $image_name, $category_id, $product_id, $seller_id);

                        if ($stmt->execute()) {
                            if ($new_image_path && !empty($old_product['image']) && $old_product['image'] !== 'no-image.png') {
                                $old_path = 'image/' . basename($old_product['image']);
                                if (is_file($old_path)) unlink($old_path);
                            }
                            $message = "Product updated successfully.";
                        } else {
                            if ($new_image_path && is_file($new_image_path)) unlink($new_image_path);
                            $error = "Product could not be updated.";
                        }
                        $stmt->close();
                    }
                }
            }
        }
    }
}

$stmt = $conn->prepare("SELECT id, name FROM category WHERE seller_id = ? ORDER BY name ASC");
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$categories = $stmt->get_result();

$category_options = [];
while ($category = $categories->fetch_assoc()) {
    $category_options[] = $category;
}
$stmt->close();

$stmt = $conn->prepare("SELECT p.*, c.name AS category_name FROM products p LEFT JOIN category c ON p.category_id = c.id WHERE p.seller_id = ? ORDER BY p.id DESC");
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$products = $stmt->get_result();

include 'header.php';
?>

<section class="container seller-page">
    <h2 class="section-title">Manage My Store</h2>

    <div class="seller-links">
        <a href="seller-dashboard.php" class="btn-small">Seller Dashboard</a>
        <a href="seller-add-category.php" class="btn-small">Add Category</a>
        <a href="seller-add-product.php" class="btn-small">Add Product</a>
        <a href="seller-logout.php" class="btn-small btn-danger-small">Logout</a>
    </div>

    <?php if ($message): ?>
        <p class="alert alert-success"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

    <?php if ($error): ?>
        <p class="alert alert-error"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <h3 class="section-title">My Categories</h3>

    <?php if (count($category_options) === 0): ?>
        <p class="empty-note">No categories added yet.</p>
    <?php else: ?>
        <div class="seller-manage-list">
            <?php foreach ($category_options as $category): ?>
                <div class="seller-manage-row">
                    <form method="POST" class="seller-inline-edit">
                        <input type="hidden" name="category_id" value="<?php echo $category['id']; ?>">
                        <input type="text" name="category_name" value="<?php echo htmlspecialchars($category['name']); ?>" required>
                        <button type="submit" name="edit_category" class="btn-small">Edit</button>
                    </form>

                    <form method="POST" class="seller-delete-form">
                        <input type="hidden" name="category_id" value="<?php echo $category['id']; ?>">
                        <button type="submit" name="delete_category" class="btn-small btn-danger-small" onclick="return confirm('Delete this category?');">Delete</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <h3 class="section-title">My Products</h3>

    <?php if ($products->num_rows === 0): ?>
        <p class="empty-note">No products added yet.</p>
    <?php else: ?>
        <div class="seller-product-list">
            <?php while ($product = $products->fetch_assoc()): ?>
                <div class="seller-product-card">
                    <div class="seller-product-summary">
                        <img src="image/<?php echo !empty($product['image']) ? htmlspecialchars($product['image']) : 'no-image.png'; ?>" class="admin-thumb" alt="Product">
                        <div class="seller-product-info">
                            <strong><?php echo htmlspecialchars($product['name']); ?></strong>
                            <span><?php echo htmlspecialchars($product['category_name'] ?? '-'); ?></span>
                            <span>Rs. <?php echo number_format($product['price'], 2); ?> &nbsp; | &nbsp; Stock: <?php echo (int)$product['stock']; ?></span>
                        </div>
                    </div>

                    <details class="seller-edit-details">
                        <summary class="btn-small">Edit</summary>
                        <form method="POST" enctype="multipart/form-data" class="simple-form seller-edit-form">
                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">

                            <div class="field-row">
                                <label>Category:</label>
                                <select name="category_id" required>
                                    <?php foreach ($category_options as $category): ?>
                                        <option value="<?php echo $category['id']; ?>" <?php echo ((int)$category['id'] === (int)$product['category_id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($category['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="field-row">
                                <label>Title:</label>
                                <input type="text" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
                            </div>

                            <div class="field-row">
                                <label>Quantity:</label>
                                <input type="number" name="stock" min="0" value="<?php echo (int)$product['stock']; ?>">
                            </div>

                            <div class="field-row">
                                <label>Price:</label>
                                <input type="number" name="price" step="0.01" min="0.01" value="<?php echo htmlspecialchars($product['price']); ?>" required>
                            </div>

                            <div class="field-row">
                                <label>Image:</label>
                                <input type="file" name="image" accept="image/*">
                            </div>

                            <div class="field-row">
                                <label>Description:</label>
                                <textarea name="description" rows="4"><?php echo htmlspecialchars($product['description']); ?></textarea>
                            </div>

                            <button type="submit" name="edit_product" class="btn-primary">Update Product</button>
                        </form>
                    </details>

                    <form method="POST">
                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                        <button type="submit" name="delete_product" class="btn-small btn-danger-small" onclick="return confirm('Delete this product?');">Delete</button>
                    </form>
                </div>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>
</section>

<?php include 'footer.php'; ?>
