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
    <link rel="stylesheet" href="erp-theme.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        .password-wrapper { position: relative; display:flex; align-items:center; }
        .show-password-toggle {
            position: absolute; right: 0.8rem; top: 50%; transform: translateY(-50%);
            background: none; border: none; cursor: pointer; font-size: 1rem; color: #6b7a99;
        }
    </style>
</head>
<body>
    <header class="header" id="header">
        <div class="container">
            <div class="header-content" style="display:flex;align-items:center;justify-content:space-between;">
                <a href="../ev-academy.html" class="logo" style="display:flex;align-items:center;gap:0.5em;">
                    <div class="logo-icon"><span>VA</span></div>
                    <span class="logo-text">EV Academy ERP</span>
                </a>
                <nav class="nav-desktop">
                    <a href="../ev-academy.html" class="nav-link">Home</a>
                    <a href="index.php" class="nav-link active">Login</a>
                </nav>
            </div>
        </div>
    </header>
    <main>
        <section class="hero">
            <div class="container">
                <section class="card" style="max-width: 480px; margin: 0 auto;">
                    <h1 class="hero-title" style="text-align:center; color:#3a4a6b;">Login to EV Academy ERP</h1>
                    <?php if ($error): ?>
                        <div class="alert"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
                    <form method="post" class="login-form" style="display: grid; gap: 1rem;">
                        <div>
                            <label for="email" style="display:block; margin-bottom:0.5rem; color:#3a4a6b; font-weight:500;">Email</label>
                            <input type="email" id="email" name="email" required class="form-input" placeholder="you@example.com">
                        </div>
                        <div>
                            <label for="password" style="display:block; margin-bottom:0.5rem; color:#3a4a6b; font-weight:500;">Password</label>
                            <div class="password-wrapper">
                                <input type="password" name="password" id="password" placeholder="Your password" required class="form-input" style="width:100%;">
                                <button type="button" class="show-password-toggle" onclick="togglePassword()" tabindex="-1" aria-label="Show password">
                                    <i id="toggleIcon" class="fa-regular fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-right-to-bracket"></i> Login</button>
                            <p style="margin-top:2em;text-align:center;color:#e67e22;">Don't have an account? <a href="register.php" style="color:#3a4a6b;text-decoration:underline;">Register here</a>.</p>
                    </form>
                </section>
            </div>
        </section>
    </main>
    <script>
        function togglePassword() {
            var pwd = document.getElementById('password');
            var icon = document.getElementById('toggleIcon');
            if (pwd.type === 'password') {
                pwd.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                pwd.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>