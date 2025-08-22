<?php
$required_role = 'student';
include '../auth.php';
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    echo '<p style="color:red">Student session not found or not a student. Please login again.</p>';
    exit;
}
$student_id = $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - EV Academy ERP</title>
    <link rel="stylesheet" href="../erp-theme.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
    <header class="header" id="header">
        <div class="container">
            <div class="header-content" style="display:flex;align-items:center;justify-content:space-between;">
                <a href="dashboard.php" class="logo" style="display:flex;align-items:center;gap:0.5em;">
                    <div class="logo-icon"><span>VA</span></div>
                    <span class="logo-text">EV Academy ERP</span>
                </a>
                <button class="mobile-menu-btn" onclick="document.body.classList.toggle('nav-open')"><i class="fa-solid fa-bars"></i></button>
                <nav class="nav-desktop">
                    <a href="dashboard.php" class="nav-link active">Dashboard</a>
                    <a href="view_attendance.php" class="nav-link">View Attendance</a>
                    <a href="fee_payment.php" class="nav-link">Fee Payment</a>
                    <a href="../logout.php" class="nav-link">Logout</a>
                </nav>
            </div>
        </div>
    </header>
    <main>
        <section class="hero">
            <div class="container">
                <div class="hero-content" style="max-width: 600px; margin: 0 auto;">
                    <div class="dashboard-actions">
                        <a href="view_attendance.php" class="dashboard-action-btn">
                            <span class="dashboard-action-icon"><i class="fa-solid fa-calendar-days"></i></span>
                            View Attendance
                        </a>
                        <a href="fee_payment.php" class="dashboard-action-btn" style="margin-top:1em;background:#e3eafc;color:#3a4a6b;">
                            <span class="dashboard-action-icon"><i class="fa-solid fa-indian-rupee-sign"></i></span>
                            Fee Payment
                        </a>
                    </div>
                </div>
            </div>
        </section>
    </main>
</body>
</html>