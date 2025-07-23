<?php
session_start();
if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    header('Location: dashboard.php');
    exit;
}
require_once 'db.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    if ($user) {
        if (!password_verify($password, $user['password'])) {
            $error = 'Invalid email or password.';
        } elseif (!$user['approved']) {
            $error = 'Account pending approval.';
        } else {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            header('Location: dashboard.php');
            exit;
        }
    } else {
        $error = 'Invalid email or password.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manpower Login - VisionAstraa</title>
    <link rel="stylesheet" href="../css/vms-styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .show-password-toggle {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1.1rem;
            color: #888;
        }
        .password-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }
    </style>
</head>
<body>
    <header class="header" id="header">
        <div class="container">
            <div class="header-content">
                <a href="../staffing-solution.html" class="logo">
                    <div class="logo-icon">
                        <span>VA</span>
                    </div>
                    <span class="logo-text">Staffing Solutions</span>
                </a>
                <nav class="nav-desktop">
                    <a href="../staffing-solution.html" class="nav-link">Home</a>
                    <a href="../manpower-services.html" class="nav-link">Get in Touch</a>
                    <a href="index.php" class="btn btn-primary nav-get-started active">Get Started</a>
                </nav>
                <button class="mobile-menu-btn" id="mobileMenuBtn">
                    <span class="hamburger"></span>
                    <span class="hamburger"></span>
                    <span class="hamburger"></span>
                </button>
            </div>
            <nav class="nav-mobile" id="mobileNav">
                <a href="../staffing-solution.html" class="nav-link-mobile">Home</a>
                <a href="../manpower-services.html" class="nav-link-mobile">Get in Touch</a>
                <a href="index.php" class="btn btn-primary nav-get-started-mobile active">Get Started</a>
            </nav>
        </div>
    </header>
    <main>
        <section class="hero">
            <div class="container">
                <div class="hero-content" style="max-width: 400px; margin: 0 auto;">
                    <h1 class="hero-title" style="text-align:center;">Login to Your Account</h1>
                    <?php if ($error): ?>
                        <div class="alert alert-danger" style="color: red; text-align:center; margin-bottom: 1rem;">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>
                    <form method="post" class="login-form" style="display: flex; flex-direction: column; gap: 1rem;">
                        <input type="email" name="email" placeholder="Email" required class="form-input">
                        <div class="password-wrapper">
                            <input type="password" name="password" id="password" placeholder="Password" required class="form-input" style="width:100%;">
                            <button type="button" class="show-password-toggle" onclick="togglePassword()" tabindex="-1">
                                <span id="toggle-icon">Show</span>
                            </button>
                        </div>
                        <button type="submit" class="btn btn-primary btn-lg">Login</button>
                    </form>
                </div>
            </div>
        </section>
    </main>
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section footer-main">
                    <div class="footer-logo">
                        <div class="logo-icon">
                            <span>VA</span>
                        </div>
                        <span class="logo-text">Staffing Solutions</span>
                    </div>
                    <p class="footer-description">
                        Your strategic talent partner for exceptional recruitment, background verification, and payroll compliance solutions.
                    </p>
                    <p class="footer-tagline">
                        Connecting talent with opportunity, building tomorrow's workforce today.
                    </p>
                </div>
                <div class="footer-section">
                    <h3 class="footer-title">Quick Links</h3>
                    <ul class="footer-links">
                        <li><a href="../staffing-solution.html">Home</a></li>
                        <li><a href="../manpower-services.html">Get in Touch</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h3 class="footer-title">Services</h3>
                    <ul class="footer-links">
                        <li>Hiring Solutions</li>
                        <li>Background Verification</li>
                        <li>Payroll & Compliance</li>
                        <li>Workforce Management</li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p class="footer-copyright">
                    © <span id="currentYear"></span> VisionAstraa. All rights reserved.
                </p>
            </div>
            <br>
        </div>
    </footer>
    <script src="../js/vms-script.js"></script>
    <script>
        function togglePassword() {
            var pwd = document.getElementById('password');
            var icon = document.getElementById('toggle-icon');
            if (pwd.type === 'password') {
                pwd.type = 'text';
                icon.textContent = 'Hide';
            } else {
                pwd.type = 'password';
                icon.textContent = 'Show';
            }
        }
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