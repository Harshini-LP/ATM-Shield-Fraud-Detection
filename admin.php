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
        // Clear out the bad login tracking counter parameters
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
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #0f172a; color: #f8fafc; padding: 20px; }
        .admin-box {
            max-width: 950px;
            width: 95%;
            background: #1e293b;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.5);
            border: 1px solid #ef4444; /* Red border for fraud monitoring alert */
            margin: 30px auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: #0f172a;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #334155;
            font-size: 14px;
        }
        th {
            background: #ef4444;
            color: white;
            font-weight: bold;
        }
        tr:hover {
            background: #1e293b;
        }
        .badge {
            background: #ef4444;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
        }
        .btn-unlock {
            background: #22c55e;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            font-size: 12px;
            transition: 0.2s;
            width: auto;
        }
        .btn-unlock:hover {
            background: #16a34a;
        }
        .btn-delete-report {
            background: #f59e0b;
            color: white;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            font-size: 12px;
            transition: 0.2s;
            width: auto;
        }
        .btn-delete-report:hover {
            background: #d97706;
        }
        .btn-logout {
            background: #ef4444;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            font-weight: bold;
            border-radius: 6px;
            display: inline-block;
            transition: 0.3s;
        }
        .btn-logout:hover {
            background: #b91c1c;
        }
    </style>
</head>
<body>
    <div class="admin-box">
        <div style="text-align: right; margin-bottom: 10px;">
            <a href="logout.php" class="btn-logout">🚪 Admin Logout</a>
        </div>

        <h2 style="color: #ef4444; text-align: center;">🚩 ATM Shield Admin Ledger Dashboard</h2>
        <p style="text-align: center; color: #94a3b8; margin-bottom: 20px;">Real-Time Suspicious Activities Transaction Log Tracking</p>
        
        <!-- ====================================================
             PANEL 1: FRAUD LOGS TRANSACTION TABLE
             ==================================================== -->
        <h3>Suspicious Transaction Logs</h3>
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
                    echo "<tr><td colspan='6' style='text-align:center; color:#94a3b8;'>No fraud alerts recorded yet. System secure.</td></tr>";
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
        <h3 style="color: #38bdf8;">🔒 Locked Accounts Management</h3>
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
                    echo "<tr><td colspan='5' style='text-align:center; color:#94a3b8;'>No customer accounts are currently locked. System running clear.</td></tr>";
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
        <div style="text-align: center;">
            <a href="index.php" style="color:#38bdf8; text-decoration:none; font-weight:bold;">← Go Back to ATM Main Login</a>
        </div>
    </div>
</body>
</html>
