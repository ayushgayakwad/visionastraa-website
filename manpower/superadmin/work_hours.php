<?php
$required_role = 'super_admin';
include '../auth.php';
require_once '../db.php';

$stmt = $pdo->prepare('SELECT u.id, u.name, c.name AS company_name FROM users u LEFT JOIN companies c ON u.company_id = c.id WHERE u.role = "user" ORDER BY u.name');
$stmt->execute();
$users = $stmt->fetchAll();

$selected_user = $_GET['user_id'] ?? ($users[0]['id'] ?? null);
$selected_month = $_GET['month'] ?? date('Y-m');
$work_hours = [];
$company_totals = [];
if ($selected_user) {
    $start = $selected_month . '-01';
    $end = date('Y-m-t', strtotime($start));
    $stmt = $pdo->prepare('SELECT wh.date, wh.hours_json, wh.company_id, c.name AS company_name FROM work_hours wh LEFT JOIN companies c ON wh.company_id = c.id WHERE wh.user_id = ? AND wh.date BETWEEN ? AND ?');
    $stmt->execute([$selected_user, $start, $end]);
    foreach ($stmt->fetchAll() as $row) {
        $entry = [
            'in' => json_decode($row['hours_json'], true)['in'] ?? '',
            'out' => json_decode($row['hours_json'], true)['out'] ?? '',
            'hours' => json_decode($row['hours_json'], true)['hours'] ?? '',
            'company_id' => $row['company_id'],
            'company_name' => $row['company_name']
        ];
        $work_hours[$row['date']][] = $entry;
        $company = $row['company_name'] ?: 'Unknown';
        $hours_val = is_numeric($entry['hours']) ? (float)$entry['hours'] : 0;
        if (!isset($company_totals[$company])) $company_totals[$company] = 0;
        $company_totals[$company] += $hours_val;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Work Hours - Super Admin</title>
    <link rel="stylesheet" href="../../css/vms-styles.css">
    <style>
        .work-hours-form { max-width: 500px; margin: 2rem auto; background: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.07); padding: 2rem; }
        .work-hours-form label { font-weight: 500; margin-top: 0.7rem; display: block; }
        .work-hours-form input, .work-hours-form select { width: 100%; padding: 0.3rem 0.5rem; border-radius: 4px; border: 1px solid #ccc; margin-bottom: 1rem; }
        .calendar-table { width: 100%; border-collapse: collapse; margin-top: 2rem; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.07); }
        .calendar-table th, .calendar-table td { border: 1px solid #eee; padding: 0.5rem; text-align: center; }
        .calendar-table th { background: #f7fafc; }
        .calendar-table td.filled { background: #e6fffa; }
        .company-totals { text-align:right; font-weight:600; margin-bottom:1rem; }
    </style>
</head>
<body>
    <header class="header" id="header">
        <div class="container">
            <div class="header-content">
                <a href="../../staffing-solution.html" class="logo">
                    <div class="logo-icon"><span>VA</span></div>
                    <span class="logo-text">Staffing Solutions</span>
                </a>
                <nav class="nav-desktop">
                    <a href="dashboard.php" class="nav-link">Dashboard</a>
                    <a href="manage_users.php" class="nav-link">Manage Users</a>
                    <a href="manage_admins.php" class="nav-link">Manage Admins</a>
                    <a href="manage_companies.php" class="nav-link">Manage Companies</a>
                    <a href="work_hours.php" class="nav-link active">Work Hours</a>
                    <a href="../logout.php" class="nav-link">Logout</a>
                </nav>
            </div>
        </div>
    </header>
    <main>
        <section class="section">
            <div class="container">
                <h1 class="hero-title" style="text-align:center;">User Work Hours</h1>
                <form method="get" id="user_month_form" style="max-width:500px;margin:2rem auto 1rem auto;display:flex;gap:1rem;align-items:center;">
                    <label for="user_id">User:</label>
                    <select name="user_id" id="user_id_select" required>
                        <?php foreach ($users as $user): ?>
                            <option value="<?php echo $user['id']; ?>" <?php if ($selected_user == $user['id']) echo 'selected'; ?>><?php echo htmlspecialchars($user['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <label for="month">Month:</label>
                    <input type="month" name="month" id="month" value="<?php echo htmlspecialchars($selected_month); ?>">
                    <button type="submit" class="btn btn-primary" style="padding:0.3rem 1rem;">View</button>
                </form>
                <script>
document.getElementById('user_id_select').addEventListener('change', function() {
    document.getElementById('user_month_form').submit();
});
</script>
                <div style="max-width:700px;margin:0 auto;">
                    <?php if ($company_totals): ?>
                        <div class="company-totals">
                            <?php foreach ($company_totals as $company => $total): ?>
                                <div><?php echo htmlspecialchars($company); ?>: <?php echo round($total,2); ?> hours</div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <table class="calendar-table">
                        <tr>
                            <th>Date</th>
                            <th>Company</th>
                            <th>In</th>
                            <th>Out</th>
                            <th>Hours</th>
                        </tr>
                        <?php
                        $days = date('t', strtotime($selected_month . '-01'));
                        for ($d = 1; $d <= $days; $d++) {
                            $date = $selected_month . '-' . str_pad($d, 2, '0', STR_PAD_LEFT);
                            if (!empty($work_hours[$date])) {
                                foreach ($work_hours[$date] as $entry) {
                                    echo '<tr class="filled">';
                                    echo '<td>' . htmlspecialchars($date) . '</td>';
                                    echo '<td>' . htmlspecialchars($entry['company_name'] ?? '') . '</td>';
                                    echo '<td>' . htmlspecialchars($entry['in'] ?? '') . '</td>';
                                    echo '<td>' . htmlspecialchars($entry['out'] ?? '') . '</td>';
                                    echo '<td>' . htmlspecialchars($entry['hours'] ?? '') . '</td>';
                                    echo '</tr>';
                                }
                            } else {
                                echo '<tr>';
                                echo '<td>' . htmlspecialchars($date) . '</td>';
                                echo '<td></td><td></td><td></td><td></td>';
                                echo '</tr>';
                            }
                        }
                        ?>
                    </table>
                </div>
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
    </script>
</body>
</html> 