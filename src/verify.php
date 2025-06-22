<?php
require_once 'functions.php';

$message = '';

if (isset($_GET['email'], $_GET['code'])) {
    $email = $_GET['email'];
    $code = $_GET['code'];

    if (verifySubscription($email, $code)) {
        $message = "Subscription verified successfully. Thank you!";
    } else {
        $message = "Verification failed. Invalid or expired code.";
    }
} else {
    $message = "Invalid verification link.";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Subscription Verification</title>
</head>
<body>
    <h2 id="verification-heading">Subscription Verification</h2>
    <p><?php echo htmlspecialchars($message); ?></p>
</body>
</html>
