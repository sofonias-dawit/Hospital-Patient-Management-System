<?php
session_start();
include 'connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['usertype'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$message = '';
$admin = ['id' => '', 'username' => ''];
$is_edit = false;

if (isset($_GET['id'])) {
    $is_edit = true;
    $stmt = $con->prepare("SELECT * FROM admin WHERE id = ?");
    $stmt->bind_param("i", $_GET['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $admin = $result->fetch_assoc();
    } else {
        $_SESSION['message'] = "<p class='error-message'>Admin not found.</p>";
        header("Location: adminpage.php?view=admins");
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if ($is_edit) {
        $admin_id = $_POST['admin_id'];
        if (!empty($password)) {
            $stmt = $con->prepare("UPDATE admin SET username=?, password=? WHERE id=?");
            $stmt->bind_param("ssi", $username, $password, $admin_id);
        } else {
            $stmt = $con->prepare("UPDATE admin SET username=? WHERE id=?");
            $stmt->bind_param("si", $username, $admin_id);
        }
        $action = 'updated';
    } else {
        if(empty($password)) {
            $message = "<p class='error-message'>Password is required for new admins.</p>";
        } else {
            $stmt = $con->prepare("INSERT INTO admin (username, password) VALUES (?, ?)");
            $stmt->bind_param("ss", $username, $password);
            $action = 'created';
        }
    }

    if (empty($message) && $stmt->execute()) {
        $_SESSION['message'] = "<p class='success-message'>Admin " . $action . " successfully.</p>";
        header("Location: adminpage.php?view=admins");
        exit();
    } else if (empty($message)) {
        $message = "<p class='error-message'>Error saving admin details. The username might already be taken.</p>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $is_edit ? 'Edit' : 'Add'; ?> Admin</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="form-page-container">
        <div class="form-container">
            <form action="admin_form.php<?php echo $is_edit ? '?id='.$admin['id'] : ''; ?>" method="post">
                <h3 class="form-header"><?php echo $is_edit ? 'Edit Admin' : 'Add New Admin'; ?></h3>
                <?php if ($message) echo $message; ?>
                <input type="hidden" name="admin_id" value="<?php echo htmlspecialchars($admin['id']); ?>">
                
                <div class="form-group"><label>Username</label><input type="text" name="username" value="<?php echo htmlspecialchars($admin['username']); ?>" required></div>
                <div class="form-group"><label>Password</label><input type="password" name="password" placeholder="<?php echo $is_edit ? 'Leave blank to keep current' : 'Required'; ?>"></div>
                
                <button type="submit" class="btn-form-submit">Save Admin</button>
                <a href="adminpage.php?view=admins" class="form-footer-text">Cancel</a>
            </form>
        </div>
    </div>
</body>
</html>