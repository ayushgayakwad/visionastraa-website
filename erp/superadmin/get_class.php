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

$stmt = $pdo->prepare('SELECT id, name, faculty_id, created_at FROM erp_classes WHERE id = ?');
$stmt->execute([$id]);
$class = $stmt->fetch();

if (!$class) {
    http_response_code(404);
    echo json_encode(['error' => 'Class not found']);
    exit;
}

echo json_encode($class);
exit;
?>


