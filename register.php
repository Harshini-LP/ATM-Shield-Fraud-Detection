<?php
include 'config.php';

$message = "";
$message_class = "";

if (isset($_POST['register'])) {
    $card = trim($_POST['card_number']);
    $pin = trim($_POST['pin']);
    $name = trim($_POST['name']);
    $initial_deposit = floatval($_POST['balance']);

    // ----------------------------------------------------
    // Validation Rule 1: Strict input formats
    // ----------------------------------------------------
    if (!preg_match('/^[0-9 ]{14,20}$/', $card)) {
        $message = "❌ Error: Card number must be a valid numeric sequence.";
        $message_class = "alert-danger";
    } elseif (!preg_match('/^[0-9]{4}$/', $pin)) {
        $message = "❌ Error: PIN must be exactly 4 numeric digits.";
        $message_class = "alert-danger";
    } elseif ($initial_deposit < 100) {
        $message = "❌ Error: Minimum initial deposit must be ₹100.";
        $message_class = "alert-danger";
    } else {
        // ----------------------------------------------------
        // Validation Rule 2: Check if Card Number already exists
        // ----------------------------------------------------
        $check_stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE card_number = ?");
        mysqli_stmt_bind_param($check_stmt, "s", $card);
        mysqli_stmt_execute($check_stmt);
        mysqli_stmt_store_result($check_stmt);
        
        if (mysqli_stmt_num_rows($check_stmt) > 0) {
            $message = "❌ Error: This card number is already registered!";
            $message_class = "alert-danger";
            mysqli_stmt_close($check_stmt);
        } else {
            mysqli_stmt_close($check_stmt);

            // ----------------------------------------------------
            // Security Feature: Cryptographic Hashing for the PIN
            // ----------------------------------------------------
            $hashed_pin = password_hash($pin, PASSWORD_BCRYPT);
            $default_status = 'Active';

            // ----------------------------------------------------
            // FIX: Corrected data type format layout string from ssvds to sssss
            // ----------------------------------------------------
            $insert_stmt = mysqli_prepare($conn, "INSERT INTO users (username, password, card_number, balance, card_status) VALUES (?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($insert_stmt, "sssss", $name, $hashed_pin, $card, $initial_deposit, $default_status);
            
            if (mysqli_stmt_execute($insert_stmt)) {
                $message = "🎉 ATM Shield Register Successful! You can now log in.";
                $message_class = "alert-success";
            } else {
                $message = "❌ System Error: Account creation failed. Try again.";
                $message_class = "alert-danger";
            }
            mysqli_stmt_close($insert_stmt);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>ATM Shield - Register</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container" style="max-width: 450px;">
        <h2>📝 ATM Shield Register</h2>
        <p style="color:#94a3b8; font-size:14px; margin-bottom:15px;">Register a new customer card in the secure network</p>

        <!-- Status Notifications Display -->
        <?php if (!empty($message)): ?>
            <div class="<?php echo $message_class; ?>" style="padding: 10px; border-radius: 6px; margin-bottom: 15px; font-weight: bold; font-size: 14px; text-align: center;">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" autocomplete="off">
            <input type="text" name="name" placeholder="👤 Full Name / Username" required maxlength="50">
            
            <input type="text" name="card_number" placeholder="💳 Enter Card Number (e.g. 0000xxxx0000xxxx)" required 
                   maxlength="30" title="Please enter your target card number sequence">
            
            <input type="password" name="pin" placeholder="🔑 Set 4-Digit Secret PIN" required 
                   maxlength="4" minlength="4" pattern="[0-9]{4}" title="Please enter exactly 4 numbers">
            
            <input type="number" name="balance" placeholder="💰 Initial Deposit Amount (₹)" required min="100" value="50000.00" step="0.01">
            
            <button type="submit" name="register" style="margin-top: 10px;">Create Account</button>
        </form>

        <br>
        <div style="text-align: center;">
            <a href="index.php" style="color:#38bdf8; text-decoration:none; font-weight:bold;">← Go Back to ATM Main Login</a>
        </div>
    </div>
</body>
</html>
