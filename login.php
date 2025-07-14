<?php
session_start();
include "connection.php";
$error_message = '';

if (isset($_SESSION['user_id'])) {
    if ($_SESSION['usertype'] == 'admin') {
        header("Location: adminpage.php");
    } else {
        header("Location: patient.php");
    }
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // DANGEROUS: Storing and checking plain text passwords
    
    // Check admin table first
    $stmt = $con->prepare("SELECT id, username, password, usertype FROM admin WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        // Plain text comparison
        if ($password === $user['password']) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['usertype'] = $user['usertype'];
            header("Location: adminpage.php");
            exit();
        }
    }

    // If not an admin, check patients table
    $stmt = $con->prepare("SELECT id, username, fname, password, usertype FROM patients WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        // Plain text comparison
        if ($password === $user['password']) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['fname'];
            $_SESSION['usertype'] = $user['usertype'];
            header("Location: patient.php");
            exit();
        }
    }
    
    $error_message = "Invalid username or password. Please try again.";
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - AMU Hospital</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'navigation.php'; ?>
    <div class="form-page-container">
        <div class="form-container">
            <form action="login.php" method="post">
                <h3 class="form-header">Login to Your Account</h3>
                <?php if ($error_message): ?>
                    <p class="error-message"><?php echo htmlspecialchars($error_message); ?></p>
                <?php endif; ?>
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" placeholder="Enter your username" required>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" placeholder="Enter your password" required>
                </div>
                <input type="submit" class="btn-form-submit" name="submit" value="Login">
                <p class="form-footer-text">Don't have an account? <a href="register.php">Register here</a></p>
            </form>
        </div>
    </div>
    <?php include 'footer.php'; ?>
    <script src="javascript.js"></script>
</body>
</html>