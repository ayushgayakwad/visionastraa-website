<?php
$required_role = 'super_admin';
include '../auth.php';
require_once '../db.php';
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_company'])) {
    $name = trim($_POST['company_name'] ?? '');
    $location = trim($_POST['company_location'] ?? '');
    if ($name && $location) {
        $stmt = $pdo->prepare('INSERT INTO companies (name, location) VALUES (?, ?)');
        $stmt->execute([$name, $location]);
        $message = 'Company added successfully!';
    } else {
        $message = 'Please provide both name and location.';
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_company_id'])) {
    $id = (int)$_POST['edit_company_id'];
    $name = trim($_POST['edit_company_name'] ?? '');
    $location = trim($_POST['edit_company_location'] ?? '');
    if ($name && $location) {
        $stmt = $pdo->prepare('UPDATE companies SET name=?, location=? WHERE id=?');
        $stmt->execute([$name, $location, $id]);
        $message = 'Company updated!';
    } else {
        $message = 'Please provide both name and location.';
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_company_id'])) {
    $id = (int)$_POST['delete_company_id'];
    $stmt = $pdo->prepare('DELETE FROM companies WHERE id=?');
    $stmt->execute([$id]);
    $message = 'Company deleted!';
}
$search = $_GET['search'] ?? '';
$where = [];
$params = [];
if ($search) {
    $where[] = '(name LIKE ? OR location LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
}
$where_sql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';
$stmt = $pdo->prepare("SELECT * FROM companies $where_sql ORDER BY created_at DESC");
$stmt->execute($params);
$companies = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Companies - Super Admin</title>
    <link rel="stylesheet" href="../../css/vms-styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .companies-container { max-width: 700px; margin: 2rem auto; }
        .tab-btns { display: flex; gap: 1rem; margin-bottom: 2rem; flex-wrap: wrap; }
        .tab-btn { padding: 0.5rem 1.5rem; border: none; background: #eee; border-radius: 6px; font-weight: 600; cursor: pointer; transition: background 0.2s; margin-bottom: 0.5rem; }
        .tab-btn.active { background: #2b6cb0; color: #fff; }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        .company-card { display: flex; align-items: center; justify-content: space-between; background: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.07); padding: 1.2rem 2rem; margin-bottom: 1.5rem; }
        .company-info { display: flex; flex-direction: column; gap: 0.3rem; }
        .company-title { font-size: 1.1rem; font-weight: 600; }
        .company-location { color: #555; }
        .company-id { color: #888; font-size: 0.95rem; }
        .company-actions { display: flex; gap: 0.7rem; }
        .company-popup-bg { display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.35); z-index: 1000; justify-content: center; align-items: center; }
        .company-popup-bg.active { display: flex; }
        .company-popup { background: #fff; border-radius: 14px; padding: 2rem 2.5rem; min-width: 350px; max-width: 95vw; box-shadow: 0 8px 32px rgba(0,0,0,0.18); position: relative; }
        .company-popup-close { position: absolute; top: 1rem; right: 1rem; font-size: 1.5rem; color: #888; cursor: pointer; }
        .company-popup-details { display: grid; grid-template-columns: 1fr; gap: 1rem 0; margin-bottom: 1.5rem; }
        .company-popup-details label { font-weight: 500; color: #333; }
        .company-popup-details input { width: 100%; padding: 0.3rem 0.5rem; border-radius: 4px; border: 1px solid #ccc; }
        @media (max-width: 600px) {
            .companies-container { padding: 0 0.5rem; }
            .company-card { flex-direction: column; align-items: flex-start; gap: 0.7rem; padding: 1rem 0.5rem; }
            .company-popup { padding: 1rem 0.5rem; min-width: 90vw; }
        }
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
                    <a href="manage_admins.php" class="nav-link">Manage Admins</a>
                    <a href="manage_companies.php" class="nav-link active">Manage Companies</a>
                    <a href="work_hours.php" class="nav-link">Work Hours</a>
                    <a href="../logout.php" class="nav-link">Logout</a>
                </nav>
            </div>
        </div>
    </header>
    <main>
        <section class="section">
            <div class="container">
                <h1 class="hero-title" style="text-align:center;">Manage Companies</h1>
                <?php if ($message): ?>
                    <div style="color:green;text-align:center;margin-bottom:1rem;">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>
                <div class="tab-btns">
                    <button class="tab-btn active" onclick="showTab('companies-list')">All Companies</button>
                    <button class="tab-btn" onclick="showTab('add-company')">Add Company</button>
                </div>
                <div id="companies-list" class="tab-content active">
                    <form method="get" style="display:flex;flex-wrap:wrap;gap:1rem;margin-bottom:1.5rem;align-items:center;">
                        <input type="text" name="search" placeholder="Search by name or location" value="<?php echo htmlspecialchars($search); ?>" class="form-input" style="min-width:180px;">
                        <button type="submit" class="btn btn-primary" style="padding:0.3rem 1.2rem;">Search</button>
                        <?php if ($search): ?>
                            <a href="<?php echo strtok($_SERVER['REQUEST_URI'], '?'); ?>" class="btn" style="background:#eee;color:#333;padding:0.3rem 1.2rem;text-decoration:none;">Clear Search</a>
                        <?php endif; ?>
                    </form>
                    <?php foreach ($companies as $company): ?>
                        <div class="company-card">
                            <div class="company-info">
                                <div class="company-title"><?php echo htmlspecialchars($company['name']); ?></div>
                                <div class="company-location">Location: <?php echo htmlspecialchars($company['location']); ?></div>
                                <div class="company-id">ID: <?php echo $company['id']; ?></div>
                            </div>
                            <div class="company-actions">
                                <button class="btn btn-primary" style="padding: 0.3rem 1rem;" onclick="showEditPopup(<?php echo $company['id']; ?>, '<?php echo htmlspecialchars(addslashes($company['name'])); ?>', '<?php echo htmlspecialchars(addslashes($company['location'])); ?>')">Edit</button>
                                <form method="post" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this company?');">
                                    <input type="hidden" name="delete_company_id" value="<?php echo $company['id']; ?>">
                                    <button type="submit" class="btn btn-primary" style="padding: 0.3rem 1rem; background:#e53e3e; border:none;">Delete</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div id="add-company" class="tab-content">
                    <form method="post" style="max-width:400px;margin:0 auto 2rem auto;display:flex;flex-direction:column;gap:1rem;">
                        <input type="hidden" name="add_company" value="1">
                        <input type="text" name="company_name" placeholder="Company Name" required class="form-input">
                        <input type="text" name="company_location" placeholder="Location" required class="form-input">
                        <button type="submit" class="btn btn-primary btn-lg" style="padding: 0.3rem 1rem;">Add Company</button>
                    </form>
                </div>
            </div>
            <div class="company-popup-bg" id="edit-popup-bg">
                <div class="company-popup">
                    <span class="company-popup-close" onclick="closeEditPopup()">&times;</span>
                    <h2 style="text-align:center; margin-bottom:1.5rem;">Edit Company</h2>
                    <form method="post" style="text-align:center;">
                        <div class="company-popup-details">
                            <label>Company Name:</label>
                            <input type="text" name="edit_company_name" id="edit_company_name" required>
                            <label>Location:</label>
                            <input type="text" name="edit_company_location" id="edit_company_location" required>
                        </div>
                        <input type="hidden" name="edit_company_id" id="edit_company_id">
                        <button type="submit" class="btn btn-primary btn-lg" style="padding: 0.3rem 1rem;">Save</button>
                    </form>
                </div>
            </div>
        </section>
    </main>
    <script>
        function showTab(tabId) {
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(tab => tab.classList.remove('active'));
            if(tabId === 'companies-list') {
                document.querySelector('.tab-btn:nth-child(1)').classList.add('active');
                document.getElementById('companies-list').classList.add('active');
            } else {
                document.querySelector('.tab-btn:nth-child(2)').classList.add('active');
                document.getElementById('add-company').classList.add('active');
            }
        }
        function showEditPopup(id, name, location) {
            document.getElementById('edit_company_id').value = id;
            document.getElementById('edit_company_name').value = name;
            document.getElementById('edit_company_location').value = location;
            document.getElementById('edit-popup-bg').classList.add('active');
        }
        function closeEditPopup() {
            document.getElementById('edit-popup-bg').classList.remove('active');
        }
    </script>
</body>
</html> 