<?php
include 'connection.php';
$sent = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sent = true;
}
include 'header.php';
?>

<section class="container form-page">
    <h2 class="section-title">Contact Us</h2>

    <?php if ($sent): ?>
        <p class="alert alert-success">Thank you! Your message has been received.</p>
    <?php endif; ?>

    <form method="POST" action="contact.php" class="form-box">
        <label>Your Name</label>
        <input type="text" name="name" required>

        <label>Your Email</label>
        <input type="email" name="email" required>

        <label>Message</label>
        <textarea name="message" rows="5" required></textarea>

        <button type="submit" class="btn-primary">Send Message</button>
    </form>

    <div class="contact-info">
        <p><strong>Address:</strong> Kathmandu, Nepal</p>
        <p><strong>Email:</strong> info@meroelectronics.com</p>
        <p><strong>Phone:</strong> +977-9800000000</p>
    </div>
</section>

<?php include 'footer.php'; ?>
