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

$tableCreationQuery = "CREATE TABLE IF NOT EXISTS ev_pbi_applications (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(50) NOT NULL,
    phone VARCHAR(15) NOT NULL,
    alt_phone VARCHAR(15) NOT NULL,
    state VARCHAR(100) NOT NULL,
    college VARCHAR(100) NOT NULL,
    usn VARCHAR(15) NOT NULL,
    degree VARCHAR(100) NOT NULL,
    specialization VARCHAR(100) NOT NULL,
    internship VARCHAR(200) NOT NULL DEFAULT 'Not Selected',
    offer VARCHAR(7) NOT NULL,
    center VARCHAR(100) NOT NULL,
    start VARCHAR(100) NOT NULL,
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
$state = $_POST['state'];
$college = $_POST['college'];
$usn = $_POST['usn'];
$degree = $_POST['degree'];
$specialization = $_POST['specialization'];
$internship = $_POST['internship'];
$offer = $_POST['offer'];
$center = $_POST['center'];
$start = $_POST['start'];

$checkQuery = $conn->prepare("SELECT id FROM ev_pbi_applications WHERE email = ? OR phone = ?");
$checkQuery->bind_param("ss", $email, $phone);
$checkQuery->execute();
$checkQuery->store_result();

if ($checkQuery->num_rows > 0) {
    echo json_encode(["success" => false, "error" => "Email or phone number already exists in the system."]);
    $checkQuery->close();
    exit();
}

$checkQuery->close();

$stmt = $conn->prepare("INSERT INTO ev_pbi_applications 
    (first_name, last_name, email, phone, alt_phone, state, college, usn, degree, specialization, internship, offer, center, start, enrolled, confirmationEmailSent) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'false', 'false')");

$stmt->bind_param("ssssssssssssss", $first_name, $last_name, $email, $phone, $alt_phone, $state, $college, $usn, $degree, $specialization, $internship, $offer, $center, $start);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "error" => $stmt->error]);
}

$stmt->close();
$conn->close();
?>
