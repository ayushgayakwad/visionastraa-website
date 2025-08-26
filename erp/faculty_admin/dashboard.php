<?php
$required_role = 'faculty_admin';
include '../auth.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Faculty Admin Dashboard - EV Academy ERP</title>
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
                    <a href="review_logs.php" class="nav-link">Review Work Logs</a>
                    <a href="submit_work_log.php" class="nav-link">Submit My Log</a>
                    <a href="../logout.php" class="nav-link">Logout</a>
                </nav>
            </div>
        </div>
    </header>
    <main>
        <section class="hero">
            <div class="container">
                <div class="hero-content" style="max-width: 600px; margin: 0 auto;">
                    <h1 class="hero-title" style="text-align:center; color:#3a4a6b;">Welcome, Faculty Admin!</h1>
                    <div class="dashboard-actions">
                        <a href="manage_timetable.php" class="dashboard-action-btn">
                            <span class="dashboard-action-icon"><i class="fa-solid fa-calendar-alt"></i></span>
                            Manage Timetable
                        </a>
                        <a href="review_logs.php" class="dashboard-action-btn">
                            <span class="dashboard-action-icon"><i class="fa-solid fa-check-to-slot"></i></span>
                            Review Faculty Work Logs
                        </a>
                        <a href="submit_work_log.php" class="dashboard-action-btn">
                            <span class="dashboard-action-icon"><i class="fa-solid fa-clock"></i></span>
                            Submit My Work Log
                        </a>
                    </div>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
