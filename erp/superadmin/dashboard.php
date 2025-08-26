<?php
$required_role = 'super_admin';
include '../auth.php';
require_once '../db.php'; // Make sure db connection is included

// Get user's name from the database
$stmt = $pdo->prepare("SELECT name FROM erp_users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
$user_name = $user ? htmlspecialchars($user['name']) : 'Super Admin';

// Dynamic greeting based on time
date_default_timezone_set('Asia/Kolkata');
$hour = date('G');
if ($hour < 12) {
    $greeting = "Good Morning";
} elseif ($hour < 17) {
    $greeting = "Good Afternoon";
} else {
    $greeting = "Good Evening";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin Dashboard - EV Academy ERP</title>
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
                    <a href="manage_timetable.php" class="nav-link">Manage Timetable</a>
                    <a href="manage_faculty.php" class="nav-link">Faculty</a>
                    <a href="manage_students.php" class="nav-link">Students</a>
                    <a href="manage_fees.php" class="nav-link">Fees</a>
                    <a href="view_attendance.php" class="nav-link">Attendance</a>
                    <a href="view_faculty_work.php" class="nav-link">Faculty Work</a>
                    <a href="../logout.php" class="nav-link">Logout</a>
                </nav>
            </div>
        </div>
    </header>
    <main>
        <section class="hero">
            <div class="container">
                <div class="hero-content" style="max-width: 600px; margin: 0 auto;">
                    <h1 class="hero-title" style="text-align:center; color:#3a4a6b;"><?php echo $greeting . ", " . $user_name; ?>!</h1>
                    <div class="dashboard-actions">
                        <a href="manage_timetable.php" class="dashboard-action-btn">
                            <span class="dashboard-action-icon"><i class="fa-solid fa-calendar-alt"></i></span>
                            Manage Timetable
                        </a>
                        <a href="manage_faculty.php" class="dashboard-action-btn">
                            <span class="dashboard-action-icon"><i class="fa-solid fa-chalkboard-teacher"></i></span>
                            Faculty
                        </a>
                        <a href="manage_students.php" class="dashboard-action-btn">
                            <span class="dashboard-action-icon"><i class="fa-solid fa-users"></i></span>
                            Students
                        </a>
                        <a href="manage_fees.php" class="dashboard-action-btn">
                            <span class="dashboard-action-icon"><i class="fa-solid fa-indian-rupee-sign"></i></span>
                            Fees
                        </a>
                        <a href="view_attendance.php" class="dashboard-action-btn">
                            <span class="dashboard-action-icon"><i class="fa-solid fa-calendar-days"></i></span>
                            Attendance
                        </a>
                        <a href="view_faculty_work.php" class="dashboard-action-btn">
                            <span class="dashboard-action-icon"><i class="fa-solid fa-clock"></i></span>
                            Faculty Work
                        </a>
                    </div>
                </div>
            </div>
        </section>
    </main>
</body>
</html>