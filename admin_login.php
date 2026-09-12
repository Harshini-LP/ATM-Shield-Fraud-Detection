<?php
include 'config.php';

$error = "";

if (isset($_POST['admin_login_btn'])) {
    $admin_user = trim($_POST['admin_user']);
    $admin_pass = trim($_POST['admin_pass']);

    // டெமோவிற்காக நிலையான (Static) பாதுகாப்பான யூசர்நேம் மற்றும் பாஸ்வேர்ட்
    // உண்மையான பயன்பாட்டில் இதையும் டேட்டாபேஸில் சேமிக்கலாம்
    $correct_username = "admin";
    $correct_password = "adminpassword123"; 

    if ($admin_user === $correct_username && $admin_pass === $correct_password) {
        $_SESSION['admin_logged_in'] = true;
        header("Location: admin.php");
        exit();
    } else {
        $error = "❌ தவறான யூசர்நேம் அல்லது பாஸ்வேர்ட்!";
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
</head>
<body>
    <div class="container" style="max-width: 400px; border-color: #ef4444;">
        <h2 style="color: #ef4444;">🛡️ Admin Login Guard</h2>
        <p style="color:#94a3b8; font-size:14px; margin-bottom:15px;">நிர்வாகி அங்கீகாரம் தேவை (Restricted Access)</p>

        <?php if (!empty($error)): ?>
            <div class="alert-danger" style="padding: 10px; border-radius: 6px; margin-bottom: 15px; font-weight: bold; font-size: 14px;">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>
        
        <form method="POST" autocomplete="off">
            <input type="text" name="admin_user" placeholder="👤 Admin Username" required>
            <input type="password" name="admin_pass" placeholder="🔑 Admin Password" required>
            <button type="submit" name="admin_login_btn" style="background: #ef4444;">Secure Login</button>
        </form>

        <br>
        <div style="text-align: center;">
            <a href="index.php" style="color:#38bdf8; text-decoration:none; font-weight:bold;">← Back to ATM Login</a>
        </div>
    </div>
</body>
</html>
