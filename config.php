<?php
// 1. Start session safely
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Real Production Cloud Database Credentials (Clever Cloud Cluster Settings)
$db_host = "br1fwj2kwymbxjsqxrtc-mysql.services.clever-cloud.com";
$db_user = "ulecruaargmbi6md";
$db_pass = "W1P8T3MHcAknbSaQlZWC";
$db_name = "br1fwj2kwymbxjsqxrtc";
$db_port = 3306;

// 3. Connect to the remote cloud server framework
$conn = mysqli_connect($db_host, $db_user, $db_pass, $db_name, $db_port);

if (!$conn) {
    die("Database Connection Failed: " . mysqli_connect_error());
}

// 🔥 CRITICAL PROTECTION PATCH: Force structural link parameters to read modern 4-byte UTF-8 emoji strings
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
)");

// FIX FOR DATA TRUNCATION: Forcefully alter column field length limits to VARCHAR(50) on existing tables
mysqli_query($conn, "ALTER TABLE users MODIFY COLUMN card_status VARCHAR(50) DEFAULT 'Active'");

// Dynamic patch execution layer: Force-inject 'balance' if missing
$balance_check = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'balance'");
if (mysqli_num_rows($balance_check) == 0) {
    mysqli_query($conn, "ALTER TABLE users ADD balance DECIMAL(10,2) DEFAULT 50000.00 AFTER card_number");
}

// Dynamic patch execution layer: Force-inject 'admin_report' if missing
$column_check = mysqli_query($conn, "SHOW COLUMNS FROM users LIKE 'admin_report'");
if (mysqli_num_rows($column_check) == 0) {
    mysqli_query($conn, "ALTER TABLE users ADD admin_report VARCHAR(255) DEFAULT NULL");
}

mysqli_query($conn, "CREATE TABLE IF NOT EXISTS login_attempts (
    card_number VARCHAR(50),
    attempt_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Force drop and clean reconstruction setup rules for transaction matrix tables
mysqli_query($conn, "DROP TABLE IF EXISTS transactions");
mysqli_query($conn, "CREATE TABLE transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    card_number VARCHAR(50) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    location VARCHAR(50) NOT NULL,
    status VARCHAR(50) NOT NULL,
    date_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

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
