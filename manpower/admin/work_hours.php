<?php
$required_role = 'admin';
include '../auth.php';
require_once '../db.php';

$stmt = $pdo->prepare('SELECT company_id FROM users WHERE id = ? AND role = "admin"');
$stmt->execute([$_SESSION['user_id']]);
$admin_company_id = $stmt->fetchColumn();
$stmt = $pdo->prepare('SELECT id, name FROM users WHERE role = "user" AND company_id = ?');
$stmt->execute([$admin_company_id]);
$users = $stmt->fetchAll();

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['user_id'], $_POST['date'])) {
    $user_id = (int)$_POST['user_id'];
    $date = $_POST['date'];
    $in = $_POST['in_time'] ?? '';
    $out = $_POST['out_time'] ?? '';
    $hours = $_POST['hours'] ?? '';
    if ($in && $out) {
        $in_dt = DateTime::createFromFormat('H:i', $in);
        $out_dt = DateTime::createFromFormat('H:i', $out);
        if ($in_dt && $out_dt) {
            $interval = $in_dt->diff($out_dt);
            $hours = $interval->h + $interval->i / 60;
            if ($interval->invert) $hours = 24 - $hours;
            $hours = round($hours, 2);
        }
    }
    $hours_json = json_encode([
        'in' => $in,
        'out' => $out,
        'hours' => $hours
    ]);
    $stmt = $pdo->prepare('INSERT INTO work_hours (user_id, company_id, date, hours_json) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE hours_json = VALUES(hours_json)');
    $stmt->execute([$user_id, $admin_company_id, $date, $hours_json]);
    $message = 'Work hours saved!';
}

$selected_user = $_GET['user_id'] ?? ($users[0]['id'] ?? null);
$selected_month = $_GET['month'] ?? date('Y-m');
$work_hours = [];
if ($selected_user) {
    $start = $selected_month . '-01';
    $end = date('Y-m-t', strtotime($start));
    $stmt = $pdo->prepare('SELECT date, hours_json FROM work_hours WHERE user_id = ? AND company_id = ? AND date BETWEEN ? AND ?');
    $stmt->execute([$selected_user, $admin_company_id, $start, $end]);
    foreach ($stmt->fetchAll() as $row) {
        $work_hours[$row['date']] = json_decode($row['hours_json'], true);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Work Hours - Admin</title>
    <link rel="stylesheet" href="../../css/vms-styles.css">
    <style>
        .work-hours-form { max-width: 500px; margin: 2rem auto; background: #fff; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,0.07); padding: 2rem; }
        .work-hours-form label { font-weight: 500; margin-top: 0.7rem; display: block; }
        .work-hours-form input, .work-hours-form select { width: 100%; padding: 0.3rem 0.5rem; border-radius: 4px; border: 1px solid #ccc; margin-bottom: 1rem; }
        .calendar-table { width: 100%; border-collapse: collapse; margin-top: 2rem; background: #fff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.07); }
        .calendar-table th, .calendar-table td { border: 1px solid #eee; padding: 0.5rem; text-align: center; }
        .calendar-table th { background: #f7fafc; }
        .calendar-table td.filled { background: #e6fffa; }
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
                    <a href="work_hours.php" class="nav-link active">Work Hours</a>
                    <a href="../logout.php" class="nav-link">Logout</a>
                </nav>
            </div>
        </div>
    </header>
    <main>
        <section class="section">
            <div class="container">
                <h1 class="hero-title" style="text-align:center;">Record Work Hours</h1>
                <?php if ($message): ?>
                    <div style="color:green;text-align:center;margin-bottom:1rem;">
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>
                <form method="post" class="work-hours-form">
                    <label>User:</label>
                    <select name="user_id" id="user_id_select" required>
                        <?php foreach ($users as $user): ?>
                            <option value="<?php echo $user['id']; ?>" <?php if ($selected_user == $user['id']) echo 'selected'; ?>><?php echo htmlspecialchars($user['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <label>Date:</label>
                    <input type="date" name="date" value="<?php echo date('Y-m-d'); ?>" required>
                    <label>In Time:</label>
                    <input type="time" name="in_time">
                    <label>Out Time:</label>
                    <input type="time" name="out_time">
                    <label>Or Enter Hours Worked Directly:</label>
                    <input type="number" name="hours" min="0" max="24" step="0.1" placeholder="e.g. 8.5">
                    <button type="submit" class="btn btn-primary btn-lg" style="padding: 0.3rem 1rem;">Save</button>
                </form>
                <form method="get" id="user_month_form" style="max-width:500px;margin:2rem auto 1rem auto;display:flex;gap:1rem;align-items:center;">
                    <input type="hidden" name="user_id" id="user_id_hidden" value="<?php echo htmlspecialchars($selected_user); ?>">
                    <label for="month">Month:</label>
                    <input type="month" name="month" id="month" value="<?php echo htmlspecialchars($selected_month); ?>">
                    <button type="submit" class="btn btn-primary" style="padding:0.3rem 1rem;">View</button>
                </form>
                <script>
document.getElementById('user_id_select').addEventListener('change', function() {
    document.getElementById('user_id_hidden').value = this.value;
    document.getElementById('user_month_form').submit();
});
</script>
                <div style="max-width:700px;margin:0 auto;">
                    <?php
                    $days = date('t', strtotime($selected_month . '-01'));
                    $total_hours = 0;
                    for ($d = 1; $d <= $days; $d++) {
                        $date = $selected_month . '-' . str_pad($d, 2, '0', STR_PAD_LEFT);
                        $entry = $work_hours[$date] ?? null;
                        $hours_val = isset($entry['hours']) && is_numeric($entry['hours']) ? (float)$entry['hours'] : 0;
                        $total_hours += $hours_val;
                    }
                    ?>
                    <div style="text-align:right;font-weight:600;margin-bottom:1rem;">Total Hours Worked: <?php echo round($total_hours,2); ?></div>
                    <table class="calendar-table">
                        <tr>
                            <th>Date</th>
                            <th>In</th>
                            <th>Out</th>
                            <th>Hours</th>
                        </tr>
                        <?php
                        for ($d = 1; $d <= $days; $d++) {
                            $date = $selected_month . '-' . str_pad($d, 2, '0', STR_PAD_LEFT);
                            $entry = $work_hours[$date] ?? null;
                            echo '<tr' . ($entry ? ' class="filled"' : '') . '>';
                            echo '<td>' . htmlspecialchars($date) . '</td>';
                            echo '<td>' . htmlspecialchars($entry['in'] ?? '') . '</td>';
                            echo '<td>' . htmlspecialchars($entry['out'] ?? '') . '</td>';
                            echo '<td>' . htmlspecialchars($entry['hours'] ?? '') . '</td>';
                            echo '</tr>';
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