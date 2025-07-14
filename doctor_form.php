<?php
session_start();
include 'connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['usertype'] !== 'admin') {
    header("Location: login.php");
    exit();
}

function handle_image_upload($file_input, $current_image) {
    if (isset($file_input) && $file_input['error'] == UPLOAD_ERR_OK) {
        $target_dir = "images/uploads/";
        $max_file_size = 2 * 1024 * 1024; // 2MB
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        
        $file_ext = strtolower(pathinfo($file_input['name'], PATHINFO_EXTENSION));

        if (!in_array($file_ext, $allowed_types)) return ['error' => 'Invalid file type. Only JPG, PNG, GIF allowed.'];
        if ($file_input['size'] > $max_file_size) return ['error' => 'File size exceeds 2MB limit.'];
        
        $new_file_name = uniqid('doc_', true) . '.' . $file_ext;
        
        if (move_uploaded_file($file_input['tmp_name'], $target_dir . $new_file_name)) {
            if ($current_image && $current_image != 'default.png' && file_exists($target_dir . $current_image)) {
                @unlink($target_dir . $current_image);
            }
            return ['success' => $new_file_name];
        } else {
            return ['error' => 'Failed to move uploaded file. Check folder permissions.'];
        }
    }
    return ['success' => $current_image];
}

$message = '';
$doctor = ['id' => '', 'full_name' => '', 'specialty' => '', 'bio' => '', 'profile_image' => 'default.png'];
$is_edit = false;

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $is_edit = true;
    $stmt = $con->prepare("SELECT * FROM doctors WHERE id = ?");
    $stmt->bind_param("i", $_GET['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $doctor = $result->fetch_assoc();
    } else {
        $_SESSION['message'] = "<p class='error-message'>Doctor not found.</p>";
        header("Location: adminpage.php?view=doctors");
        exit();
    }
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if the main form fields are submitted before trying to access them
    if (isset($_POST['full_name'], $_POST['specialty'], $_POST['bio'])) {
        $full_name = trim($_POST['full_name']);
        $specialty = trim($_POST['specialty']);
        $bio = trim($_POST['bio']);
        $doctor_id = $_POST['doctor_id'];
        $current_image = $_POST['current_image'];

        $upload_result = handle_image_upload($_FILES['profile_image'], $current_image);
        
        if (isset($upload_result['error'])) {
            $message = "<p class='error-message'>" . htmlspecialchars($upload_result['error']) . "</p>";
        } else {
            $image_name = $upload_result['success'];
            
            if ($is_edit) {
                $stmt = $con->prepare("UPDATE doctors SET full_name = ?, specialty = ?, bio = ?, profile_image = ? WHERE id = ?");
                $stmt->bind_param("ssssi", $full_name, $specialty, $bio, $image_name, $doctor_id);
                $action = 'updated';
            } else {
                $stmt = $con->prepare("INSERT INTO doctors (full_name, specialty, bio, profile_image) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssss", $full_name, $specialty, $bio, $image_name);
                $action = 'created';
            }

            if ($stmt->execute()) {
                $_SESSION['message'] = "<p class='success-message'>Doctor " . $action . " successfully.</p>";
                header("Location: adminpage.php?view=doctors");
                exit();
            } else {
                $message = "<p class='error-message'>Error: Could not save details. Username might be taken.</p>";
            }
            $stmt->close();
        }
    } else {
        $message = "<p class='error-message'>Form data was not submitted correctly.</p>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $is_edit ? 'Edit' : 'Add'; ?> Doctor</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="form-page-container">
        <div class="form-container">
            <form action="doctor_form.php<?php echo $is_edit ? '?id='.htmlspecialchars($doctor['id']) : ''; ?>" method="post" enctype="multipart/form-data">
                <h3 class="form-header"><?php echo $is_edit ? 'Edit Doctor' : 'Add New Doctor'; ?></h3>
                <?php if ($message) echo $message; ?>
                <input type="hidden" name="doctor_id" value="<?php echo htmlspecialchars($doctor['id']); ?>">
                <input type="hidden" name="current_image" value="<?php echo htmlspecialchars($doctor['profile_image']); ?>">
                
                <div class="form-group"><label>Full Name</label><input type="text" name="full_name" value="<?php echo htmlspecialchars($doctor['full_name']); ?>" required></div>
                <div class="form-group"><label>Specialty / Department</label><select name="specialty" required><option value="">-- Select --</option><option value="Cardiology" <?php if($doctor['specialty'] == 'Cardiology') echo 'selected';?>>Cardiology</option><option value="Dermatology" <?php if($doctor['specialty'] == 'Dermatology') echo 'selected';?>>Dermatology</option><option value="Dentistry" <?php if($doctor['specialty'] == 'Dentistry') echo 'selected';?>>Dentistry</option><option value="Hematology" <?php if($doctor['specialty'] == 'Hematology') echo 'selected';?>>Hematology</option><option value="Infectious Disease" <?php if($doctor['specialty'] == 'Infectious Disease') echo 'selected';?>>Infectious Disease</option><option value="Other" <?php if($doctor['specialty'] == 'Other') echo 'selected';?>>Other</option></select></div>
                <div class="form-group"><label>Biography</label><textarea name="bio" rows="4"><?php echo htmlspecialchars($doctor['bio']); ?></textarea></div>
                <div class="form-group"><label>Profile Image (JPG, PNG, GIF, max 2MB)</label><input type="file" name="profile_image" accept="image/jpeg,image/png,image/gif">
                    <?php if ($is_edit && $doctor['profile_image'] && file_exists('images/uploads/' . $doctor['profile_image'])): ?>
                        <p style="margin-top:10px;">Current image:</p><img src="images/uploads/<?php echo htmlspecialchars($doctor['profile_image']); ?>" alt="Current Profile Photo" style="max-width: 100px; margin-top: 5px; border-radius: 5px;">
                    <?php endif; ?>
                </div>
                
                <button type="submit" class="btn-form-submit">Save Doctor</button>
                <a href="adminpage.php?view=doctors" class="form-footer-text">Cancel</a>
            </form>
        </div>
    </div>
</body>
</html>