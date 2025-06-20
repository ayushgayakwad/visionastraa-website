<?php
$db = new mysqli('srv1640.hstgr.io', 'u707137586_Campus_Hiring', '6q+SFd~o[go', 'u707137586_Campus_Hiring');
if ($db->connect_error) exit;

$email = filter_input(INPUT_GET, 'email', FILTER_VALIDATE_EMAIL);
$target = filter_input(INPUT_GET, 'target', FILTER_SANITIZE_URL);
$campaign_id = filter_input(INPUT_GET, 'campaign_id', FILTER_SANITIZE_STRING);

if ($email && $target && $campaign_id) {
    $check = $db->prepare("SELECT 1 FROM email_clicks WHERE email=? AND url=? AND campaign_id=? LIMIT 1");
    $check->bind_param("sss", $email, $target, $campaign_id);
    $check->execute();
    $check->store_result();

    if ($check->num_rows === 0) {
        $stmt = $db->prepare("INSERT INTO email_clicks (email, clicked_at, url, campaign_id) VALUES (?, NOW(), ?, ?)");
        $stmt->bind_param("sss", $email, $target, $campaign_id);
        $stmt->execute();
    }

    $check->close();
}

header("Location: $target");
exit;
