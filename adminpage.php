<?php
session_start();
include 'connection.php';

// Authentication and Authorization
if (!isset($_SESSION['user_id']) || $_SESSION['usertype'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$current_admin_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// Display session-based messages for user feedback
$message = '';
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}

// --- ACTION HANDLING (ALL POST REQUESTS) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Patient Deletion
    if (isset($_POST['delete_patient'])) {
        $stmt = $con->prepare("DELETE FROM patients WHERE id = ?");
        $stmt->bind_param("i", $_POST['patient_id']);
        $_SESSION['message'] = $stmt->execute() ? "<p class='success-message'>Patient deleted successfully.</p>" : "<p class='error-message'>Error deleting patient.</p>";
        header("Location: adminpage.php?view=patients");
        exit();
    }
    // Admin Deletion
    if (isset($_POST['delete_admin'])) {
        $admin_id_to_delete = $_POST['admin_id'];
        if ($admin_id_to_delete == $current_admin_id) {
            $_SESSION['message'] = "<p class='error-message'>You cannot delete your own account.</p>";
        } else {
            $stmt = $con->prepare("DELETE FROM admin WHERE id = ?");
            $stmt->bind_param("i", $admin_id_to_delete);
            $_SESSION['message'] = $stmt->execute() ? "<p class='success-message'>Admin deleted successfully.</p>" : "<p class='error-message'>Error deleting admin.</p>";
        }
        header("Location: adminpage.php?view=admins");
        exit();
    }
    // Doctor Deletion
    if (isset($_POST['delete_doctor'])) {
        $stmt = $con->prepare("DELETE FROM doctors WHERE id = ?");
        $stmt->bind_param("i", $_POST['doctor_id']);
        $_SESSION['message'] = $stmt->execute() ? "<p class='success-message'>Doctor deleted successfully.</p>" : "<p class='error-message'>Error deleting doctor.</p>";
        header("Location: adminpage.php?view=doctors");
        exit();
    }
    // Message Deletion
    if (isset($_POST['delete_message'])) {
        $stmt = $con->prepare("DELETE FROM messages WHERE id = ?");
        $stmt->bind_param("i", $_POST['message_id']);
        $_SESSION['message'] = $stmt->execute() ? "<p class='success-message'>Message deleted successfully.</p>" : "<p class='error-message'>Error deleting message.</p>";
        header("Location: adminpage.php?view=messages");
        exit();
    }
    // Appointment Assignment
    if (isset($_POST['assign_doctor'])) {
        $stmt = $con->prepare("UPDATE appointments SET doctor_id = ?, status = 'Approved' WHERE id = ?");
        $stmt->bind_param("ii", $_POST['doctor_id'], $_POST['appointment_id']);
        $_SESSION['message'] = $stmt->execute() ? "<p class='success-message'>Doctor assigned successfully.</p>" : "<p class='error-message'>Error assigning doctor.</p>";
        header("Location: adminpage.php?view=appointments");
        exit();
    }
}

// --- DATA FETCHING FOR ALL SECTIONS ---
$total_patients = $con->query("SELECT COUNT(*) as count FROM patients")->fetch_assoc()['count'];
$total_doctors = $con->query("SELECT COUNT(*) as count FROM doctors")->fetch_assoc()['count'];
$pending_appointments = $con->query("SELECT COUNT(*) as count FROM appointments WHERE status = 'Pending'")->fetch_assoc()['count'];

$patients_result = $con->query("SELECT * FROM patients ORDER BY fname ASC");
$doctors_result = $con->query("SELECT * FROM doctors ORDER BY full_name ASC");
$admins_result = $con->query("SELECT * FROM admin ORDER BY username ASC");
$appointments_result = $con->query("SELECT a.id, a.appointment_date, a.department, a.status, p.fname, p.lname, d.full_name AS doctor_name FROM appointments a JOIN patients p ON a.patient_id = p.id LEFT JOIN doctors d ON a.doctor_id = d.id ORDER BY a.appointment_date DESC");
$messages_result = $con->query("SELECT * FROM messages ORDER BY created_at DESC");

// Prepare doctors list for assignment dropdown
$doctors_by_specialty = [];
$all_doctors_result = $con->query("SELECT id, full_name, specialty FROM doctors");
while ($doc = $all_doctors_result->fetch_assoc()) {
    $doctors_by_specialty[$doc['specialty']][] = $doc;
}

// Determine which view to show
$view = isset($_GET['view']) ? $_GET['view'] : 'dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - AMU Hospital</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="dashboard-body">
    <div class="dashboard-navbar">
        <span class="dashboard-welcome">Admin: <?php echo htmlspecialchars($username); ?></span>
        <div class="dashboard-nav-links">
            <a href="adminpage.php?view=dashboard" data-target="dashboard" class="dash-nav-link">Dashboard</a>
            <a href="adminpage.php?view=appointments" data-target="appointments" class="dash-nav-link">Appointments</a>
            <a href="adminpage.php?view=doctors" data-target="doctors" class="dash-nav-link">Manage Doctors</a>
            <a href="adminpage.php?view=patients" data-target="patients" class="dash-nav-link">Manage Patients</a>
            <a href="adminpage.php?view=admins" data-target="admins" class="dash-nav-link">Manage Admins</a>
            <a href="adminpage.php?view=messages" data-target="messages" class="dash-nav-link">Messages</a>
            <a href="logout.php" class="dash-logout-btn">Logout</a>
        </div>
    </div>

    <main class="dashboard-main">
        <div class="message-container"><?php if ($message) { echo $message; } ?></div>

        <!-- Dashboard Section -->
        <div class="dashboard-section" id="dashboard">
            <h1 class="dashboard-header">Admin Dashboard</h1>
            <div class="stat-card-container">
                <div class="stat-card"><h3><?php echo $total_patients; ?></h3><p>Total Patients</p></div>
                <div class="stat-card"><h3><?php echo $total_doctors; ?></h3><p>Total Doctors</p></div>
                <div class="stat-card"><h3><?php echo $pending_appointments; ?></h3><p>Pending Appointments</p></div>
            </div>
        </div>

        <!-- Appointments Section -->
        <div class="dashboard-section" id="appointments">
            <h1 class="dashboard-header">Manage Appointments</h1>
            <div class="table-container">
                <table>
                    <thead><tr><th>Patient</th><th>Date</th><th>Department</th><th>Assigned Doctor</th><th>Status</th><th>Action</th></tr></thead>
                    <tbody>
                        <?php if($appointments_result->num_rows > 0): while ($app = $appointments_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($app['fname'] . ' ' . $app['lname']); ?></td>
                            <td><?php echo htmlspecialchars($app['appointment_date']); ?></td>
                            <td><?php echo htmlspecialchars($app['department']); ?></td>
                            <td><?php echo $app['doctor_name'] ? htmlspecialchars($app['doctor_name']) : 'N/A'; ?></td>
                            <td><span class="status-<?php echo strtolower(htmlspecialchars($app['status'])); ?>"><?php echo htmlspecialchars($app['status']); ?></span></td>
                            <td>
                                <?php if ($app['status'] == 'Pending'): ?>
                                <form action="adminpage.php" method="POST" class="inline-form">
                                    <input type="hidden" name="appointment_id" value="<?php echo $app['id']; ?>">
                                    <select name="doctor_id" required>
                                        <option value="">Assign Doctor...</option>
                                        <?php $department = $app['department']; if (isset($doctors_by_specialty[$department])): foreach ($doctors_by_specialty[$department] as $doc): ?>
                                            <option value="<?php echo $doc['id']; ?>"><?php echo htmlspecialchars($doc['full_name']); ?></option>
                                        <?php endforeach; else: ?>
                                            <option value="" disabled>No doctors in <?php echo htmlspecialchars($department); ?></option>
                                        <?php endif; ?>
                                    </select>
                                    <button type="submit" name="assign_doctor" class="btn-action-small">Assign</button>
                                </form>
                                <?php else: echo 'No action required'; endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; else: ?>
                            <tr><td colspan="6">No appointments found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Manage Doctors Section -->
        <div class="dashboard-section" id="doctors">
            <h1 class="dashboard-header">Manage Doctors</h1>
            <a href="doctor_form.php" class="btn-main" style="margin-bottom: 20px; display: inline-block;">Add New Doctor</a>
            <div class="table-container">
                <table>
                    <thead><tr><th>Name</th><th>Specialty</th><th>Action</th></tr></thead>
                    <tbody>
                        <?php if($doctors_result->num_rows > 0): while ($doc = $doctors_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($doc['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($doc['specialty']); ?></td>
                            <td>
                                <a href="doctor_form.php?id=<?php echo $doc['id']; ?>" class="btn-action-small btn-edit">Edit</a>
                                <form action="adminpage.php" method="POST" class="inline-form" onsubmit="return confirm('Delete this doctor?');"><input type="hidden" name="doctor_id" value="<?php echo $doc['id']; ?>"><button type="submit" name="delete_doctor" class="btn-action-small btn-delete">Delete</button></form>
                            </td>
                        </tr>
                        <?php endwhile; else: ?>
                            <tr><td colspan="3">No doctors found. Click "Add New Doctor" to begin.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Manage Patients Section -->
        <div class="dashboard-section" id="patients">
             <h1 class="dashboard-header">Manage Patients</h1>
             <a href="patient_form.php" class="btn-main" style="margin-bottom: 20px; display: inline-block;">Add New Patient</a>
             <div class="table-container">
                <table>
                    <thead><tr><th>Name</th><th>Username</th><th>Phone</th><th>Actions</th></tr></thead>
                    <tbody>
                        <?php if($patients_result->num_rows > 0): while ($pat = $patients_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($pat['fname'] . ' ' . $pat['lname']); ?></td>
                            <td><?php echo htmlspecialchars($pat['username']); ?></td>
                            <td><?php echo htmlspecialchars($pat['phone']); ?></td>
                            <td>
                                <a href="patient_form.php?id=<?php echo $pat['id']; ?>" class="btn-action-small btn-edit">Edit</a>
                                <form action="adminpage.php" method="POST" class="inline-form" onsubmit="return confirm('Delete this patient?');"><input type="hidden" name="patient_id" value="<?php echo $pat['id']; ?>"><button type="submit" name="delete_patient" class="btn-action-small btn-delete">Delete</button></form>
                            </td>
                        </tr>
                        <?php endwhile; else: ?>
                            <tr><td colspan="4">No patients have registered yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Manage Admins Section -->
        <div class="dashboard-section" id="admins">
             <h1 class="dashboard-header">Manage Administrators</h1>
             <a href="admin_form.php" class="btn-main" style="margin-bottom: 20px; display: inline-block;">Add New Admin</a>
             <div class="table-container">
                <table>
                    <thead><tr><th>Username</th><th>Actions</th></tr></thead>
                    <tbody>
                        <?php if($admins_result->num_rows > 0): while ($admin = $admins_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($admin['username']); ?></td>
                            <td>
                                <a href="admin_form.php?id=<?php echo $admin['id']; ?>" class="btn-action-small btn-edit">Edit</a>
                                <form action="adminpage.php" method="POST" class="inline-form" onsubmit="return confirm('Delete this admin?');">
                                    <input type="hidden" name="admin_id" value="<?php echo $admin['id']; ?>">
                                    <button type="submit" name="delete_admin" class="btn-action-small btn-delete" <?php if ($admin['id'] == $current_admin_id) echo 'disabled title="You cannot delete your own account"'; ?>>Delete</button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; else: ?>
                            <tr><td colspan="2">Only one admin account exists.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Messages Section -->
        <div class="dashboard-section" id="messages">
             <h1 class="dashboard-header">Contact Messages</h1>
             <div class="table-container">
                <table>
                    <thead><tr><th>Name</th><th>Email</th><th>Message</th><th>Received</th><th>Action</th></tr></thead>
                    <tbody>
                        <?php if($messages_result->num_rows > 0): while ($msg = $messages_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($msg['name']); ?></td>
                            <td><?php echo htmlspecialchars($msg['email']); ?></td>
                            <td class="message-cell"><?php echo nl2br(htmlspecialchars($msg['message'])); ?></td>
                            <td><?php echo htmlspecialchars($msg['created_at']); ?></td>
                            <td>
                                <form action="adminpage.php" method="POST" class="inline-form" onsubmit="return confirm('Delete this message?');"><input type="hidden" name="message_id" value="<?php echo $msg['id']; ?>"><button type="submit" name="delete_message" class="btn-action-small btn-delete">Delete</button></form>
                            </td>
                        </tr>
                        <?php endwhile; else: ?>
                            <tr><td colspan="5">There are no messages.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </main>
    <script src="javascript.js"></script>
</body>
</html>