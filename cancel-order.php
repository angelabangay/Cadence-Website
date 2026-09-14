<?php
session_start();
require_once 'database/config.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = intval($_POST['order_id'] ?? 0);
    $userId = $_SESSION['user_id'] ?? null;
    $username = $_SESSION['username'] ?? '';

    if ($order_id && ($userId || $username)) {
        try {
            // Get the user's email from the database to match the order
            $stmtUser = $pdo->prepare("SELECT email FROM users WHERE id = ? OR username = ?");
            $stmtUser->execute([$userId, $username]);
            $userData = $stmtUser->fetch(PDO::FETCH_ASSOC);
            $userEmail = $userData['email'] ?? '';

            if (!empty($userEmail)) {
                // Verify the order is pending and belongs to this user before deleting
                $stmt = $pdo->prepare("SELECT id, status FROM orders WHERE id = ? AND email = ?");
                $stmt->execute([$order_id, $userEmail]);
                $order = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($order && strtolower(trim($order['status'])) === 'pending') {
                    $deleteStmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
                    $deleteStmt->execute([$order_id]);
                }
            }
        } catch (PDOException $e) {
            // Handle error silently or log if needed
        }
    }
}

header('Location: profile.php');
exit;