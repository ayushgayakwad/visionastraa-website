<?php
$required_role = 'super_admin';
include '../auth.php';
require_once '../db.php';

header('Content-Type: application/json');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid ID']);
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM erp_timetable WHERE id = ?');
$stmt->execute([$id]);
$slot = $stmt->fetch();

if (!$slot) {
    http_response_code(404);
    echo json_encode(['error' => 'Timetable slot not found']);
    exit;
}

echo json_encode($slot);
exit;
?>