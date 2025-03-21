<?php 
header('Content-Type: application/json');

$servername = "localhost";
$username = "u707137586_EV_Careers";
$password = "^2R!20Av]ok";
$dbname = "u707137586_EV_Careers";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(["success" => false, "error" => "Connection failed: " . $conn->connect_error]);
    exit();
}

$tableCreationQuery = "CREATE TABLE IF NOT EXISTS career_applications (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(50) NOT NULL,
    phone VARCHAR(15) NOT NULL,
    dob VARCHAR(25) NOT NULL,
    state VARCHAR(50) NOT NULL,
    location VARCHAR(255) NOT NULL,
    college VARCHAR(100) NOT NULL,
    degree VARCHAR(100) NOT NULL,
    specialization VARCHAR(100) NOT NULL,
    graduation_year VARCHAR(4) NOT NULL,
    resume LONGBLOB NOT NULL,
    role VARCHAR(100) NOT NULL DEFAULT 'Sales And Marketing Intern',
    resume_filename VARCHAR(100) NOT NULL,
    goals TEXT NOT NULL,
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
$dob = $_POST['dob'];
$state = $_POST['state'];
$location = $_POST['location'];
$college = $_POST['college'];
$degree = $_POST['degree'];
$specialization = $_POST['specialization'];
$graduation_year = $_POST['graduation'];
$goals = $_POST['goals'];
$role = $_POST['role'];

$resume = file_get_contents($_FILES['resume']['tmp_name']);
$resume_filename = $_FILES['resume']['name'];

$checkQuery = $conn->prepare("SELECT id FROM career_applications WHERE email = ? OR phone = ?");
$checkQuery->bind_param("ss", $email, $phone);
$checkQuery->execute();
$checkQuery->store_result();

if ($checkQuery->num_rows > 0) {
    echo json_encode(["success" => false, "error" => "Email or phone number already exists in the system."]);
    $checkQuery->close();
    exit();
}

$checkQuery->close();

$stmt = $conn->prepare("INSERT INTO career_applications 
    (first_name, last_name, email, phone, dob, state, location, college, degree, specialization, graduation_year, resume, resume_filename, goals, role, confirmationEmailSent) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'false')");

$stmt->bind_param("sssssssssssssss", $first_name, $last_name, $email, $phone, $dob, $state, $location, $college, $degree, $specialization, $graduation_year, $resume, $resume_filename, $goals, $role);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "error" => $stmt->error]);
}

$stmt->close();
$conn->close();
?>
