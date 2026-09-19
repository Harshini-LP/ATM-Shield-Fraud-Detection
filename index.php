<?php
session_start();

// Database Connection Settings (Adjust DB credentials if needed)
$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "atm_fraud";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Variables & Message Flags
$login_error = "";
$balance_error = "";
$balance_display = "";
$show_balance_sec = false;

// -------------------------------------------------------------
// Step 1: Main Login Verification (Card Number & ATM PIN)
// -------------------------------------------------------------
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login_submit'])) {
    $card_no = trim($_POST['card_number']);
    $pin     = trim($_POST['pin']);

    if (!empty($card_no) && !empty($pin)) {
        // Checking DB for user credentials
        $stmt = $conn->prepare("SELECT * FROM users WHERE card_number = ? AND pin = ?");
        $stmt->bind_param("ss", $card_no, $pin);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            $_SESSION['user_id']      = $user['id'];
            $_SESSION['card_number']  = $user['card_number'];
            $_SESSION['account_name'] = $user['name'];
            $_SESSION['is_logged_in'] = true;
        } else {
            $login_error = "Invalid Card Number or ATM PIN!";
        }
        $stmt->close();
    } else {
        $login_error = "Please fill in all login fields.";
    }
}

// -------------------------------------------------------------
// Step 2: "Check Balance" Click Handler
// -------------------------------------------------------------
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['check_balance_btn'])) {
    $show_balance_sec = true;
}

// -------------------------------------------------------------
// Step 3: Balance Password Verification
// -------------------------------------------------------------
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['verify_balance_pass'])) {
    $entered_bal_pass = trim($_POST['balance_password']);
    $card_no = $_SESSION['card_number'];

    if (!empty($entered_bal_pass)) {
        // Querying for balance and secondary balance password
        $stmt = $conn->prepare("SELECT balance, balance_password FROM users WHERE card_number = ?");
        $stmt->bind_param("s", $card_no);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows == 1) {
            $row = $res->fetch_assoc();
            
            // Verifying the Balance Password
            if ($row['balance_password'] === $entered_bal_pass) {
                $balance_display = "💰 Current Balance: ₹" . number_format($row['balance'], 2);
            } else {
                $balance_error = "❌ Incorrect Balance Password! Access Denied.";
                $show_balance_sec = true; // Retain prompt for retry
            }
        }
        $stmt->close();
    } else {
        $balance_error = "Please enter your Balance Password.";
        $show_balance_sec = true;
    }
}

// Logout Handler
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
    <title>ATM Shield - Fraud Detection & Security System</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #eef2f5; margin: 0; padding: 0; }
        .container { max-width: 450px; margin: 60px auto; background: #ffffff; padding: 30px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); text-align: center; }
        h2 { color: #1a365d; margin-bottom: 20px; }
        input[type="text"], input[type="password"] { width: 90%; padding: 12px; margin: 10px 0; border: 1px solid #cbd5e0; border-radius: 6px; font-size: 15px; }
        .btn { width: 95%; padding: 12px; background: #2b6cb0; color: white; border: none; border-radius: 6px; font-size: 16px; cursor: pointer; margin-top: 10px; font-weight: bold; }
        .btn:hover { background: #2c5282; }
        .btn-balance { background: #2f855a; }
        .btn-balance:hover { background: #22543d; }
        .btn-logout { background: #c53030; }
        .btn-logout:hover { background: #9b2c2c; }
        .alert-error { color: #c53030; background: #fff5f5; border: 1px solid #feb2b2; padding: 10px; border-radius: 5px; margin-bottom: 15px; font-size: 14px; }
        .alert-success { color: #276749; background: #f0fff4; border: 1px solid #9ae6b4; padding: 15px; border-radius: 5px; font-size: 18px; font-weight: bold; margin-top: 15px; }
        .sec-box { background: #f7fafc; border: 1px dashed #cbd5e0; padding: 20px; border-radius: 8px; margin-top: 20px; }
    </style>
</head>
<body>

<div class="container">
    <h2>🛡️ ATM Shield System</h2>

    <?php if (!isset($_SESSION['is_logged_in']) || $_SESSION['is_logged_in'] !== true): ?>
        
        <!-- STEP 1: LOGIN FORM -->
        <h3>Account Login</h3>
        <?php if (!empty($login_error)): ?>
            <div class="alert-error"><?php echo $login_error; ?></div>
        <?php endif; ?>

        <form method="POST" action="index.php">
            <input type="text" name="card_number" placeholder="Card Number" required><br>
            <input type="password" name="pin" placeholder="4-Digit ATM PIN" required><br>
            <button type="submit" name="login_submit" class="btn">Login to Account</button>
        </form>

    <?php else: ?>

        <!-- STEP 2: MAIN DASHBOARD -->
        <h3>Welcome, <?php echo htmlspecialchars($_SESSION['account_name'] ?? 'User'); ?>!</h3>
        <p>Card Number: <b><?php echo htmlspecialchars($_SESSION['card_number']); ?></b></p>
        <hr>

        <form method="POST" action="index.php">
            <button type="submit" name="check_balance_btn" class="btn btn-balance">Check Balance</button>
            <button type="submit" name="logout_btn" class="btn btn-logout">Logout</button>
        </form>

        <!-- STEP 3: BALANCE SECURITY PIN PROMPT -->
        <?php if ($show_balance_sec && empty($balance_display)): ?>
            <div class="sec-box">
                <h4>🔒 Balance Security Verification</h4>
                <p style="font-size: 13px; color: #4a5568;">Enter your secondary password to reveal balance:</p>

                <?php if (!empty($balance_error)): ?>
                    <div class="alert-error"><?php echo $balance_error; ?></div>
                <?php endif; ?>

                <form method="POST" action="index.php">
                    <input type="password" name="balance_password" placeholder="Enter Balance Password" required><br>
                    <button type="submit" name="verify_balance_pass" class="btn">Verify Password</button>
                </form>
            </div>
        <?php endif; ?>

        <!-- STEP 4: DISPLAY BALANCE RESULT -->
        <?php if (!empty($balance_display)): ?>
            <div class="alert-success">
                <?php echo $balance_display; ?>
            </div>
        <?php endif; ?>

    <?php endif; ?>
</div>

</body>
</html>
