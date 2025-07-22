<?php
$required_role = 'super_admin';
include '../auth.php';
require_once '../db.php';
$message = '';
// Handle admin creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_admin'])) {
    $name = $_POST['name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $dob = $_POST['dob'] ?? null;
    $aadhaar = $_POST['aadhaar'] ?? null;
    $pan = $_POST['pan'] ?? null;
    $location = $_POST['location'] ?? null;
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Invalid email address.';
    } elseif (strlen($password) < 6) {
        $message = 'Password must be at least 6 characters.';
    } elseif (empty($name) || empty($phone)) {
        $message = 'Name and phone are required.';
    } else {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $message = 'Email already exists.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO users (name, phone, dob, aadhaar, pan, location, email, password, role, approved, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, "admin", 1, ?)');
            $stmt->execute([$name, $phone, $dob, $aadhaar, $pan, $location, $email, $hash, $_SESSION['user_id']]);
            $message = 'Admin created successfully!';
        }
    }
}
// Handle admin update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_admin_id'])) {
    $edit_id = (int)$_POST['edit_admin_id'];
    $name = $_POST['edit_name'] ?? '';
    $phone = $_POST['edit_phone'] ?? '';
    $dob = $_POST['edit_dob'] ?? null;
    $aadhaar = $_POST['edit_aadhaar'] ?? null;
    $pan = $_POST['edit_pan'] ?? null;
    $location = $_POST['edit_location'] ?? null;
    $email = $_POST['edit_email'] ?? '';
    $update_sql = 'UPDATE users SET name=?, phone=?, dob=?, aadhaar=?, pan=?, location=?, email=? WHERE id=? AND role="admin"';
    $stmt = $pdo->prepare($update_sql);
    $stmt->execute([$name, $phone, $dob, $aadhaar, $pan, $location, $email, $edit_id]);
    $message = 'Admin details updated!';
}
// Fetch all admins
$stmt = $pdo->prepare('SELECT id, name, phone, dob, aadhaar, pan, location, email, created_at FROM users WHERE role = "admin"');
$stmt->execute();
$admins = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Admins - Super Admin</title>
    <link rel="stylesheet" href="../../css/vms-styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .tab-btns { display: flex; gap: 1rem; margin-bottom: 2rem; }
        .tab-btn { padding: 0.5rem 1.5rem; border: none; background: #eee; border-radius: 6px; font-weight: 600; cursor: pointer; transition: background 0.2s; }
        .tab-btn.active { background: #2b6cb0; color: #fff; }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        .admin-card { display: flex; align-items: center; justify-content: space-between; background: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.07); padding: 1.2rem 2rem; margin-bottom: 1.5rem; cursor: pointer; transition: box-shadow 0.2s; }
        .admin-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,0.13); }
        .admin-card-content { display: flex; flex-direction: row; gap: 2rem; align-items: center; }
        .admin-card-title { font-size: 1.2rem; font-weight: 600; }
        .admin-card-email { color: #555; }
        .admin-card-date { color: #888; font-size: 0.95rem; }
        .admin-card-action { margin-left: auto; }
        .admin-popup-bg { display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.35); z-index: 1000; justify-content: center; align-items: center; }
        .admin-popup-bg.active { display: flex; }
        .admin-popup { background: #fff; border-radius: 14px; padding: 2rem 2.5rem; min-width: 350px; max-width: 95vw; box-shadow: 0 8px 32px rgba(0,0,0,0.18); position: relative; }
        .admin-popup-close { position: absolute; top: 1rem; right: 1rem; font-size: 1.5rem; color: #888; cursor: pointer; }
        .admin-popup-details { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem 2rem; margin-bottom: 1.5rem; }
        .admin-popup-details label { font-weight: 500; color: #333; }
        .admin-popup-details input { width: 100%; padding: 0.3rem 0.5rem; border-radius: 4px; border: 1px solid #ccc; }
        .admin-popup-details span { color: #444; }
        @media (max-width: 600px) { .admin-popup { padding: 1rem 0.5rem; } .admin-popup-details { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <header class="header" id="header">
        <div class="container">
            <div class="header-content">
                <a href="../../manpower.html" class="logo">
                    <div class="logo-icon">
                        <span>VA</span>
                    </div>
                    <span class="logo-text">Staffing Solutions</span>
                </a>
                <nav class="nav-desktop">
                    <a href="dashboard.php" class="nav-link">Dashboard</a>
                    <a href="manage_users.php" class="nav-link">Manage Users</a>
                    <a href="manage_admins.php" class="nav-link active">Manage Admins</a>
                    <a href="../logout.php" class="nav-link">Logout</a>
                </nav>
            </div>
        </div>
    </header>
    <main>
        <section class="section">
            <div class="container">
                <h1 class="hero-title" style="text-align:center;">Manage Admins</h1>
                <?php if ($message): ?>
                    <div style="color:<?php echo ($message === 'Admin created successfully!') ? 'green' : 'red'; ?>;text-align:center;margin-bottom:1rem;">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>
                <div class="tab-btns">
                    <button class="tab-btn active" onclick="showTab('admins-list')">All Admins</button>
                    <button class="tab-btn" onclick="showTab('create-admin')">Create Admin</button>
                </div>
                <div id="admins-list" class="tab-content active">
                    <?php foreach ($admins as $admin): ?>
                        <div class="admin-card" onclick="showAdminPopup(<?php echo $admin['id']; ?>)">
                            <div class="admin-card-content">
                                <div>
                                    <div class="admin-card-title"><?php echo htmlspecialchars($admin['name']); ?></div>
                                    <div class="admin-card-email"><?php echo htmlspecialchars($admin['email']); ?></div>
                                </div>
                                <div class="admin-card-date">
                                    Registered At: <?php echo htmlspecialchars($admin['created_at']); ?>
                                </div>
                            </div>
                            <div class="admin-card-action">
                                <button class="btn btn-primary" style="padding: 0.3rem 1rem;" onclick="event.stopPropagation(); showAdminPopup(<?php echo $admin['id']; ?>)">Edit</button>
                            </div>
                        </div>
                        <div class="admin-popup-bg" id="admin-popup-bg-<?php echo $admin['id']; ?>">
                            <div class="admin-popup">
                                <span class="admin-popup-close" onclick="closeAdminPopup(<?php echo $admin['id']; ?>)">&times;</span>
                                <h2 style="text-align:center; margin-bottom:1.5rem;">Edit Admin Details</h2>
                                <form method="post" style="text-align:center;">
                                    <div class="admin-popup-details">
                                        <label>Name:</label><input type="text" name="edit_name" value="<?php echo htmlspecialchars($admin['name']); ?>" required>
                                        <label>Email:</label><input type="email" name="edit_email" value="<?php echo htmlspecialchars($admin['email']); ?>" required>
                                        <label>Phone:</label><input type="text" name="edit_phone" value="<?php echo htmlspecialchars($admin['phone']); ?>" required>
                                        <label>Date of Birth:</label><input type="date" name="edit_dob" value="<?php echo htmlspecialchars($admin['dob']); ?>">
                                        <label>Aadhaar Card:</label><input type="text" name="edit_aadhaar" value="<?php echo htmlspecialchars($admin['aadhaar']); ?>">
                                        <label>PAN Card:</label><input type="text" name="edit_pan" value="<?php echo htmlspecialchars($admin['pan']); ?>">
                                        <label>Location:</label><input type="text" name="edit_location" value="<?php echo htmlspecialchars($admin['location']); ?>">
                                    </div>
                                    <input type="hidden" name="edit_admin_id" value="<?php echo $admin['id']; ?>">
                                    <button type="submit" class="btn btn-primary btn-lg" style="padding: 0.3rem 1rem;">Save</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div id="create-admin" class="tab-content">
                    <form method="post" style="max-width:400px;margin:0 auto;display:flex;flex-direction:column;gap:1rem;">
                        <input type="hidden" name="create_admin" value="1">
                        <input type="text" name="name" placeholder="Full Name" required class="form-input">
                        <input type="text" name="phone" placeholder="Phone Number" required class="form-input">
                        <input type="date" name="dob" placeholder="Date of Birth" class="form-input">
                        <input type="text" name="aadhaar" placeholder="Aadhaar Card" class="form-input">
                        <input type="text" name="pan" placeholder="PAN Card" class="form-input">
                        <input type="text" name="location" placeholder="Location" class="form-input">
                        <input type="email" name="email" placeholder="Admin Email" required class="form-input">
                        <input type="password" name="password" placeholder="Password" required class="form-input">
                        <button type="submit" class="btn btn-primary btn-lg" style="padding: 0.3rem 1rem;">Create Admin</button>
                    </form>
                </div>
            </div>
        </section>
    </main>
    <script>
        function showTab(tabId) {
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
            if(tabId === 'admins-list') {
                document.querySelector('.tab-btn:nth-child(1)').classList.add('active');
                document.getElementById('admins-list').classList.add('active');
            } else {
                document.querySelector('.tab-btn:nth-child(2)').classList.add('active');
                document.getElementById('create-admin').classList.add('active');
            }
        }
        function showAdminPopup(id) {
            document.getElementById('admin-popup-bg-' + id).classList.add('active');
        }
        function closeAdminPopup(id) {
            document.getElementById('admin-popup-bg-' + id).classList.remove('active');
        }
    </script>
</body>
</html> 