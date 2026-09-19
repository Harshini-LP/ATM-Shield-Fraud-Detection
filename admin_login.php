<?php 
// 🛠️ நமது புதிய config.php-ஐ இணைக்கிறோம் (அதில் ஏற்கனவே ob_start மற்றும் session_start உள்ளது)
require_once 'config.php'; 

$error = "";

if (isset($_POST['admin_login_btn'])) {
    $admin_user = trim($_POST['admin_user']);
    $admin_pass = trim($_POST['admin_pass']);

    // டெமோவிற்காக நிலையான (Static) பாதுகாப்பான யூசர்நேம் மற்றும் பாஸ்வேர்ட்
    $correct_username = "admin";
    $correct_password = "adminpassword123"; 

    if ($admin_user === $correct_username && $admin_pass === $correct_password) {
        $_SESSION['admin_logged_in'] = true;
        header("Location: admin.php");
        exit();
    } else {
        $error = "❌ Wrong Username and Password!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <!-- 🔥 MOBILE RESPONSIVE SCALING ENGINE TRIGGER INTERFACES -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
 
    <title>ATM Shield - Admin Login</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #0f172a; color: #f8fafc; padding: 20px; }
        .container { 
            max-width: 400px; 
            margin: 80px auto; 
            background: #1e293b; 
            padding: 30px; 
            border-radius: 10px; 
            box-shadow: 0 4px 15px rgba(0,0,0,0.5); 
            text-align: center;
            border: 1px solid #ef4444;
        }
        input[type="text"], input[type="password"] { 
            width: 90%; 
            padding: 12px; 
            margin: 10px 0; 
            background: #0f172a; 
            border: 1px solid #334155; 
            color: #fff; 
            border-radius: 6px; 
        }
        button { 
            width: 95%; 
            padding: 12px; 
            background: #ef4444; 
            color: white; 
            border: none; 
            border-radius: 6px; 
            font-weight: bold; 
            cursor: pointer; 
            margin-top: 10px; 
        }
        button:hover { background: #b91c1c; }
        .alert-danger { color: #ef4444; background: #221111; border: 1px solid #ef4444; padding: 10px; border-radius: 6px; margin-bottom: 15px; }
    </style>
</head>
<body>
    <div class="container">
        <h2 style="color: #ef4444; margin-top: 0;">🛡️ Admin Login Guard</h2>
        <p style="color:#94a3b8; font-size:14px; margin-bottom:20px;">நிர்வாகி அங்கீகாரம் தேவை (Restricted Access)</p>

        <?php if (!empty($error)): ?>
            <div class="alert-danger">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" autocomplete="off">
            <input type="text" name="admin_user" placeholder="👤 Admin Username" required><br>
            <input type="password" name="admin_pass" placeholder="🔑 Admin Password" required><br>
            <button type="submit" name="admin_login_btn">Secure Login</button>
        </form>

        <br>
        <div style="text-align: center;">
            <a href="index.php" style="color:#38bdf8; text-decoration:none; font-weight:bold;">← Back to ATM Login</a>
        </div>
    </div>
</body>
</html>
