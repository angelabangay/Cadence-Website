<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "1. PHP is working.<br>";

// Test including config
$configPath = __DIR__ . '/database/config.php';
if (file_exists($configPath)) {
    echo "2. Config file found at: $configPath<br>";
    require_once $configPath;
    echo "3. Config file included successfully.<br>";
} else {
    die("2. ERROR: Config file NOT found at $configPath");
}

// Test database connection variable
if (isset($pdo)) {
    echo "4. PDO connection variable exists.<br>";
    $stmt = $pdo->query("SELECT * FROM products LIMIT 1");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "5. Database query successful! First product found: " . htmlspecialchars($row['name']);
} else {
    echo "4. ERROR: \$pdo is not set.";
}
?>