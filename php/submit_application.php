<?php
header('Content-Type: application/json');

$servername = "localhost";
$username = "u707137586_EV_Reg_25";
$password = "bC9#w!Dqb2kn";
$dbname = "u707137586_EV_Reg_25";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(["success" => false, "error" => "Connection failed: " . $conn->connect_error]);
    exit();
}

$tableCreationQuery = "CREATE TABLE IF NOT EXISTS applications (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(50) NOT NULL,
    phone VARCHAR(15) NOT NULL,
    state VARCHAR(50) NOT NULL,
    college VARCHAR(100) NOT NULL,
    degree VARCHAR(100) NOT NULL,
    specialization VARCHAR(100) NOT NULL,
    graduation_year VARCHAR(4) NOT NULL,
    resume LONGBLOB NOT NULL,
    resume_filename VARCHAR(100) NOT NULL,
    goals TEXT NOT NULL,
    referral_code VARCHAR(50),
    term VARCHAR(20) NOT NULL DEFAULT 'April 2025 (Summer)',
    enrolled ENUM('true', 'false') NOT NULL DEFAULT 'false',
    confirmationEmailSent ENUM('true', 'false') NOT NULL DEFAULT 'false',
    submission_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if ($conn->query($tableCreationQuery) === FALSE) {
    echo json_encode(["success" => false, "error" => "Error creating table: " . $conn->error]);
    exit();
}

$first_name = $_POST['first_name'];
$last_name = $_POST['last_name'];
$email = $_POST['email'];
$phone = $_POST['phone'];
$state = $_POST['state'];
$college = $_POST['college'];
$degree = $_POST['degree'];
$specialization = $_POST['specialization'];
$graduation_year = $_POST['graduation'];
$goals = $_POST['goals'];
$referral_code = isset($_POST['referral']) ? $_POST['referral'] : null;
$term = $_POST['term'];

$resume = file_get_contents($_FILES['resume']['tmp_name']);
$resume_filename = $_FILES['resume']['name'];

$checkQuery = $conn->prepare("SELECT id FROM applications WHERE email = ? OR phone = ?");
$checkQuery->bind_param("ss", $email, $phone);
$checkQuery->execute();
$checkQuery->store_result();

if ($checkQuery->num_rows > 0) {
    echo json_encode(["success" => false, "error" => "Email or phone number already exists in the system."]);
    $checkQuery->close();
    exit();
}

$checkQuery->close();

$stmt = $conn->prepare("INSERT INTO applications 
    (first_name, last_name, email, phone, state, college, degree, specialization, graduation_year, resume, resume_filename, goals, referral_code, term, enrolled, confirmationEmailSent) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'false', 'false')");

$stmt->bind_param("ssssssssssssss", $first_name, $last_name, $email, $phone, $state, $college, $degree, $specialization, $graduation_year, $resume, $resume_filename, $goals, $referral_code, $term);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "error" => $stmt->error]);
}

$stmt->close();
$conn->close();
?>
