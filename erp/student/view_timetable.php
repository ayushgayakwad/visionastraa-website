<?php
$required_role = 'student';
include '../auth.php';
require_once '../db.php';

$message = '';
$student_id = $_SESSION['user_id'];

// --- Get current date from server (UTC) and convert to IST ---
$stmt_time = $pdo->query("SELECT NOW() as current_utc");
$current_utc_time = $stmt_time->fetchColumn();
$utc_date_obj = new DateTime($current_utc_time, new DateTimeZone('UTC'));
$utc_date_obj->setTimezone(new DateTimeZone('Asia/Kolkata'));
$current_date = $utc_date_obj->format('Y-m-d');


// Handle feedback submission from the modal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_feedback'])) {
    $timetable_id = $_POST['timetable_id'];
    $rating = $_POST['rating'];
    $comments = $_POST['comments'];

    $stmt = $pdo->prepare('INSERT INTO erp_feedback (student_id, timetable_id, rating, comments) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE rating=VALUES(rating), comments=VALUES(comments)');
    $stmt->execute([$student_id, $timetable_id, $rating, $comments]);
    $message = 'Thank you for your feedback!';
}


// --- Time Slot Definitions ---
$time_slots = [
    'GD_MORNING' => '9:00 AM - 9:30 AM',
    'CLASS_1' => '9:30 AM - 11:00 AM',
    'BREAK' => '11:00 AM - 11:30 AM',
    'CLASS_2' => '11:30 AM - 1:30 PM',
    'LUNCH' => '1:30 PM - 2:30 PM',
    'LAB' => '2:30 PM - 4:30 PM',
    'GD_EVENING' => '4:30 PM - 6:00 PM'
];

// --- Week Selection & Date Calculation ---
$selected_week = $_GET['week'] ?? date('Y-\WW');
$year = (int)substr($selected_week, 0, 4);
$week_num = (int)substr($selected_week, 6, 2);
$date_obj = new DateTime();
$date_obj->setISODate($year, $week_num);
$week_start_date = $date_obj->format('Y-m-d');

$week_dates = [];
$days_of_week = [];
for ($i = 0; $i < 5; $i++) {
    $week_dates[$date_obj->format('l')] = $date_obj->format('Y-m-d');
    $days_of_week[] = $date_obj->format('l');
    $date_obj->modify('+1 day');
}
// --- End Week Logic ---

// Fetch the timetable for the entire week and join with users to get faculty name
$stmt_timetable = $pdo->prepare("
    SELECT tt.id as timetable_id, tt.day_of_week, tt.time_slot, tt.class_name, tt.class_type, u.name as faculty_name
    FROM erp_timetable tt
    LEFT JOIN erp_users u ON tt.faculty_id = u.id
    WHERE tt.week_start_date = ?
    ORDER BY tt.day_of_week, FIELD(tt.time_slot, 'GD_MORNING', 'CLASS_1', 'BREAK', 'CLASS_2', 'LUNCH', 'LAB', 'GD_EVENING')
");
$stmt_timetable->execute([$week_start_date]);
$timetable_data = [];
while($row = $stmt_timetable->fetch()){
    $timetable_data[$row['day_of_week']][$row['time_slot']] = $row;
}

// Fetch existing feedback for the week to show status
$stmt_existing_feedback = $pdo->prepare("
    SELECT timetable_id FROM erp_feedback 
    WHERE student_id = ? AND timetable_id IN 
        (SELECT id FROM erp_timetable WHERE week_start_date = ?)");
$stmt_existing_feedback->execute([$student_id, $week_start_date]);
$existing_feedback = $stmt_existing_feedback->fetchAll(PDO::FETCH_COLUMN, 0);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Weekly Timetable - Student | EV Academy ERP</title>
    <link rel="stylesheet" href="../erp-theme.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>
    <header class="header" id="header">
        <div class="container">
            <div class="header-content" style="display:flex;align-items:center;justify-content:space-between;">
                <a href="dashboard.php" class="logo" style="display:flex;align-items:center;gap:0.5em;">
                    <img src="../logo.png" alt="Logo" style="height: 80px;">
                </a>
                <button class="mobile-menu-btn" onclick="document.body.classList.toggle('nav-open')"><i class="fa-solid fa-bars"></i></button>
                <nav class="nav-desktop">
                    <a href="dashboard.php" class="nav-link">Dashboard</a>
                    <a href="view_timetable.php" class="nav-link">View Timetable</a>
                    <a href="view_attendance.php" class="nav-link">View Attendance</a>
                    <a href="fee_payment.php" class="nav-link">Fee Payment</a>
                    <a href="upload_documents.php" class="nav-link">Upload Documents</a>
                    <a href="submit_assignment.php" class="nav-link">Submit Assignment</a>
                    <a href="../logout.php" class="nav-link">Logout</a>
                </nav>
            </div>
        </div>
    </header>
    <main>
        <div class="container">
            <section class="card">
                <h2 style="color:#3a4a6b;">My Weekly Timetable</h2>
                <h3 style="color:#6b7a99; margin-bottom: 1.5rem;">Week of <?php echo date("F j, Y", strtotime($week_start_date)); ?></h3>
                 <form method="GET" style="display:flex; gap:1rem; align-items:center; flex-wrap:wrap; margin-bottom: 2rem;">
                    <label for="week" style="font-weight:500;">Select Week:</label>
                    <input type="week" name="week" id="week" value="<?php echo htmlspecialchars($selected_week); ?>" class="form-input" style="max-width:200px;">
                    <button type="submit" class="btn btn-primary">View</button>
                </form>

                <div style="overflow-x:auto;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th style="text-align:left;">Day</th>
                                <?php foreach($time_slots as $time_string): ?>
                                    <th><?php echo $time_string; ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($days_of_week as $day): ?>
                                <?php $date = $week_dates[$day]; ?>
                                <tr>
                                    <td style="text-align:left; font-weight:500;"><?php echo date('M d (l)', strtotime($date)); ?></td>
                                    <?php
                                        foreach (array_keys($time_slots) as $slot_key):
                                            $slot_data = $timetable_data[$day][$slot_key] ?? null;

                                            if ($slot_key === 'BREAK' || $slot_key === 'LUNCH') {
                                                 echo '<td style="background-color:#f0f2f8; color:#6b7a99; text-align:center; font-weight:500;">' . ucfirst(strtolower($slot_key)) . '</td>';
                                            } elseif ($slot_data) {
                                                $is_past_or_today = $date <= $current_date;
                                                $is_submitted = in_array($slot_data['timetable_id'], $existing_feedback);
                                                $class_name = htmlspecialchars($slot_data['class_name']);
                                                $class_type = htmlspecialchars($slot_data['class_type']);
                                                $faculty_name = htmlspecialchars($slot_data['faculty_name'] ?? 'N/A');
                                                $timetable_id = $slot_data['timetable_id'];

                                                $onclick = $is_past_or_today && !$is_submitted ? "openFeedbackModal('$class_name', '$faculty_name', $timetable_id)" : '';
                                                $style = $is_past_or_today && !$is_submitted ? 'cursor:pointer;' : '';

                                                echo "<td style='text-align:center; $style' onclick=\"$onclick\">
                                                        <strong>$class_name</strong><br>
                                                        <small>($class_type)</small><br>
                                                        <small style='color:#555;'><em>$faculty_name</em></small>";
                                                
                                                if ($is_past_or_today && !$is_submitted) {
                                                    echo '<br><span style="font-size:0.8em; color:#3a4a6b; text-decoration: underline;">Give Feedback</span>';
                                                }
                                                elseif ($is_submitted) {
                                                    echo '<br><span style="font-size:0.8em; color:green;">Feedback Submitted</span>';
                                                }
                                                echo "</td>";
                                            } else {
                                                echo '<td style="background-color:#f8fafc;"></td>'; // No class scheduled
                                            }
                                        endforeach;
                                    ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </main>

    <div id="feedbackModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:1000; align-items:center; justify-content:center;">
        <div style="background:white; padding:2rem; border-radius:14px; width:90%; max-width:500px;">
            <h3 id="modalClassName" style="color:#3a4a6b; margin-bottom:0.5rem;"></h3>
            <p id="modalFacultyName" style="color:#6b7a99; margin-top:0; margin-bottom:1.5rem;"></p>
            <form method="POST">
                <input type="hidden" name="timetable_id" id="modalTimetableId">
                <div>
                    <label>Rating (1-5):</label>
                    <select name="rating" required class="form-input">
                        <option value="5">5 - Excellent</option>
                        <option value="4">4 - Very Good</option>
                        <option value="3">3 - Good</option>
                        <option value="2">2 - Fair</option>
                        <option value="1">1 - Poor</option>
                    </select>
                </div>
                <div>
                    <label>Comments:</label>
                    <textarea name="comments" class="form-input" rows="3"></textarea>
                </div>
                <div style="display:flex; gap:1rem; justify-content:flex-end; margin-top:1rem;">
                    <button type="button" class="btn" onclick="closeFeedbackModal()">Cancel</button>
                    <button type="submit" name="submit_feedback" class="btn btn-primary">Submit Feedback</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const feedbackModal = document.getElementById('feedbackModal');
        
        function openFeedbackModal(className, facultyName, timetableId) {
            document.getElementById('modalClassName').innerText = className;
            document.getElementById('modalFacultyName').innerText = 'Faculty: ' + facultyName;
            document.getElementById('modalTimetableId').value = timetableId;
            feedbackModal.style.display = 'flex';
        }

        function closeFeedbackModal() {
            feedbackModal.style.display = 'none';
        }
    </script>
</body>
</html>
