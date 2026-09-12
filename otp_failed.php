<?php
include 'config.php';
// Redirect to index if the user does not have an active session active state
if(!isset($_SESSION['user_card'])) { 
    header("Location: index.php"); 
    exit(); 
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>ATM Shield - Transaction Denied</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container" style="max-width: 500px;">
        <!-- Fraud Alert Vector Illustration Banner Display Layer -->
        <img src='fraud-alert.png' alt='Fraud Alert' style='width: 100%; border-radius: 8px; margin-bottom: 15px;'>
        
        <h2 style='color:#ef4444;'>Transaction Denied! ❌</h2>
        <p style='color:#94a3b8; margin-bottom:15px;'>Reason: Incorrect OTP entered for suspicious high-value or out-station activity. Transaction terminated for your account's safety.</p>
        
        <br>
        <a href='dashboard.php' style='color:#38bdf8; text-decoration:none; font-weight:bold;'>Back to Dashboard</a>
    </div>
</body>
</html>
