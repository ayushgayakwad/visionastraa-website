<?php

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['full_name'];
    $email = $_POST['email'];
    $uid = $_POST['uid'];
    $phone = $_POST['phone'];
    $alt_phone = $_POST['alt_phone'];
    $state = $_POST['state'];
    $college = $_POST['college'];
    $degree = $_POST['degree'];
    $specialization = $_POST['specialization'];
    $graduation = $_POST['graduation'];
    $linkedin_profile_link = $_POST['linkedin_profile_link'];
    $cgpa = $_POST['cgpa'];

    $host = 'localhost';
    $db = 'u707137586_UserData';
    $user = 'u707137586_UserData';
    $pass = '7eJ@>/K#vLLm';
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
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $emailExists = $stmt->fetchColumn();

    if ($emailExists > 0) {
        echo json_encode(["success" => false, "error" => "Email is already registered."]);
        exit();
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO users (uid, name, email, phone, alt_phone, state, college, degree, specialization, graduation, linkedin_profile_link, cgpa) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$uid, $name, $email, $phone, $alt_phone, $state, $college, $degree, $specialization, $graduation, $linkedin_profile_link, $cgpa]);
        echo json_encode(["success" => true]);
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "error" => "Failed to insert user: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "error" => "Invalid request method"]);
}
?>
