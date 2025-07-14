<?php
include 'connection.php';
$doctors_result = $con->query("SELECT * FROM doctors ORDER BY RAND() LIMIT 3");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>AMU Hospital - Modern Healthcare</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'navigation.php'; ?>
    <main>
        <section class="cover">
            <div class="cover-content">
                <h2 class="animate-on-scroll">AMU Hospital</h2>
                <p class="animate-on-scroll">Advanced, Compassionate, and Comprehensive Medical Services.</p>
                <div class="animate-on-scroll">
                    <a href="login.php" class="btn-main">Make Appointment</a>
                    <a href="services.php" class="btn-secondary">Explore Services</a>
                </div>
            </div>
        </section>

        <section class="info-section">
            <h2 class="section-title">Why Choose AMU Hospital?</h2>
            <div class="why-us-container">
                <div class="why-us-card">
                    <i class="fas fa-user-md"></i>
                    <h3>Expert Doctors</h3>
                    <p>Our team consists of highly skilled and experienced medical professionals.</p>
                </div>
                <div class="why-us-card">
                    <i class="fas fa-microscope"></i>
                    <h3>Modern Technology</h3>
                    <p>We use state-of-the-art equipment for accurate diagnosis and treatment.</p>
                </div>
                <div class="why-us-card">
                    <i class="fas fa-ambulance"></i>
                    <h3>24/7 Emergency Care</h3>
                    <p>Our emergency services are available around the clock to handle any situation.</p>
                </div>
            </div>
        </section>

        <section class="stats-section">
            <div class="stats-overlay"></div>
            <div class="stats-container">
                <h2 class="section-title-light">Our Commitment By The Numbers</h2>
                <div class="stats-grid">
                    <div class="stat-item">
                        <i class="fas fa-procedures"></i>
                        <p class="stat-number" data-target="1200">0</p>
                        <p class="stat-label">Successful Surgeries</p>
                    </div>
                    <div class="stat-item">
                        <i class="fas fa-user-md"></i>
                        <p class="stat-number" data-target="150">0</p>
                        <p class="stat-label">Expert Doctors</p>
                    </div>
                    <div class="stat-item">
                        <i class="fas fa-smile"></i>
                        <p class="stat-number" data-target="15000">0</p>
                        <p class="stat-label">Happy Patients</p>
                    </div>
                    <div class="stat-item">
                        <i class="fas fa-bed"></i>
                        <p class="stat-number" data-target="300">0</p>
                        <p class="stat-label">Hospital Beds</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="doctors-preview">
            <h2 class="section-title">Meet Our Specialists</h2>
            <div class="doctor-cards-container">
                <?php if ($doctors_result->num_rows > 0): ?>
                    <?php while($doctor = $doctors_result->fetch_assoc()):
                        $image_path = 'images/uploads/' . htmlspecialchars($doctor['profile_image']);
                        if (!file_exists($image_path) || empty($doctor['profile_image'])) {
                            $image_path = 'images/uploads/default.png';
                        }
                    ?>
                        <div class="doctor-card">
                            <img src="<?php echo $image_path; ?>" alt="Photo of <?php echo htmlspecialchars($doctor['full_name']); ?>">
                            <h3><?php echo htmlspecialchars($doctor['full_name']); ?></h3>
                            <p><?php echo htmlspecialchars($doctor['specialty']); ?></p>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>Our dedicated team of doctors will be featured here soon.</p>
                <?php endif; ?>
            </div>
        </section>

        <section class="testimonial-section">
            <h2 class="section-title">What Our Patients Say</h2>
            <div class="testimonial-container">
                <div class="testimonial-card">
                    <p>"The care I received at AMU Hospital was exceptional. The doctors and staff were professional and compassionate throughout my treatment."</p>
                    <h4>- Abebe Besintu</h4>
                </div>
                <div class="testimonial-card">
                    <p>"A modern facility with a focus on patient comfort. I highly recommend their services to anyone in need of medical attention."</p>
                    <h4>- Lidia Musse </h4>
                </div>
            </div>
        </section>

    </main>
    <?php include 'footer.php'; ?>
    <script src="javascript.js"></script>
</body>
</html>