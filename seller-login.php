<?php
include 'connection.php';
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, name, password FROM seller WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $seller = $result->fetch_assoc();
        if (password_verify($password, $seller['password'])) {
            $_SESSION['seller_id'] = $seller['id'];
            $_SESSION['seller_name'] = $seller['name'];
            header("Location: seller-dashboard.php");
            exit();
        } else {
            $error = "Incorrect password.";
        }
    } else {
        $error = "No seller account found with that email.";
    }
    $stmt->close();
}
include 'header.php';
?>

<section class="container form-page">
    <h2 class="section-title">Seller Login</h2>

    <?php if ($error): ?><p class="alert alert-error"><?php echo htmlspecialchars($error); ?></p><?php endif; ?>

    <form method="POST" action="seller-login.php" class="form-box">
        <label>Email</label>
        <input type="email" name="email" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <button type="submit" class="btn-primary">Login</button>
    </form>
    <p>Don't have a seller account? <a href="seller-register.php">Register as Seller</a></p>
</section>

<?php include 'footer.php'; ?>
