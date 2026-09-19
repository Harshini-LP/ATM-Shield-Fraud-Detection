<?php
// 🛡️ ATM Shield - Landing & Card Authentication Gate
require_once 'config.php';

$login_error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['login_submit'])) {
    $card_no = trim($_POST['card_number']);
    $pin     = trim($_POST['pin']);

    if (!empty($card_no) && !empty($pin)) {
        // உங்க டேட்டாபேஸில் கார்டு நம்பரைச் சரிபார்க்கிறோம்
        $stmt = $conn->prepare("SELECT * FROM users WHERE card_number = ?");
        $stmt->bind_param("s", $card_no);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            
            // password_hash-ஐச் சரிபார்க்கிறோம் (config.php-ல் உள்ள BCRYPT-க்கு ஏற்ப)
            if (password_verify($pin, $user['password']) || $pin === '1234') { // டெமோவிற்காக 1234-ம் அனுமதிக்கப்படுகிறது
                
                if ($user['card_status'] === 'Locked') {
                    $login_error = "❌ This card is locked due to suspicious activity!";
                } else {
                    $_SESSION['user_id']      = $user['id'];
                    $_SESSION['card_number']  = $user['card_number'];
                    $_SESSION['account_name'] = $user['username'];
                    $_SESSION['is_logged_in'] = true;
                    
                    // 🚀 வெற்றிகரமாக லாகின் ஆனதும் முதன்மை டேஷ்போர்டிற்கு அனுப்புகிறோம்!
                    header("Location: dashboard.php");
                    exit();
                }
            } else {
                $login_error = "Invalid ATM PIN!";
            }
        } else {
            $login_error = "Invalid Card Number!";
        }
        $stmt->close();
    } else {
        $login_error = "Please fill in all fields.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ATM Shield - Cyber-Dark Auth Gate</title>
    <!-- உங்க ஒரிஜினல் ஸ்டைல் ஷீட்டை இணைக்கிறோம் -->
    <link rel="stylesheet" href="style.css"> 
</head>
<body>

<div class="container" style="max-width: 400px; margin: 80px auto; text-align: center; background: #111; color: #fff; padding: 30px; border-radius: 8px; border: 1px solid #00ffcc;">
    <!-- உங்க பிராண்ட் லோகோ -->
    <img src="logo.png" alt="ATM Shield Logo" style="max-width: 150px; margin-bottom: 20px;">
    <h2>🛡️ ATM Shield Gate</h2>
    <p style="color: #888;">Real-Time Fraud Detection Infrastructure</p>

    <?php if (!empty($login_error)): ?>
        <div style="color: #ff3333; background: #221111; border: 1px solid #ff3333; padding: 10px; margin-bottom: 15px; border-radius: 4px; font-size: 14px;">
            <?php echo $login_error; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="index.php">
        <input type="text" name="card_number" placeholder="Enter 16-Digit Card Number" style="width: 90%; padding: 10px; margin: 10px 0; background: #222; border: 1px solid #444; color: #fff;" required><br>
        <input type="password" name="pin" placeholder="Enter 4-Digit ATM PIN" style="width: 90%; padding: 10px; margin: 10px 0; background: #222; border: 1px solid #444; color: #fff;" required><br>
        <button type="submit" name="login_submit" style="width: 95%; padding: 12px; background: #00ffcc; color: #000; border: none; font-weight: bold; cursor: pointer; margin-top: 10px;">AUTHENTICATE CARD</button>
    </form>
    
    <p style="font-size: 12px; color: #555; margin-top: 20px;">Default Demo Card: 4000 1234 5678 8742 | PIN: 1234</p>
</div>

</body>
</html>
