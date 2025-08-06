<?php
$required_role = 'admin';
include '../auth.php';
require_once '../db.php';
$message = '';

// Handle Add Student
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_student'])) {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $dob = $_POST['dob'] ?? null;
    $phone = $_POST['phone'] ?? '';
    $college_name = $_POST['college_name'] ?? '';
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
            $stmt = $pdo->prepare('INSERT INTO erp_users (name, email, password, dob, phone, college_name, role, approved) VALUES (?, ?, ?, ?, ?, ?, "student", 1)');
            $stmt->execute([$name, $email, $hash, $dob, $phone, $college_name]);
            $message = 'Student created successfully!';
        }
    }
}
// Handle Edit Student
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_student_id'])) {
    $edit_id = (int)$_POST['edit_student_id'];
    $name = $_POST['edit_name'] ?? '';
    $email = $_POST['edit_email'] ?? '';
    $dob = $_POST['edit_dob'] ?? null;
    $phone = $_POST['edit_phone'] ?? '';
    $college_name = $_POST['edit_college_name'] ?? '';
    $update_sql = 'UPDATE erp_users SET name=?, email=?, dob=?, phone=?, college_name=? WHERE id=? AND role="student"';
    $stmt = $pdo->prepare($update_sql);
    $stmt->execute([$name, $email, $dob, $phone, $college_name, $edit_id]);
    $message = 'Student details updated!';
}
// List Students
$search = $_GET['search'] ?? '';
$where = ['role = "student"'];
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
                    <div class="logo-icon"><span>VA</span></div>
                    <span class="logo-text">EV Academy ERP</span>
                </a>
                <nav class="nav-desktop">
                    <a href="dashboard.php" class="nav-link">Dashboard</a>
                    <a href="manage_students.php" class="nav-link active">Students</a>
                    <a href="mark_attendance.php" class="nav-link">Mark Attendance</a>
                    <a href="view_attendance.php" class="nav-link">View Attendance</a>
                    <a href="../logout.php" class="nav-link">Logout</a>
                </nav>
            </div>
        </div>
    </header>
    <main>
        <div class="container">
            <h1 style="text-align:center; color:#1b5e20; margin:2rem 0;">Manage Students</h1>
            <?php if ($message): ?>
                <div class="alert alert-success" style="color: #1b5e20; text-align:center; margin-bottom: 1rem;">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
            <section class="form-section card">
                <h2 style="color:#3a4a6b;">Add Student</h2>
                <form method="post" style="display: flex; flex-wrap: wrap; gap: 1rem;">
                    <input type="text" name="name" placeholder="Name" required class="form-input" style="flex:1;">
                    <input type="email" name="email" placeholder="Email" required class="form-input" style="flex:1;">
                    <input type="password" name="password" placeholder="Password" required class="form-input" style="flex:1;">
                    <input type="date" name="dob" placeholder="Date of Birth" class="form-input" style="flex:1;">
                    <input type="text" name="phone" placeholder="Phone" required class="form-input" style="flex:1;">
                    <input type="text" name="college_name" placeholder="College Name" class="form-input" style="flex:1;">
                    <button type="submit" name="create_student" class="btn btn-primary" style="flex:1;">Add Student</button>
                </form>
            </section>
            <section class="card">
                <h2 style="color:#3a4a6b;">Student List</h2>
                <form method="get" style="margin-bottom:1rem;">
                    <input type="text" name="search" placeholder="Search by name, email, phone, college" value="<?php echo htmlspecialchars($search); ?>" class="form-input" style="width:300px;">
                    <button type="submit" class="btn btn-primary">Search</button>
                </form>
                <table class="table">
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>DOB</th>
                        <th>Phone</th>
                        <th>College</th>
                        <th>Actions</th>
                    </tr>
                    <?php foreach ($students as $student): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($student['name']); ?></td>
                            <td><?php echo htmlspecialchars($student['email']); ?></td>
                            <td><?php echo htmlspecialchars($student['dob']); ?></td>
                            <td><?php echo htmlspecialchars($student['phone']); ?></td>
                            <td><?php echo htmlspecialchars($student['college_name']); ?></td>
                            <td>
                                <form method="post" style="display:inline;">
                                    <input type="hidden" name="edit_student_id" value="<?php echo $student['id']; ?>">
                                    <input type="text" name="edit_name" value="<?php echo htmlspecialchars($student['name']); ?>" required style="width:100px;">
                                    <input type="email" name="edit_email" value="<?php echo htmlspecialchars($student['email']); ?>" required style="width:150px;">
                                    <input type="date" name="edit_dob" value="<?php echo htmlspecialchars($student['dob']); ?>" style="width:120px;">
                                    <input type="text" name="edit_phone" value="<?php echo htmlspecialchars($student['phone']); ?>" required style="width:100px;">
                                    <input type="text" name="edit_college_name" value="<?php echo htmlspecialchars($student['college_name']); ?>" style="width:120px;">
                                    <button type="submit" class="edit-btn">Save</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </section>
        </div>
    </main>
</body>
</html>