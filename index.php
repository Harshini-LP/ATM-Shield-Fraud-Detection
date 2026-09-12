<?php
include 'config.php';

if (isset($_POST['login'])) {
    // Trim accidental leading/trailing spaces
    $card = trim($_POST['card_number']);
    $pin = trim($_POST['pin']);

    // 1. Safe Prepared Statement: Check account using your live column names (card_status, password)
    $status_stmt = mysqli_prepare($conn, "SELECT card_status, password FROM users WHERE card_number = ?");
    mysqli_stmt_bind_param($status_stmt, "s", $card);
    mysqli_stmt_execute($status_stmt);
    $status_res = mysqli_stmt_get_result($status_stmt);
    $user_data = mysqli_fetch_assoc($status_res);
    mysqli_stmt_close($status_stmt);

    if ($user_data && $user_data['card_status'] == 'Locked') {
        echo "<script>alert('Your account is LOCKED due to 3 failed attempts!');</script>";
    } elseif ($user_data) {
        // 2. Cryptographic Match: Verify plaintext PIN against database password hash column
        if (password_verify($pin, $user_data['password'])) {
            
            // Login Success: Clear failed logs
            $clear_stmt = mysqli_prepare($conn, "DELETE FROM login_attempts WHERE card_number = ?");
            mysqli_stmt_bind_param($clear_stmt, "s", $card);
            mysqli_stmt_execute($clear_stmt);
            mysqli_stmt_close($clear_stmt);

            $_SESSION['user_card'] = $card;
            header("Location: dashboard.php");
            exit();
        } else {
            // Login Failed (Wrong PIN): Log attempt
            $log_stmt = mysqli_prepare($conn, "INSERT INTO login_attempts (card_number) VALUES (?)");
            mysqli_stmt_bind_param($log_stmt, "s", $card);
            mysqli_stmt_execute($log_stmt);
            mysqli_stmt_close($log_stmt);

            // Fetch the aggregated total of failed validation attempts
            $count_stmt = mysqli_prepare($conn, "SELECT COUNT(*) as total FROM login_attempts WHERE card_number = ?");
            mysqli_stmt_bind_param($count_stmt, "s", $card);
            mysqli_stmt_execute($count_stmt);
            $count_res = mysqli_stmt_get_result($count_stmt);
            $count_data = mysqli_fetch_assoc($count_res);
            mysqli_stmt_close($count_stmt);

            // 3. Security Rule: Lock account after 3 continuous failures
            if ($count_data['total'] >= 3) {
                $lock_stmt = mysqli_prepare($conn, "UPDATE users SET card_status = 'Locked' WHERE card_number = ?");
                mysqli_stmt_bind_param($lock_stmt, "s", $card);
                mysqli_stmt_execute($lock_stmt);
                mysqli_stmt_close($lock_stmt);

                echo "<script>alert('Fraud Alert! Account Locked after 3 wrong tries.');</script>";
            } else {
                $rem = 3 - $count_data['total'];
                echo "<script>alert('Wrong PIN! Remaining attempts: $rem');</script>";
            }
        }
    } else {
        echo "<script>alert('Invalid Card Number or Card not found!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>ATM Shield - Secure Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <!-- App Logo -->
        <img src="logo.png" alt="Logo" style="width: 100px; height: auto; margin-bottom: 10px;">
        
        <h2>🛡️ ATM Shield</h2>
        <p style="color:#94a3b8; font-size:14px; margin-bottom:15px;">Real-Time Protection Enabled</p>
        
        <form method="POST" autocomplete="off">
            <input type="text" name="card_number" placeholder="💳 Enter Card Number (only 16 digits)" required 
                   maxlength="30" title="Please enter your card number exactly as registered">
            <input type="password" name="pin" placeholder="🔑 Enter 4 Digit PIN" required 
                   maxlength="4" minlength="4" pattern="[0-9]{4}" title="Please enter exactly 4 numbers">
            <button type="submit" name="login">Insert Card & Login</button>
        </form>

        <br>
        <div style="text-align: center; margin-top: 10px;">
            <a href="register.php" style="color:#38bdf8; text-decoration:none; font-size:14px; font-weight:bold;">🆕 Open New Account (Register)</a>
        </div>

        <!-- SECRET ADMIN LINK AREA -->
        <div style="text-align: center; margin-top: 25px; font-size: 11px; color: #475569;">
            <span>v1.2.0 Network Secure </span>
            <a href="admin_login.php" style="color: #475569; text-decoration: none; cursor: default;">.</a>
            <span> Powered by ATM Shield</span>
        </div>
    </div>
</body>
</html>
