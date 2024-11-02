<?php

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['full_name'];
    $email = $_POST['email'];
    $uid = $_POST['uid'];
    $phone = $_POST['phone'];
    $role = $_POST['role'];
    $state = $_POST['state'];
    $college = $_POST['college'];
    $degree = $_POST['degree'];
    $specialization = $_POST['specialization'];
    $graduation = $_POST['graduation'];
    $linkedin_profile_link = $_POST['linkedin_profile_link'];
    $description = $_POST['description'];

    $resume = file_get_contents($_FILES['resume']['tmp_name']);
    $resume_filename = $_FILES['resume']['name'];

    $host = 'localhost';
    $db = 'u707137586_UserAccounts';
    $user = 'u707137586_UserAccounts';
    $pass = 'egtA*XgA+J>2';
    $charset = 'utf8mb4';

    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    try {
        $pdo = new PDO($dsn, $user, $pass, $options);
    } catch (\PDOException $e) {
        echo json_encode(["success" => false, "error" => "Database connection failed: " . $e->getMessage()]);
        exit();
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO users (uid, name, email, phone, role, state, college, degree, specialization, graduation, linkedin_profile_link, description, resume, resume_filename) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$uid, $name, $email, $phone, $role, $state, $college, $degree, $specialization, $graduation, $linkedin_profile_link, $description, $resume, $resume_filename]);
        echo json_encode(["success" => true]);
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "error" => "Failed to insert user: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "error" => "Invalid request method"]);
}
?>
