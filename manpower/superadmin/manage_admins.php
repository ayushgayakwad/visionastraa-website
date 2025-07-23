<?php
$required_role = 'super_admin';
include '../auth.php';
require_once '../db.php';
$message = '';
$stmt = $pdo->prepare('SELECT id, name FROM companies ORDER BY name ASC');
$stmt->execute();
$companies_list = $stmt->fetchAll();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_admin'])) {
    $name = $_POST['name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $dob = $_POST['dob'] ?? null;
    $aadhaar = $_POST['aadhaar'] ?? null;
    $pan = $_POST['pan'] ?? null;
    $location = $_POST['location'] ?? null;
    $email = $_POST['email'] ?? '';
    $company_id = $_POST['company_id'] ?? null;
    $password = $_POST['password'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $bank_name = $_POST['bank_name'] ?? '';
    $bank_account_number = $_POST['bank_account_number'] ?? '';
    $bank_branch = $_POST['bank_branch'] ?? '';
    $bank_ifsc = $_POST['bank_ifsc'] ?? '';
    $bank_account_type = $_POST['bank_account_type'] ?? '';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Invalid email address.';
    } elseif (strlen($password) < 6) {
        $message = 'Password must be at least 6 characters.';
    } elseif (empty($name) || empty($phone)) {
        $message = 'Name and phone are required.';
    } elseif (!$company_id) {
        $message = 'Please select a company.';
    } else {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $message = 'Email already exists.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO users (name, phone, dob, aadhaar, pan, location, email, password, gender, role, approved, created_by, company_id, bank_name, bank_account_number, bank_branch, bank_ifsc, bank_account_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, "admin", 1, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$name, $phone, $dob, $aadhaar, $pan, $location, $email, $hash, $gender, $_SESSION['user_id'], $company_id, $bank_name, $bank_account_number, $bank_branch, $bank_ifsc, $bank_account_type]);
            $message = 'Admin created successfully!';
        }
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_admin_id'])) {
    $edit_id = (int)$_POST['edit_admin_id'];
    $name = $_POST['edit_name'] ?? '';
    $phone = $_POST['edit_phone'] ?? '';
    $dob = $_POST['edit_dob'] ?? null;
    $aadhaar = $_POST['edit_aadhaar'] ?? null;
    $pan = $_POST['edit_pan'] ?? null;
    $location = $_POST['edit_location'] ?? null;
    $email = $_POST['edit_email'] ?? '';
    $company_id = $_POST['edit_company_id'] ?? null;
    $gender = $_POST['edit_gender'] ?? '';
    $bank_name = $_POST['edit_bank_name'] ?? '';
    $bank_account_number = $_POST['edit_bank_account_number'] ?? '';
    $bank_branch = $_POST['edit_bank_branch'] ?? '';
    $bank_ifsc = $_POST['edit_bank_ifsc'] ?? '';
    $bank_account_type = $_POST['edit_bank_account_type'] ?? '';
    $update_sql = 'UPDATE users SET name=?, phone=?, dob=?, aadhaar=?, pan=?, location=?, email=?, company_id=?, gender=?, bank_name=?, bank_account_number=?, bank_branch=?, bank_ifsc=?, bank_account_type=? WHERE id=? AND role="admin"';
    $stmt = $pdo->prepare($update_sql);
    $stmt->execute([$name, $phone, $dob, $aadhaar, $pan, $location, $email, $company_id, $gender, $bank_name, $bank_account_number, $bank_branch, $bank_ifsc, $bank_account_type, $edit_id]);
    $message = 'Admin details updated!';
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['unassign_admin_id'])) {
    $unassign_id = (int)$_POST['unassign_admin_id'];
    $stmt = $pdo->prepare('UPDATE users SET company_id = 0 WHERE id = ? AND role = "admin"');
    $stmt->execute([$unassign_id]);
    $message = 'Admin unassigned from company!';
}
$search = $_GET['search'] ?? '';
$filter_company = $_GET['filter_company'] ?? '';
$filter_gender = $_GET['filter_gender'] ?? '';

$where = ['u.role = "admin"'];
$params = [];
if ($search) {
    $where[] = '(u.name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($filter_company !== '' && $filter_company !== 'all') {
    $where[] = 'u.company_id = ?';
    $params[] = $filter_company;
}
if ($filter_gender !== '' && $filter_gender !== 'all') {
    $where[] = 'u.gender = ?';
    $params[] = $filter_gender;
}
$where_sql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

$stmt = $pdo->prepare("SELECT u.*, c.name AS company_name FROM users u LEFT JOIN companies c ON u.company_id = c.id $where_sql ORDER BY u.created_at DESC");
$stmt->execute($params);
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
        .tab-btns { display: flex; gap: 1rem; margin-bottom: 2rem; flex-wrap: wrap; }
        .tab-btn { padding: 0.5rem 1.5rem; border: none; background: #eee; border-radius: 6px; font-weight: 600; cursor: pointer; transition: background 0.2s; margin-bottom: 0.5rem; }
        .tab-btn.active { background: #2b6cb0; color: #fff; }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        .admin-card { display: flex; align-items: center; justify-content: space-between; background: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.07); padding: 1.2rem 2rem; margin-bottom: 1.5rem; cursor: pointer; transition: box-shadow 0.2s; flex-wrap: wrap; }
        .admin-card-content { display: flex; flex-direction: row; gap: 2rem; align-items: center; flex-wrap: wrap; }
        .admin-card-title { font-size: 1.2rem; font-weight: 600; }
        .admin-card-email { color: #555; }
        .admin-card-date { color: #888; font-size: 0.95rem; }
        .admin-card-company { color: #2b6cb0; font-size: 1rem; font-weight: 500; }
        .admin-card-action { margin-left: auto; }
        .admin-popup-bg { display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.35); z-index: 1000; justify-content: center; align-items: center; }
        .admin-popup-bg.active { display: flex; }
        .admin-popup { background: #fff; border-radius: 14px; padding: 2rem 2.5rem; min-width: 350px; max-width: 95vw; box-shadow: 0 8px 32px rgba(0,0,0,0.18); position: relative; max-height: 90vh; overflow-y: auto; }
        .admin-popup-close { position: absolute; top: 1rem; right: 1rem; font-size: 1.5rem; color: #888; cursor: pointer; }
        .admin-popup-details { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem 2rem; margin-bottom: 1.5rem; }
        .admin-popup-details label { font-weight: 500; color: #333; }
        .admin-popup-details input, .admin-popup-details select { width: 100%; padding: 0.3rem 0.5rem; border-radius: 4px; border: 1px solid #ccc; }
        .admin-popup-details span { color: #444; }
        @media (max-width: 600px) { .admin-popup { padding: 1rem 0.5rem; min-width: 90vw; } .admin-popup-details { grid-template-columns: 1fr; gap: 0.7rem 0; } .tab-btn { width: 100%; min-width: 120px; } .admin-card, .admin-card-content { flex-direction: column; align-items: flex-start; gap: 0.7rem; } }
    </style>
</head>
<body>
    <header class="header" id="header">
        <div class="container">
            <div class="header-content">
                <a href="../../staffing-solution.html" class="logo">
                    <div class="logo-icon">
                        <span>VA</span>
                    </div>
                    <span class="logo-text">Staffing Solutions</span>
                </a>
                <nav class="nav-desktop">
                    <a href="dashboard.php" class="nav-link">Dashboard</a>
                    <a href="manage_users.php" class="nav-link">Manage Users</a>
                    <a href="manage_admins.php" class="nav-link active">Manage Admins</a>
                    <a href="manage_companies.php" class="nav-link">Manage Companies</a>
                    <a href="work_hours.php" class="nav-link">Work Hours</a>
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
                    <form method="get" style="display:flex;flex-wrap:wrap;gap:1rem;margin-bottom:1.5rem;align-items:center;">
                        <input type="text" name="search" placeholder="Search by name, email, phone" value="<?php echo htmlspecialchars($search); ?>" class="form-input" style="min-width:180px;">
                        <select name="filter_company" class="form-input">
                            <option value="all">All Companies</option>
                            <option value="0" <?php if ($filter_company === '0') echo 'selected'; ?>>Unemployed</option>
                            <?php foreach ($companies_list as $company): ?>
                                <option value="<?php echo $company['id']; ?>" <?php if ($filter_company == $company['id']) echo 'selected'; ?>><?php echo htmlspecialchars($company['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <select name="filter_gender" class="form-input">
                            <option value="all">All Genders</option>
                            <option value="Male" <?php if ($filter_gender === 'Male') echo 'selected'; ?>>Male</option>
                            <option value="Female" <?php if ($filter_gender === 'Female') echo 'selected'; ?>>Female</option>
                            <option value="Other" <?php if ($filter_gender === 'Other') echo 'selected'; ?>>Other</option>
                        </select>
                        <button type="submit" class="btn btn-primary" style="padding:0.3rem 1.2rem;">Filter</button>
                        <?php if ($search || ($filter_company !== '' && $filter_company !== 'all') || ($filter_gender !== '' && $filter_gender !== 'all')): ?>
                            <a href="<?php echo strtok($_SERVER['REQUEST_URI'], '?'); ?>" class="btn" style="background:#eee;color:#333;padding:0.3rem 1.2rem;text-decoration:none;">Clear Filters</a>
                        <?php endif; ?>
                    </form>
                    <?php foreach ($admins as $admin): ?>
                        <div class="admin-card" onclick="showAdminPopup(<?php echo $admin['id']; ?>)">
                            <div class="admin-card-content">
                                <div>
                                    <div class="admin-card-title"><?php echo htmlspecialchars($admin['name']); ?></div>
                                    <div class="admin-card-email"><?php echo htmlspecialchars($admin['email']); ?></div>
                                    <div class="admin-card-company">Company: <?php echo htmlspecialchars($admin['company_name'] ?? '—'); ?></div>
                                </div>
                                <div class="admin-card-date">
                                    Registered At: <?php echo htmlspecialchars($admin['created_at']); ?>
                                </div>
                            </div>
                            <div class="admin-card-action">
                                <button class="btn btn-primary" style="padding: 0.3rem 1rem;" onclick="event.stopPropagation(); showAdminPopup(<?php echo $admin['id']; ?>)">Edit</button>
                                <?php if ($admin['company_id'] != 0): ?>
                                    <button class="btn btn-danger" style="padding: 0.3rem 1rem; margin-left:0.5rem;" onclick="event.stopPropagation(); unassignAdmin(<?php echo $admin['id']; ?>)">Unassign</button>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="admin-popup-bg" id="admin-popup-bg-<?php echo $admin['id']; ?>">
                            <div class="admin-popup">
                                <span class="admin-popup-close" onclick="closeAdminPopup(<?php echo $admin['id']; ?>)">&times;</span>
                                <h2 style="text-align:center; margin-bottom:1.5rem;">Edit Admin Details</h2>
                                <form method="post" style="text-align:center;">
                                    <div class="admin-popup-details">
                                        <label>Name:</label><input type="text" name="edit_name" value="<?php echo htmlspecialchars($admin['name'] ?? ''); ?>" required>
                                        <label>Email:</label><input type="email" name="edit_email" value="<?php echo htmlspecialchars($admin['email'] ?? ''); ?>" required>
                                        <label>Phone:</label><input type="text" name="edit_phone" value="<?php echo htmlspecialchars($admin['phone'] ?? ''); ?>" required>
                                        <label>Date of Birth:</label><input type="date" name="edit_dob" value="<?php echo htmlspecialchars($admin['dob'] ?? ''); ?>">
                                        <label>Aadhaar Card:</label><input type="text" name="edit_aadhaar" value="<?php echo htmlspecialchars($admin['aadhaar'] ?? ''); ?>">
                                        <label>PAN Card:</label><input type="text" name="edit_pan" value="<?php echo htmlspecialchars($admin['pan'] ?? ''); ?>">
                                        <label>Location:</label><input type="text" name="edit_location" value="<?php echo htmlspecialchars($admin['location'] ?? ''); ?>">
                                        <label>Gender:</label>
                                        <select name="edit_gender" required>
                                            <option value="">Select Gender</option>
                                            <option value="Male" <?php if (($admin['gender'] ?? '') == 'Male') echo 'selected'; ?>>Male</option>
                                            <option value="Female" <?php if (($admin['gender'] ?? '') == 'Female') echo 'selected'; ?>>Female</option>
                                            <option value="Other" <?php if (($admin['gender'] ?? '') == 'Other') echo 'selected'; ?>>Other</option>
                                        </select>
                                        <label>Company:</label>
                                        <select name="edit_company_id" required>
                                            <option value="0" <?php if (($admin['company_id'] ?? 0) == 0) echo 'selected'; ?>>Unemployed</option>
                                            <?php foreach ($companies_list as $company): ?>
                                                <option value="<?php echo $company['id']; ?>" <?php if (($admin['company_id'] ?? '') == $company['id']) echo 'selected'; ?>><?php echo htmlspecialchars($company['name']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <label>Bank Name:</label>
                                        <select name="edit_bank_name" required>
                                            <option value="">Select Bank</option>
                                            <option value="Axis Bank" <?php if (($admin['bank_name'] ?? '') == 'Axis Bank') echo 'selected'; ?>>Axis Bank</option>
                                            <option value="Bank of Baroda" <?php if (($admin['bank_name'] ?? '') == 'Bank of Baroda') echo 'selected'; ?>>Bank of Baroda</option>
                                            <option value="Bank of India" <?php if (($admin['bank_name'] ?? '') == 'Bank of India') echo 'selected'; ?>>Bank of India</option>
                                            <option value="Bank of Maharashtra" <?php if (($admin['bank_name'] ?? '') == 'Bank of Maharashtra') echo 'selected'; ?>>Bank of Maharashtra</option>
                                            <option value="Canara Bank" <?php if (($admin['bank_name'] ?? '') == 'Canara Bank') echo 'selected'; ?>>Canara Bank</option>
                                            <option value="Central Bank of India" <?php if (($admin['bank_name'] ?? '') == 'Central Bank of India') echo 'selected'; ?>>Central Bank of India</option>
                                            <option value="Federal Bank" <?php if (($admin['bank_name'] ?? '') == 'Federal Bank') echo 'selected'; ?>>Federal Bank</option>
                                            <option value="HDFC Bank" <?php if (($admin['bank_name'] ?? '') == 'HDFC Bank') echo 'selected'; ?>>HDFC Bank</option>
                                            <option value="ICICI Bank" <?php if (($admin['bank_name'] ?? '') == 'ICICI Bank') echo 'selected'; ?>>ICICI Bank</option>
                                            <option value="Indian Bank" <?php if (($admin['bank_name'] ?? '') == 'Indian Bank') echo 'selected'; ?>>Indian Bank</option>
                                            <option value="Indian Overseas Bank (IOB)" <?php if (($admin['bank_name'] ?? '') == 'Indian Overseas Bank (IOB)') echo 'selected'; ?>>Indian Overseas Bank (IOB)</option>
                                            <option value="IDBI Bank" <?php if (($admin['bank_name'] ?? '') == 'IDBI Bank') echo 'selected'; ?>>IDBI Bank</option>
                                            <option value="IDFC First Bank" <?php if (($admin['bank_name'] ?? '') == 'IDFC First Bank') echo 'selected'; ?>>IDFC First Bank</option>
                                            <option value="IndusInd Bank" <?php if (($admin['bank_name'] ?? '') == 'IndusInd Bank') echo 'selected'; ?>>IndusInd Bank</option>
                                            <option value="Jammu & Kashmir Bank" <?php if (($admin['bank_name'] ?? '') == 'Jammu & Kashmir Bank') echo 'selected'; ?>>Jammu & Kashmir Bank</option>
                                            <option value="Karnataka Bank" <?php if (($admin['bank_name'] ?? '') == 'Karnataka Bank') echo 'selected'; ?>>Karnataka Bank</option>
                                            <option value="Kotak Mahindra Bank" <?php if (($admin['bank_name'] ?? '') == 'Kotak Mahindra Bank') echo 'selected'; ?>>Kotak Mahindra Bank</option>
                                            <option value="Punjab National Bank (PNB)" <?php if (($admin['bank_name'] ?? '') == 'Punjab National Bank (PNB)') echo 'selected'; ?>>Punjab National Bank (PNB)</option>
                                            <option value="RBL Bank" <?php if (($admin['bank_name'] ?? '') == 'RBL Bank') echo 'selected'; ?>>RBL Bank</option>
                                            <option value="South Indian Bank" <?php if (($admin['bank_name'] ?? '') == 'South Indian Bank') echo 'selected'; ?>>South Indian Bank</option>
                                            <option value="State Bank of India (SBI)" <?php if (($admin['bank_name'] ?? '') == 'State Bank of India (SBI)') echo 'selected'; ?>>State Bank of India (SBI)</option>
                                            <option value="Tamilnad Mercantile Bank" <?php if (($admin['bank_name'] ?? '') == 'Tamilnad Mercantile Bank') echo 'selected'; ?>>Tamilnad Mercantile Bank</option>
                                            <option value="UCO Bank" <?php if (($admin['bank_name'] ?? '') == 'UCO Bank') echo 'selected'; ?>>UCO Bank</option>
                                            <option value="Union Bank of India" <?php if (($admin['bank_name'] ?? '') == 'Union Bank of India') echo 'selected'; ?>>Union Bank of India</option>
                                            <option value="Yes Bank" <?php if (($admin['bank_name'] ?? '') == 'Yes Bank') echo 'selected'; ?>>Yes Bank</option>
                                            <option value="Others" <?php if (($admin['bank_name'] ?? '') == 'Others') echo 'selected'; ?>>Others</option>
                                        </select>
                                        <label>Bank Account Number:</label><input type="text" name="edit_bank_account_number" value="<?php echo htmlspecialchars($admin['bank_account_number'] ?? ''); ?>" required>
                                        <label>Bank Branch:</label><input type="text" name="edit_bank_branch" value="<?php echo htmlspecialchars($admin['bank_branch'] ?? ''); ?>" required>
                                        <label>Bank IFSC Code:</label><input type="text" name="edit_bank_ifsc" value="<?php echo htmlspecialchars($admin['bank_ifsc'] ?? ''); ?>" required>
                                        <label>Type of Account:</label>
                                        <select name="edit_bank_account_type" required>
                                            <option value="">Select Account Type</option>
                                            <option value="Savings" <?php if (($admin['bank_account_type'] ?? '') == 'Savings') echo 'selected'; ?>>Savings</option>
                                            <option value="Current" <?php if (($admin['bank_account_type'] ?? '') == 'Current') echo 'selected'; ?>>Current</option>
                                        </select>
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
                        <select name="gender" required class="form-input">
                            <option value="">Select Gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                        <select name="company_id" required class="form-input">
                            <option value="">Select Company</option>
                            <?php foreach ($companies_list as $company): ?>
                                <option value="<?php echo $company['id']; ?>"><?php echo htmlspecialchars($company['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <select name="bank_name" required class="form-input">
                            <option value="">Select Bank</option>
                            <option value="Axis Bank">Axis Bank</option>
                            <option value="Bank of Baroda">Bank of Baroda</option>
                            <option value="Bank of India">Bank of India</option>
                            <option value="Bank of Maharashtra">Bank of Maharashtra</option>
                            <option value="Canara Bank">Canara Bank</option>
                            <option value="Central Bank of India">Central Bank of India</option>
                            <option value="Federal Bank">Federal Bank</option>
                            <option value="HDFC Bank">HDFC Bank</option>
                            <option value="ICICI Bank">ICICI Bank</option>
                            <option value="Indian Bank">Indian Bank</option>
                            <option value="Indian Overseas Bank (IOB)">Indian Overseas Bank (IOB)</option>
                            <option value="IDBI Bank">IDBI Bank</option>
                            <option value="IDFC First Bank">IDFC First Bank</option>
                            <option value="IndusInd Bank">IndusInd Bank</option>
                            <option value="Jammu & Kashmir Bank">Jammu & Kashmir Bank</option>
                            <option value="Karnataka Bank">Karnataka Bank</option>
                            <option value="Kotak Mahindra Bank">Kotak Mahindra Bank</option>
                            <option value="Punjab National Bank (PNB)">Punjab National Bank (PNB)</option>
                            <option value="RBL Bank">RBL Bank</option>
                            <option value="South Indian Bank">South Indian Bank</option>
                            <option value="State Bank of India (SBI)">State Bank of India (SBI)</option>
                            <option value="Tamilnad Mercantile Bank">Tamilnad Mercantile Bank</option>
                            <option value="UCO Bank">UCO Bank</option>
                            <option value="Union Bank of India">Union Bank of India</option>
                            <option value="Yes Bank">Yes Bank</option>
                            <option value="Others">Others</option>
                        </select>
                        <input type="text" name="bank_account_number" placeholder="Bank Account Number" required class="form-input">
                        <input type="text" name="bank_branch" placeholder="Bank Branch" required class="form-input">
                        <input type="text" name="bank_ifsc" placeholder="Bank IFSC Code" required class="form-input">
                        <select name="bank_account_type" required class="form-input">
                            <option value="">Select Account Type</option>
                            <option value="Savings">Savings</option>
                            <option value="Current">Current</option>
                        </select>
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
        function unassignAdmin(id) {
            if (confirm('Are you sure you want to unassign this admin from their current company?')) {
                const form = document.createElement('form');
                form.method = 'post';
                form.action = '';
                const unassignInput = document.createElement('input');
                unassignInput.type = 'hidden';
                unassignInput.name = 'unassign_admin_id';
                unassignInput.value = id;
                form.appendChild(unassignInput);
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
    <script>
        window.addEventListener('scroll', function() {
            var header = document.getElementById('header');
            if (window.scrollY > 10) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });
    </script>
</body>
</html> 