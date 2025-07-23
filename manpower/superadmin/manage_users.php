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
    $gender = $_POST['edit_gender'] ?? '';
    $bank_name = $_POST['edit_bank_name'] ?? '';
    $bank_account_number = $_POST['edit_bank_account_number'] ?? '';
    $bank_branch = $_POST['edit_bank_branch'] ?? '';
    $bank_ifsc = $_POST['edit_bank_ifsc'] ?? '';
    $bank_account_type = $_POST['edit_bank_account_type'] ?? '';
    $update_sql = 'UPDATE users SET name=?, phone=?, dob=?, aadhaar=?, pan=?, location=?, email=?, company_id=?, gender=?, bank_name=?, bank_account_number=?, bank_branch=?, bank_ifsc=?, bank_account_type=? WHERE id=? AND role="user"';
    $stmt = $pdo->prepare($update_sql);
    $stmt->execute([$name, $phone, $dob, $aadhaar, $pan, $location, $email, $company_id, $gender, $bank_name, $bank_account_number, $bank_branch, $bank_ifsc, $bank_account_type, $edit_id]);
    $message = 'User details updated!';
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_user'])) {
    $name = $_POST['name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $dob = $_POST['dob'] ?? null;
    $aadhaar = $_POST['aadhaar'] ?? null;
    $pan = $_POST['pan'] ?? null;
    $location = $_POST['location'] ?? null;
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $company_id = $_POST['company_id'] ?? null;
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
    } elseif ($company_id === null || $company_id === '') {
        $message = 'Please select a company or Unemployed.';
    } else {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $message = 'Email already exists.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO users (name, phone, dob, aadhaar, pan, location, email, password, gender, role, approved, created_by, company_id, bank_name, bank_account_number, bank_branch, bank_ifsc, bank_account_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, "user", 1, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([$name, $phone, $dob, $aadhaar, $pan, $location, $email, $hash, $gender, $_SESSION['user_id'], $company_id, $bank_name, $bank_account_number, $bank_branch, $bank_ifsc, $bank_account_type]);
            $message = 'User created successfully!';
        }
    }
}
$search = $_GET['search'] ?? '';
$filter_company = $_GET['filter_company'] ?? '';
$filter_gender = $_GET['filter_gender'] ?? '';
$filter_status = $_GET['filter_status'] ?? '';

$where = ['u.role = "user"'];
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
if ($filter_status !== '' && $filter_status !== 'all') {
    if ($filter_status === 'approved') {
        $where[] = 'u.approved = 1';
    } elseif ($filter_status === 'pending') {
        $where[] = 'u.approved = 0';
    }
}
$where_sql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

$stmt = $pdo->prepare("SELECT u.id, u.name, u.phone, u.dob, u.aadhaar, u.pan, u.location, u.email, u.gender, u.created_at, u.approved, u.company_id, u.bank_name, u.bank_account_number, u.bank_branch, u.bank_ifsc, u.bank_account_type, c.name AS company_name FROM users u LEFT JOIN companies c ON u.company_id = c.id $where_sql ORDER BY u.created_at DESC");
$stmt->execute($params);
$all_users = $stmt->fetchAll();
$stmt = $pdo->prepare('SELECT u.id, u.name, u.phone, u.dob, u.aadhaar, u.pan, u.location, u.email, u.gender, u.created_at, u.approved, u.company_id, u.bank_name, u.bank_account_number, u.bank_branch, u.bank_ifsc, u.bank_account_type, c.name AS company_name FROM users u LEFT JOIN companies c ON u.company_id = c.id WHERE u.role = "user" AND u.approved = 0');
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
        .user-popup { background: #fff; border-radius: 14px; padding: 2rem 2.5rem; min-width: 350px; max-width: 95vw; box-shadow: 0 8px 32px rgba(0,0,0,0.18); position: relative; max-height: 90vh; overflow-y: auto; }
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
                <a href="../../staffing-solution.html" class="logo">
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
                    <a href="work_hours.php" class="nav-link">Work Hours</a>
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
                    <button class="tab-btn" onclick="showTab('create-user')">Create User</button>
                </div>
                <div id="users-list" class="tab-content active">
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
                        <select name="filter_status" class="form-input">
                            <option value="all">All Statuses</option>
                            <option value="approved" <?php if ($filter_status === 'approved') echo 'selected'; ?>>Approved</option>
                            <option value="pending" <?php if ($filter_status === 'pending') echo 'selected'; ?>>Pending</option>
                        </select>
                        <button type="submit" class="btn btn-primary" style="padding:0.3rem 1.2rem;">Filter</button>
                        <?php if ($search || ($filter_company !== '' && $filter_company !== 'all') || ($filter_gender !== '' && $filter_gender !== 'all') || ($filter_status !== '' && $filter_status !== 'all')): ?>
                            <a href="<?php echo strtok($_SERVER['REQUEST_URI'], '?'); ?>" class="btn" style="background:#eee;color:#333;padding:0.3rem 1.2rem;text-decoration:none;">Clear Filters</a>
                        <?php endif; ?>
                    </form>
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
                                        <label>Name:</label><input type="text" name="edit_name" value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" required>
                                        <label>Email:</label><input type="email" name="edit_email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required>
                                        <label>Phone:</label><input type="text" name="edit_phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" required>
                                        <label>Date of Birth:</label><input type="date" name="edit_dob" value="<?php echo htmlspecialchars($user['dob'] ?? ''); ?>">
                                        <label>Aadhaar Card:</label><input type="text" name="edit_aadhaar" value="<?php echo htmlspecialchars($user['aadhaar'] ?? ''); ?>">
                                        <label>PAN Card:</label><input type="text" name="edit_pan" value="<?php echo htmlspecialchars($user['pan'] ?? ''); ?>">
                                        <label>Location:</label><input type="text" name="edit_location" value="<?php echo htmlspecialchars($user['location'] ?? ''); ?>">
                                        <label>Gender:</label>
                                        <select name="edit_gender" required>
                                            <option value="">Select Gender</option>
                                            <option value="Male" <?php if (($user['gender'] ?? '') == 'Male') echo 'selected'; ?>>Male</option>
                                            <option value="Female" <?php if (($user['gender'] ?? '') == 'Female') echo 'selected'; ?>>Female</option>
                                            <option value="Other" <?php if (($user['gender'] ?? '') == 'Other') echo 'selected'; ?>>Other</option>
                                        </select>
                                        <label>Company:</label>
                                        <select name="edit_company_id" required>
                                            <option value="0">Unemployed</option>
                                            <?php foreach ($companies_list as $company): ?>
                                                <option value="<?php echo $company['id']; ?>" <?php echo (($user['company_id'] ?? '') == $company['id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($company['name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <label>Bank Name:</label>
                                        <select name="edit_bank_name" required>
                                            <option value="">Select Bank</option>
                                            <option value="Axis Bank" <?php if (($user['bank_name'] ?? '') == 'Axis Bank') echo 'selected'; ?>>Axis Bank</option>
                                            <option value="Bank of Baroda" <?php if (($user['bank_name'] ?? '') == 'Bank of Baroda') echo 'selected'; ?>>Bank of Baroda</option>
                                            <option value="Bank of India" <?php if (($user['bank_name'] ?? '') == 'Bank of India') echo 'selected'; ?>>Bank of India</option>
                                            <option value="Bank of Maharashtra" <?php if (($user['bank_name'] ?? '') == 'Bank of Maharashtra') echo 'selected'; ?>>Bank of Maharashtra</option>
                                            <option value="Canara Bank" <?php if (($user['bank_name'] ?? '') == 'Canara Bank') echo 'selected'; ?>>Canara Bank</option>
                                            <option value="Central Bank of India" <?php if (($user['bank_name'] ?? '') == 'Central Bank of India') echo 'selected'; ?>>Central Bank of India</option>
                                            <option value="Federal Bank" <?php if (($user['bank_name'] ?? '') == 'Federal Bank') echo 'selected'; ?>>Federal Bank</option>
                                            <option value="HDFC Bank" <?php if (($user['bank_name'] ?? '') == 'HDFC Bank') echo 'selected'; ?>>HDFC Bank</option>
                                            <option value="ICICI Bank" <?php if (($user['bank_name'] ?? '') == 'ICICI Bank') echo 'selected'; ?>>ICICI Bank</option>
                                            <option value="Indian Bank" <?php if (($user['bank_name'] ?? '') == 'Indian Bank') echo 'selected'; ?>>Indian Bank</option>
                                            <option value="Indian Overseas Bank (IOB)" <?php if (($user['bank_name'] ?? '') == 'Indian Overseas Bank (IOB)') echo 'selected'; ?>>Indian Overseas Bank (IOB)</option>
                                            <option value="IDBI Bank" <?php if (($user['bank_name'] ?? '') == 'IDBI Bank') echo 'selected'; ?>>IDBI Bank</option>
                                            <option value="IDFC First Bank" <?php if (($user['bank_name'] ?? '') == 'IDFC First Bank') echo 'selected'; ?>>IDFC First Bank</option>
                                            <option value="IndusInd Bank" <?php if (($user['bank_name'] ?? '') == 'IndusInd Bank') echo 'selected'; ?>>IndusInd Bank</option>
                                            <option value="Jammu & Kashmir Bank" <?php if (($user['bank_name'] ?? '') == 'Jammu & Kashmir Bank') echo 'selected'; ?>>Jammu & Kashmir Bank</option>
                                            <option value="Karnataka Bank" <?php if (($user['bank_name'] ?? '') == 'Karnataka Bank') echo 'selected'; ?>>Karnataka Bank</option>
                                            <option value="Kotak Mahindra Bank" <?php if (($user['bank_name'] ?? '') == 'Kotak Mahindra Bank') echo 'selected'; ?>>Kotak Mahindra Bank</option>
                                            <option value="Punjab National Bank (PNB)" <?php if (($user['bank_name'] ?? '') == 'Punjab National Bank (PNB)') echo 'selected'; ?>>Punjab National Bank (PNB)</option>
                                            <option value="RBL Bank" <?php if (($user['bank_name'] ?? '') == 'RBL Bank') echo 'selected'; ?>>RBL Bank</option>
                                            <option value="South Indian Bank" <?php if (($user['bank_name'] ?? '') == 'South Indian Bank') echo 'selected'; ?>>South Indian Bank</option>
                                            <option value="State Bank of India (SBI)" <?php if (($user['bank_name'] ?? '') == 'State Bank of India (SBI)') echo 'selected'; ?>>State Bank of India (SBI)</option>
                                            <option value="Tamilnad Mercantile Bank" <?php if (($user['bank_name'] ?? '') == 'Tamilnad Mercantile Bank') echo 'selected'; ?>>Tamilnad Mercantile Bank</option>
                                            <option value="UCO Bank" <?php if (($user['bank_name'] ?? '') == 'UCO Bank') echo 'selected'; ?>>UCO Bank</option>
                                            <option value="Union Bank of India" <?php if (($user['bank_name'] ?? '') == 'Union Bank of India') echo 'selected'; ?>>Union Bank of India</option>
                                            <option value="Yes Bank" <?php if (($user['bank_name'] ?? '') == 'Yes Bank') echo 'selected'; ?>>Yes Bank</option>
                                            <option value="Others" <?php if (($user['bank_name'] ?? '') == 'Others') echo 'selected'; ?>>Others</option>
                                        </select>
                                        <label>Bank Account Number:</label><input type="text" name="edit_bank_account_number" value="<?php echo htmlspecialchars($user['bank_account_number'] ?? ''); ?>" required>
                                        <label>Bank Branch:</label><input type="text" name="edit_bank_branch" value="<?php echo htmlspecialchars($user['bank_branch'] ?? ''); ?>" required>
                                        <label>Bank IFSC Code:</label><input type="text" name="edit_bank_ifsc" value="<?php echo htmlspecialchars($user['bank_ifsc'] ?? ''); ?>" required>
                                        <label>Type of Account:</label>
                                        <select name="edit_bank_account_type" required>
                                            <option value="">Select Account Type</option>
                                            <option value="Savings" <?php if (($user['bank_account_type'] ?? '') == 'Savings') echo 'selected'; ?>>Savings</option>
                                            <option value="Current" <?php if (($user['bank_account_type'] ?? '') == 'Current') echo 'selected'; ?>>Current</option>
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
                                        <label>Gender:</label><span><?php echo htmlspecialchars($user['gender']); ?></span>
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
                <div id="create-user" class="tab-content">
                    <form method="post" style="max-width:400px;margin:0 auto;display:flex;flex-direction:column;gap:1rem;">
                        <input type="hidden" name="create_user" value="1">
                        <input type="text" name="name" placeholder="Full Name" required class="form-input">
                        <input type="text" name="phone" placeholder="Phone Number" required class="form-input">
                        <input type="date" name="dob" placeholder="Date of Birth" class="form-input">
                        <select name="gender" required class="form-input">
                            <option value="">Select Gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                        <input type="text" name="aadhaar" placeholder="Aadhaar Card" class="form-input">
                        <input type="text" name="pan" placeholder="PAN Card" class="form-input">
                        <input type="text" name="location" placeholder="Location" class="form-input">
                        <select name="company_id" required class="form-input">
                            <option value="">Select Company or Unemployed</option>
                            <option value="0">Unemployed</option>
                            <?php foreach ($companies_list as $company): ?>
                                <option value="<?php echo $company['id']; ?>"><?php echo htmlspecialchars($company['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <label>Bank Name:</label>
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
                        <input type="email" name="email" placeholder="User Email" required class="form-input">
                        <input type="password" name="password" placeholder="Password" required class="form-input">
                        <button type="submit" class="btn btn-primary btn-lg" style="padding: 0.3rem 1rem;">Create User</button>
                    </form>
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
            } else if(tabId === 'approve-users') {
                document.querySelector('.tab-btn:nth-child(2)').classList.add('active');
                document.getElementById('approve-users').classList.add('active');
            } else {
                document.querySelector('.tab-btn:nth-child(3)').classList.add('active');
                document.getElementById('create-user').classList.add('active');
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