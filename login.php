<?php
include 'connection.php';
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, name, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['role'] = $user['role'];

            if ($user['role'] === 'admin') {
                header("Location: add-product.php");
            } else {
                header("Location: index.php");
            }
            exit();
        } else {
            $error = "Incorrect password.";
        }
    } else {
        $error = "No account found with that email.";
    }
    $stmt->close();
}
include 'header.php';
?>

<section class="container form-page">
    <h2 class="section-title">Login</h2>

    <?php if ($error): ?><p class="alert alert-error"><?php echo $error; ?></p><?php endif; ?>

    <form method="POST" action="login.php" class="form-box">
        <label>Email</label>
        <input type="email" name="email" required>

        <label>Password</label>
        <input type="password" name="password" required>

        <button type="submit" class="btn-primary">Login</button>
    </form>
    <p>Don't have an account? <a href="register.php">Register here</a></p>
</section>

<?php include 'footer.php'; ?>
