<?php
$required_role = 'faculty_admin';
include '../auth.php';
require_once '../db.php';
$message = '';

// Handle creating a new faculty member
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_faculty'])) {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $dob = $_POST['dob'] ?? null;
    $phone = $_POST['phone'] ?? '';
    
    // Date of birth validation
    $today = new DateTime();
    $min_age_date = $today->modify('-21 years');
    $dob_date = new DateTime($dob);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Invalid email address.';
    } elseif (!preg_match('/^[0-9]{10}$/', $phone)) {
        $message = 'Phone number must be 10 digits.';
    } elseif ($dob && $dob_date > $min_age_date) {
        $message = 'Invalid date of birth.';
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
            // **CHANGE:** Role is hardcoded to 'faculty' and approved is set to 0 (pending approval)
            $stmt = $pdo->prepare('INSERT INTO erp_users (name, email, password, dob, phone, role, approved) VALUES (?, ?, ?, ?, ?, "faculty", 0)');
            $stmt->execute([$name, $email, $hash, $dob, $phone]);
            $message = 'Faculty user created successfully! Awaiting approval from Super Admin.';
        }
    }
}

// Fetch all existing faculty members to display in the list
$search = $_GET['search'] ?? '';
$where = ['role = "faculty"'];
$params = [];
if ($search) {
    $where[] = '(name LIKE ? OR email LIKE ? OR phone LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$where_sql = 'WHERE ' . implode(' AND ', $where);
$stmt = $pdo->prepare("SELECT * FROM erp_users $where_sql ORDER BY created_at DESC");
$stmt->execute($params);
$faculty = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Faculty - Faculty Admin | EV Academy ERP</title>
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
                    <a href="manage_faculty.php" class="nav-link">Manage Faculty</a>
                    <a href="manage_timetable.php" class="nav-link">Manage Timetable</a>
                    <a href="view_timetable.php" class="nav-link">View Timetable</a>
                    <a href="review_logs.php" class="nav-link">Review Work Logs</a>
                    <a href="submit_work_log.php" class="nav-link">Submit My Log</a>
                    <a href="view_submissions.php" class="nav-link">View Submissions</a>
                    <a href="view_work_stats.php" class="nav-link">Work Stats</a>
                    <a href="view_feedback.php" class="nav-link">View Feedback</a>
                    <a href="../logout.php" class="nav-link">Logout</a>
                </nav>
            </div>
        </div>
    </header>
    <main>
        <div class="container">
            <?php if ($message): ?>
                <div class="alert"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <section class="form-section card">
                <h2 style="color:#3a4a6b; margin-bottom: 1.5rem;">Add New Faculty</h2>
                <form method="POST" style="display: grid; gap: 1rem; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));">
                    <div><label style="display: block; margin-bottom: 0.5rem;">Name *</label><input type="text" name="name" class="form-input" required></div>
                    <div><label style="display: block; margin-bottom: 0.5rem;">Email *</label><input type="email" name="email" class="form-input" required pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$"></div>
                    <div><label style="display: block; margin-bottom: 0.5rem;">Password *</label><input type="password" name="password" class="form-input" required minlength="6"></div>
                    <div><label style="display: block; margin-bottom: 0.5rem;">Date of Birth</label><input type="date" name="dob" class="form-input" max="<?php echo date('Y-m-d', strtotime('-21 years')); ?>"></div>
                    <div><label style="display: block; margin-bottom: 0.5rem;">Phone *</label><input type="tel" name="phone" class="form-input" required pattern="[0-9]{10}" maxlength="10" placeholder="10 digit phone number"></div>
                    <div style="grid-column: 1 / -1;"><button type="submit" name="create_faculty" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Add Faculty</button></div>
                </form>
            </section>

            <section class="card">
                <div class="responsive-actions" style="margin-bottom: 1.5rem;">
                    <h2 style="color:#3a4a6b;">Faculty List</h2>
                    <form method="GET">
                        <input type="text" name="search" placeholder="Search..." class="form-input" style="width: 250px;" value="<?php echo htmlspecialchars($search); ?>">
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-search"></i> Search</button>
                    </form>
                </div>
                <div style="overflow-x: auto;">
                    <table class="table">
                        <thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Created</th></tr></thead>
                        <tbody>
                            <?php foreach ($faculty as $faculty_member): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($faculty_member['name']); ?></td>
                                <td><?php echo htmlspecialchars($faculty_member['email']); ?></td>
                                <td><?php echo htmlspecialchars($faculty_member['phone']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($faculty_member['created_at'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </main>
    <script>
        // Phone number validation - only allow digits
        document.querySelector('input[name="phone"]').addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });

        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const phone = document.querySelector('input[name="phone"]').value;
            const email = document.querySelector('input[name="email"]').value;
            const password = document.querySelector('input[name="password"]').value;
            const dob = document.querySelector('input[name="dob"]').value;

            if (phone.length !== 10) {
                alert('Phone number must be exactly 10 digits.');
                e.preventDefault();
                return;
            }

            if (password.length < 6) {
                alert('Password must be at least 6 characters long.');
                e.preventDefault();
                return;
            }

            if (dob) {
                const dobDate = new Date(dob);
                const today = new Date();
                const age = today.getFullYear() - dobDate.getFullYear();
                if (age < 21) {
                    alert('Date of birth must be at least 21 years ago.');
                    e.preventDefault();
                    return;
                }
            }
        });
    </script>
</body>
</html>