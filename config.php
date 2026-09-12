<?php
// 1. Start session safely
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Real Production Cloud Database Credentials (Clever Cloud Settings)
$db_host = "://clever-cloud.com";
$db_user = "ulecruaargmbi6md";
$db_pass = "W1P8T3MHcAknbSaQlZWC";
$db_name = "br1fwj2kwymbxjsqxrtc";
$db_port = 3306;

// 3. Connect to the remote cloud server framework
$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name, $db_port);

if (!$conn) {
    die("Database Connection Failed: " . mysqli_connect_error());
}

// Force link parameters parameters to securely scale for high-end emoji tokens (📍)
mysqli_set_charset($conn, "utf8mb4");

// 4. Create/Align tables matching your live structure columns
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50),
    password VARCHAR(255) NOT NULL, 
    card_number VARCHAR(50) NOT NULL,
    balance DECIMAL(10,2) DEFAULT 50000.00,
    card_status VARCHAR(50) DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    admin_report VARCHAR(255) DEFAULT NULL
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

// Force update column limits limits sets to handle global inputs
mysqli_query($conn, "ALTER TABLE users CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

mysqli_query($conn, "CREATE TABLE IF NOT EXISTS login_attempts (
    card_number VARCHAR(50),
    attempt_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

// Reconstruction setup rules for transaction matrix tables
mysqli_query($conn, "DROP TABLE IF EXISTS transactions");
mysqli_query($conn, "CREATE TABLE transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    card_number VARCHAR(50) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    location VARCHAR(255) NOT NULL,
    status VARCHAR(50) NOT NULL,
    date_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

// 5. Automatically populate sample credentials if table reads completely empty
$check_user = mysqli_query($conn, "SELECT * FROM users LIMIT 1");
if (mysqli_num_rows($check_user) == 0) {
    $dummy_pin_hash = password_hash('1234', PASSWORD_BCRYPT);
    
    $init_stmt = mysqli_prepare($conn, "INSERT INTO users (username, password, card_number, balance, card_status) VALUES (?, ?, ?, ?, ?)");
    $dummy_user = 'demo_user';
    $dummy_card = '4000 1234 5678 8742';
    $dummy_bal = 60000.00;
    $dummy_status = 'Active';
    
    mysqli_stmt_bind_param($init_stmt, "sssss", $dummy_user, $dummy_pin_hash, $dummy_card, $dummy_bal, $dummy_status);
    mysqli_stmt_execute($init_stmt);
    mysqli_stmt_close($init_stmt);
}
?>
