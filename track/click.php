<?php
$db = new mysqli('srv1640.hstgr.io', 'u707137586_Campus_Hiring', '6q+SFd~o[go', 'u707137586_Campus_Hiring');
if ($db->connect_error) exit;
$email = filter_input(INPUT_GET, 'email', FILTER_VALIDATE_EMAIL);
$target = filter_input(INPUT_GET, 'target', FILTER_SANITIZE_URL);
if ($email && $target) {
    $stmt = $db->prepare("INSERT INTO email_clicks (email, clicked_at, url) VALUES (?, NOW(), ?)");
    $stmt->bind_param("ss", $email, $target);
    $stmt->execute();
}
header("Location: $target");
exit;
