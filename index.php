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
    <!-- 🔥 MOBILE RESPONSIVE SCALING ENGINE TRIGGER INTERFACES -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
 
    <title>ATM Shield - Cyber-Dark Auth Gate</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #0f172a; color: #f8fafc; padding: 20px; display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 100vh; margin: 0; }
        .container { max-width: 400px; width: 100%; text-align: center; background: #1e293b; color: #fff; padding: 30px; border-radius: 12px; border: 1px solid #334155; box-shadow: 0 10px 25px rgba(0,0,0,0.5); box-sizing: border-box; }
        input[type="text"], input[type="password"] { width: 90%; padding: 12px; margin: 10px auto; background: #0f172a; border: 1px solid #334155; color: #fff; border-radius: 6px; font-size: 15px; text-align: center; display: block; box-sizing: border-box; }
        .btn-main { width: 90%; padding: 12px; background: #38bdf8; color: #0f172a; border: none; font-weight: bold; cursor: pointer; margin: 12px auto; border-radius: 6px; display: block; font-size: 15px; transition: 0.2s; text-transform: uppercase; letter-spacing: 0.5px; }
        .btn-main:hover { background: #0ea5e9; }
        .alert-error { color: #f87171; background: #451a03; border: 1px solid #7f1d1d; padding: 10px; margin-bottom: 15px; border-radius: 6px; font-size: 14px; width: 90%; margin-left: auto; margin-right: auto; box-sizing: border-box; }
        .vector-badge { width: 100%; height: 90px; margin-bottom: 15px; display: flex; align-items: center; justify-content: center; }
        .action-link { display: block; padding: 12px; margin: 8px auto; text-decoration: none; font-weight: bold; font-size: 15px; border-radius: 6px; width: 90%; border: 1px solid transparent; transition: 0.2s; text-align: center; box-sizing: border-box; }
        .btn-register { color: #0f172a; background: #4ade80; }
        .btn-register:hover { background: #22c55e; }
        
        /* 🤫 ரகசிய லிங்க்கிற்கான ஸ்டைல் (Secret Hidden Dot Link Style) */
        .secret-dot {
            color: #475569;
            text-decoration: none;
            cursor: default;
        }
        .secret-dot:hover {
            color: #475569; /* ஹோவர் செய்தாலும் நிறம் மாறாது, சாதாரண புள்ளி போலவே இருக்கும் */
        }
    </style>
</head>

    <body>

<div class="container">
    <!-- High-Tech Vector Shield Logo Asset -->
    <div class="vector-badge">
        <svg width="80" height="80" viewBox="0 0 100 100" xmlns="http://w3.org">
            <path d="M50,10 L85,25 L85,55 C85,75 50,85 50,85 C50,85 15,75 15,55 L15,25 Z" fill="#1e293b" stroke="#38bdf8" stroke-width="3"/>
            <path d="M38,48 L46,56 L64,36" fill="none" stroke="#38bdf8" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
    </div>
    
    <h2 style="color: #38bdf8; margin: 0;">🛡️ ATM Shield Gate</h2>
    <p style="color: #94a3b8; font-size: 13px; margin: 5px 0 20px 0;">Real-Time Fraud Detection Infrastructure</p>

    <?php if (!empty($login_error)): ?>
        <div class="alert-error">
            <?php echo $login_error; ?>
        </div>
    <?php endif; ?>

    <!-- LOGIN FORM -->
    <form method="POST" action="index.php">
        <input type="text" name="card_number" placeholder="Enter 16-Digit Card Number" required>
        <input type="password" name="pin" placeholder="Enter 4-Digit ATM PIN" required>
        <button type="submit" name="login_submit" class="btn-main">AUTHENTICATE CARD</button>
    </form>
    
    <!-- New Account Button Grid -->
    <div style="margin-top: 25px; padding-top: 20px; border-top: 1px solid #334155;">
        <a href="register.php" class="action-link btn-register">📝 Create New Account</a>
    </div>
    
    <!-- 🤫 SECRET NAV LAYER: அட்மின் பட்டன் நீக்கப்பட்டு, சாதாரண வரியின் முற்றுப்புள்ளியாக ( . ) மாற்றப்பட்டுள்ளது -->
    <p style="font-size: 11px; color: #475569; margin-top: 25px; margin-bottom: 0;">
        Demo Asset: 4000 1234 5678 8742 | PIN: 1234<a href="admin_login.php" class="secret-dot">.</a>
    </p>
</div>

</body>
</html>

