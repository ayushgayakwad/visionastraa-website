<?php
$required_role = 'admin';
include '../auth.php';
require_once '../db.php';
$message = '';
$tab = $_GET['tab'] ?? 'all';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_student'])) {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $dob = $_POST['dob'] ?? null;
    $phone = $_POST['phone'] ?? '';
    $college_name = $_POST['college_name'] ?? '';
    $batch = $_POST['batch'] ?? '';
    $location = $_POST['location'] ?? '';
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
            $stmt = $pdo->prepare('INSERT INTO erp_users (name, email, password, dob, phone, college_name, role, approved, batch, location) VALUES (?, ?, ?, ?, ?, ?, "student", 1, ?, ?)');
            $stmt->execute([$name, $email, $hash, $dob, $phone, $college_name, $batch, $location]);
            $message = 'Student created successfully!';
        }
    }
}
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

if (isset($_POST['action']) && isset($_POST['student_id'])) {
    $student_id = intval($_POST['student_id']);
    $action = $_POST['action'];
    if ($action === 'approve') {
        $stmt = $pdo->prepare("UPDATE erp_users SET approved = 1 WHERE id = ?");
        $stmt->execute([$student_id]);
        $message = 'Student approved successfully!';
    } elseif ($action === 'reject') {
        $stmt = $pdo->prepare("DELETE FROM erp_users WHERE id = ?");
        $stmt->execute([$student_id]);
        $message = 'Student rejected successfully!';
    }
}

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

if ($tab === 'approve') {
    $where[] = 'approved = 0';
} else {
    $where[] = 'approved = 1';
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
    <style>
        .tab-btns { display: flex; gap: 1em; margin-bottom: 2em; flex-wrap: wrap; }
        .tab-btn { padding: 0.7em 2em; border-radius: 8px; background: #e3eafc; color: #3a4a6b; font-weight: 500; border: none; cursor: pointer; text-align:center; text-decoration: none; }
        .tab-btn.active { background: #3a4a6b; color: #fff; }
    </style>
</head>
<body>
    <header class="header" id="header">
        <div class="container">
            <div class="header-content" style="display:flex;align-items:center;justify-content:space-between;">
                <a href="dashboard.php" class="logo" style="display:flex;align-items:center;gap:0.5em;">
                    <div class="logo-icon"><span>VA</span></div>
                    <span class="logo-text">EV Academy ERP</span>
                </a>
                <button class="mobile-menu-btn" onclick="document.body.classList.toggle('nav-open')"><i class="fa-solid fa-bars"></i></button>
                <nav class="nav-desktop">
                    <a href="dashboard.php" class="nav-link">Dashboard</a>
                    <a href="manage_students.php" class="nav-link active">Students</a>
                    <a href="mark_attendance.php" class="nav-link">Mark Attendance</a>
                    <a href="view_attendance.php" class="nav-link">View Attendance</a>
                    <a href="upload_documents.php" class="nav-link">Upload Documents</a>
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
                    <div>
                        <label style="display:block; margin-bottom:0.5rem; color:#3a4a6b; font-weight:500;">Name *</label>
                        <input type="text" name="name" required class="form-input">
                    </div>
                    <div>
                        <label style="display:block; margin-bottom:0.5rem; color:#3a4a6b; font-weight:500;">Email *</label>
                        <input type="email" name="email" required class="form-input">
                    </div>
                    <div>
                        <label style="display:block; margin-bottom:0.5rem; color:#3a4a6b; font-weight:500;">Password *</label>
                        <input type="password" name="password" required class="form-input">
                    </div>
                    <div>
                        <label style="display:block; margin-bottom:0.5rem; color:#3a4a6b; font-weight:500;">Date of Birth</label>
                        <input type="date" name="dob" class="form-input">
                    </div>
                    <div>
                        <label style="display:block; margin-bottom:0.5rem; color:#3a4a6b; font-weight:500;">Phone *</label>
                        <input type="text" name="phone" required class="form-input">
                    </div>
                    <div>
                        <label style="display:block; margin-bottom:0.5rem; color:#3a4a6b; font-weight:500;">College Name</label>
                        <input type="text" name="college_name" class="form-input">
                    </div>
                    <div>
                        <label for="batch" style="display: block; margin-bottom: 0.5rem; color: #3a4a6b; font-weight: 500;">Batch</label>
                        <input type="text" id="batch" name="batch" class="form-input">
                    </div>
                    <div>
                        <label for="location" style="display: block; margin-bottom: 0.5rem; color: #3a4a6b; font-weight: 500;">Location</label>
                        <input type="text" id="location" name="location" class="form-input">
                    </div>
                    <div style="grid-column: 1 / -1;">
                        <button type="submit" name="create_student" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Add Student</button>
                    </div>
                </form>
            </section>

            <section class="card">
                <div class="tab-btns">
                    <a href="?tab=all" class="tab-btn <?php echo ($tab === 'all' ? 'active' : ''); ?>">All Students</a>
                    <a href="?tab=approve" class="tab-btn <?php echo ($tab === 'approve' ? 'active' : ''); ?>">Approve Students</a>
                </div>
                <div class="responsive-actions" style="margin-bottom: 1.5rem;">
                <h2 style="color:#3a4a6b;"><?php echo ($tab === 'approve' ? 'Pending Approvals' : 'Student List'); ?></h2>
                    <form method="get">
                        <input type="hidden" name="tab" value="<?php echo htmlspecialchars($tab); ?>">
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
                                <th>Date of Birth</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $student): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($student['name']); ?></td>
                                <td><?php echo htmlspecialchars($student['email']); ?></td>
                                <td><?php echo htmlspecialchars($student['phone']); ?></td>
                                <td><?php echo htmlspecialchars($student['college_name']); ?></td>
                                <td><?php echo $student['dob'] ? date('M d, Y', strtotime($student['dob'])) : '-'; ?></td>
                                <td><?php echo date('M d, Y', strtotime($student['created_at'])); ?></td>
                                <td>
                                    <?php if ($tab === 'approve'): ?>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="student_id" value="<?php echo $student['id']; ?>">
                                            <button type="submit" name="action" value="approve" class="btn btn-primary">Approve</button>
                                            <button type="submit" name="action" value="reject" class="btn">Reject</button>
                                        </form>
                                    <?php else: ?>
                                    <button class="btn" style="background:#e3eafc; color:#3a4a6b; padding:0.3rem 0.6rem; font-size:0.9rem;"
                                        onclick="editStudent(
                                            <?php echo (int)$student['id']; ?>,
                                            '<?php echo htmlspecialchars($student['name'], ENT_QUOTES); ?>',
                                            '<?php echo htmlspecialchars($student['email'], ENT_QUOTES); ?>',
                                            '<?php echo htmlspecialchars($student['phone'], ENT_QUOTES); ?>',
                                            '<?php echo htmlspecialchars($student['college_name'], ENT_QUOTES); ?>',
                                            '<?php echo htmlspecialchars($student['dob'], ENT_QUOTES); ?>'
                                        )">
                                        <i class="fa-solid fa-edit"></i> Edit
                                    </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </main>
    <div id="editModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000;">
        <div style="position:absolute; top:50%; left:50%; transform:translate(-50%, -50%); background:white; padding:2rem; border-radius:14px; width:90%; max-width:500px;">
            <h3 style="color:#3a4a6b; margin-bottom:1.5rem;">Edit Student</h3>
            <form method="POST" id="editForm" style="display:grid; gap:1rem;">
                <input type="hidden" name="edit_student_id" id="edit_student_id">
                <div>
                    <label style="display:block; margin-bottom:0.5rem; color:#3a4a6b; font-weight:500;">Name</label>
                    <input type="text" name="edit_name" id="edit_name" class="form-input" required>
                </div>
                <div>
                    <label style="display:block; margin-bottom:0.5rem; color:#3a4a6b; font-weight:500;">Email</label>
                    <input type="email" name="edit_email" id="edit_email" class="form-input" required>
                </div>
                <div>
                    <label style="display:block; margin-bottom:0.5rem; color:#3a4a6b; font-weight:500;">Phone</label>
                    <input type="text" name="edit_phone" id="edit_phone" class="form-input" required>
                </div>
                <div>
                    <label style="display:block; margin-bottom:0.5rem; color:#3a4a6b; font-weight:500;">College Name</label>
                    <input type="text" name="edit_college_name" id="edit_college_name" class="form-input">
                </div>
                <div>
                    <label style="display:block; margin-bottom:0.5rem; color:#3a4a6b; font-weight:500;">Date of Birth</label>
                    <input type="date" name="edit_dob" id="edit_dob" class="form-input">
                </div>
                <div style="display:flex; gap:1rem; justify-content:flex-end;">
                    <button type="button" class="btn" style="background:#f6f8fb; color:#3a4a6b;" onclick="closeEditModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Student</button>
                </div>
            </form>
        </div>
    </div>
    <script>
        function editStudent(id, name, email, phone, college, dob) {
            document.getElementById('edit_student_id').value = id;
            document.getElementById('edit_name').value = name || '';
            document.getElementById('edit_email').value = email || '';
            document.getElementById('edit_phone').value = phone || '';
            document.getElementById('edit_college_name').value = college || '';
            document.getElementById('edit_dob').value = dob || '';
            document.getElementById('editModal').style.display = 'block';
        }
        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }
        document.getElementById('editModal').addEventListener('click', function(e){
            if (e.target === this) closeEditModal();
        });
    </script>
</body>
</html>