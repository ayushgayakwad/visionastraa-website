<?php
$required_role = 'super_admin';
include '../auth.php';
require_once '../db.php';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['quick_add_faculty'])) {
    $name = $_POST['faculty_name'] ?? '';
    $email = $_POST['faculty_email'] ?? '';
    $password = $_POST['faculty_password'] ?? '';
    $dob = $_POST['faculty_dob'] ?? null;
    $phone = $_POST['faculty_phone'] ?? '';
    $assigned_class = '';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Invalid faculty email address.';
    } elseif (strlen($password) < 6) {
        $message = 'Faculty password must be at least 6 characters.';
    } elseif (empty($name) || empty($phone)) {
        $message = 'Faculty name and phone are required.';
    } else {
        $stmt = $pdo->prepare('SELECT id FROM erp_users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $message = 'Faculty email already exists.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO erp_users (name, email, password, dob, phone, assigned_class, role, approved) VALUES (?, ?, ?, ?, ?, ?, "faculty", 1)');
            $stmt->execute([$name, $email, $hash, $dob, $phone, $assigned_class]);
            $message = 'Faculty added successfully!';
        }
    }
}

$stmt = $pdo->prepare('SELECT id, name FROM erp_users WHERE role = "faculty" ORDER BY name ASC');
$stmt->execute();
$faculty_list = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_class'])) {
    $name = $_POST['name'] ?? '';
    $faculty_id = $_POST['faculty_id'] !== '' ? $_POST['faculty_id'] : null;
    if (empty($name)) {
        $message = 'Class name is required.';
    } else {
        $stmt = $pdo->prepare('INSERT INTO erp_classes (name, faculty_id) VALUES (?, ?)');
        $stmt->execute([$name, $faculty_id]);
        $message = 'Class created successfully!';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_class_id'])) {
    $edit_id = (int)$_POST['edit_class_id'];
    $name = $_POST['edit_name'] ?? '';
    $faculty_id = $_POST['edit_faculty_id'] !== '' ? $_POST['edit_faculty_id'] : null;
    $update_sql = 'UPDATE erp_classes SET name=?, faculty_id=? WHERE id=?';
    $stmt = $pdo->prepare($update_sql);
    $stmt->execute([$name, $faculty_id, $edit_id]);
    $message = 'Class details updated!';
}

$stmt = $pdo->prepare('SELECT c.*, f.name AS faculty_name FROM erp_classes c LEFT JOIN erp_users f ON c.faculty_id = f.id ORDER BY c.created_at DESC');
$stmt->execute();
$classes = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Classes - Super Admin | EV Academy ERP</title>
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
                    <a href="manage_students.php" class="nav-link">Students</a>
                    <a href="manage_fees.php" class="nav-link">Fees</a>
                    <a href="manage_classes.php" class="nav-link active">Classes</a>
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
                <h2 style="color:#3a4a6b; margin-bottom: 1.5rem;">Add New Class</h2>
                <form method="POST" style="display: grid; gap: 1rem; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));">
                    <div>
                        <label for="name" style="display: block; margin-bottom: 0.5rem; color: #3a4a6b; font-weight: 500;">Class Name *</label>
                        <input type="text" id="name" name="name" class="form-input" required>
                    </div>
                    <div>
                        <label for="faculty_id" style="display: block; margin-bottom: 0.5rem; color: #3a4a6b; font-weight: 500;">Assign Faculty (Optional)</label>
                        <select id="faculty_id" name="faculty_id" class="form-input">
                            <option value="">No faculty assigned</option>
                            <?php foreach ($faculty_list as $faculty): ?>
                                <option value="<?php echo $faculty['id']; ?>">
                                    <?php echo htmlspecialchars($faculty['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div style="grid-column: 1 / -1;">
                        <button type="submit" name="create_class" class="btn btn-primary">
                            <i class="fa-solid fa-plus"></i> Add Class
                        </button>
                        <button type="button" onclick="showAddFacultyModal()" class="btn" style="background: #e3eafc; color: #3a4a6b; margin-left: 0.5rem;">
                            <i class="fa-solid fa-user-plus"></i> Quick Add Faculty
                        </button>
                    </div>
                </form>
            </section>

            <section class="card">
                <h2 style="color:#3a4a6b; margin-bottom: 1.5rem;">Class List</h2>
                <div style="overflow-x: auto;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Class Name</th>
                                <th>Assigned Faculty</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($classes as $class): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($class['name']); ?></td>
                                <td><?php echo htmlspecialchars($class['faculty_name'] ?? 'No faculty assigned'); ?></td>
                                <td><?php echo date('M d, Y', strtotime($class['created_at'])); ?></td>
                                <td>
                                    <button onclick="editClass(<?php echo $class['id']; ?>)" class="btn" style="background: #e3eafc; color: #3a4a6b; padding: 0.3rem 0.6rem; font-size: 0.9rem;">
                                        <i class="fa-solid fa-edit"></i> Edit
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </main>

    <!-- Edit Class Modal -->
    <div id="editModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 2rem; border-radius: 14px; width: 90%; max-width: 500px;">
            <h3 style="color:#3a4a6b; margin-bottom: 1.5rem;">Edit Class</h3>
            <form method="POST" id="editForm" style="display: grid; gap: 1rem;">
                <input type="hidden" name="edit_class_id" id="edit_class_id">
                <div>
                    <label for="edit_name" style="display: block; margin-bottom: 0.5rem; color: #3a4a6b; font-weight: 500;">Class Name</label>
                    <input type="text" id="edit_name" name="edit_name" class="form-input" required>
                </div>
                <div>
                    <label for="edit_faculty_id" style="display: block; margin-bottom: 0.5rem; color: #3a4a6b; font-weight: 500;">Assign Faculty</label>
                    <select id="edit_faculty_id" name="edit_faculty_id" class="form-input">
                        <option value="">No faculty assigned</option>
                        <?php foreach ($faculty_list as $faculty): ?>
                            <option value="<?php echo $faculty['id']; ?>">
                                <?php echo htmlspecialchars($faculty['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                    <button type="button" onclick="closeEditModal()" class="btn" style="background: #f6f8fb; color: #3a4a6b;">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Class</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Quick Add Faculty Modal -->
    <div id="addFacultyModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000;">
        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 2rem; border-radius: 14px; width: 90%; max-width: 500px;">
            <h3 style="color:#3a4a6b; margin-bottom: 1.5rem;">Quick Add Faculty</h3>
            <form method="POST" style="display: grid; gap: 1rem;">
                <div>
                    <label for="faculty_name" style="display: block; margin-bottom: 0.5rem; color: #3a4a6b; font-weight: 500;">Name *</label>
                    <input type="text" id="faculty_name" name="faculty_name" class="form-input" required>
                </div>
                <div>
                    <label for="faculty_email" style="display: block; margin-bottom: 0.5rem; color: #3a4a6b; font-weight: 500;">Email *</label>
                    <input type="email" id="faculty_email" name="faculty_email" class="form-input" required>
                </div>
                <div>
                    <label for="faculty_password" style="display: block; margin-bottom: 0.5rem; color: #3a4a6b; font-weight: 500;">Password *</label>
                    <input type="password" id="faculty_password" name="faculty_password" class="form-input" required>
                </div>
                <div>
                    <label for="faculty_phone" style="display: block; margin-bottom: 0.5rem; color: #3a4a6b; font-weight: 500;">Phone *</label>
                    <input type="tel" id="faculty_phone" name="faculty_phone" class="form-input" required>
                </div>
                <div>
                    <label for="faculty_dob" style="display: block; margin-bottom: 0.5rem; color: #3a4a6b; font-weight: 500;">Date of Birth</label>
                    <input type="date" id="faculty_dob" name="faculty_dob" class="form-input">
                </div>
                <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                    <button type="button" onclick="closeAddFacultyModal()" class="btn" style="background: #f6f8fb; color: #3a4a6b;">Cancel</button>
                    <button type="submit" name="quick_add_faculty" class="btn btn-primary">Add Faculty</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function editClass(id) {
            // Fetch class data and populate modal
            fetch(`get_class.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('edit_class_id').value = data.id;
                    document.getElementById('edit_name').value = data.name;
                    document.getElementById('edit_faculty_id').value = data.faculty_id || '';
                    document.getElementById('editModal').style.display = 'block';
                });
        }

        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        function showAddFacultyModal() {
            document.getElementById('addFacultyModal').style.display = 'block';
        }

        function closeAddFacultyModal() {
            document.getElementById('addFacultyModal').style.display = 'none';
        }

        // Close modals when clicking outside
        document.getElementById('editModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeEditModal();
            }
        });

        document.getElementById('addFacultyModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeAddFacultyModal();
            }
        });
    </script>
</body>
</html>