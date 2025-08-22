<?php
$required_role = 'super_admin';
include '../auth.php';
require_once '../db.php';
$message = '';
$tab = $_GET['tab'] ?? 'all';

$stmt = $pdo->prepare('SELECT id, name FROM erp_classes ORDER BY name ASC');
$stmt->execute();
$class_list = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_faculty'])) {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $dob = $_POST['dob'] ?? null;
    $phone = $_POST['phone'] ?? '';
    $assigned_class = $_POST['assigned_class'] ?? '';
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
            $stmt = $pdo->prepare('INSERT INTO erp_users (name, email, password, dob, phone, assigned_class, role, approved) VALUES (?, ?, ?, ?, ?, ?, "faculty", 1)');
            $stmt->execute([$name, $email, $hash, $dob, $phone, $assigned_class]);
            $message = 'Faculty created successfully!';
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_faculty_id'])) {
    $edit_id = (int)$_POST['edit_faculty_id'];
    $name = $_POST['edit_name'] ?? '';
    $email = $_POST['edit_email'] ?? '';
    $dob = $_POST['edit_dob'] ?? null;
    $phone = $_POST['edit_phone'] ?? '';
    $assigned_class = $_POST['edit_assigned_class'] ?? '';
    $update_sql = 'UPDATE erp_users SET name=?, email=?, dob=?, phone=?, assigned_class=? WHERE id=? AND role="faculty"';
    $stmt = $pdo->prepare($update_sql);
    $stmt->execute([$name, $email, $dob, $phone, $assigned_class, $edit_id]);
    $message = 'Faculty details updated!';
}

if (isset($_POST['action']) && isset($_POST['faculty_id'])) {
    $faculty_id = intval($_POST['faculty_id']);
    $action = $_POST['action'];
    if ($action === 'approve') {
        $stmt = $pdo->prepare("UPDATE erp_users SET approved = 1 WHERE id = ?");
        $stmt->execute([$faculty_id]);
        $message = 'Faculty approved successfully!';
    } elseif ($action === 'reject') {
        $stmt = $pdo->prepare("DELETE FROM erp_users WHERE id = ?");
        $stmt->execute([$faculty_id]);
        $message = 'Faculty rejected successfully!';
    }
}


$search = $_GET['search'] ?? '';
$where = ['role = "faculty"'];
$params = [];
if ($search) {
    $where[] = '(name LIKE ? OR email LIKE ? OR phone LIKE ? OR assigned_class LIKE ?)';
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
$faculty = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Faculty - Super Admin | EV Academy ERP</title>
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
                    <a href="manage_admins.php" class="nav-link">Admins</a>
                    <a href="manage_faculty.php" class="nav-link active">Faculty</a>
                    <a href="manage_students.php" class="nav-link">Students</a>
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
                <h2 style="color:#3a4a6b; margin-bottom: 1.5rem;">Add New Faculty</h2>
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
                        <label for="assigned_class" style="display: block; margin-bottom: 0.5rem; color: #3a4a6b; font-weight: 500;">Assigned Class</label>
                        <select id="assigned_class" name="assigned_class" class="form-input">
                            <option value="">Select a class (optional)</option>
                            <?php foreach ($class_list as $class): ?>
                                <option value="<?php echo htmlspecialchars($class['name']); ?>">
                                    <?php echo htmlspecialchars($class['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div style="grid-column: 1 / -1;">
                        <button type="submit" name="create_faculty" class="btn btn-primary">
                            <i class="fa-solid fa-plus"></i> Add Faculty
                        </button>
                    </div>
                </form>
            </section>

            <section class="card">
                <div class="tab-btns">
                    <a href="?tab=all" class="tab-btn <?php echo ($tab === 'all' ? 'active' : ''); ?>">All Faculty</a>
                    <a href="?tab=approve" class="tab-btn <?php echo ($tab === 'approve' ? 'active' : ''); ?>">Approve Faculty</a>
                </div>
                <div class="responsive-actions" style="margin-bottom: 1.5rem;">
                    <h2 style="color:#3a4a6b;"><?php echo ($tab === 'approve' ? 'Pending Approvals' : 'Faculty List'); ?></h2>
                    <form method="GET">
                        <input type="hidden" name="tab" value="<?php echo htmlspecialchars($tab); ?>">
                        <input type="text" name="search" placeholder="Search faculty..." class="form-input" style="width: 250px;" value="<?php echo htmlspecialchars($search); ?>">
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
                                <th>Assigned Class</th>
                                <th>Date of Birth</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($faculty as $faculty_member): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($faculty_member['name']); ?></td>
                                <td><?php echo htmlspecialchars($faculty_member['email']); ?></td>
                                <td><?php echo htmlspecialchars($faculty_member['phone']); ?></td>
                                <td><?php echo htmlspecialchars($faculty_member['assigned_class']); ?></td>
                                <td><?php echo $faculty_member['dob'] ? date('M d, Y', strtotime($faculty_member['dob'])) : '-'; ?></td>
                                <td><?php echo date('M d, Y', strtotime($faculty_member['created_at'])); ?></td>
                                <td>
                                    <?php if ($tab === 'approve'): ?>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="faculty_id" value="<?php echo $faculty_member['id']; ?>">
                                            <button type="submit" name="action" value="approve" class="btn btn-primary">Approve</button>
                                            <button type="submit" name="action" value="reject" class="btn">Reject</button>
                                        </form>
                                    <?php else: ?>
                                        <button onclick="editFaculty(<?php echo $faculty_member['id']; ?>)" class="btn" style="background: #e3eafc; color: #3a4a6b; padding: 0.3rem 0.6rem; font-size: 0.9rem;">
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

    <!-- Edit Modal -->
    <div id="editModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 2rem; border-radius: 14px; width: 90%; max-width: 500px;">
            <h3 style="color:#3a4a6b; margin-bottom: 1.5rem;">Edit Faculty</h3>
            <form method="POST" id="editForm" style="display: grid; gap: 1rem;">
                <input type="hidden" name="edit_faculty_id" id="edit_faculty_id">
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
                    <label for="edit_assigned_class" style="display: block; margin-bottom: 0.5rem; color: #3a4a6b; font-weight: 500;">Assigned Class</label>
                    <select id="edit_assigned_class" name="edit_assigned_class" class="form-input">
                        <option value="">Select a class (optional)</option>
                        <?php foreach ($class_list as $class): ?>
                            <option value="<?php echo htmlspecialchars($class['name']); ?>">
                                <?php echo htmlspecialchars($class['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="edit_dob" style="display: block; margin-bottom: 0.5rem; color: #3a4a6b; font-weight: 500;">Date of Birth</label>
                    <input type="date" id="edit_dob" name="edit_dob" class="form-input">
                </div>
                <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                    <button type="button" onclick="closeEditModal()" class="btn" style="background: #f6f8fb; color: #3a4a6b;">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Faculty</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function editFaculty(id) {
            // Fetch faculty data and populate modal
            fetch(`get_faculty.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('edit_faculty_id').value = data.id;
                    document.getElementById('edit_name').value = data.name;
                    document.getElementById('edit_email').value = data.email;
                    document.getElementById('edit_phone').value = data.phone;
                    document.getElementById('edit_assigned_class').value = data.assigned_class;
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