<?php
// Set timezone to Indian Standard Time
date_default_timezone_set('Asia/Kolkata');

// Database configuration for Hostinger
// Update these values with your Hostinger database credentials

define('DB_HOST', 'localhost'); // Usually 'localhost' on Hostinger
define('DB_NAME', 'u506790298_amity2'); // Your database name from Hostinger
define('DB_USER', 'u506790298_samarth'); // Your database username
define('DB_PASS', '1!3#5%QwE'); // Your database password

// Create database connection
function getDBConnection() {
    try {
        $conn = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );
        // Set MySQL session timezone to IST
        $conn->exec("SET time_zone = '+05:30'");
        return $conn;
    } catch(PDOException $e) {
        error_log("Connection failed: " . $e->getMessage());
        return null;
    }
}
?>
