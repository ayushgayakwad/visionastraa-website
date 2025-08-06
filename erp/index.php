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
    $stmt = $pdo->prepare('SELECT * FROM erp_users WHERE email = ?');
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
    <title>EV Academy ERP Login - VisionAstraa</title>
    <link rel="stylesheet" href="../css/vms-styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { background: #e6f4ea; }
        .header { background: #1b5e20; }
        .logo-icon span { background: #43a047; color: #fff; }
        .logo-text { color: #1b5e20; }
        .btn-primary, .tab-btn.active { background: #43a047; color: #fff; }
        .btn-primary:hover, .tab-btn.active:hover { background: #388e3c; }
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
                <a href="../ev-academy.html" class="logo">
                    <div class="logo-icon">
                        <span>VA</span>
                    </div>
                    <span class="logo-text">EV Academy ERP</span>
                </a>
                <nav class="nav-desktop">
                    <a href="../ev-academy.html" class="nav-link">Home</a>
                    <a href="index.php" class="btn btn-primary nav-get-started active">Login</a>
                </nav>
                <button class="mobile-menu-btn" id="mobileMenuBtn">
                    <span class="hamburger"></span>
                    <span class="hamburger"></span>
                    <span class="hamburger"></span>
                </button>
            </div>
            <nav class="nav-mobile" id="mobileNav">
                <a href="../ev-academy.html" class="nav-link-mobile">Home</a>
                <a href="index.php" class="btn btn-primary nav-get-started-mobile active">Login</a>
            </nav>
        </div>
    </header>
    <main>
        <section class="hero">
            <div class="container">
                <div class="hero-content" style="max-width: 400px; margin: 0 auto;">
                    <h1 class="hero-title" style="text-align:center; color:#1b5e20;">Login to EV Academy ERP</h1>
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
                                <span id="toggleIcon">👁️</span>
                            </button>
                        </div>
                        <button type="submit" class="btn btn-primary">Login</button>
                    </form>
                </div>
            </div>
        </section>
    </main>
    <script>
        function togglePassword() {
            var pwd = document.getElementById('password');
            var icon = document.getElementById('toggleIcon');
            if (pwd.type === 'password') {
                pwd.type = 'text';
                icon.textContent = '🙈';
            } else {
                pwd.type = 'password';
                icon.textContent = '👁️';
            }
        }
    </script>
</body>
</html>