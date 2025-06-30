<?php
if (isset($_GET['email']) && isset($_GET['campaign_id'])) {
    $email = $_GET['email'];
    $campaign_id = $_GET['campaign_id'];

    $conn = new mysqli("srv1640.hstgr.io", "u707137586_Campus_Hiring", "6q+SFd~o[go", "u707137586_Campus_Hiring");

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $stmt = $conn->prepare("INSERT IGNORE INTO unsubscribed_emails (email, campaign_id) VALUES (?, ?)");
    $stmt->bind_param("ss", $email, $campaign_id);
    $stmt->execute();
    $stmt->close();
    $conn->close();

    echo "You have been unsubscribed successfully.";
} else {
    echo "Invalid unsubscribe request.";
}
?>
