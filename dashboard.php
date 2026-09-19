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
        $stmt = $conn->prepare("SELECT balance FROM users WHERE card_number = ?");
        $stmt->bind_param("s", $card_number);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows == 1) {
            $row = $res->fetch_assoc();
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
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f0f4f8; margin: 0; padding: 0; }
        .navbar { background: #1a365d; color: white; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; }
        .navbar h2 { margin: 0; font-size: 20px; }
        .dashboard-container { max-width: 500px; margin: 50px auto; background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); text-align: center; }
        .user-info { background: #e2e8f0; padding: 12px; border-radius: 6px; font-weight: 500; margin-bottom: 25px; }
        .btn { padding: 12px 20px; font-size: 15px; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; margin: 5px; transition: 0.2s; }
        .btn-balance { background: #2f855a; color: white; width: 80%; }
        .btn-balance:hover { background: #22543d; }
        .btn-verify { background: #3182ce; color: white; width: 90%; margin-top: 10px; }
        .btn-verify:hover { background: #2b6cb0; }
        .btn-logout { background: #e53e3e; color: white; }
        .btn-logout:hover { background: #c53030; }
        input[type="password"], input[type="number"] { width: 85%; padding: 12px; margin: 12px 0; border: 1px solid #cbd5e0; border-radius: 6px; font-size: 15px; text-align: center; }
        .alert-error { color: #9b2c2c; background: #fff5f5; border: 1px solid #feb2b2; padding: 10px; border-radius: 6px; margin: 10px 0; font-size: 14px; }
        .alert-success { color: #22543d; background: #f0fff4; border: 1px solid #9ae6b4; padding: 18px; border-radius: 8px; font-size: 20px; font-weight: bold; margin-top: 20px; }
        .sec-card { background: #f7fafc; border: 1px dashed #a0aec0; padding: 20px; border-radius: 8px; margin-top: 20px; }
        .withdraw-box { margin-top: 35px; padding-top: 25px; border-top: 2px dashed #cbd5e0; }
        
        /* ⚠️ பதாகை படத்திற்கான புதிய ஸ்டைல்ஸ் (Banner Responsive Styling) */
        .dashboard-banner {
            width: 100%;
            height: auto;
            max-height: 180px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #cbd5e0;
        }
    </style>
</head>
<body>

    <!-- Header Navbar -->
    <div class="navbar">
        <h2>🛡️ ATM Shield Fraud Detection</h2>
        <form method="POST" style="margin: 0;">
            <button type="submit" name="logout_btn" class="btn btn-logout">Logout</button>
        </form>
    </div>

    <!-- Main Dashboard Container -->
    <div class="dashboard-container">
        
        <!-- 📸 ====================================================
             நெவிகேஷனுக்கு கீழே பதாகை படம் (Banner Image Display)
             ==================================================== -->
        <img src="dashboard-banner.png" alt="ATM Shield Monitoring Live Network" class="dashboard-banner" onerror="this.src='https://decentro.tech';" />

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
                <p style="color: #4a5568; font-size: 14px;">Enter your Balance Password to view details:</p>

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
                <p style="color: #718096; font-size: 13px; margin: 0 0 10px 0;">Velocity tracking & IP geocoding will evaluate threats dynamically.</p>
                
                <input type="number" name="amount" placeholder="Enter Amount to Withdraw (₹)" min="100" required><br>
                <button type="submit" name="withdraw" class="btn btn-verify" style="width: 90%; background: #1a365d;">Confirm & Dispense Cash</button>
            </form>
        </div>

    </div>

</body>
</html>
