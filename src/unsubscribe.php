<?php
require_once 'functions.php';

$message = '';

if (isset($_GET['email'])) {
    $encoded_email = $_GET['email'];
    $email = base64_decode($encoded_email);

    if ($email && unsubscribeEmail($email)) {
        $message = "You have been unsubscribed successfully.";
    } else {
        $message = "Unsubscription failed. Invalid email or already unsubscribed.";
    }
} else {
    $message = "Invalid unsubscribe link.";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Unsubscribe from Task Updates</title>
</head>
<body>
    <h2 id="unsubscription-heading">Unsubscribe from Task Updates</h2>
    <p><?php echo htmlspecialchars($message); ?></p>
</body>
</html>
