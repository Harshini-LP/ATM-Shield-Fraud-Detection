<?php
include 'config.php';

// செஷன் அல்லது ரசீது தரவுகள் இல்லை என்றால் டேஷ்போர்டிற்குத் திருப்பி அனுப்பும்
if(!isset($_SESSION['user_card']) || !isset($_SESSION['success_amount'])) { 
    header("Location: dashboard.php"); 
    exit(); 
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <!-- 🔥 MOBILE RESPONSIVE SCALING ENGINE TRIGGER INTERFACES -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
 
    <title>ATM Shield - Transaction Success</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .success-receipt-card {
            background: linear-gradient(135deg, #22c55e 0%, #15803d 100%) !important;
            border-radius: 12px !important;
            padding: 25px !important;
            margin-bottom: 20px !important;
            box-shadow: 0 10px 20px rgba(34, 197, 94, 0.4) !important;
            text-align: center !important;
            border: 2px solid #4ade80 !important;
        }
        .status-icon {
            font-size: 55px !important;
            margin-bottom: 10px !important;
            display: inline-block !important;
            animation: bounce-pulse 1.5s infinite !important;
        }
        .status-title {
            font-size: 24px !important;
            font-weight: bold !important;
            color: #ffffff !important;
            letter-spacing: 1px !important;
            text-transform: uppercase !important;
        }
        @keyframes bounce-pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
    </style>
</head>
<body>
    <div class="container" style="max-width: 500px;">
        <div class="success-receipt-card">
            <div class="status-icon">✅</div>
            <div class="status-title">Transaction Success</div>
            <p style="color:#e8f5e9 !important; font-size:16px !important; margin:10px 0 0 0 !important;">
                Amount Dispensed: <b style="font-size:18px;">₹<?php echo number_format($_SESSION['success_amount'], 2); ?></b>
            </p>
            <p style="color:#c8e6c9 !important; font-size:13px !important; margin:5px 0 0 0 !important;">
                Location: <?php echo htmlspecialchars($_SESSION['success_location']); ?>
            </p>
        </div>

        <h2 style="color:#22c55e; text-align:center;">Please collect your cash safely! 💰</h2>
        <p style="color:#94a3b8; text-align:center; font-size:14px; margin-bottom:20px;">
            The requested funds have been successfully debited from your secure account ecosystem.
        </p>
        
        <br>
        <div style="text-align: center;">
            <a href='dashboard.php' style='color:#38bdf8; text-decoration:none; font-weight:bold; font-size:16px;'>← Back to Dashboard</a>
        </div>
    </div>
</body>
</html>
