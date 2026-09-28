<?php
include 'connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = (float)$_POST['price'];
    $stock = (int)$_POST['stock'];
    $category_id = (int)$_POST['category_id'];

    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $image_name = 'product_' . time() . '.' . $ext;
        move_uploaded_file($_FILES['image']['tmp_name'], 'image/' . $image_name);
        $stmt = $conn->prepare("UPDATE products SET name=?, description=?, price=?, stock=?, category_id=?, image=? WHERE id=?");
        $stmt->bind_param("ssdiisi", $name, $description, $price, $stock, $category_id, $image_name, $id);
    } else {
        $stmt = $conn->prepare("UPDATE products SET name=?, description=?, price=?, stock=?, category_id=? WHERE id=?");
        $stmt->bind_param("ssdiii", $name, $description, $price, $stock, $category_id, $id);
    }

    if ($stmt->execute()) {
        $success = "Product updated successfully.";
    } else {
        $error = "Update failed.";
    }
    $stmt->close();
}

$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    header("Location: add-product.php");
    exit();
}

$categories = $conn->query("SELECT * FROM category ORDER BY name ASC");
include 'header.php';
?>

<section class="container">
    <h2 class="section-title">Edit Product</h2>

    <?php if ($error): ?><p class="alert alert-error"><?php echo $error; ?></p><?php endif; ?>
    <?php if ($success): ?><p class="alert alert-success"><?php echo $success; ?></p><?php endif; ?>

    <form method="POST" action="edit-product.php?id=<?php echo $product['id']; ?>" enctype="multipart/form-data" class="simple-form">
        <div class="field-row">
            <label>Category:</label>
            <select name="category_id" required>
                <option value="">Select Category</option>
                <?php while ($c = $categories->fetch_assoc()): ?>
                    <option value="<?php echo $c['id']; ?>" <?php echo $c['id'] == $product['category_id'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($c['name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="field-row">
            <label>Title:</label>
            <input type="text" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>
        </div>

        <div class="field-row">
            <label>Quantity:</label>
            <input type="number" name="stock" value="<?php echo $product['stock']; ?>">
        </div>

        <div class="field-row">
            <label>Price:</label>
            <input type="number" step="0.01" name="price" value="<?php echo $product['price']; ?>" required>
        </div>

        <div class="field-row">
            <label>Image:</label>
            <input type="file" name="image" accept="image/*">
        </div>

        <div class="field-row">
            <label>Description:</label>
            <textarea name="description" rows="4"><?php echo htmlspecialchars($product['description']); ?></textarea>
        </div>

        <button type="submit" class="btn-primary">Update Product</button>
    </form>
    <p><a href="add-product.php">&larr; Back to Product List</a></p>
</section>

<?php include 'footer.php'; ?>
