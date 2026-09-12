<?php
include 'config.php';
if(!isset($_SESSION['user_card'])) { 
    header("Location: index.php"); 
    exit();
}

if (isset($_POST['withdraw'])) {
    $card = $_SESSION['user_card'];
    $amount = intval($_POST['amount']);

    // சிஸ்டம் கட்டுப்பாடுகள்
    $max_single_limit = 20000;   
    $daily_max_limit = 50000;    
    $usual_location = "Madurai"; 

    // 1. பயனர் தகவல்களைப் பாதுகாப்பாக எடுத்தல்
    $user_stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE card_number = ?");
    mysqli_stmt_bind_param($user_stmt, "s", $card);
    mysqli_stmt_execute($user_stmt);
    $user_res = mysqli_stmt_get_result($user_stmt);
    $user = mysqli_fetch_assoc($user_res);
    mysqli_stmt_close($user_stmt);

    if (!$user) {
        header("Location: dashboard.php");
        exit();
    }

    // இருப்பிடம் கண்டறிதல்
    $user_ip = $_SERVER['REMOTE_ADDR'];
    if($user_ip == '::1' || $user_ip == '127.0.0.1') {
        $user_ip = '103.60.172.0'; 
    }
    $geo_api = @file_get_contents("http://ip-api.com{$user_ip}");
    $geo_data = json_decode($geo_api, true);
    $detected_location = (isset($geo_data['city']) && !empty($geo_data['city'])) ? $geo_data['city'] : "Unknown";

    // தினசரி பண வரம்பு கட்டுப்பாடு
    $limit_stmt = mysqli_prepare($conn, "SELECT SUM(amount) as total FROM transactions WHERE card_number = ? AND status = 'Success' AND DATE(date_time) = CURDATE()");
    mysqli_stmt_bind_param($limit_stmt, "s", $card);
    mysqli_stmt_execute($limit_stmt);
    $limit_res = mysqli_stmt_get_result($limit_stmt);
    $today_data = mysqli_fetch_assoc($limit_res);
    mysqli_stmt_close($limit_stmt);
    $today_spent = ($today_data && isset($today_data['total']) && $today_data['total'] != null) ? floatval($today_data['total']) : 0.00;

    if (($today_spent + $amount) > $daily_max_limit) {
        $_SESSION['error_msg'] = "Daily withdrawal limit of ₹{$daily_max_limit} exceeded. You already withdrew ₹{$today_spent} today.";
        header("Location: dashboard.php");
        exit();
    }

    // நேர அடிப்படையிலான மோசடி தடுப்பு (Velocity Check)
    $vel_stmt = mysqli_prepare($conn, "SELECT date_time FROM transactions WHERE card_number = ? ORDER BY id DESC LIMIT 1");
    mysqli_stmt_bind_param($vel_stmt, "s", $card);
    mysqli_stmt_execute($vel_stmt);
    $vel_res = mysqli_stmt_get_result($vel_stmt);
    
    if (mysqli_num_rows($vel_res) > 0) {
        $last_trans = mysqli_fetch_assoc($vel_res);
        $last_time = strtotime($last_trans['date_time']);
        $current_time = time();
        $time_difference = $current_time - $last_time;

        if ($time_difference < 120) { 
            $flag_msg = "📍 " . $detected_location . " (Rapid Attempt)";
            $flag_status = "Flagged Fraud";
            
            $fraud_stmt = mysqli_prepare($conn, "INSERT INTO transactions (card_number, amount, location, status) VALUES (?, ?, ?, ?)");
            mysqli_stmt_bind_param($fraud_stmt, "sdss", $card, $amount, $flag_msg, $flag_status);
            mysqli_stmt_execute($fraud_stmt);
            mysqli_stmt_close($fraud_stmt);
            
            $_SESSION['fraud_detected'] = true;
            header("Location: dashboard.php");
            mysqli_stmt_close($vel_stmt);
            exit();
        }
    }
    mysqli_stmt_close($vel_stmt);

    // அடிப்படை இருப்புச் சரிபார்ப்பு
    if ($amount > $user['balance']) {
        $_SESSION['error_msg'] = "Insufficient Balance in your account!";
        header("Location: dashboard.php");
        exit();
    }

    // அட்வான்ஸ்டு OTP சரிபார்ப்பு நிபந்தனை
    if ($amount > $max_single_limit || $detected_location != $usual_location) {
        $_SESSION['pending_amount'] = $amount;
        $_SESSION['pending_location'] = $detected_location;
        $_SESSION['auth_otp'] = rand(100000, 999999);
        header("Location: verify_otp.php");
        exit();
        } else {
        // நேரடிப் பரிவர்த்தனை வெற்றி
        $new_balance = $user['balance'] - $amount;
        $update_stmt = mysqli_prepare($conn, "UPDATE users SET balance = ? WHERE card_number = ?");
        mysqli_stmt_bind_param($update_stmt, "ds", $new_balance, $card);
        mysqli_stmt_execute($update_stmt);
        mysqli_stmt_close($update_stmt);

        $loc_string = "📍 " . $detected_location;
        $success_status = "Success";
        $log_stmt = mysqli_prepare($conn, "INSERT INTO transactions (card_number, amount, location, status) VALUES (?, ?, ?, ?)");
        mysqli_stmt_bind_param($log_stmt, "sdss", $card, $amount, $loc_string, $success_status);
        mysqli_stmt_execute($log_stmt);
        mysqli_stmt_close($log_stmt);
        
        // புதிய தனிப் பக்கத்திற்குத் தரவுகளை அனுப்பி திருப்பி விடுதல் (Redirect)
        $_SESSION['success_amount'] = $amount;
        $_SESSION['success_location'] = $detected_location;
        header("Location: tx_success.php");
        exit();
    }

}
?>
