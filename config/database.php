<?php
/*
 * Local database connection for Booking Manager.
 * Update these values to match your local MySQL setup.
 */

$host = 'localhost';
$dbname = 'booking_manager';
$username = 'root';
$password = '';

$dsn = "mysql:host={$host};dbname={$dbname};charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    // Keep technical details out of the page shown to visitors.
    error_log($e->getMessage());
    exit('Database connection failed. Check config/database.php and make sure MySQL is running.');
}
