<?php
$required_role = 'super_admin';
include '../auth.php';
require_once '../db.php';

header('Content-Type: application/json');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid id']);
    exit;
}

// Updated to fetch both faculty and faculty_admin
$stmt = $pdo->prepare('SELECT id, name, email, phone, dob, assigned_class, college_name, created_at FROM erp_users WHERE id = ? AND role IN ("faculty", "faculty_admin")');
$stmt->execute([$id]);
$faculty = $stmt->fetch();

if (!$faculty) {
    http_response_code(404);
    echo json_encode(['error' => 'Faculty user not found']);
    exit;
}

echo json_encode($faculty);
exit;
?>
