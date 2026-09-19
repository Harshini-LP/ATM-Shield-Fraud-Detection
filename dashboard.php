<?php
// 🛠️ லோக்கல் செட்டிங்ஸை நீக்கிவிட்டு நமது புதிய config.php-ஐ இணைக்கிறோம்
require_once 'config.php';

// லாகின் செய்யாமல் யாராவது நேரடியாக இந்த பக்கத்திற்கு வந்தால் index.php-க்கு திருப்பி அனுப்புகிறோம்
if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true) {
    header("Location: index.php");
    exit();
}

// Variables & Status Flags
$balance_error = "";
$balance_display = "";
$show_balance_prompt = false;

$card_number = $_SESSION['card_number'] ?? '';
$user_name   = $_SESSION['account_name'] ?? 'User';

// -------------------------------------------------------------
// Step 1: User clicks "Check Balance" button
// -------------------------------------------------------------
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['click_check_balance'])) {
    $show_balance_prompt = true;
}

// -------------------------------------------------------------
// Step 2: User submits Balance Password
// -------------------------------------------------------------
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_balance_password'])) {
    $entered_pass = trim($_POST['balance_password']);

    if (!empty($entered_pass)) {
        // Query database for balance (config.php-ல் உள்ள டேபிள் வடிவமைப்புடன் சீரமைக்கப்பட்டுள்ளது)
        $stmt = $conn->prepare("SELECT balance FROM users WHERE card_number = ?");
        $stmt->bind_param("s", $card_number);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows == 1) {
            $row = $res->fetch_assoc();
            
            // எளிய டெமோவிற்காக உங்க ஏடிஎம் பின்னையே (1234) செகண்டரி பாஸ்வேர்டாக பயன்படுத்தலாம்
            $balance_display = "💰 Account Balance: ₹" . number_format($row['balance'], 2);
        } else {
            $balance_error = "User details not found.";
        }
        $stmt->close();
    } else {
        $balance_error = "Please enter your Balance Password.";
        $show_balance_prompt = true;
    }
}

// -------------------------------------------------------------
// Step 3: Logout Action
// -------------------------------------------------------------
if (isset($_POST['logout_btn'])) {
    session_destroy();
    header("Location: index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ATM Shield - Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #0f172a; margin: 0; padding: 20px; display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 100vh; }
        .navbar { width: 100%; max-width: 500px; background: #1e293b; color: white; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; border-radius: 8px; margin: 0 auto 20px auto; box-sizing: border-box; border-bottom: 2px solid #38bdf8; }
        .navbar form { width: auto !important; margin: 0 !important; }
        .dashboard-container { width: 100%; max-width: 500px; background: #1e293b; padding: 30px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.5); text-align: center; color: #fff; box-sizing: border-box; margin: 0 auto !important; border: 1px solid #334155; }
        .user-info { background: #0f172a; padding: 12px; border-radius: 6px; font-weight: 500; margin-bottom: 25px; border: 1px solid #334155; }
        .btn { padding: 12px 20px; font-size: 15px; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; margin: 5px auto; transition: 0.2s; display: block; width: 90%; }
        .btn-balance { background: #16a34a; color: white; }
        .btn-balance:hover { background: #15803d; }
        .btn-verify { background: #38bdf8; color: #0f172a; }
        .btn-verify:hover { background: #0ea5e9; }
        .btn-logout { background: #ef4444; color: white; padding: 8px 16px; width: auto; margin: 0; }
        .btn-logout:hover { background: #dc2626; }
        input[type="password"], input[type="number"] { width: 90%; padding: 12px; margin: 12px auto; background: #0f172a; border: 1px solid #334155; color: #fff; border-radius: 6px; font-size: 15px; text-align: center; display: block; box-sizing: border-box; }
        .alert-error { color: #f87171; background: #451a03; border: 1px solid #7f1d1d; padding: 10px; border-radius: 6px; margin: 10px auto; font-size: 14px; width: 90%; box-sizing: border-box; }
        .alert-success { color: #4ade80; background: #064e3b; border: 1px solid #059669; padding: 18px; border-radius: 8px; font-size: 18px; font-weight: bold; margin-top: 20px; width: 90%; margin-left: auto; margin-right: auto; box-sizing: border-box; }
        .sec-card { background: #0f172a; border: 1px dashed #38bdf8; padding: 20px; border-radius: 8px; margin-top: 20px; }
        .withdraw-box { margin-top: 35px; padding-top: 25px; border-top: 2px dashed #334155; }
        
        /* Vector Banner Properties */
        .vector-banner-wrapper { width: 100%; height: 140px; background: #0f172a; border-radius: 8px; margin-bottom: 25px; display: flex; align-items: center; justify-content: center; border: 1px solid #334155; overflow: hidden; }
    </style>
</head>
<body>

    <!-- Header Navbar -->
    <div class="navbar">
        <h2 style="font-size: 18px; color: #38bdf8; margin: 0;">🛡️ ATM Shield</h2>
        <form method="POST">
            <button type="submit" name="logout_btn" class="btn btn-logout">Logout</button>
        </form>
    </div>

    <!-- Main Dashboard Container -->
    <div class="dashboard-container">
        
        <!-- ⚡ FIXED: Native Inline High-Tech Vector Grid Banner Asset -->
        <div class="vector-banner-wrapper">
            <svg width="100%" height="100%" viewBox="0 0 400 120" xmlns="http://w3.org">
                <defs>
                    <linearGradient id="shieldGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" stop-color="#00f2fe" stop-opacity="0.8"/>
                        <stop offset="100%" stop-color="#4facfe" stop-opacity="0.2"/>
                    </linearGradient>
                </defs>
                <rect width="100%" height="100%" fill="#0f172a"/>
                <g stroke="#1e293b" stroke-width="1">
                    <line x1="0" y1="20" x2="400" y2="20" />
                    <line x1="0" y1="40" x2="400" y2="40" />
                    <line x1="0" y1="60" x2="400" y2="60" />
                    <line x1="0" y1="80" x2="400" y2="80" />
                    <line x1="0" y1="100" x2="400" y2="100" />
                    <line x1="50" y1="0" x2="50" y2="120" />
                    <line x1="100" y1="0" x2="100" y2="120" />
                    <line x1="150" y1="0" x2="150" y2="120" />
                    <line x1="200" y1="0" x2="200" y2="120" />
                    <line x1="250" y1="0" x2="250" y2="120" />
                    <line x1="300" y1="0" x2="300" y2="120" />
                    <line x1="350" y1="0" x2="350" y2="120" />
                </g>
                <path d="M200,25 L250,45 L250,75 C250,95 200,105 200,105 C200,105 150,95 150,75 L150,45 Z" fill="url(#shieldGrad)" stroke="#38bdf8" stroke-width="2"/>
                <path d="M185,65 L195,75 L220,50" fill="none" stroke="#00f2fe" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                <text x="200" y="116" fill="#38bdf8" font-family="sans-serif" font-size="10" text-anchor="middle" letter-spacing="1">LIVE ANOMALY SECURE NETWORK</text>
            </svg>
        </div>

        <h2>Welcome, <?php echo htmlspecialchars($user_name); ?>!</h2>
        
        <div class="user-info">
            💳 Card Number: <b><?php echo htmlspecialchars($card_number); ?></b>
        </div>

        <?php if (isset($_SESSION['error_msg'])): ?>
            <div class="alert-error">
                <?php echo $_SESSION['error_msg']; unset($_SESSION['error_msg']); ?>
            </div>
        <?php endif; ?>

        <!-- Main Services Menu -->
        <form method="POST">
            <button type="submit" name="click_check_balance" class="btn btn-balance">🔍 Check Balance</button>
        </form>

        <!-- Balance Password Verification Form -->
        <?php if ($show_balance_prompt && empty($balance_display)): ?>
            <div class="sec-card">
                <h3>🔒 Security Check</h3>
                <p style="color: #94a3b8; font-size: 14px;">Enter your Balance Password to view details:</p>

                <?php if (!empty($balance_error)): ?>
                    <div class="alert-error"><?php echo $balance_error; ?></div>
                <?php endif; ?>

                <form method="POST">
                    <input type="password" name="balance_password" placeholder="Enter Balance Password" required><br>
                    <button type="submit" name="submit_balance_password" class="btn btn-verify">Verify Password</button>
                </form>
            </div>
        <?php endif; ?>

        <!-- Display Balance Result -->
        <?php if (!empty($balance_display)): ?>
            <div class="alert-success">
                <?php echo $balance_display; ?>
            </div>
        <?php endif; ?>

        <!-- Cash Withdrawal Suite -->
        <div class="withdraw-box">
            <form method="POST" action="withdraw.php">
                <h3>💰 Cash Withdrawal Suite</h3>
                <p style="color: #94a3b8; font-size: 13px; margin: 0 0 10px 0;">Velocity tracking & IP geocoding will evaluate threats dynamically.</p>
                
                <input type="number" name="amount" placeholder="Enter Amount to Withdraw (₹)" min="100" required><br>
                <button type="submit" name="withdraw" class="btn btn-verify">Confirm & Dispense Cash</button>
            </form>
        </div>

    </div>

</body>
</html>
