<?php
$required_role = 'super_admin';
include '../auth.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin Dashboard - VisionAstraa</title>
    <link rel="stylesheet" href="../../css/vms-styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <header class="header" id="header">
        <div class="container">
            <div class="header-content">
                <a href="../../manpower.html" class="logo">
                    <div class="logo-icon">
                        <span>VA</span>
                    </div>
                    <span class="logo-text">Staffing Solutions</span>
                </a>
                <nav class="nav-desktop">
                    <a href="dashboard.php" class="nav-link active">Dashboard</a>
                    <a href="manage_users.php" class="nav-link">Manage Users</a>
                    <a href="manage_admins.php" class="nav-link">Manage Admins</a>
                    <a href="../logout.php" class="nav-link">Logout</a>
                </nav>
            </div>
        </div>
    </header>
    <main>
        <section class="hero">
            <div class="container">
                <div class="hero-content" style="max-width: 600px; margin: 0 auto;">
                    <h1 class="hero-title" style="text-align:center;">Welcome, Super Admin!</h1>
                    <div style="text-align:center; margin-top:2rem;">
                        <a href="manage_users.php" class="btn btn-primary" style="margin:0 1rem; padding: 0.3rem 1rem;">Manage Users</a>
                        <a href="manage_admins.php" class="btn btn-primary" style="margin:0 1rem; padding: 0.3rem 1rem;">Manage Admins</a>
                        <a href="../logout.php" class="btn btn-primary" style="margin:0 1rem; padding: 0.3rem 1rem;">Logout</a>
                    </div>
                </div>
            </div>
        </section>
    </main>
</body>
</html> 