<?php
$required_role = 'super_admin';
include '../auth.php';
require_once '../db.php';
$message = '';

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
    <title>Manage Students - Super Admin | EV Academy ERP</title>
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
                <button class="mobile-menu-btn" onclick="document.body.classList.toggle('nav-open')"><i class="fa-solid fa-bars"></i></button>
                <nav class="nav-desktop">
                    <a href="dashboard.php" class="nav-link">Dashboard</a>
                    <a href="manage_admins.php" class="nav-link">Admins</a>
                    <a href="manage_faculty.php" class="nav-link">Faculty</a>
                    <a href="manage_students.php" class="nav-link active">Students</a>
                    <a href="manage_fees.php" class="nav-link">Fees</a>
                    <a href="manage_classes.php" class="nav-link">Classes</a>
                    <a href="view_attendance.php" class="nav-link">Attendance</a>
                    <a href="view_faculty_work.php" class="nav-link">Faculty Work</a>
                    <a href="../logout.php" class="nav-link">Logout</a>
                </nav>
            </div>
        </div>
    </header>
    <main>
        <div class="container">
            <?php if ($message): ?>
                <div class="alert"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            
            <section class="form-section card">
                <h2 style="color:#3a4a6b; margin-bottom: 1.5rem;">Add New Student</h2>
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
                    <div style="grid-column: 1 / -1;">
                        <button type="submit" name="create_student" class="btn btn-primary">
                            <i class="fa-solid fa-plus"></i> Add Student
                        </button>
                    </div>
                </form>
            </section>

            <section class="card">
                <div class="responsive-actions" style="margin-bottom: 1.5rem;">
                    <h2 style="color:#3a4a6b;">Student List</h2>
                    <form method="GET">
                        <input type="text" name="search" placeholder="Search students..." class="form-input" style="width: 250px;" value="<?php echo htmlspecialchars($search); ?>">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-search"></i> Search
                        </button>
                    </form>
                </div>
                
                <div style="overflow-x: auto;">
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
                                    <button onclick="editStudent(<?php echo $student['id']; ?>)" class="btn" style="background: #e3eafc; color: #3a4a6b; padding: 0.3rem 0.6rem; font-size: 0.9rem;">
                                        <i class="fa-solid fa-edit"></i> Edit
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
    <hr>
    <h2>Student Fee Approvals</h2>
    <?php
    require_once '../db.php';
    $stmt = $pdo->query("SELECT sf.*, s.name FROM student_fees sf JOIN students s ON sf.student_id = s.id WHERE sf.status = 'pending' ORDER BY sf.created_at DESC");
    $fees = $stmt->fetchAll();
    if ($fees):
    ?>
    <table border="1" cellpadding="8">
        <tr>
            <th>Student Name</th>
            <th>Paid Amount</th>
            <th>Screenshot</th>
            <th>Submitted At</th>
            <th>Action</th>
        </tr>
        <?php foreach ($fees as $fee): ?>
        <tr>
            <td><?php echo htmlspecialchars($fee['name']); ?></td>
            <td>₹<?php echo number_format($fee['paid_amount']); ?></td>
            <td><a href="../uploads/<?php echo htmlspecialchars($fee['screenshot']); ?>" target="_blank">View</a></td>
            <td><?php echo $fee['created_at']; ?></td>
            <td>
                <form method="post" style="display:inline">
                    <input type="hidden" name="fee_id" value="<?php echo $fee['id']; ?>">
                    <button type="submit" name="approve_fee">Approve</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
    <?php else: ?>
    <p>No pending fee approvals.</p>
    <?php endif; ?>

    <?php
    if (isset($_POST['approve_fee'])) {
        $fee_id = intval($_POST['fee_id']);
        $stmt = $pdo->prepare("UPDATE student_fees SET status = 'approved' WHERE id = ?");
        $stmt->execute([$fee_id]);
        echo "<script>location.reload();</script>";
    }
    ?>
        </div>
    </main>

    <!-- Edit Modal -->
    <div id="editModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 2rem; border-radius: 14px; width: 90%; max-width: 500px;">
            <h3 style="color:#3a4a6b; margin-bottom: 1.5rem;">Edit Student</h3>
            <form method="POST" id="editForm" style="display: grid; gap: 1rem;">
                <input type="hidden" name="edit_student_id" id="edit_student_id">
                <div>
                    <label for="edit_name" style="display: block; margin-bottom: 0.5rem; color: #3a4a6b; font-weight: 500;">Name</label>
                    <input type="text" id="edit_name" name="edit_name" class="form-input" required>
                </div>
                <div>
                    <label for="edit_email" style="display: block; margin-bottom: 0.5rem; color: #3a4a6b; font-weight: 500;">Email</label>
                    <input type="email" id="edit_email" name="edit_email" class="form-input" required>
                </div>
                <div>
                    <label for="edit_phone" style="display: block; margin-bottom: 0.5rem; color: #3a4a6b; font-weight: 500;">Phone</label>
                    <input type="tel" id="edit_phone" name="edit_phone" class="form-input" required>
                </div>
                <div>
                    <label for="edit_college_name" style="display: block; margin-bottom: 0.5rem; color: #3a4a6b; font-weight: 500;">College Name</label>
                    <input type="text" id="edit_college_name" name="edit_college_name" class="form-input">
                </div>
                <div>
                    <label for="edit_dob" style="display: block; margin-bottom: 0.5rem; color: #3a4a6b; font-weight: 500;">Date of Birth</label>
                    <input type="date" id="edit_dob" name="edit_dob" class="form-input">
                </div>
                <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                    <button type="button" onclick="closeEditModal()" class="btn" style="background: #f6f8fb; color: #3a4a6b;">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Student</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function editStudent(id) {
            // Fetch student data and populate modal
            fetch(`get_student.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('edit_student_id').value = data.id;
                    document.getElementById('edit_name').value = data.name;
                    document.getElementById('edit_email').value = data.email;
                    document.getElementById('edit_phone').value = data.phone;
                    document.getElementById('edit_college_name').value = data.college_name;
                    document.getElementById('edit_dob').value = data.dob;
                    document.getElementById('editModal').style.display = 'block';
                });
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        // Close modal when clicking outside
        document.getElementById('editModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeEditModal();
            }
        });
    </script>
</body>
</html>