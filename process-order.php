<?php
session_start();
require_once 'database/config.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_SESSION['username'] ?? '';
    
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $zip = trim($_POST['zip'] ?? '');

    try {
        $stmt = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ?, address = ?, city = ?, zip = ? WHERE username = ?");
        $stmt->execute([$firstName, $lastName, $email, $address, $city, $zip, $username]);

        unset($_SESSION['cart']);

        // Redirect with success flag for the popup
        header('Location: cart.php?order=success');
        exit;

    } catch (\PDOException $e) {
        header('Location: cart.php?error=database_error');
        exit;
    }
} else {
    header('Location: cart.php');
    exit;
}
?>