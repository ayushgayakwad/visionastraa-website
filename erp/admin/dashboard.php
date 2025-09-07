<?php
$required_role = 'admin';
include '../auth.php';
require_once '../db.php';

// Get user's name
$stmt = $pdo->prepare("SELECT name FROM erp_users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
$user_name = $user ? htmlspecialchars($user['name']) : 'Admin';

// Check for role update notification
$role_updated = isset($_GET['role_updated']) && $_GET['role_updated'] == '1';

// Dynamic greeting
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
    <title>Admin Dashboard - EV Academy ERP</title>
    <link rel="stylesheet" href="../erp-theme.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
    <header class="header" id="header">
        <div class="container">
            <div class="header-content" style="display:flex;align-items:center;justify-content:space-between;">
                <a href="dashboard.php" class="logo" style="display:flex;align-items:center;gap:0.5em;">
                    <img src="../logo.png" alt="Logo" style="height: 80px;">
                </a>
                <button class="mobile-menu-btn" onclick="document.body.classList.toggle('nav-open')"><i class="fa-solid fa-bars"></i></button>
                <nav class="nav-desktop">
                    <a href="dashboard.php" class="nav-link">Dashboard</a>
                    <a href="manage_students.php" class="nav-link">Students</a>
                    <a href="view_timetable.php" class="nav-link">View Timetable</a>
                    <a href="mark_attendance.php" class="nav-link">Mark Attendance</a>
                    <a href="view_attendance.php" class="nav-link">View Attendance</a>
                    <a href="upload_documents.php" class="nav-link">Upload Documents</a>
                    <a href="fee_payment.php" class="nav-link">Fee Payment</a>
                    <a href="give_feedback.php" class="nav-link">Give Feedback</a>
                    <a href="submit_assignment.php" class="nav-link">Submit Assignment</a>
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
                    <?php if ($role_updated): ?>
                        <div class="alert alert-success" style="background: #d4edda; color: #155724; border: 1px solid #c3e6cb; padding: 12px; border-radius: 4px; margin: 1rem 0; text-align: center;">
                            <i class="fa-solid fa-check-circle"></i> Your role has been updated! You now have Admin access.
                        </div>
                    <?php endif; ?>
                    <div class="dashboard-actions">
                        <a href="manage_students.php" class="dashboard-action-btn">
                            <span class="dashboard-action-icon"><i class="fa-solid fa-users"></i></span>
                            Students
                        </a>
                        <a href="view_timetable.php" class="dashboard-action-btn">
                            <span class="dashboard-action-icon"><i class="fa-solid fa-calendar-day"></i></span>
                            View Timetable
                        </a>
                        <a href="mark_attendance.php" class="dashboard-action-btn">
                            <span class="dashboard-action-icon"><i class="fa-solid fa-calendar-check"></i></span>
                            Mark Attendance
                        </a>
                        <a href="view_attendance.php" class="dashboard-action-btn">
                            <span class="dashboard-action-icon"><i class="fa-solid fa-calendar-days"></i></span>
                            View Attendance
                        </a>
                        <a href="upload_documents.php" class="dashboard-action-btn">
                            <span class="dashboard-action-icon"><i class="fa-solid fa-file-arrow-up"></i></span>
                            Upload Documents
                        </a>
                        <a href="fee_payment.php" class="dashboard-action-btn">
                            <span class="dashboard-action-icon"><i class="fa-solid fa-indian-rupee-sign"></i></span>
                            Fee Payment
                        </a>
                        <a href="give_feedback.php" class="dashboard-action-btn">
                            <span class="dashboard-action-icon"><i class="fa-solid fa-comments"></i></span>
                            Give Feedback
                        </a>
                         <a href="submit_assignment.php" class="dashboard-action-btn">
                            <span class="dashboard-action-icon"><i class="fa-solid fa-tasks"></i></span>
                            Submit Assignment
                        </a>
                    </div>
                </div>
            </div>
        </section>
    </main>
    <script>
        // Auto-hide role update notification after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const notification = document.querySelector('.alert-success');
            if (notification) {
                setTimeout(function() {
                    notification.style.transition = 'opacity 0.5s ease';
                    notification.style.opacity = '0';
                    setTimeout(function() {
                        notification.remove();
                    }, 500);
                }, 5000);
            }
        });
    </script>
</body>
</html>