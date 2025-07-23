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
    <style>
        .dashboard-actions {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 1.5rem;
            margin-top: 2.5rem;
        }
        .dashboard-action-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-width: 160px;
            min-height: 120px;
            padding: 1.2rem 1.5rem;
            background: #2b6cb0;
            color: #fff;
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
            transition: background 0.2s, box-shadow 0.2s, transform 0.1s;
            text-decoration: none;
            margin-bottom: 0.5rem;
        }
        .dashboard-action-btn:hover {
            background: #205080;
            box-shadow: 0 4px 16px rgba(0,0,0,0.13);
            transform: translateY(-2px) scale(1.03);
        }
        .dashboard-action-icon {
            font-size: 2.2rem;
            margin-bottom: 0.7rem;
        }
        @media (max-width: 600px) {
            .dashboard-actions {
                flex-direction: column;
                gap: 1rem;
            }
            .dashboard-action-btn {
                min-width: 100%;
                width: 100%;
                font-size: 1rem;
                padding: 1rem 0.5rem;
            }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
    <header class="header" id="header">
        <div class="container">
            <div class="header-content">
                <a href="../../staffing-solution.html" class="logo">
                    <div class="logo-icon">
                        <span>VA</span>
                    </div>
                    <span class="logo-text">Staffing Solutions</span>
                </a>
                <nav class="nav-desktop">
                    <a href="dashboard.php" class="nav-link active">Dashboard</a>
                    <a href="manage_users.php" class="nav-link">Manage Users</a>
                    <a href="manage_admins.php" class="nav-link">Manage Admins</a>
                    <a href="manage_companies.php" class="nav-link">Manage Companies</a>
                    <a href="work_hours.php" class="nav-link">Work Hours</a>
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
                    <div class="dashboard-actions">
                        <a href="manage_users.php" class="dashboard-action-btn">
                            <span class="dashboard-action-icon"><i class="fa-solid fa-users"></i></span>
                            Manage Users
                        </a>
                        <a href="manage_admins.php" class="dashboard-action-btn">
                            <span class="dashboard-action-icon"><i class="fa-solid fa-user-tie"></i></span>
                            Manage Admins
                        </a>
                        <a href="manage_companies.php" class="dashboard-action-btn">
                            <span class="dashboard-action-icon"><i class="fa-solid fa-building"></i></span>
                            Manage Companies
                        </a>
                        <a href="work_hours.php" class="dashboard-action-btn">
                            <span class="dashboard-action-icon"><i class="fa-solid fa-clock"></i></span>
                            Work Hours
                        </a>
                        <a href="../logout.php" class="dashboard-action-btn" style="background:#e53e3e;">
                            <span class="dashboard-action-icon"><i class="fa-solid fa-right-from-bracket"></i></span>
                            Logout
                        </a>
                    </div>
                </div>
            </div>
        </section>
    </main>
    <script>
        window.addEventListener('scroll', function() {
            var header = document.getElementById('header');
            if (window.scrollY > 10) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });
    </script>
</body>
</html> 