<?php
$required_role = 'super_admin';
include '../auth.php';
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
                <nav class="nav-desktop">
                    <a href="dashboard.php" class="nav-link active">Dashboard</a>
                    <a href="manage_admins.php" class="nav-link">Admins</a>
                    <a href="manage_faculty.php" class="nav-link">Faculty</a>
                    <a href="manage_students.php" class="nav-link">Students</a>
                    <a href="manage_classes.php" class="nav-link">Classes</a>
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
                    <h1 class="hero-title" style="text-align:center; color:#3a4a6b;">Welcome, Super Admin!</h1>
                    <div class="dashboard-actions">
                        <a href="manage_admins.php" class="dashboard-action-btn">
                            <span class="dashboard-action-icon"><i class="fa-solid fa-user-tie"></i></span>
                            Admins
                        </a>
                        <a href="manage_faculty.php" class="dashboard-action-btn">
                            <span class="dashboard-action-icon"><i class="fa-solid fa-chalkboard-teacher"></i></span>
                            Faculty
                        </a>
                        <a href="manage_students.php" class="dashboard-action-btn">
                            <span class="dashboard-action-icon"><i class="fa-solid fa-users"></i></span>
                            Students
                        </a>
                        <a href="manage_classes.php" class="dashboard-action-btn">
                            <span class="dashboard-action-icon"><i class="fa-solid fa-book"></i></span>
                            Classes
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