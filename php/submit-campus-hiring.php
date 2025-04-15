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

$tableCreationQuery = "CREATE TABLE IF NOT EXISTS crdf25 (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(50) NOT NULL,
    phone VARCHAR(15) NOT NULL,
    alt_phone VARCHAR(15) NOT NULL,
    dob VARCHAR(25) NOT NULL,
    state VARCHAR(50) NOT NULL,
    college VARCHAR(100) NOT NULL,
    degree VARCHAR(100) NOT NULL,
    specialization VARCHAR(100) NOT NULL,
    graduation_year VARCHAR(4) NOT NULL,
    cgpa VARCHAR(10) NOT NULL,
    linkedin VARCHAR(200) NOT NULL,
    goals TEXT NOT NULL,
    role VARCHAR(200) NOT NULL DEFAULT 'Unknown',
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
$alt_phone = $_POST['alt_phone'];
$dob = $_POST['dob'];
$state = $_POST['state'];
$college = $_POST['college'];
$degree = $_POST['degree'];
$specialization = $_POST['specialization'];
$graduation_year = $_POST['graduation'];
$cgpa = $_POST['cgpa'];
$linkedin = $_POST['linkedin'];
$goals = $_POST['goals'];
$roleArray = $_POST['role'];
$role = implode(", ", $roleArray);

$checkQuery = $conn->prepare("SELECT id FROM crdf25 WHERE email = ? OR phone = ?");
$checkQuery->bind_param("ss", $email, $phone);
$checkQuery->execute();
$checkQuery->store_result();

if ($checkQuery->num_rows > 0) {
    echo json_encode(["success" => false, "error" => "Email or phone number already exists in the system."]);
    $checkQuery->close();
    exit();
}

$checkQuery->close();

$stmt = $conn->prepare("INSERT INTO crdf25 
    (first_name, last_name, email, phone, alt_phone, dob, state, college, degree, specialization, graduation_year, cgpa, linkedin, goals, role, enrolled, confirmationEmailSent) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'false', 'false')");

$stmt->bind_param("sssssssssssssss", $first_name, $last_name, $email, $phone, $alt_phone, $dob, $state, $college, $degree, $specialization, $graduation_year, $cgpa, $linkedin, $goals, $role);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "error" => $stmt->error]);
}

$stmt->close();
$conn->close();
?>
