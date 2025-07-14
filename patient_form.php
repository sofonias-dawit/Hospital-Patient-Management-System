<?php
session_start();
include 'connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['usertype'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$message = '';
$patient = ['id' => '', 'fname' => '', 'lname' => '', 'username' => '', 'phone' => '', 'sex' => 'Male'];
$is_edit = false;

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $is_edit = true;
    $stmt = $con->prepare("SELECT * FROM patients WHERE id = ?");
    $stmt->bind_param("i", $_GET['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $patient = $result->fetch_assoc();
    } else {
        $_SESSION['message'] = "<p class='error-message'>Patient not found.</p>";
        header("Location: adminpage.php?view=patients");
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['fname'], $_POST['lname'], $_POST['username'], $_POST['phone'], $_POST['sex'])) {
        $fname = trim($_POST['fname']);
        $lname = trim($_POST['lname']);
        $username = trim($_POST['username']);
        $phone = trim($_POST['phone']);
        $sex = $_POST['sex'];
        $password = $_POST['password'];

        if ($is_edit) {
            $patient_id = $_POST['patient_id'];
            if (!empty($password)) {
                $stmt = $con->prepare("UPDATE patients SET fname=?, lname=?, username=?, phone=?, sex=?, password=? WHERE id=?");
                $stmt->bind_param("ssssssi", $fname, $lname, $username, $phone, $sex, $password, $patient_id);
            } else {
                $stmt = $con->prepare("UPDATE patients SET fname=?, lname=?, username=?, phone=?, sex=? WHERE id=?");
                $stmt->bind_param("sssssi", $fname, $lname, $username, $phone, $sex, $patient_id);
            }
            $action = 'updated';
        } else {
            if(empty($password)) {
                $message = "<p class='error-message'>Password is required for new patients.</p>";
            } else {
                $stmt = $con->prepare("INSERT INTO patients (fname, lname, username, phone, sex, password) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssss", $fname, $lname, $username, $phone, $sex, $password);
                $action = 'created';
            }
        }

        if (empty($message) && isset($stmt) && $stmt->execute()) {
            $_SESSION['message'] = "<p class='success-message'>Patient " . $action . " successfully.</p>";
            header("Location: adminpage.php?view=patients");
            exit();
        } else if (empty($message)) {
            $message = "<p class='error-message'>Error saving patient details. The username might already be taken.</p>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $is_edit ? 'Edit' : 'Add'; ?> Patient</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="form-page-container">
        <div class="form-container">
            <form action="patient_form.php<?php echo $is_edit ? '?id='.htmlspecialchars($patient['id']) : ''; ?>" method="post">
                <h3 class="form-header"><?php echo $is_edit ? 'Edit Patient' : 'Add New Patient'; ?></h3>
                <?php if ($message) echo $message; ?>
                <input type="hidden" name="patient_id" value="<?php echo htmlspecialchars($patient['id']); ?>">
                
                <div class="form-group"><label>First Name</label><input type="text" name="fname" value="<?php echo htmlspecialchars($patient['fname']); ?>" required></div>
                <div class="form-group"><label>Last Name</label><input type="text" name="lname" value="<?php echo htmlspecialchars($patient['lname']); ?>" required></div>
                <div class="form-group"><label>Username</label><input type="text" name="username" value="<?php echo htmlspecialchars($patient['username']); ?>" required></div>
                <div class="form-group"><label>Phone</label><input type="tel" name="phone" value="<?php echo htmlspecialchars($patient['phone']); ?>" required></div>
                <div class="form-group"><label>Gender</label><select name="sex" required><option value="Male" <?php echo ($patient['sex'] == 'Male') ? 'selected' : ''; ?>>Male</option><option value="Female" <?php echo ($patient['sex'] == 'Female') ? 'selected' : ''; ?>>Female</option></select></div>
                <div class="form-group"><label>Password</label><input type="password" name="password" placeholder="<?php echo $is_edit ? 'Leave blank to keep current' : 'Required'; ?>"></div>
                
                <button type="submit" class="btn-form-submit">Save Patient</button>
                <a href="adminpage.php?view=patients" class="form-footer-text">Cancel</a>
            </form>
        </div>
    </div>
</body>
</html>