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
    $paymentMethod = trim($_POST['payment_method'] ?? 'card');

    $paymentDetails = '';
    if ($paymentMethod === 'card') {
        $cardNum = trim($_POST['card_number'] ?? '');
        $paymentDetails = 'Card ending in ' . substr($cardNum, -4);
    } else {
        $paymentDetails = 'PayPal Account: ' . trim($_POST['paypal_number'] ?? '');
    }

    $PRODUCTS = [
        ['id' => 'velo', 'name' => 'Velo', 'price' => 149],
        ['id' => 'aero', 'name' => 'Aero', 'price' => 99],
        ['id' => 'ion', 'name' => 'Ion', 'price' => 139],
        ['id' => 'tempo', 'name' => 'Tempo', 'price' => 129],
        ['id' => 'ridge', 'name' => 'Ridge', 'price' => 119],
        ['id' => 'terra', 'name' => 'Terra', 'price' => 109],
        ['id' => 'surge', 'name' => 'Surge', 'price' => 159],
        ['id' => 'nova', 'name' => 'Nova', 'price' => 169],
        ['id' => 'vantage', 'name' => 'Vantage', 'price' => 99],
        ['id' => 'lumen', 'name' => 'Lumen', 'price' => 129],
        ['id' => 'flux', 'name' => 'Flux', 'price' => 119],
        ['id' => 'arc', 'name' => 'Arc', 'price' => 139],
        ['id' => 'strato', 'name' => 'Strato', 'price' => 129],
        ['id' => 'drift', 'name' => 'Drift', 'price' => 149],
        ['id' => 'glide', 'name' => 'Glide', 'price' => 109]
    ];

    $cart = $_SESSION['cart'] ?? [];
    $total = 0;
    $orderItemsArray = [];

    foreach ($cart as $id => $qty) {
        foreach ($PRODUCTS as $p) {
            if ($p['id'] === $id) {
                $itemTotal = $p['price'] * $qty;
                $total += $itemTotal;
                $orderItemsArray[] = "{$p['name']} (Qty: {$qty}) - \${$itemTotal}";
                break;
            }
        }
    }

    $orderItemsText = implode("\n", $orderItemsArray);

    try {
        $pdo->beginTransaction();

        $stmtUser = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ?, address = ?, city = ?, zip = ? WHERE username = ?");
        $stmtUser->execute([$firstName, $lastName, $email, $address, $city, $zip, $username]);

        if ($total > 0) {
            $stmtOrder = $pdo->prepare("INSERT INTO orders (first_name, last_name, email, address, city, zip, payment_method, payment_details, order_items, total, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())");
            
            $stmtOrder->execute([
                $firstName,
                $lastName,
                $email,
                $address,
                $city,
                $zip,
                $paymentMethod,
                $paymentDetails,
                $orderItemsText,
                $total
            ]);
        }

        $pdo->commit();

        unset($_SESSION['cart']);

        header('Location: cart.php?order=success');
        exit;

    } catch (\PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        header('Location: cart.php?error=database_error');
        exit;
    }
} else {
    header('Location: cart.php');
    exit;
}
?>