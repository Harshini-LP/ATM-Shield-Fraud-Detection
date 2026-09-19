<?php
// 🛠️ நமது புதிய config.php-ஐ இணைக்கிறோம் (அதில் ஏற்கனவே ob_start மற்றும் session_start உள்ளது)
require_once 'config.php';

// ⚠️ SESSION NAME FIX: உங்க லாகின் பக்கத்தில் நாம் 'card_number' தான் பயன்படுத்தினோம்
if(!isset($_SESSION['card_number'])) { 
    header("Location: index.php"); 
    exit(); 
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <!-- 🔥 MOBILE RESPONSIVE SCALING ENGINE TRIGGER INTERFACES -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
 
    <title>ATM Shield - Transaction Denied</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container" style="max-width: 500px; margin: 60px auto; background: #ffffff; padding: 30px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); text-align: center;">
        <!-- Fraud Alert Vector Illustration Banner Display Layer -->
        <img src='fraud-alert.png' alt='Fraud Alert' style='width: 100%; border-radius: 8px; margin-bottom: 15px;'>
        
        <h2 style='color:#ef4444;'>Transaction Denied! ❌</h2>
        <p style='color:#4a5568; margin-bottom:15px;'>Reason: Incorrect OTP entered for suspicious high-value or out-station activity. Transaction terminated for your account's safety.</p>
        
        <br>
        <a href='dashboard.php' style='color:#38bdf8; text-decoration:none; font-weight:bold; font-size: 16px;'>Back to Dashboard</a>
    </div>
</body>
</html>
