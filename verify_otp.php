<?php
// 🛠️ நமது புதிய config.php-ஐ இணைக்கிறோம் (அதில் ஏற்கனவே ob_start மற்றும் session_start உள்ளது)
require_once 'config.php';

// ⚠️ SESSION NAME FIX: உங்க லாகின் பக்கத்தில் நாம் 'card_number' தான் பயன்படுத்தினோம்
if (!isset($_SESSION['card_number']) || !isset($_SESSION['auth_otp'])) { 
    header("Location: dashboard.php"); 
    exit(); 
}

$card = $_SESSION['card_number']; // சீரமைக்கப்பட்டுள்ளது
$error = "";

if (isset($_POST['verify_otp_btn'])) {
    $entered_otp = trim($_POST['otp_code']);

    if ($entered_otp == $_SESSION['auth_otp']) {
        // ----------------------------------------------------
        // OTP SUCCESS ROUTINE
        // ----------------------------------------------------
        $amount = $_SESSION['pending_amount'];
        $location = $_SESSION['pending_location'];

        // 1. Fetch current balance safely
        $user_stmt = mysqli_prepare($conn, "SELECT balance FROM users WHERE card_number = ?");
        mysqli_stmt_bind_param($user_stmt, "s", $card);
        mysqli_stmt_execute($user_stmt);
        $user_res = mysqli_stmt_get_result($user_stmt);
        $user = mysqli_fetch_assoc($user_res);
        mysqli_stmt_close($user_stmt);

        if ($user) {
            $new_balance = $user['balance'] - $amount;

            // 2. Deduct Funds Securely
            $update_stmt = mysqli_prepare($conn, "UPDATE users SET balance = ? WHERE card_number = ?");
            mysqli_stmt_bind_param($update_stmt, "ds", $new_balance, $card);
            mysqli_stmt_execute($update_stmt);
            mysqli_stmt_close($update_stmt);

            // 3. Log the successful transaction history record
            $loc_string = "[LOCATION] " . $location . " (OTP Verified)";
            $status_success = "Success";
            $log_stmt = mysqli_prepare($conn, "INSERT INTO transactions (card_number, amount, location, status) VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($log_stmt, "sdss", $card, $amount, $loc_string, $status_success);
            mysqli_stmt_execute($log_stmt);
            mysqli_stmt_close($log_stmt);
            
            // Set volatile transient parameters for the Dashboard receipt display
            $_SESSION['success_amount'] = $amount;
            $_SESSION['success_location'] = $location;
        }

        unset($_SESSION['auth_otp']);
        unset($_SESSION['pending_amount']);
        unset($_SESSION['pending_location']);

        // 🔥 Render கிளவுட் சர்வரில் 404 எர்ரர் வராமல் தடுக்க நேரடியாக ஃபைல் பெயரை மட்டும் பயன்படுத்துகிறோம்
        echo "<script>alert('OTP Verified! Transaction Success.'); window.location.href='tx_success.php';</script>";
        exit();

    } else {
        // ----------------------------------------------------
        // OTP FAILED / FRAUD ATTEMPT ROUTINE
        // ----------------------------------------------------
        $amount = $_SESSION['pending_amount'];
        $location = $_SESSION['pending_location'];

        // Log the failure to the transaction monitoring matrix securely
        $loc_fail_string = "[LOC] " . $location . " (Failed OTP)";
        $status_fraud = "Flagged Fraud";
        
        $fraud_stmt = mysqli_prepare($conn, "INSERT INTO transactions (card_number, amount, location, status) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($fraud_stmt, "sdss", $card, $amount, $loc_fail_string, $status_fraud);
        mysqli_stmt_execute($fraud_stmt);
        mysqli_stmt_close($fraud_stmt);
        
        unset($_SESSION['auth_otp']);
        unset($_SESSION['pending_amount']);
        unset($_SESSION['pending_location']);
        
        // 🔥 Render கிளவுட் ரூட்டிங் பிக்ஸ்
        header("Location: otp_failed.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <!-- 🔥 MOBILE RESPONSIVE SCALING ENGINE TRIGGER INTERFACES -->
    <meta name="viewport" style="max-width: 400px;" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
 
    <title>ATM Shield - OTP Verification</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container" style="max-width: 400px; margin: 60px auto; background: #ffffff; padding: 30px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); text-align: center;">
        <h2>🔒 Security Verification</h2>
        <p style="color:#ef4444; font-size:14px; margin-bottom:15px;">High Risk / Unusual activity detected!</p>
        
        <!-- [Demo Mode] Simulated SMS OTP display layer -->
        <div style="background:#334155; padding:10px; border-radius:6px; margin-bottom:15px; color:#38bdf8; text-align:center;">
            <strong>[Demo Mode] Simulated SMS OTP:</strong> <br>
            <span style="font-size:22px; letter-spacing:4px; font-weight:bold; color:#fff; display:block; margin-top:5px;"><?php echo htmlspecialchars($_SESSION['auth_otp'] ?? ''); ?></span>
        </div>

        <form method="POST" autocomplete="off">
            <p style="color:#4a5568; font-size:14px; text-align:center; margin-bottom:15px;">
                Enter the 6-digit OTP sent to your registered mobile number to approve ₹<?php echo htmlspecialchars(number_format($_SESSION['pending_amount'] ?? 0, 2)); ?>.
            </p>
            <input type="text" name="otp_code" placeholder="Enter 6 Digit OTP" maxlength="6" required 
                   style="text-align:center; font-size:18px; letter-spacing:4px; width: 85%; padding: 10px; margin-bottom: 15px;" pattern="[0-9]{6}" title="Please enter 6 numeric digits only.">
            <button type="submit" name="verify_otp_btn" style="width: 90%; padding: 12px; background: #ef4444; color: white; border: none; border-radius: 6px; font-weight: bold; cursor: pointer;">Verify & Approve</button>
        </form>
        <br>
        <div style="text-align: center;">
            <a href="dashboard.php" style="color:#94a3b8; text-decoration:none; font-weight:bold;">Cancel Transaction</a>
        </div>
    </div>
</body>
</html>
