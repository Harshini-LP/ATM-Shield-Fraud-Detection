<?php
// 1. செஷனை கோப்பின் தொடக்கத்திலேயே தொடங்க வேண்டும் (Headers error வராமல் தடுக்க சிறந்த முறை)
session_start();

// 2. Connect to local server using port 3307 as detected in your phpMyAdmin panel
$conn = mysqli_connect("localhost", "root", "", "", 3307);

if (!$conn) {
    die("Connection Failed: " . mysqli_connect_error());
}

// 3. Automated Script: Selects or creates your active database 'atm_fraud'
mysqli_query($conn, "CREATE DATABASE IF NOT EXISTS atm_fraud");

// 4. Select the active database
mysqli_select_db($conn, "atm_fraud");

// 5. Create/Align tables matching your live structure columns
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50),
    password VARCHAR(255) NOT NULL, 
    card_number VARCHAR(50) NOT NULL,
    balance DECIMAL(10,2) DEFAULT 50000.00,
    card_status VARCHAR(50) DEFAULT 'Active', /* FIX: Expanded from VARCHAR(10) to VARCHAR(50) */
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

// 6. Automatically populate sample credentials if table reads completely empty
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
