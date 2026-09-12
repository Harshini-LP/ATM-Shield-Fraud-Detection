<?php
include 'config.php';

if(!isset($_SESSION['user_card'])) { 
    header("Location: index.php"); 
    exit(); 
}

$card = $_SESSION['user_card'];

$user_stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE card_number = ?");
mysqli_stmt_bind_param($user_stmt, "s", $card);
mysqli_stmt_execute($user_stmt);
$user_res = mysqli_stmt_get_result($user_stmt);
$user = mysqli_fetch_assoc($user_res);
mysqli_stmt_close($user_stmt);

if (!$user) {
    session_unset();
    session_destroy();
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
 
    <title>ATM Shield - Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .custom-card {
            border-radius: 12px !important;
            padding: 20px !important;
            margin-bottom: 20px !important;
            text-align: center !important;
            display: block !important;
            width: 100% !important;
            box-sizing: border-box !important;
        }
        .success-receipt {
            background: linear-gradient(135deg, #22c55e 0%, #15803d 100%) !important;
            border: 2px solid #4ade80 !important;
            box-shadow: 0 10px 20px rgba(34, 197, 94, 0.3) !important;
        }
        .fraud-alert {
            background: linear-gradient(135deg, #ef4444 0%, #991b1b 100%) !important;
            border: 2px solid #f87171 !important;
            box-shadow: 0 10px 20px rgba(239, 68, 68, 0.3) !important;
        }
        .status-icon {
            font-size: 40px !important;
            margin-bottom: 5px !important;
            animation: bounce-pulse 1.5s infinite !important;
        }
        .status-title {
            font-size: 20px !important;
            font-weight: bold !important;
            color: #ffffff !important;
            letter-spacing: 1px !important;
            text-transform: uppercase !important;
            margin: 0 !important;
        }
        @keyframes bounce-pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
    </style>
</head>
<body>
    <div class="container" style="max-width: 500px;">
        <img src="banner.png" alt="ATM Security" style="width: 100%; border-radius: 8px; margin-bottom: 15px;">

       

        <!-- 2. நேர அடிப்படையிலான மோசடி தடுப்பு கார்டு (Velocity Fraud Card) -->
        <?php if (isset($_SESSION['fraud_detected'])): ?>
            <div class="custom-card fraud-alert">
                <div class="status-icon">🚨</div>
                <div class="status-title">Fraud Blocked</div>
                <p style="color:#fee2e2 !important; font-size:14px !important; margin:5px 0 0 0 !important;">Multiple rapid transactions detected within 2 minutes!</p>
            </div>
            <?php unset($_SESSION['fraud_detected']); ?>
        <?php endif; ?>

        <!-- 3. தினசரி லிமிட் / பேலன்ஸ் எரர் மெசேஜ் (Error Notification Box) -->
        <?php if (isset($_SESSION['error_msg'])): ?>
            <div style="background: #ef4444; color: white; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-weight: bold; font-size: 14px; text-align: center;">
                ❌ <?php echo htmlspecialchars($_SESSION['error_msg']); ?>
            </div>
            <?php unset($_SESSION['error_msg']); ?>
        <?php endif; ?>

        <h2>Welcome, <?php echo htmlspecialchars($user['username']); ?></h2>
        
        <!-- అడ్మిன் பாதுகாப்பு அறிக்கை செய்தி -->
        <?php if (!empty($user['admin_report'])): ?>
            <div style="background: #f59e0b; color: #0f172a; padding: 12px; border-radius: 8px; margin-bottom: 20px; font-weight: bold; font-size: 14px; text-align: left;">
                <?php echo htmlspecialchars($user['admin_report']); ?>
                <?php 
                    $clear_report_stmt = mysqli_prepare($conn, "UPDATE users SET admin_report = NULL WHERE card_number = ?");
                    mysqli_stmt_bind_param($clear_report_stmt, "s", $card);
                    mysqli_stmt_execute($clear_report_stmt);
                    mysqli_stmt_close($clear_report_stmt);
                ?>
            </div>
        <?php endif; ?>

        <div style="background:#0f172a; padding:15px; border-radius:8px; margin-bottom:20px;">
            <p style="color:#94a3b8;">Available Balance</p>
            <h1 style="color:#22c55e;">₹<?php echo number_format($user['balance'], 2); ?></h1>
        </div>

        <form action="withdraw.php" method="POST" autocomplete="off">
            <h3>💸 Cash Withdrawal</h3>
            <input type="number" name="amount" placeholder="Enter Amount" min="100" required>
            <button type="submit" name="withdraw">Proceed Withdrawal</button>
        </form>
        <br>
        <a href="logout.php" style="color:#ef4444; text-decoration:none; font-weight:bold;">Exit / Logout</a>
    </div>
</body>
</html>
