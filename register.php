<?php
include 'connection.php';
$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);

    if ($name === "" || $email === "" || $password === "") {
        $error = "Please fill all required fields.";
    } else {
       
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "Email already registered. Please login instead.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt2 = $conn->prepare("INSERT INTO users (name, email, password, phone, address, role) VALUES (?, ?, ?, ?, ?, 'customer')");
            $stmt2->bind_param("sssss", $name, $email, $hashed_password, $phone, $address);
            if ($stmt2->execute()) {
                $success = "Registration successful! You can now login.";
            } else {
                $error = "Something went wrong. Please try again.";
            }
            $stmt2->close();
        }
        $stmt->close();
    }
}
include 'header.php';
?>

<section class="container form-page">
    <h2 class="section-title">Create an Account</h2>

    <?php if ($error): ?><p class="alert alert-error"><?php echo $error; ?></p><?php endif; ?>
    <?php if ($success): ?><p class="alert alert-success"><?php echo $success; ?> <a href="login.php">Login here</a></p><?php endif; ?>

    <form method="POST" action="register.php" class="form-box">
        <label>Full Name *</label>
        <input type="text" name="name" required>

        <label>Email *</label>
        <input type="email" name="email" required>

        <label>Password *</label>
        <input type="password" name="password" required>

        <label>Phone</label>
        <input type="text" name="phone">

        <label>Address</label>
        <input type="text" name="address">

        <button type="submit" class="btn-primary">Register</button>
    </form>
    <p>Already have an account? <a href="login.php">Login here</a></p>
</section>

<?php include 'footer.php'; ?>
