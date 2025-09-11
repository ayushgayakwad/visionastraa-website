<?php
// This file can be accessed by both admin and super_admin
session_start();
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'super_admin'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

require_once '../db.php';

header('Content-Type: application/json');

$student_id = isset($_GET['student_id']) ? (int)$_GET['student_id'] : 0;
$week_string = $_GET['week'] ?? date('Y-\WW');

if ($student_id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid Student ID']);
    exit;
}

// --- Week Calculation ---
$year = (int)substr($week_string, 0, 4);
$week_num = (int)substr($week_string, 6, 2);
$date_obj = new DateTime();
$date_obj->setISODate($year, $week_num);
$week_start_date = $date_obj->format('Y-m-d');
$week_end_date = $date_obj->modify('+6 days')->format('Y-m-d');

// --- Data Fetching ---
// Fetch timetable for the week
$stmt_timetable = $pdo->prepare("SELECT id, day_of_week, time_slot, class_name FROM erp_timetable WHERE week_start_date = ? ORDER BY day_of_week, time_slot");
$stmt_timetable->execute([$week_start_date]);
$timetable_data = [];
while($row = $stmt_timetable->fetch()){
    $timetable_data[$row['day_of_week']][$row['time_slot']] = $row['class_name'];
}

// Fetch attendance for the student for that week
$stmt_attendance = $pdo->prepare("
    SELECT tt.day_of_week, tt.time_slot, att.status 
    FROM erp_attendance att
    JOIN erp_timetable tt ON att.timetable_id = tt.id
    WHERE att.student_id = ? AND att.date BETWEEN ? AND ?
");
$stmt_attendance->execute([$student_id, $week_start_date, $week_end_date]);
$attendance_data = [];
while($row = $stmt_attendance->fetch()){
    $attendance_data[$row['day_of_week']][$row['time_slot']] = $row['status'];
}

// Combine data and send as JSON
echo json_encode([
    'timetable' => $timetable_data,
    'attendance' => $attendance_data
]);
exit;