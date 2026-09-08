<?php
$host = 'localhost';
$db   = 'cadence_db'; // Change to your actual database name
$user = 'root';     // Change to your database username (default is root for XAMPP)
$pass = 'user123';     // Change to your database password (default is empty for XAMPP)

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Database connected successfully!";
} catch (\PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>