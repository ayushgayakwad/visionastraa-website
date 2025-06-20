<?php
$db = new mysqli('srv1640.hstgr.io', 'u707137586_Campus_Hiring', '6q+SFd~o[go', 'u707137586_Campus_Hiring');
if ($db->connect_error) exit;
$email = filter_input(INPUT_GET, 'email', FILTER_VALIDATE_EMAIL);
if ($email) {
    $stmt = $db->prepare("INSERT INTO email_opens (email, opened_at) VALUES (?, NOW())");
    $stmt->bind_param("s", $email);
    $stmt->execute();
}
$db->close();
header('Content-Type: image/png');
readfile(__DIR__ . '/pixel.png');
