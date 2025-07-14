<?php
session_start();
include 'connection.php';
$message_status = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_message'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $message = trim($_POST['message']);

    if (!empty($name) && !empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL) && !empty($message)) {
        $stmt = $con->prepare("INSERT INTO messages (name, email, message) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $message);

        if ($stmt->execute()) {
            $message_status = "<p class='success-message'>Message sent successfully! We will get back to you shortly.</p>";
        } else {
            $message_status = "<p class='error-message'>Error: Could not send message. Please try again later.</p>";
        }
        $stmt->close();
    } else {
        $message_status = "<p class='error-message'>All fields are required and email must be valid.</p>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - AMU Hospital</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'navigation.php'; ?>

    <main class="page-container">
        <div class="contact-page-container">
            <h2 class="section-title">Get In Touch</h2>
            <p class="section-subtitle">We are here for you. Contact us with any questions or for collaborations.</p>

            <div class="contact-content-wrapper">
                <div class="contact-form-section">
                    <h3>Send us a Message</h3>
                    <form action="contact.php" method="post">
                        <?php echo $message_status; ?>
                        <div class="form-group">
                            <label for="name">Name</label>
                            <input type="text" id="name" name="name" placeholder="Your Full Name" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" placeholder="Your Email Address" required>
                        </div>
                        <div class="form-group">
                            <label for="message">Message</label>
                            <textarea id="message" rows="5" placeholder="Your Message" name="message" required></textarea>
                        </div>
                        <button type="submit" name="submit_message" class="btn-main">Send Message</button>
                    </form>
                </div>
                <div class="contact-info-section">
                    <h3>Contact Information</h3>
                    <div class="info-item">
                        <i class="fa-solid fa-map-marker-alt"></i>
                        <span>Arba Minch, Ethiopia</span>
                    </div>
                    <div class="info-item">
                        <i class="fa-solid fa-phone"></i>
                        <span><a href="tel:+251923490535">+251 11 199 9898</a></span>
                    </div>
                    <div class="info-item">
                        <i class="fa-solid fa-envelope"></i>
                        <span><a href="mailto:contact@amuhospital.com">contact@amuhospital.com</a></span>
                    </div>
                    <h3 style="margin-top: 2rem;">Follow Us</h3>
                    <div class="social-icons">
                        <a href="#" target="_blank"><i class="fab fa-youtube"></i></a>
                        <a href="#" target="_blank"><i class="fab fa-telegram"></i></a>
                        <a href="#" target="_blank"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" target="_blank"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include 'footer.php'; ?>
    <script src="javascript.js"></script>
</body>
</html>