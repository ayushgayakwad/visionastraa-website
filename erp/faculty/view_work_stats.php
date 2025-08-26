<?php
$required_role = 'faculty';
include '../auth.php';
require_once '../db.php';

$time_slot_durations = [
    'GD_MORNING' => 0.5, 'CLASS_1' => 1.5, 'BREAK' => 0.5, 'CLASS_2' => 2.0,
    'LUNCH' => 1.0, 'LAB' => 2.0, 'GD_EVENING' => 1.5
];

$faculty_id = $_SESSION['user_id'];
$selected_date = $_GET['date'] ?? date('Y-m-d');
$selected_month = date('Y-m', strtotime($selected_date));

// Daily
$day_of_week = date('l', strtotime($selected_date));
$week_start_date = date('Y-m-d', strtotime('monday this week', strtotime($selected_date)));
$stmt_daily_expected = $pdo->prepare("SELECT time_slot FROM erp_timetable WHERE faculty_id = ? AND week_start_date = ? AND day_of_week = ?");
$stmt_daily_expected->execute([$faculty_id, $week_start_date, $day_of_week]);
$daily_expected = 0;
foreach ($stmt_daily_expected->fetchAll(PDO::FETCH_COLUMN) as $slot) { $daily_expected += $time_slot_durations[$slot] ?? 0; }
$stmt_daily_actual = $pdo->prepare("SELECT tt.time_slot FROM erp_faculty_logs fl JOIN erp_timetable tt ON fl.timetable_id = tt.id WHERE fl.faculty_id = ? AND fl.date = ? AND fl.status = 'approved'");
$stmt_daily_actual->execute([$faculty_id, $selected_date]);
$daily_actual = 0;
foreach ($stmt_daily_actual->fetchAll(PDO::FETCH_COLUMN) as $slot) { $daily_actual += $time_slot_durations[$slot] ?? 0; }

// Monthly
$month_start = date('Y-m-01', strtotime($selected_date));
$month_end = date('Y-m-t', strtotime($selected_date));
$stmt_monthly_expected = $pdo->prepare("SELECT time_slot FROM erp_timetable WHERE faculty_id = ? AND week_start_date BETWEEN ? AND ?");
$stmt_monthly_expected->execute([$faculty_id, date('Y-m-d', strtotime('monday this week', strtotime($month_start))), $month_end]);
$monthly_expected = 0;
foreach ($stmt_monthly_expected->fetchAll(PDO::FETCH_COLUMN) as $slot) { $monthly_expected += $time_slot_durations[$slot] ?? 0; }
$stmt_monthly_actual = $pdo->prepare("SELECT tt.time_slot FROM erp_faculty_logs fl JOIN erp_timetable tt ON fl.timetable_id = tt.id WHERE fl.faculty_id = ? AND fl.date BETWEEN ? AND ? AND fl.status = 'approved'");
$stmt_monthly_actual->execute([$faculty_id, $month_start, $month_end]);
$monthly_actual = 0;
foreach ($stmt_monthly_actual->fetchAll(PDO::FETCH_COLUMN) as $slot) { $monthly_actual += $time_slot_durations[$slot] ?? 0; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Work Stats - Faculty | EV Academy ERP</title>
    <link rel="stylesheet" href="../erp-theme.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
    <header class="header" id="header">
        <div class="container">
            <div class="header-content" style="display:flex;align-items:center;justify-content:space-between;">
                <a href="dashboard.php" class="logo" style="display:flex;align-items:center;gap:0.5em;"><div class="logo-icon"><span>VA</span></div><span class="logo-text">EV Academy ERP</span></a>
                <button class="mobile-menu-btn" onclick="document.body.classList.toggle('nav-open')"><i class="fa-solid fa-bars"></i></button>
                <nav class="nav-desktop">
                    <a href="dashboard.php" class="nav-link">Dashboard</a>
                    <a href="submit_work_log.php" class="nav-link">Submit Work Log</a>
                    <a href="view_work_logs.php" class="nav-link">View Work Logs</a>
                    <a href="view_work_stats.php" class="nav-link active">My Work Stats</a>
                    <a href="../logout.php" class="nav-link">Logout</a>
                </nav>
            </div>
        </div>
    </header>
    <main>
        <div class="container">
            <section class="card">
                <h2 style="color:#3a4a6b; margin-bottom: 1.5rem;">My Working Hours Statistics</h2>
                <form method="GET" style="display:flex; flex-wrap:wrap; gap:1.5rem; align-items:flex-end; margin-bottom:2rem;">
                    <div>
                        <label for="date" style="font-weight:500;">Select Date:</label>
                        <input type="date" name="date" id="date" value="<?php echo htmlspecialchars($selected_date); ?>" class="form-input">
                    </div>
                    <button type="submit" class="btn btn-primary">View Stats</button>
                </form>

                <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem;">
                    <div class="card">
                        <h3 style="color:#3a4a6b;">Daily Stats for <?php echo date("F j, Y", strtotime($selected_date)); ?></h3>
                        <p><strong>Expected Hours:</strong> <?php echo number_format($daily_expected, 2); ?> hrs</p>
                        <p><strong>Actual Worked Hours:</strong> <?php echo number_format($daily_actual, 2); ?> hrs</p>
                    </div>
                    <div class="card">
                        <h3 style="color:#3a4a6b;">Monthly Stats for <?php echo date("F Y", strtotime($selected_month)); ?></h3>
                        <p><strong>Expected Hours:</strong> <?php echo number_format($monthly_expected, 2); ?> hrs</p>
                        <p><strong>Actual Worked Hours:</strong> <?php echo number_format($monthly_actual, 2); ?> hrs</p>
                    </div>
                </div>
            </section>
        </div>
    </main>
</body>
</html>