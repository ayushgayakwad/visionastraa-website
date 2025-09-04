<?php
$required_role = 'admin';
include '../auth.php';
require_once '../db.php';
$message = '';
$admin_id = $_SESSION['user_id'];

// **NEW:** Fetch the logged-in admin's details to get their batch and location
$stmt_admin = $pdo->prepare("SELECT batch, location FROM erp_users WHERE id = ?");
$stmt_admin->execute([$admin_id]);
$admin_details = $stmt_admin->fetch();
$admin_batch = $admin_details['batch'] ?? '';
$admin_location = $admin_details['location'] ?? '';


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_student'])) {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $dob = $_POST['dob'] ?? null;
    $phone = $_POST['phone'] ?? '';
    $college_name = $_POST['college_name'] ?? '';
    // **CHANGE:** Batch and location are no longer taken from the form
    // $batch = $_POST['batch'] ?? '';
    // $location = $_POST['location'] ?? '';

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
            // **CHANGE:** New students are created with approved = 0 and the admin's batch/location
            $stmt = $pdo->prepare('INSERT INTO erp_users (name, email, password, dob, phone, college_name, role, approved, batch, location) VALUES (?, ?, ?, ?, ?, ?, "student", 0, ?, ?)');
            $stmt->execute([$name, $email, $hash, $dob, $phone, $college_name, $admin_batch, $admin_location]);
            $message = 'Student created successfully! Awaiting approval from Super Admin.';
        }
    }
}

// Admins can only see approved students now
$search = $_GET['search'] ?? '';
$where = ['role = "student"', 'approved = 1'];
$params = [];
if ($search) {
    $where[] = '(name LIKE ? OR email LIKE ? OR phone LIKE ? OR college_name LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$where_sql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';
$stmt = $pdo->prepare("SELECT * FROM erp_users $where_sql ORDER BY created_at DESC");
$stmt->execute($params);
$students = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students - Admin | EV Academy ERP</title>
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
        <div class="container">
            <h1 class="hero-title" style="text-align:center; color:#3a4a6b; margin:2rem 0;">Manage Students</h1>
            <?php if ($message): ?>
                <div class="alert"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>

            <section class="form-section card">
                <h2 style="color:#3a4a6b; margin-bottom: 1.5rem;">Add New Student</h2>
                <form method="post" style="display: grid; gap: 1rem; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));">
                    <div><label style="display:block; margin-bottom:0.5rem; color:#3a4a6b; font-weight:500;">Name *</label><input type="text" name="name" required class="form-input"></div>
                    <div><label style="display:block; margin-bottom:0.5rem; color:#3a4a6b; font-weight:500;">Email *</label><input type="email" name="email" required class="form-input" pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$"></div>
                    <div><label style="display:block; margin-bottom:0.5rem; color:#3a4a6b; font-weight:500;">Password *</label><input type="password" name="password" required class="form-input" minlength="6"></div>
                    <div><label style="display:block; margin-bottom:0.5rem; color:#3a4a6b; font-weight:500;">Date of Birth</label><input type="date" name="dob" class="form-input" max="<?php echo date('Y-m-d', strtotime('-21 years')); ?>"></div>
                    <div><label style="display:block; margin-bottom:0.5rem; color:#3a4a6b; font-weight:500;">Phone *</label><input type="tel" name="phone" required class="form-input" pattern="[0-9]{10}" maxlength="10" placeholder="10 digit phone number"></div>
                    <div><label style="display:block; margin-bottom:0.5rem; color:#3a4a6b; font-weight:500;">College Name</label><input type="text" name="college_name" class="form-input"></div>
                    
                    <div style="grid-column: 1 / -1;"><button type="submit" name="create_student" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Add Student</button></div>
                </form>
            </section>

            <section class="card">
                <div class="responsive-actions" style="margin-bottom: 1.5rem;">
                <h2 style="color:#3a4a6b;">Student List</h2>
                    <form method="get">
                        <input type="text" name="search" placeholder="Search students..." value="<?php echo htmlspecialchars($search); ?>" class="form-input" style="width:250px;">
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-search"></i> Search</button>
                    </form>
                </div>
                <div style="overflow-x:auto;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>College</th>
                                <th>Batch</th>
                                <th>Location</th>
                                <th>Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $student): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($student['name']); ?></td>
                                <td><?php echo htmlspecialchars($student['email']); ?></td>
                                <td><?php echo htmlspecialchars($student['phone']); ?></td>
                                <td><?php echo htmlspecialchars($student['college_name']); ?></td>
                                <td><?php echo htmlspecialchars($student['batch']); ?></td>
                                <td><?php echo htmlspecialchars($student['location']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($student['created_at'])); ?></td>
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