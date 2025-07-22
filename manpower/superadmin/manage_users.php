<?php
$required_role = 'super_admin';
include '../auth.php';
require_once '../db.php';
$message = '';
$stmt = $pdo->prepare('SELECT id, name FROM companies ORDER BY name ASC');
$stmt->execute();
$companies_list = $stmt->fetchAll();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['unassign_user_id'])) {
    $unassign_id = (int)$_POST['unassign_user_id'];
    $stmt = $pdo->prepare('UPDATE users SET company_id = 0 WHERE id = ?');
    $stmt->execute([$unassign_id]);
    $message = 'User unassigned from company!';
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['approve_id'])) {
    $approve_id = (int)$_POST['approve_id'];
    $stmt = $pdo->prepare('UPDATE users SET approved = 1 WHERE id = ?');
    $stmt->execute([$approve_id]);
    $message = 'User approved!';
}
// Handle user update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_user_id'])) {
    $edit_id = (int)$_POST['edit_user_id'];
    $name = $_POST['edit_name'] ?? '';
    $phone = $_POST['edit_phone'] ?? '';
    $dob = $_POST['edit_dob'] ?? null;
    $aadhaar = $_POST['edit_aadhaar'] ?? null;
    $pan = $_POST['edit_pan'] ?? null;
    $location = $_POST['edit_location'] ?? null;
    $email = $_POST['edit_email'] ?? '';
    $company_id = $_POST['edit_company_id'] ?? null;
    $update_sql = 'UPDATE users SET name=?, phone=?, dob=?, aadhaar=?, pan=?, location=?, email=?, company_id=? WHERE id=? AND role="user"';
    $stmt = $pdo->prepare($update_sql);
    $stmt->execute([$name, $phone, $dob, $aadhaar, $pan, $location, $email, $company_id, $edit_id]);
    $message = 'User details updated!';
}
$stmt = $pdo->prepare('SELECT u.*, c.name AS company_name FROM users u LEFT JOIN companies c ON u.company_id = c.id WHERE u.role = "user"');
$stmt->execute();
$all_users = $stmt->fetchAll();
$stmt = $pdo->prepare('SELECT u.*, c.name AS company_name FROM users u LEFT JOIN companies c ON u.company_id = c.id WHERE u.role = "user" AND u.approved = 0');
$stmt->execute();
$pending_users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Super Admin</title>
    <link rel="stylesheet" href="../../css/vms-styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .tab-btns { display: flex; gap: 1rem; margin-bottom: 2rem; }
        .tab-btn { padding: 0.5rem 1.5rem; border: none; background: #eee; border-radius: 6px; font-weight: 600; cursor: pointer; transition: background 0.2s; }
        .tab-btn.active { background: #2b6cb0; color: #fff; }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        .user-card { display: flex; align-items: center; justify-content: space-between; background: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.07); padding: 1.2rem 2rem; margin-bottom: 1.5rem; cursor: pointer; transition: box-shadow 0.2s; }
        .user-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,0.13); }
        .user-card-content { display: flex; flex-direction: row; gap: 2rem; align-items: center; }
        .user-card-title { font-size: 1.2rem; font-weight: 600; }
        .user-card-email { color: #555; }
        .user-card-date { color: #888; font-size: 0.95rem; }
        .user-card-company { color: #2b6cb0; font-size: 1rem; font-weight: 500; }
        .user-card-action { margin-left: auto; display: flex; gap: 0.5rem; }
        .user-popup-bg { display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.35); z-index: 1000; justify-content: center; align-items: center; }
        .user-popup-bg.active { display: flex; }
        .user-popup { background: #fff; border-radius: 14px; padding: 2rem 2.5rem; min-width: 350px; max-width: 95vw; box-shadow: 0 8px 32px rgba(0,0,0,0.18); position: relative; }
        .user-popup-close { position: absolute; top: 1rem; right: 1rem; font-size: 1.5rem; color: #888; cursor: pointer; }
        .user-popup-details { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem 2rem; margin-bottom: 1.5rem; }
        .user-popup-details label { font-weight: 500; color: #333; }
        .user-popup-details input, .user-popup-details select { width: 100%; padding: 0.3rem 0.5rem; border-radius: 4px; border: 1px solid #ccc; }
        .user-popup-details span { color: #444; }
        @media (max-width: 600px) { .user-popup { padding: 1rem 0.5rem; } .user-popup-details { grid-template-columns: 1fr; } }
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
                    <a href="manage_users.php" class="nav-link active">Manage Users</a>
                    <a href="manage_admins.php" class="nav-link">Manage Admins</a>
                    <a href="manage_companies.php" class="nav-link">Manage Companies</a>
                    <a href="../logout.php" class="nav-link">Logout</a>
                </nav>
            </div>
        </div>
    </header>
    <main>
        <section class="section">
            <div class="container">
                <h1 class="hero-title" style="text-align:center;">Manage Users</h1>
                <?php if ($message): ?>
                    <div style="color:green;text-align:center;margin-bottom:1rem;">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>
                <div class="tab-btns">
                    <button class="tab-btn active" onclick="showTab('users-list')">All Users</button>
                    <button class="tab-btn" onclick="showTab('approve-users')">Approve Users</button>
                </div>
                <div id="users-list" class="tab-content active">
                    <?php foreach ($all_users as $user): ?>
                        <div class="user-card" onclick="showUserEditPopup(<?php echo $user['id']; ?>)">
                            <div class="user-card-content">
                                <div>
                                    <div class="user-card-title"><?php echo htmlspecialchars($user['name']); ?></div>
                                    <div class="user-card-email"><?php echo htmlspecialchars($user['email']); ?></div>
                                    <?php if ($user['company_id'] != 0): ?>
                                        <div class="user-card-company">Company: <?php echo htmlspecialchars($user['company_name']); ?></div>
                                    <?php else: ?>
                                        <div class="user-card-company">Unemployed</div>
                                    <?php endif; ?>
                                </div>
                                <div class="user-card-date">
                                    Registered At: <?php echo htmlspecialchars($user['created_at']); ?>
                                </div>
                                <div class="user-card-date">
                                    Status: <?php echo $user['approved'] ? 'Approved' : 'Pending'; ?>
                                </div>
                            </div>
                            <div class="user-card-action">
                                <button class="btn btn-primary" style="padding: 0.3rem 1rem;" onclick="event.stopPropagation(); showUserEditPopup(<?php echo $user['id']; ?>)">Edit</button>
                                <?php if ($user['company_id'] != 0): ?>
                                    <button class="btn btn-danger" style="padding: 0.3rem 1rem;" onclick="event.stopPropagation(); unassignUser(<?php echo $user['id']; ?>)">Unassign</button>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="user-popup-bg" id="user-edit-popup-bg-<?php echo $user['id']; ?>">
                            <div class="user-popup">
                                <span class="user-popup-close" onclick="closeUserEditPopup(<?php echo $user['id']; ?>)">&times;</span>
                                <h2 style="text-align:center; margin-bottom:1.5rem;">Edit User Details</h2>
                                <form method="post" style="text-align:center;">
                                    <div class="user-popup-details">
                                        <label>Name:</label><input type="text" name="edit_name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                                        <label>Email:</label><input type="email" name="edit_email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                        <label>Phone:</label><input type="text" name="edit_phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                                        <label>Date of Birth:</label><input type="date" name="edit_dob" value="<?php echo htmlspecialchars($user['dob']); ?>">
                                        <label>Aadhaar Card:</label><input type="text" name="edit_aadhaar" value="<?php echo htmlspecialchars($user['aadhaar']); ?>">
                                        <label>PAN Card:</label><input type="text" name="edit_pan" value="<?php echo htmlspecialchars($user['pan']); ?>">
                                        <label>Location:</label><input type="text" name="edit_location" value="<?php echo htmlspecialchars($user['location']); ?>">
                                        <label>Company:</label>
                                        <select name="edit_company_id" required>
                                            <option value="0">Unemployed</option>
                                            <?php foreach ($companies_list as $company): ?>
                                                <option value="<?php echo $company['id']; ?>" <?php echo ($user['company_id'] == $company['id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($company['name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <input type="hidden" name="edit_user_id" value="<?php echo $user['id']; ?>">
                                    <button type="submit" class="btn btn-primary btn-lg" style="padding: 0.3rem 1rem;">Save</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div id="approve-users" class="tab-content">
                    <?php if (empty($pending_users)): ?>
                        <p style="text-align:center;">No users pending approval.</p>
                    <?php else: ?>
                        <?php foreach ($pending_users as $user): ?>
                            <div class="user-card" onclick="showUserApprovePopup(<?php echo $user['id']; ?>)">
                                <div class="user-card-content">
                                    <div>
                                        <div class="user-card-title"><?php echo htmlspecialchars($user['name']); ?></div>
                                        <div class="user-card-email"><?php echo htmlspecialchars($user['email']); ?></div>
                                    </div>
                                    <div class="user-card-company">
                                        Company: <?php echo htmlspecialchars($user['company_name']); ?>
                                    </div>
                                    <div class="user-card-date">
                                        Registered At: <?php echo htmlspecialchars($user['created_at']); ?>
                                    </div>
                                </div>
                                <div class="user-card-action">
                                    <button class="btn btn-primary" style="padding: 0.3rem 1rem;" onclick="event.stopPropagation(); showUserApprovePopup(<?php echo $user['id']; ?>)">View & Approve</button>
                                </div>
                            </div>
                            <div class="user-popup-bg" id="user-approve-popup-bg-<?php echo $user['id']; ?>">
                                <div class="user-popup">
                                    <span class="user-popup-close" onclick="closeUserApprovePopup(<?php echo $user['id']; ?>)">&times;</span>
                                    <h2 style="text-align:center; margin-bottom:1.5rem;">User Details</h2>
                                    <div class="user-popup-details">
                                        <label>Name:</label><span><?php echo htmlspecialchars($user['name']); ?></span>
                                        <label>Email:</label><span><?php echo htmlspecialchars($user['email']); ?></span>
                                        <label>Phone:</label><span><?php echo htmlspecialchars($user['phone']); ?></span>
                                        <label>Date of Birth:</label><span><?php echo htmlspecialchars($user['dob']); ?></span>
                                        <label>Aadhaar Card:</label><span><?php echo htmlspecialchars($user['aadhaar']); ?></span>
                                        <label>PAN Card:</label><span><?php echo htmlspecialchars($user['pan']); ?></span>
                                        <label>Location:</label><span><?php echo htmlspecialchars($user['location']); ?></span>
                                        <label>Company:</label><span><?php echo htmlspecialchars($user['company_name']); ?></span>
                                        <label>Registered At:</label><span><?php echo htmlspecialchars($user['created_at']); ?></span>
                                    </div>
                                    <form method="post" style="text-align:center;">
                                        <input type="hidden" name="approve_id" value="<?php echo $user['id']; ?>">
                                        <button type="submit" class="btn btn-primary btn-lg" style="padding: 0.3rem 1rem;">Approve</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </main>
    <script>
        function showTab(tabId) {
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
            if(tabId === 'users-list') {
                document.querySelector('.tab-btn:nth-child(1)').classList.add('active');
                document.getElementById('users-list').classList.add('active');
            } else {
                document.querySelector('.tab-btn:nth-child(2)').classList.add('active');
                document.getElementById('approve-users').classList.add('active');
            }
        }
        function showUserEditPopup(id) {
            document.getElementById('user-edit-popup-bg-' + id).classList.add('active');
        }
        function closeUserEditPopup(id) {
            document.getElementById('user-edit-popup-bg-' + id).classList.remove('active');
        }
        function showUserApprovePopup(id) {
            document.getElementById('user-approve-popup-bg-' + id).classList.add('active');
        }
        function closeUserApprovePopup(id) {
            document.getElementById('user-approve-popup-bg-' + id).classList.remove('active');
        }
        function unassignUser(id) {
            if (confirm('Are you sure you want to unassign this user from their current company?')) {
                const form = document.createElement('form');
                form.method = 'post';
                form.action = ''; // Submit to the current page
                const unassignInput = document.createElement('input');
                unassignInput.type = 'hidden';
                unassignInput.name = 'unassign_user_id';
                unassignInput.value = id;
                form.appendChild(unassignInput);
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html> 