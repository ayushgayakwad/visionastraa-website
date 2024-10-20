<?php
header('Content-Type: application/json');

$servername = "localhost";
$username = "u707137586_EV_Reg_T1_24";
$password = "DMKL0IYoP&4";
$dbname = "u707137586_EV_Reg_2024_T1";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(["success" => false, "error" => "Connection failed: " . $conn->connect_error]);
    exit();
}

$tableCreationQuery = "CREATE TABLE IF NOT EXISTS vsa_job_applications (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(50) NOT NULL,
    phone VARCHAR(15) NOT NULL,
    role VARCHAR(100) NOT NULL,
    state VARCHAR(50) NOT NULL,
    college VARCHAR(100) NOT NULL,
    degree VARCHAR(100) NOT NULL,
    specialization VARCHAR(100) NOT NULL,
    graduation_year VARCHAR(4) NOT NULL,
    resume LONGBLOB NOT NULL,
    resume_filename VARCHAR(100) NOT NULL,
    linkedin_profile_link VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    submission_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if ($conn->query($tableCreationQuery) === FALSE) {
    echo json_encode(["success" => false, "error" => "Error creating table: " . $conn->error]);
    exit();
}

$full_name = $_POST['full_name'];
$email = $_POST['email'];
$phone = $_POST['phone'];
$role = $_POST['Choose'];
$state = $_POST['state'];
$college = $_POST['college'];
$degree = $_POST['degree'];
$specialization = $_POST['specialization'];
$graduation_year = $_POST['graduation'];
$linkedin_profile_link = $_POST['linkedin_profile_link'];
$description = $_POST['description'];

$resume = file_get_contents($_FILES['resume']['tmp_name']);
$resume_filename = $_FILES['resume']['name'];

$checkQuery = $conn->prepare("SELECT id FROM vsa_job_applications WHERE email = ? OR phone = ?");
$checkQuery->bind_param("ss", $email, $phone);
$checkQuery->execute();
$checkQuery->store_result();

if ($checkQuery->num_rows > 0) {
    echo json_encode(["success" => false, "error" => "Email or phone number already exists in the system."]);
    $checkQuery->close();
    exit();
}

$checkQuery->close();

$stmt = $conn->prepare("INSERT INTO vsa_job_applications 
    (full_name, email, phone, role, state, college, degree, specialization, graduation_year, resume, resume_filename, linkedin_profile_link, description) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

$stmt->bind_param("sssssssssssss", $full_name, $email, $phone, $role, $state, $college, $degree, $specialization, $graduation_year, $resume, $resume_filename, $linkedin_profile_link, $description);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "error" => $stmt->error]);
}

$stmt->close();
$conn->close();
?>
