<?php
$servername = "localhost";
$username = "u707137586_EV_Reg_25";
$password = "bC9#w!Dqb2kn";
$dbname = "u707137586_EV_Reg_25";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    error_log("Connection failed: " . $conn->connect_error);
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT id, first_name, email FROM applications WHERE confirmationEmailSent = 'false'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $to = $row['email'];
        $subject = 'Confirmation of Application Submission';
        $message = 'Dear ' . $row['first_name'] . ',<br><br>Thank you for your application. We have received your details and will get back to you soon.<br><br>Best regards,<br>VisionAstraa EV Academy';
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= 'From: recruitment@visionastraa.com' . "\r\n";

        if (mail($to, $subject, $message, $headers)) {
            $updateSql = "UPDATE applications SET confirmationEmailSent = 'true' WHERE id = ?";
            $stmt = $conn->prepare($updateSql);
            $stmt->bind_param("i", $row['id']);
            if (!$stmt->execute()) {
                error_log("Error updating confirmationEmailSent for ID " . $row['id'] . ": " . $stmt->error);
            }
            $stmt->close();
        } else {
            error_log("Failed to send email to " . $row['email']);
        }
    }
} else {
    error_log("No records found where confirmationEmailSent is false.");
}

$conn->close();
?>
