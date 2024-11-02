<?php

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get user data from POST
    $name = $_POST['name'];
    $email = $_POST['email'];
    $uid = $_POST['uid']; // Assuming UID is sent from the frontend

    // Database connection details
    $host = 'localhost'; // Adjust as needed
    $db = 'u707137586_UserAccounts'; // Your database name
    $user = 'u707137586_UserAccounts'; // Your database user
    $pass = 'egtA*XgA+J>2'; // Your database password
    $charset = 'utf8mb4';

    // Create a new PDO instance
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

    // Insert user data into the database
    try {
        $stmt = $pdo->prepare("INSERT INTO users (uid, name, email) VALUES (?, ?, ?)");
        $stmt->execute([$uid, $name, $email]);
        echo json_encode(["success" => true]);
    } catch (PDOException $e) {
        echo json_encode(["success" => false, "error" => "Failed to insert user: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "error" => "Invalid request method"]);
}
?>
