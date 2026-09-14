<?php
include 'config.php';
if(isset($_POST['login']))
{
alert('Your account is LOCKED due to 3 failed attempts!');";
    } elseif ($user_data) {
       // 2. Check 2-Minute Rapid Login/Transaction Limit
        \(time_stmt = mysqli_prepare(\)conn, "SELECT TIMESTAMPDIFF(SECOND, last_login, NOW()) AS diff FROM users WHERE card_number = ? AND last_login IS NOT NULL");
        mysqli_stmt_bind_param(\(time_stmt, "s",\)card);
        mysqli_stmt_execute($time_stmt);
        \(time_res = mysqli_stmt_get_result(\)time_stmt);
        \(time_data = mysqli_fetch_assoc(\)time_res);
        mysqli_stmt_close($time_stmt);


        // 120 வினாடிக்குள் மீண்டும் வந்தால் Pop-up காட்டும்
        if (\(time_data &&\)time_data['diff'] < 120 && !isset($_POST['user_verified'])) {
            $show_verification_popup = true;
            \(pending_card =\)card;
            \(pending_pin =\)pin;
        } else {
            // PIN Verification
            if (password_verify(\(pin,\)user_data['password'])) {

                // Update Last Login Time
                \(update_stmt = mysqli_prepare(\)conn, "UPDATE users SET last_login = NOW() WHERE card_number = ?");
                mysqli_stmt_bind_param(\(update_stmt, "s",\)card);
                mysqli_stmt_execute($update_stmt);
                mysqli_stmt_close($update_stmt);

                // Clear Failed Logins
                \(clear_stmt = mysqli_prepare(\)conn, "DELETE FROM login_attempts WHERE card_number = ?");
                mysqli_stmt_bind_param(\(clear_stmt, "s",\)card);
                mysqli_stmt_execute($clear_stmt);
                mysqli_stmt_close($clear_stmt);

                \(_SESSION['user_card'] =\)card;
                header("Location: dashboard.php");
                exit();
            } else {
                // Wrong PIN Logic
                \(log_stmt = mysqli_prepare(\)conn, "INSERT INTO login_attempts (card_number) VALUES (?)");
                mysqli_stmt_bind_param(\(log_stmt, "s",\)card);
                mysqli_stmt_execute($log_stmt);
                mysqli_stmt_close($log_stmt);

                \(count_stmt = mysqli_prepare(\)conn, "SELECT COUNT(*) as total FROM login_attempts WHERE card_number = ?");
                mysqli_stmt_bind_param(\(count_stmt, "s",\)card);
                mysqli_stmt_execute($count_stmt);
                \(count_res = mysqli_stmt_get_result(\)count_stmt);
                \(count_data = mysqli_fetch_assoc(\)count_res);
                mysqli_stmt_close($count_stmt);

                if ($count_data['total'] >= 3) {
                    \(lock_stmt = mysqli_prepare(\)conn, "UPDATE users SET card_status = 'Locked' WHERE card_number = ?");
                    mysqli_stmt_bind_param(\(lock_stmt, "s",\)card);
                    mysqli_stmt_execute($lock_stmt);
                    mysqli_stmt_close($lock_stmt);

                    echo "";
                } else {
                    \(rem = 3 -\)count_data['total'];
                    echo "";
                }
            }
        }
    } else {
        echo "";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <!-- 🔥 MOBILE RESPONSIVE SCALING ENGINE TRIGGER INTERFACES -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
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
            <button type="submit" name="login">Login</button>
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
