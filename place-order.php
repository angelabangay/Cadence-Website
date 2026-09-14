<?php
session_start();
require_once 'database/config.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['user_id'] ?? null;
    
    $email = trim($_POST['email'] ?? '');
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $zip = trim($_POST['zip'] ?? '');
    $paymentMethod = trim($_POST['payment_method'] ?? 'card');
    $saveInfo = isset($_POST['save_info']) ? true : false;

    if ($userId && $saveInfo) {
        try {
            $stmtUpdate = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ?, address = ?, city = ?, zip = ? WHERE id = ?");
            $stmtUpdate->execute([$firstName, $lastName, $email, $address, $city, $zip, $userId]);
            
            $_SESSION['first_name'] = $firstName;
            $_SESSION['last_name'] = $lastName;
        } catch (PDOException $e) {
        }
    }

    $cart = $_SESSION['cart'] ?? [];
    if (empty($cart)) {
        header('Location: cart.php');
        exit;
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

    $total = 0;
    $orderLines = [];

    foreach ($cart as $id => $qty) {
        foreach ($PRODUCTS as $p) {
            if ($p['id'] === $id) {
                $itemTotal = $p['price'] * $qty;
                $total += $itemTotal;
                $orderLines[] = $p['name'] . ' x ' . $qty . ' ($' . $itemTotal . ')';
                break;
            }
        }
    }

    $orderItemsStr = implode("\n", $orderLines);
    $status = 'Completed';

    try {
        $stmtOrder = $pdo->prepare("INSERT INTO orders (email, first_name, last_name, address, city, zip, payment_method, order_items, total, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
        $stmtOrder->execute([$email, $firstName, $lastName, $address, $city, $zip, $paymentMethod, $orderItemsStr, $total, $status]);
        
        unset($_SESSION['cart']);
        
        header('Location: checkout.php?order=success');
        exit;
    } catch (PDOException $e) {
        die("Order placement failed: " . $e->getMessage());
    }
} else {
    header('Location: checkout.php');
    exit;
}