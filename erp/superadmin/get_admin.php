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

$stmt = $pdo->prepare('SELECT id, name, email, phone, college_name, dob, created_at FROM erp_users WHERE id = ? AND role = "admin"');
$stmt->execute([$id]);
$admin = $stmt->fetch();

if (!$admin) {
    http_response_code(404);
    echo json_encode(['error' => 'Admin not found']);
    exit;
}

echo json_encode($admin);
exit;
?>


