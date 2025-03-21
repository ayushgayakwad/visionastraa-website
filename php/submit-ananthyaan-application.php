<?php
header('Content-Type: application/json');

$servername = "localhost";
$username = "u707137586_Ananthyaan_25";
$password = "5wYhxe/D0U>f";
$dbname = "u707137586_Ananthyaan_25";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(["success" => false, "error" => "Connection failed: " . $conn->connect_error]);
    exit();
}

$tableCreationQuery = "CREATE TABLE IF NOT EXISTS registrations (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    team_name VARCHAR(255) NOT NULL,
    tl_name VARCHAR(50) NOT NULL,
    tl_year VARCHAR(50) NOT NULL,
    tl_dept VARCHAR(100) NOT NULL,
    tl_email VARCHAR(50) NOT NULL,
    tl_phone VARCHAR(15) NOT NULL,
    state VARCHAR(50) NOT NULL,
    college VARCHAR(100) NOT NULL,
    m2_name VARCHAR(50),
    m2_year VARCHAR(50),
    m2_dept VARCHAR(100),
    m2_email VARCHAR(50),
    m2_phone VARCHAR(15),
    m3_name VARCHAR(50),
    m3_year VARCHAR(50),
    m3_dept VARCHAR(100),
    m3_email VARCHAR(50),
    m3_phone VARCHAR(15),
    m4_name VARCHAR(50),
    m4_year VARCHAR(50),
    m4_dept VARCHAR(100),
    m4_email VARCHAR(50),
    m4_phone VARCHAR(15),
    abstract LONGBLOB NOT NULL,
    abstract_filename VARCHAR(100) NOT NULL,
    submission_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";

if ($conn->query($tableCreationQuery) === FALSE) {
    echo json_encode(["success" => false, "error" => "Error creating table: " . $conn->error]);
    exit();
}

$team_name = $_POST['team_name'];
$tl_name = $_POST['tl_name'];
$tl_year = $_POST['tl_year'];
$tl_dept = $_POST['tl_dept'];
$tl_email = $_POST['tl_email'];
$tl_phone = $_POST['tl_phone'];

$state = $_POST['state'];
$college = $_POST['college'];

$m2_name = isset($_POST['m2_name']) ? $_POST['m2_name'] : null;
$m2_year = isset($_POST['m2_year']) ? $_POST['m2_year'] : null;
$m2_dept = isset($_POST['m2_dept']) ? $_POST['m2_dept'] : null;
$m2_email = isset($_POST['m2_email']) ? $_POST['m2_email'] : null;
$m2_phone = isset($_POST['m2_phone']) ? $_POST['m2_phone'] : null;

$m3_name = isset($_POST['m3_name']) ? $_POST['m3_name'] : null;
$m3_year = isset($_POST['m3_year']) ? $_POST['m3_year'] : null;
$m3_dept = isset($_POST['m3_dept']) ? $_POST['m3_dept'] : null;
$m3_email = isset($_POST['m3_email']) ? $_POST['m3_email'] : null;
$m3_phone = isset($_POST['m3_phone']) ? $_POST['m3_phone'] : null;

$m4_name = isset($_POST['m4_name']) ? $_POST['m4_name'] : null;
$m4_year = isset($_POST['m4_year']) ? $_POST['m4_year'] : null;
$m4_dept = isset($_POST['m4_dept']) ? $_POST['m4_dept'] : null;
$m4_email = isset($_POST['m4_email']) ? $_POST['m4_email'] : null;
$m4_phone = isset($_POST['m4_phone']) ? $_POST['m4_phone'] : null;

$abstract = file_get_contents($_FILES['abstract']['tmp_name']);
$abstract_filename = $_FILES['abstract']['name'];

$checkQuery = $conn->prepare("SELECT id FROM registrations WHERE tl_email = ? OR tl_phone = ? OR m2_email = ? OR m2_phone = ? OR m3_email = ? OR m3_phone = ? OR m4_email = ? OR m4_phone = ?");
$checkQuery->bind_param("ssssssss", $tl_email, $tl_phone, $m2_email, $m2_phone, $m3_email, $m3_phone, $m4_email, $m4_phone);
$checkQuery->execute();
$checkQuery->store_result();

if ($checkQuery->num_rows > 0) {
    echo json_encode(["success" => false, "error" => "Email or phone number already exists in the system."]);
    $checkQuery->close();
    exit();
}

$checkQuery->close();

$stmt = $conn->prepare("INSERT INTO registrations 
    (team_name, tl_name, tl_year, tl_dept, tl_email, tl_phone, state, college,
    m2_name, m2_year, m2_dept, m2_email, m2_phone, 
    m3_name, m3_year, m3_dept, m3_email, m3_phone, 
    m4_name, m4_year, m4_dept, m4_email, m4_phone, 
    abstract, abstract_filename) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

$stmt->bind_param("sssssssssssssssssssssssss", 
    $team_name, $tl_name, $tl_year, $tl_dept, $tl_email, $tl_phone, $state, $college,
    $m2_name, $m2_year, $m2_dept, $m2_email, $m2_phone, 
    $m3_name, $m3_year, $m3_dept, $m3_email, $m3_phone, 
    $m4_name, $m4_year, $m4_dept, $m4_email, $m4_phone, 
    $abstract, $abstract_filename);

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["success" => false, "error" => $stmt->error]);
}

$stmt->close();
$conn->close();
?>
