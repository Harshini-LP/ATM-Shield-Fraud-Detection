<?php 
// 🛠️ நமது புதிய config.php-ஐ இணைக்கிறோம் (அதில் ஏற்கனவே ob_start மற்றும் session_start உள்ளது)
require_once 'config.php'; 

// ----------------------------------------------------
// Security Session Guard Check
// ----------------------------------------------------
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: admin_login.php");
    exit();
}

// ----------------------------------------------------
// Core Action Feature: Unlock Account & Wipe Failure History
// ----------------------------------------------------
if (isset($_POST['unlock_account'])) {
    $target_card = trim($_POST['target_card_number']);

    $status_active = 'Active';
    $unlock_stmt = mysqli_prepare($conn, "UPDATE users SET card_status = ? WHERE card_number = ?");
    mysqli_stmt_bind_param($unlock_stmt, "ss", $status_active, $target_card);
    $unlock_success = mysqli_stmt_execute($unlock_stmt);
    mysqli_stmt_close($unlock_stmt);

    if ($unlock_success) {
        $clear_stmt = mysqli_prepare($conn, "DELETE FROM login_attempts WHERE card_number = ?");
        mysqli_stmt_bind_param($clear_stmt, "s", $target_card);
        mysqli_stmt_execute($clear_stmt);
        mysqli_stmt_close($clear_stmt);

        echo "<script>alert('Account " . htmlspecialchars($target_card) . " successfully unlocked!'); window.location.href='admin.php';</script>";
        exit();
    }
}

// ----------------------------------------------------
// Core Action Feature: Fraud பரிவர்த்தனையை நீக்கி பயனருக்கு அறிக்கை அனுப்புதல்
// ----------------------------------------------------
if (isset($_POST['delete_and_report'])) {
    $tx_id = intval($_POST['transaction_id']);
    $target_card = trim($_POST['target_card_number']);

    $report_msg = "⚠️ Security Update: A suspicious transaction on your card was intercepted and deleted by Admin for your safety.";

    $report_stmt = mysqli_prepare($conn, "UPDATE users SET admin_report = ? WHERE card_number = ?");
    mysqli_stmt_bind_param($report_stmt, "ss", $report_msg, $target_card);
    mysqli_stmt_execute($report_stmt);
    mysqli_stmt_close($report_stmt);

    $delete_stmt = mysqli_prepare($conn, "DELETE FROM transactions WHERE id = ?");
    mysqli_stmt_bind_param($delete_stmt, "i", $tx_id);
    $delete_success = mysqli_stmt_execute($delete_stmt);
    mysqli_stmt_close($delete_stmt);

    if ($delete_success) {
        echo "<script>alert('Fraud log deleted and security report sent to User " . htmlspecialchars($target_card) . "!'); window.location.href='admin.php';</script>";
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <!-- 🔥 MOBILE RESPONSIVE SCALING ENGINE TRIGGER INTERFACES -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
 
    <title>ATM Shield Admin Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* 🚀 OVERRIDE FIX: style.css-ல் உள்ள ஃபிலெக்ஸ் முரண்பாடுகளை அட்மின் பக்கத்தில் மட்டும் நீக்குகிறது */
        body {
            display: block !important; /* லேயர்களைப் பக்கவாட்டில் மோதவிடாமல் தனித்தனி வரிகளாகப் பிரிக்கிறது */
            padding: 20px !important;
            background-color: #0f172a !important;
        }
        .admin-header-bar {
            width: 100%;
            max-width: 1100px;
            margin: 10px auto 25px auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-sizing: border-box;
            clear: both;
        }
        .admin-box {
            display: block !important;
            width: 100% !important;
            max-width: 1100px !important;
            margin: 0 auto !important;
            clear: both;
        }
    </style>
</head>
<body>

    <!-- 🚀 FIXED HEADER BAR LAYER -->
    <div class="admin-header-bar">
        <h2 style="color: #ef4444; margin: 0; font-size: 24px; font-weight: bold; letter-spacing: 0.5px;">🛡️ ATM Shield Security Command Center</h2>
        <a href="logout.php" style="background: #ef4444; color: white; padding: 10px 22px; text-decoration: none; font-weight: bold; border-radius: 6px; font-size: 14px; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.2); transition: 0.2s; display: inline-block;">🚪 Admin Logout</a>
    </div>

    <!-- 🚀 FIXED CONTENT LEDGER CANVAS -->
    <div class="admin-box">
        <h3 style="color: #ef4444; margin-top: 0; text-align: left;">🚩 Live Fraud Threat Transaction Logs</h3>
        <p style="color: #94a3b8; font-size: 14px; margin-bottom: 25px; text-align: left;">Real-Time Suspicious Activities Transaction Log Tracking</p>
        
        <!-- ====================================================
             PANEL 1: FRAUD LOGS TRANSACTION TABLE
             ==================================================== -->
        <table>
            <thead>
                <tr>
                    <th>Card Number</th>
                    <th>Requested Amount</th>
                    <th>Location</th>
                    <th>Security Status</th>
                    <th>Date & Time</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $status_param = 'Flagged Fraud';
                $log_stmt = mysqli_prepare($conn, "SELECT * FROM transactions WHERE status = ? ORDER BY id DESC");
                mysqli_stmt_bind_param($log_stmt, "s", $status_param);
                mysqli_stmt_execute($log_stmt);
                $logs = mysqli_stmt_get_result($log_stmt);

                if (mysqli_num_rows($logs) == 0) {
                    echo "<tr><td colspan='6' style='text-align:center; color:#94a3b8; padding: 25px;'>No fraud alerts recorded yet. System secure.</td></tr>";
                } else {
                    while($row = mysqli_fetch_assoc($logs)) {
                        echo "<tr>
                            <td>" . htmlspecialchars($row['card_number']) . "</td>
                            <td style='color:#ef4444; font-weight:bold;'>₹" . htmlspecialchars(number_format($row['amount'], 2)) . "</td>
                            <td>" . htmlspecialchars($row['location']) . "</td>
                            <td><span class='badge'>" . htmlspecialchars($row['status']) . "</span></td>
                            <td style='color:#94a3b8;'>" . htmlspecialchars($row['date_time']) . "</td>
                            <td>
                                <form method='POST' style='margin:0; padding:0;' onsubmit='return confirm(\"Delete log and report to user?\");'>
                                    <input type='hidden' name='transaction_id' value='" . htmlspecialchars($row['id']) . "'>
                                    <input type='hidden' name='target_card_number' value='" . htmlspecialchars($row['card_number']) . "'>
                                    <button type='submit' name='delete_and_report' class='btn-delete-report'>🗑️ Delete & Report</button>
                                </form>
                            </td>
                        </tr>";
                    }
                }
                mysqli_stmt_close($log_stmt);
                ?>
            </tbody>
        </table>

        <br><br><hr style="border: 0; border-top: 1px solid #334155;"><br>

        <!-- ====================================================
             PANEL 2: LOCKED USER ACCOUNTS & MANAGEMENT
             ==================================================== -->
        <h3 style="color: #38bdf8; text-align: left; margin-top: 20px;">🔒 Locked Accounts Management</h3>
        <table>
            <thead>
                <tr>
                    <th>Customer Name</th>
                    <th>Card Number</th>
                    <th>Account Balance</th>
                    <th>Current Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $lock_param = 'Locked';
                $locked_stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE card_status = ? ORDER BY id DESC");
                mysqli_stmt_bind_param($locked_stmt, "s", $lock_param);
                mysqli_stmt_execute($locked_stmt);
                $locked_accounts = mysqli_stmt_get_result($locked_stmt);

                if (mysqli_num_rows($locked_accounts) == 0) {
                    echo "<tr><td colspan='5' style='text-align:center; color:#94a3b8; padding: 25px;'>No customer accounts are currently locked. System running clear.</td></tr>";
                } else {
                    while($user_row = mysqli_fetch_assoc($locked_accounts)) {
                        echo "<tr>
                            <td>" . htmlspecialchars($user_row['username']) . "</td>
                            <td>" . htmlspecialchars($user_row['card_number']) . "</td>
                            <td>₹" . htmlspecialchars(number_format($user_row['balance'], 2)) . "</td>
                            <td><span class='badge' style='background:#f59e0b;'>Locked</span></td>
                            <td>
                                <form method='POST' style='margin:0; padding:0;' onsubmit='return confirm(\"Are you sure you want to unlock this account?\");'>
                                    <input type='hidden' name='target_card_number' value='" . htmlspecialchars($user_row['card_number']) . "'>
                                    <button type='submit' name='unlock_account' class='btn-unlock'>🔓 Unlock Account</button>
                                </form>
                            </td>
                        </tr>";
                    }
                }
                mysqli_stmt_close($locked_stmt);
                ?>
            </tbody>
        </table>

        <br><br>
        <div style="text-align: center; margin-top: 10px;">
            <a href="index.php" style="color:#38bdf8; text-decoration:none; font-weight:bold; font-size: 15px;">← Go Back to ATM Main Login</a>
        </div>
    </div>
</body>
</html>
