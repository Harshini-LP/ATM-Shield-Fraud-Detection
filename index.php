<?php 
include 'config.php'; 

$error_msg = "";

if (isset($_POST['login'])) { 
    // 1. படிவத்திலிருந்து உள்ளீடுகளைப் பெற்று சுத்தம் செய்தல்
    $card = trim($_POST['card_number']);
    $pin = trim($_POST['pin']);

    // 2. தரவுத்தளத்திலிருந்து பயனர் தகவலை எடுத்தல்
    $user_stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE card_number = ?");
    mysqli_stmt_bind_param($user_stmt, "s", $card);
    mysqli_stmt_execute($user_stmt);
    $user_res = mysqli_stmt_get_result($user_stmt);
    $user_data = mysqli_fetch_assoc($user_res);
    mysqli_stmt_close($user_stmt);

    if ($user_data) {
        // 3. கணக்கு ஏற்கனவே முடக்கப்பட்டிருந்தால் (Locked) உடனடியாக தடுத்தல்
        if ($user_data['card_status'] === 'Locked') {
            $error_msg = "❌ Your account is LOCKED due to 3 failed attempts! Contact admin.";
        } else {
            // 4. Time-Based Transaction Velocity Check (பாதுகாப்பாக சரிபார்க்கப்படுகிறது)
            $column_check = mysqli_query($conn, "SHOW COLUMNS FROM `users` LIKE 'last_login'");
            
            if (mysqli_num_rows($column_check) > 0) {
                           // விரைவான லாகின் முயற்சிகளை சரிபார்த்தல் (120 வினாடிகள்)
            if ($time_data && $time_data['diff'] < 120 && !isset($_POST['user_verified'])) { 
                echo "
                <script>
                    // பயனர் லாகின் செய்யும் போது 2 நிமிட எச்சரிக்கை பாப்-அப்
                    let userChoice = confirm('🚨 Security Alert: Multiple rapid authentication attempts detected within 2 minutes!\\n\\nIs this you trying to log in? Click OK (Yes) or Cancel (No).');
                    
                    if (userChoice) {
                        // பயனர் 'Yes' (OK) அழுத்தினால் OTP பக்கத்திற்குச் செல்லும்
                        window.location.href = 'verify_otp.php?card=" . urlencode($card) . "';
                    } else {
                        // பயனர் 'No' (Cancel) அழுத்தினால் லாகின் ரத்து செய்யப்படும்
                        alert('❌ Session terminated. Security team notified.');
                        window.location.href = 'index.php';
                    }
                </script>";
                exit();
            }
            } else {
                // 5. ஹேஷ் செய்யப்பட்ட பின்னை (PIN) சரிபார்த்தல்
                if (password_verify($pin, $user_data['password'])) { 
                    
                    // லாகின் நேரத்தை புதுப்பிக்கும் முன் அந்த காலம் உள்ளதா என சரிபார்க்கிறது
                    if (mysqli_num_rows($column_check) > 0) {
                        $update_stmt = mysqli_prepare($conn, "UPDATE users SET last_login = NOW() WHERE card_number = ?"); 
                        mysqli_stmt_bind_param($update_stmt, "s", $card); 
                        mysqli_stmt_execute($update_stmt); 
                        mysqli_stmt_close($update_stmt); 
                    }

                    $clear_stmt = mysqli_prepare($conn, "DELETE FROM login_attempts WHERE card_number = ?"); 
                    mysqli_stmt_bind_param($clear_stmt, "s", $card); 
                    mysqli_stmt_execute($clear_stmt); 
                    mysqli_stmt_close($clear_stmt); 

                    $_SESSION['user_card'] = $card; 
                    header("Location: dashboard.php"); 
                    exit(); 
                } else { 
                    // தோல்வி: தவறான லாகின் முயற்சியை பதிவு செய்தல்
                    $log_stmt = mysqli_prepare($conn, "INSERT INTO login_attempts (card_number) VALUES (?)"); 
                    mysqli_stmt_bind_param($log_stmt, "s", $card); 
                    mysqli_stmt_execute($log_stmt); 
                    mysqli_stmt_close($log_stmt); 

                    // தோல்விகளின் எண்ணிக்கையை சரிபார்த்தல்
                    $count_stmt = mysqli_prepare($conn, "SELECT COUNT(*) as total FROM login_attempts WHERE card_number = ?"); 
                    mysqli_stmt_bind_param($count_stmt, "s", $card); 
                    mysqli_stmt_execute($count_stmt); 
                    $count_res = mysqli_stmt_get_result($count_stmt); 
                    $count_data = mysqli_fetch_assoc($count_res); 
                    mysqli_stmt_close($count_stmt); 

                    if ($count_data['total'] >= 3) { 
                        $lock_stmt = mysqli_prepare($conn, "UPDATE users SET card_status = 'Locked' WHERE card_number = ?"); 
                        mysqli_stmt_bind_param($lock_stmt, "s", $card); 
                        mysqli_stmt_execute($lock_stmt); 
                        mysqli_stmt_close($lock_stmt); 
                        $error_msg = "❌ Your account is LOCKED due to 3 failed attempts!";
                    } else { 
                        $rem = 3 - $count_data['total']; 
                        $error_msg = "❌ Invalid PIN. You have " . $rem . " attempts remaining."; 
                    } 
                } 
            }
        }
    } else {
        $error_msg = "❌ Card number not found inside system directory records.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
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
        
        <!-- பிழை செய்திகளைத் திரையில் காண்பித்தல் -->
        <?php if (!empty($error_msg)): ?>
            <div class="alert-danger" style="background:#ef4444; color:white; padding:10px; border-radius:6px; margin-bottom:15px; font-weight:bold; font-size:13px; text-align:center;">
                <?php echo $error_msg; ?>
            </div>
        <?php endif; ?>
        
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
