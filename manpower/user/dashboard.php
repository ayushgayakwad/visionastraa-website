<?php
$required_role = 'user';
include '../auth.php';
require_once '../db.php';
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare('SELECT u.name, u.email, u.phone, u.dob, u.aadhaar, u.pan, u.location, u.created_at, u.gender, u.company_id, c.name AS company_name, u.bank_name, u.bank_account_number, u.bank_branch, u.bank_ifsc, u.bank_account_type FROM users u LEFT JOIN companies c ON u.company_id = c.id WHERE u.id = ?');
$stmt->execute([$user_id]);
$user = $stmt->fetch();
$company_hours = [];
$stmt = $pdo->prepare('SELECT wh.company_id, c.name AS company_name, SUM(CAST(JSON_UNQUOTE(JSON_EXTRACT(wh.hours_json, "$.hours")) AS DECIMAL(5,2))) AS total_hours FROM work_hours wh LEFT JOIN companies c ON wh.company_id = c.id WHERE wh.user_id = ? GROUP BY wh.company_id');
$stmt->execute([$user_id]);
foreach ($stmt->fetchAll() as $row) {
    $company = $row['company_id'] == 0 ? 'Unemployed' : ($row['company_name'] ?: 'Unknown');
    $company_hours[$company] = round($row['total_hours'], 2);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - VisionAstraa</title>
    <link rel="stylesheet" href="../../css/vms-styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .dashboard-container {
            max-width: 600px;
            margin: 2rem auto 0 auto;
        }
        .tab-btns { display: flex; gap: 1rem; margin-bottom: 2rem; justify-content: center; }
        .tab-btn { padding: 0.5rem 1.5rem; border: none; background: #eee; border-radius: 6px; font-weight: 600; cursor: pointer; transition: background 0.2s; }
        .tab-btn.active { background: #2b6cb0; color: #fff; }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        .profile-card, .account-card, .id-card {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
            padding: 2rem 2.5rem;
            margin-bottom: 2rem;
        }
        .profile-title {
            text-align: center;
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
        }
        .profile-grid, .account-grid, .id-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem 2rem;
        }
        .profile-label, .account-label, .id-label {
            font-weight: 500;
            color: #333;
        }
        .profile-value, .account-value, .id-value {
            color: #444;
        }
        @media (max-width: 600px) {
            .profile-card, .account-card, .id-card { padding: 1rem 0.5rem; }
            .profile-grid, .account-grid, .id-grid { grid-template-columns: 1fr; }
        }
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
                    <a href="dashboard.php" class="nav-link active">Dashboard</a>
                    <a href="../logout.php" class="nav-link">Logout</a>
                </nav>
            </div>
        </div>
    </header>
    <main>
        <section class="hero">
            <div class="container dashboard-container">
                <h1 class="hero-title" style="text-align:center; margin-bottom:2rem;">Welcome, <?php echo htmlspecialchars($user['name']); ?>!</h1>
                <div class="tab-btns">
                    <button class="tab-btn active" onclick="showTab('profile-tab')">Profile</button>
                    <button class="tab-btn" onclick="showTab('account-tab')">Account</button>
                    <button class="tab-btn" onclick="showTab('id-tab')">ID Info</button>
                    <button class="tab-btn" onclick="showTab('bank-tab')">Bank Details</button>
                </div>
                <div id="profile-tab" class="tab-content active">
                    <div class="profile-card">
                        <div class="profile-title">Personal Information</div>
                        <div class="profile-grid">
                            <div class="profile-label">Name:</div><div class="profile-value"><?php echo htmlspecialchars($user['name']); ?></div>
                            <div class="profile-label">Gender:</div><div class="profile-value"><?php echo htmlspecialchars($user['gender']); ?></div>
                            <div class="profile-label">Phone:</div><div class="profile-value"><?php echo htmlspecialchars($user['phone']); ?></div>
                            <div class="profile-label">Date of Birth:</div><div class="profile-value"><?php echo htmlspecialchars($user['dob']); ?></div>
                            <div class="profile-label">Location:</div><div class="profile-value"><?php echo htmlspecialchars($user['location']); ?></div>
                            <div class="profile-label">Company:</div><div class="profile-value"><?php echo $user['company_id'] == 0 ? 'Unemployed' : htmlspecialchars($user['company_name']); ?></div>
                        </div>
                        <?php if ($company_hours): ?>
                        <div style="margin-top:1.5rem;">
                            <div style="font-weight:600;">Total Working Hours by Company:</div>
                            <ul style="margin:0.5rem 0 0 1.2rem;">
                                <?php foreach ($company_hours as $cname => $hours): ?>
                                    <li><?php echo htmlspecialchars($cname); ?>: <?php echo $hours; ?> hours</li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div id="account-tab" class="tab-content">
                    <div class="account-card">
                        <div class="profile-title">Account Information</div>
                        <div class="account-grid">
                            <div class="account-label">Email:</div><div class="account-value"><?php echo htmlspecialchars($user['email']); ?></div>
                            <div class="account-label">Registered At:</div><div class="account-value"><?php echo htmlspecialchars($user['created_at']); ?></div>
                        </div>
                    </div>
                </div>
                <div id="id-tab" class="tab-content">
                    <div class="id-card">
                        <div class="profile-title">ID Information</div>
                        <div class="id-grid">
                            <div class="id-label">Aadhaar Card:</div><div class="id-value"><?php echo htmlspecialchars($user['aadhaar']); ?></div>
                            <div class="id-label">PAN Card:</div><div class="id-value"><?php echo htmlspecialchars($user['pan']); ?></div>
                        </div>
                    </div>
                </div>
                <div id="bank-tab" class="tab-content">
                    <div class="id-card">
                        <div class="profile-title">Bank Details</div>
                        <div class="id-grid">
                            <div class="id-label">Bank Name:</div><div class="id-value"><?php echo htmlspecialchars($user['bank_name']); ?></div>
                            <div class="id-label">Account Number:</div><div class="id-value"><?php echo htmlspecialchars($user['bank_account_number']); ?></div>
                            <div class="id-label">Branch:</div><div class="id-value"><?php echo htmlspecialchars($user['bank_branch']); ?></div>
                            <div class="id-label">IFSC Code:</div><div class="id-value"><?php echo htmlspecialchars($user['bank_ifsc']); ?></div>
                            <div class="id-label">Account Type:</div><div class="id-value"><?php echo htmlspecialchars($user['bank_account_type']); ?></div>
                        </div>
                    </div>
                </div>
                <p style="text-align:center; margin-top:2rem;">
                    <a href="../logout.php" class="btn btn-primary" style="padding: 0.3rem 1rem;">Logout</a>
                </p>
            </div>
        </section>
    </main>
    <script>
        window.addEventListener('scroll', function() {
            var header = document.getElementById('header');
            if (window.scrollY > 10) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });
        function showTab(tabId) {
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
            if(tabId === 'profile-tab') {
                document.querySelector('.tab-btn:nth-child(1)').classList.add('active');
                document.getElementById('profile-tab').classList.add('active');
            } else if(tabId === 'account-tab') {
                document.querySelector('.tab-btn:nth-child(2)').classList.add('active');
                document.getElementById('account-tab').classList.add('active');
            } else if(tabId === 'id-tab') {
                document.querySelector('.tab-btn:nth-child(3)').classList.add('active');
                document.getElementById('id-tab').classList.add('active');
            } else if(tabId === 'bank-tab') {
                document.querySelector('.tab-btn:nth-child(4)').classList.add('active');
                document.getElementById('bank-tab').classList.add('active');
            }
        }
    </script>
</body>
</html> 