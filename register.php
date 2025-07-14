<?php
session_start();
include "connection.php";
$error_message = '';

if (isset($_SESSION['user_id'])) {
    header("Location: patient.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])) {
    $username = trim($_POST['username']);
    $fname = trim($_POST['fname']);
    $lname = trim($_POST['lname']);
    $phone = trim($_POST['phone']);
    $sex = $_POST['sex'];
    $password = $_POST['password']; // Storing plain text password
    $repassword = $_POST['repassword'];

    if (empty($username) || empty($fname) || empty($lname) || empty($phone) || empty($password)) {
        $error_message = "All fields are required.";
    } elseif ($password !== $repassword) {
        $error_message = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $error_message = "Password must be at least 6 characters long.";
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $error_message = "Username can only contain letters, numbers, and underscores.";
    } else {
        $stmt = $con->prepare("SELECT id FROM patients WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $error_message = "Username is already taken. Please choose another one.";
        } else {
            // DANGEROUS: Storing password in plain text. Hashing is removed.
            $plain_password = $password; 
            
            $insert_stmt = $con->prepare("INSERT INTO patients (username, fname, lname, phone, sex, password) VALUES (?, ?, ?, ?, ?, ?)");
            $insert_stmt->bind_param("ssssss", $username, $fname, $lname, $phone, $sex, $plain_password);
            
            if ($insert_stmt->execute()) {
                $_SESSION['user_id'] = $insert_stmt->insert_id;
                $_SESSION['username'] = $fname;
                $_SESSION['usertype'] = 'patient';
                header("Location: patient.php");
                exit();
            } else {
                $error_message = "Registration failed. Please try again.";
            }
            $insert_stmt->close();
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - AMU Hospital</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'navigation.php'; ?>
    <div class="form-page-container">
        <div class="form-container">
            <form action="register.php" method="post">
                <h3 class="form-header">Create a New Account</h3>
                <?php if ($error_message): ?>
                    <p class="error-message"><?php echo htmlspecialchars($error_message); ?></p>
                <?php endif; ?>
                <div class="form-group">
                    <label for="fname">First Name</label>
                    <input type="text" name="fname" id="fname" required>
                </div>
                <div class="form-group">
                    <label for="lname">Last Name</label>
                    <input type="text" name="lname" id="lname" required>
                </div>
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" name="username" id="username" required>
                </div>
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" name="phone" id="phone" required>
                </div>
                <div class="form-group">
                    <label>Gender</label>
                    <div class="radio-group">
                        <label><input type="radio" name="sex" value="Male" checked> Male</label>
                        <label><input type="radio" name="sex" value="Female"> Female</label>
                    </div>
                </div>
                <div class="form-group">
                    <label for="password">Password (min. 6 characters)</label>
                    <input type="password" name="password" id="password" required>
                </div>
                <div class="form-group">
                    <label for="repassword">Confirm Password</label>
                    <input type="password" name="repassword" id="repassword" required>
                </div>
                <input type="submit" value="Register" name="register" class="btn-form-submit">
                <p class="form-footer-text">Already have an account? <a href="login.php">Sign in</a></p>
            </form>
        </div>
    </div>
    <?php include 'footer.php'; ?>
    <script src="javascript.js"></script>
</body>
</html>