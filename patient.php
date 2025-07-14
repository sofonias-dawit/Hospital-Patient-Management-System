<?php
session_start();
include 'connection.php';

if (!isset($_SESSION['user_id']) || $_SESSION['usertype'] !== 'patient') {
    header("Location: login.php");
    exit();
}

$patient_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$message = '';

if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_appointment'])) {
    $appointment_date = $_POST['appointment_date'];
    $department = $_POST['department'];
    $today = date("Y-m-d");

    if ($appointment_date < $today) {
        $_SESSION['message'] = "<p class='error-message'>You cannot book an appointment in the past.</p>";
    } else {
        $stmt = $con->prepare("INSERT INTO appointments (patient_id, appointment_date, department) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $patient_id, $appointment_date, $department);

        if ($stmt->execute()) {
            $_SESSION['message'] = "<p class='success-message'>Appointment booked successfully! You can view its status in your history.</p>";
        } else {
            $_SESSION['message'] = "<p class='error-message'>Failed to book appointment. Please try again.</p>";
        }
        $stmt->close();
    }
    header("Location: patient.php");
    exit();
}

$history_stmt = $con->prepare("
    SELECT a.appointment_date, a.department, a.status, d.full_name AS doctor_name
    FROM appointments a
    LEFT JOIN doctors d ON a.doctor_id = d.id
    WHERE a.patient_id = ?
    ORDER BY a.appointment_date DESC
");
$history_stmt->bind_param("i", $patient_id);
$history_stmt->execute();
$history_result = $history_stmt->get_result();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Dashboard - AMU Hospital</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="dashboard-body">
    <div class="dashboard-navbar">
        <span class="dashboard-welcome">Welcome, <?php echo htmlspecialchars($username); ?></span>
        <div class="dashboard-nav-links">
            <a href="#dashboard" class="dash-nav-link active" data-target="dashboard">Dashboard</a>
            <a href="#book-appointment" class="dash-nav-link" data-target="book-appointment">Book Appointment</a>
            <a href="#appointment-history" class="dash-nav-link" data-target="appointment-history">My History</a>
            <a href="logout.php" class="dash-logout-btn">Logout</a>
        </div>
    </div>

    <main class="dashboard-main">
        <div class="message-container">
            <?php if ($message) { echo $message; } ?>
        </div>
        
        <div class="dashboard-section active" id="dashboard">
            <h1 class="dashboard-header">Patient Dashboard</h1>
            <div class="dashboard-cards-container">
                <div class="dashboard-card" data-target="book-appointment">
                    <h3>Book New Appointment</h3>
                    <p>Schedule a visit with one of our departments quickly and easily.</p>
                </div>
                <div class="dashboard-card" data-target="appointment-history">
                    <h3>View History</h3>
                    <p>Check the status of your upcoming and past appointments.</p>
                </div>
            </div>
        </div>

        <div class="dashboard-section" id="book-appointment">
            <h1 class="dashboard-header">Book An Appointment</h1>
            <div class="form-container-dashboard">
                <form action="patient.php" method="POST">
                    <div class="form-group">
                        <label for="appointment_date">Select Date:</label>
                        <input type="date" name="appointment_date" id="appointment_date" required>
                    </div>
                    <div class="form-group">
                        <label for="department">Select Department:</label>
                        <select name="department" id="department" required>
                            <option value="">-- Select Department --</option>
                            <option value="Cardiology">Cardiology</option>
                            <option value="Dermatology">Dermatology</option>
                            <option value="Dentistry">Dentistry</option>
                            <option value="Hematology">Hematology</option>
                            <option value="Infectious Disease">Infectious Disease</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <button type="submit" name="book_appointment" class="btn-form-submit">Book Now</button>
                </form>
            </div>
        </div>

        <div class="dashboard-section" id="appointment-history">
            <h1 class="dashboard-header">Appointment History</h1>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Department</th>
                            <th>Assigned Doctor</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($history_result->num_rows > 0): ?>
                            <?php while($row = $history_result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['appointment_date']); ?></td>
                                    <td><?php echo htmlspecialchars($row['department']); ?></td>
                                    <td><?php echo $row['doctor_name'] ? htmlspecialchars($row['doctor_name']) : 'Not Assigned Yet'; ?></td>
                                    <td>
                                        <span class="status-<?php echo strtolower(htmlspecialchars($row['status'])); ?>">
                                            <?php echo htmlspecialchars($row['status']); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="4">You have no appointment history.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
    <script src="javascript.js"></script>
</body>
</html>