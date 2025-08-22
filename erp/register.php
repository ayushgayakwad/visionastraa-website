<?php
require_once 'db.php';
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_student'])) {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $dob = $_POST['dob'] ?? null;
    $phone = $_POST['phone'] ?? '';
    $college_name = $_POST['college_name'] ?? '';
    $batch = '25B01';
    $location = 'Bangalore';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Invalid email address.';
    } elseif (strlen($password) < 6) {
        $message = 'Password must be at least 6 characters.';
    } elseif (empty($name) || empty($phone)) {
        $message = 'Name and phone are required.';
    } else {
        $stmt = $pdo->prepare('SELECT id FROM erp_users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $message = 'Email already exists.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO erp_users (name, email, password, dob, phone, college_name, role, approved, batch, location) VALUES (?, ?, ?, ?, ?, ?, "student", 0, ?, ?)');
            $stmt->execute([$name, $email, $hash, $dob, $phone, $college_name, $batch, $location]);
            $message = 'Registration successful! Awaiting approval by super admin.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration | EV Academy ERP</title>
    <link rel="stylesheet" href="erp-theme.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
    <header class="header" id="header">
        <div class="container">
            <div class="header-content" style="display:flex;align-items:center;justify-content:space-between;">
                <a href="index.php" class="logo" style="display:flex;align-items:center;gap:0.5em;">
                    <div class="logo-icon"><span>VA</span></div>
                    <span class="logo-text">EV Academy ERP</span>
                </a>
            </div>
        </div>
    </header>
    <main>
        <div class="container">
            <section class="form-section card" style="max-width:600px;margin:2em auto;">
                <h2 style="color:#3a4a6b; margin-bottom: 1.5rem; text-align:center;">Student Registration</h2>
                <?php if ($message): ?>
                    <div class="alert" style="margin-bottom:1em;text-align:center;"> <?php echo htmlspecialchars($message); ?> </div>
                <?php endif; ?>
                <form method="POST" style="display: grid; gap: 1rem; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));">
                    <div>
                        <label for="name" style="display: block; margin-bottom: 0.5rem; color: #3a4a6b; font-weight: 500;">Name *</label>
                        <input type="text" id="name" name="name" class="form-input" required>
                    </div>
                    <div>
                        <label for="email" style="display: block; margin-bottom: 0.5rem; color: #3a4a6b; font-weight: 500;">Email *</label>
                        <input type="email" id="email" name="email" class="form-input" required>
                    </div>
                    <div>
                        <label for="password" style="display: block; margin-bottom: 0.5rem; color: #3a4a6b; font-weight: 500;">Password *</label>
                        <input type="password" id="password" name="password" class="form-input" required>
                    </div>
                    <div>
                        <label for="dob" style="display: block; margin-bottom: 0.5rem; color: #3a4a6b; font-weight: 500;">Date of Birth</label>
                        <input type="date" id="dob" name="dob" class="form-input">
                    </div>
                    <div>
                        <label for="phone" style="display: block; margin-bottom: 0.5rem; color: #3a4a6b; font-weight: 500;">Phone *</label>
                        <input type="tel" id="phone" name="phone" class="form-input" required>
                    </div>
                    <div>
                        <label for="college_name" style="display: block; margin-bottom: 0.5rem; color: #3a4a6b; font-weight: 500;">College Name</label>
                        <input type="text" id="college_name" name="college_name" class="form-input">
                    </div>
                    <div style="grid-column: 1 / -1; text-align:center;">
                        <button type="submit" name="register_student" class="btn btn-primary">
                            <i class="fa-solid fa-user-plus"></i> Register
                        </button>
                    </div>
                </form>
                <p style="margin-top:2em;text-align:center;color:#e67e22;">Note: Your registration will be reviewed by the super admin. You can login only after approval.<br>If you already have an account, <a href="index.php" style="color:#3a4a6b;text-decoration:underline;">Login here</a>.</p>
            </section>
        </div>
    </main>
</body>
</html>
