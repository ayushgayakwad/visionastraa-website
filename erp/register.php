<?php
require_once 'db.php';
$message = '';
$form_type = $_POST['form_type'] ?? 'student';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $dob = $_POST['dob'] ?? null;
    $phone = $_POST['phone'] ?? '';

    if ($password !== $confirm_password) {
        $message = 'Passwords do not match.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
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
            if ($form_type === 'student') {
                $college_name = $_POST['college_name'] ?? '';
                $batch = '25B01';
                $location = 'Bangalore';
                $stmt = $pdo->prepare('INSERT INTO erp_users (name, email, password, dob, phone, college_name, role, approved, batch, location) VALUES (?, ?, ?, ?, ?, ?, "student", 0, ?, ?)');
                $stmt->execute([$name, $email, $hash, $dob, $phone, $college_name, $batch, $location]);
                $message = 'Registration successful! Awaiting approval by super admin.';
            } elseif ($form_type === 'faculty') {
                $stmt = $pdo->prepare('INSERT INTO erp_users (name, email, password, dob, phone, role, approved) VALUES (?, ?, ?, ?, ?, "faculty", 0)');
                $stmt->execute([$name, $email, $hash, $dob, $phone]);
                $message = 'Faculty registration successful! Awaiting approval by super admin.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration | EV Academy ERP</title>
    <link rel="stylesheet" href="erp-theme.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        .tab-btns { display: flex; gap: 1em; margin-bottom: 2em; flex-wrap: wrap; justify-content: center; }
        .tab-btn { padding: 0.7em 2em; border-radius: 8px; background: #e3eafc; color: #3a4a6b; font-weight: 500; border: none; cursor: pointer; text-align:center; text-decoration: none; }
        .tab-btn.active { background: #3a4a6b; color: #fff; }
        .form-container { display: none; }
        .form-container.active { display: block; }
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
                <h2 style="color:#3a4a6b; margin-bottom: 1.5rem; text-align:center;">Registration</h2>
                <?php if ($message): ?>
                    <div class="alert" style="margin-bottom:1em;text-align:center;"> <?php echo htmlspecialchars($message); ?> </div>
                <?php endif; ?>

                <div class="tab-btns">
                    <button class="tab-btn active" onclick="showForm('student')">Register as Student</button>
                    <button class="tab-btn" onclick="showForm('faculty')">Register as Faculty</button>
                </div>

                <div id="student-form" class="form-container active">
                    <form method="POST" style="display: grid; gap: 1rem; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));">
                        <input type="hidden" name="form_type" value="student">
                        <div><label style="display: block; margin-bottom: 0.5rem;">Name *</label><input type="text" name="name" class="form-input" required></div>
                        <div><label style="display: block; margin-bottom: 0.5rem;">Email *</label><input type="email" name="email" class="form-input" required></div>
                        <div>
                            <label style="display: block; margin-bottom: 0.5rem;">Password *</label>
                            <div class="password-wrapper">
                                <input type="password" id="student_password" name="password" class="form-input" required>
                                <button type="button" class="show-password-toggle" onclick="togglePassword('student_password')"><i class="fa-regular fa-eye"></i></button>
                            </div>
                        </div>
                        <div>
                            <label style="display: block; margin-bottom: 0.5rem;">Confirm Password *</label>
                            <div class="password-wrapper">
                                <input type="password" id="student_confirm_password" name="confirm_password" class="form-input" required>
                                <button type="button" class="show-password-toggle" onclick="togglePassword('student_confirm_password')"><i class="fa-regular fa-eye"></i></button>
                            </div>
                        </div>
                        <div><label style="display: block; margin-bottom: 0.5rem;">Date of Birth</label><input type="date" name="dob" class="form-input"></div>
                        <div><label style="display: block; margin-bottom: 0.5rem;">Phone *</label><input type="tel" name="phone" class="form-input" required></div>
                        <div><label style="display: block; margin-bottom: 0.5rem;">College Name</label><input type="text" name="college_name" class="form-input"></div>
                        <div style="grid-column: 1 / -1; text-align:center;"><button type="submit" name="register_student" class="btn btn-primary"><i class="fa-solid fa-user-plus"></i> Register as Student</button></div>
                    </form>
                </div>

                <div id="faculty-form" class="form-container">
                    <form method="POST" style="display: grid; gap: 1rem; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));">
                        <input type="hidden" name="form_type" value="faculty">
                        <div><label style="display: block; margin-bottom: 0.5rem;">Name *</label><input type="text" name="name" class="form-input" required></div>
                        <div><label style="display: block; margin-bottom: 0.5rem;">Email *</label><input type="email" name="email" class="form-input" required></div>
                         <div>
                            <label style="display: block; margin-bottom: 0.5rem;">Password *</label>
                            <div class="password-wrapper">
                                <input type="password" id="faculty_password" name="password" class="form-input" required>
                                <button type="button" class="show-password-toggle" onclick="togglePassword('faculty_password')"><i class="fa-regular fa-eye"></i></button>
                            </div>
                        </div>
                        <div>
                            <label style="display: block; margin-bottom: 0.5rem;">Confirm Password *</label>
                            <div class="password-wrapper">
                                <input type="password" id="faculty_confirm_password" name="confirm_password" class="form-input" required>
                                <button type="button" class="show-password-toggle" onclick="togglePassword('faculty_confirm_password')"><i class="fa-regular fa-eye"></i></button>
                            </div>
                        </div>
                        <div><label style="display: block; margin-bottom: 0.5rem;">Date of Birth</label><input type="date" name="dob" class="form-input"></div>
                        <div><label style="display: block; margin-bottom: 0.5rem;">Phone *</label><input type="tel" name="phone" class="form-input" required></div>
                        <div style="grid-column: 1 / -1; text-align:center;"><button type="submit" name="register_faculty" class="btn btn-primary"><i class="fa-solid fa-chalkboard-teacher"></i> Register as Faculty</button></div>
                    </form>
                </div>

                <p style="margin-top:2em;text-align:center;color:#e67e22;">Note: Your registration will be reviewed by the super admin. You can login only after approval.<br>If you already have an account, <a href="index.php" style="color:#3a4a6b;text-decoration:underline;">Login here</a>.</p>
            </section>
        </div>
    </main>
    <script>
        function showForm(formName) {
            document.getElementById('student-form').classList.remove('active');
            document.getElementById('faculty-form').classList.remove('active');
            document.querySelector('.tab-btn.active').classList.remove('active');
            
            document.getElementById(formName + '-form').classList.add('active');
            event.currentTarget.classList.add('active');
        }

        function togglePassword(inputId) {
            const passwordInput = document.getElementById(inputId);
            const icon = passwordInput.nextElementSibling.querySelector('i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>
