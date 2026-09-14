<?php
session_start();

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

$host = 'localhost';
$db   = 'cadence_db';
$user = 'root';
$pass = 'user123';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $action = $_POST['auth_action'] ?? 'login';

    if (empty($username) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        if ($action === 'register') {
            // Password criteria: >= 8 chars, 1 uppercase, 1 lowercase, 1 number
            if (
                strlen($password) < 8 || 
                !preg_match('/[A-Z]/', $password) || 
                !preg_match('/[a-z]/', $password) || 
                !preg_match('/[0-9]/', $password)
            ) {
                $error = 'Password must be at least 8 characters and include uppercase, lowercase, and a number.';
            } else {
                $checkStmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
                $checkStmt->execute([$username]);
                
                if ($checkStmt->fetch()) {
                    $error = 'An account with this username already exists. Please log in instead.';
                } else {
                    $role = ($username === 'admin') ? 'admin' : 'customer';
                    $insertStmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
                    $insertStmt->execute([$username, $password, $role]);

                    $_SESSION['logged_in'] = true;
                    $_SESSION['username'] = htmlspecialchars($username);
                    $_SESSION['role'] = $role;
                    
                    if ($role === 'admin') {
                        header('Location: admin.php');
                    } else {
                        header('Location: home.php');
                    }
                    exit;
                }
            }
        } else {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $dbUser = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($dbUser && $dbUser['password'] === $password) {
                $_SESSION['logged_in'] = true;
                $_SESSION['username'] = htmlspecialchars($dbUser['username']);
                $_SESSION['role'] = $dbUser['role'];
                
                if ($dbUser['role'] === 'admin') {
                    header('Location: admin.php');
                } else {
                    header('Location: home.php');
                }
                exit;
            } else {
                $error = 'Invalid username or password, or account does not exist. Please register.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sign In / Register — Cadence</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Afacad+Flux:wght@500;600;700;800&family=Afacad:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/style.css">
<style>
  body { 
    background-color: #0c0d10; 
    background-image: linear-gradient(to bottom, rgba(12, 13, 16, 0.75), rgba(12, 13, 16, 0.92)), url('images/login.png');
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    color: #ffffff; 
    font-family: 'Afacad', sans-serif; 
    margin: 0; 
    display: flex; 
    align-items: center; 
    justify-content: center; 
    height: 100vh; 
  }
  .login-card { 
    background: rgba(20, 21, 26, 0.85); 
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border: 1.5px solid rgba(255,255,255,0.08); 
    padding: 40px; 
    border-radius: 20px; 
    width: 100%; 
    max-width: 400px; 
    box-sizing: border-box; 
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.5);
  }
  .login-logo { font-family: 'Afacad Flux', sans-serif; font-size: 24px; font-weight: 800; text-align: center; margin-bottom: 8px; color: #fff; text-decoration: none; display: block; }
  .login-sub { text-align: center; color: #8e8e93; font-size: 14px; margin-bottom: 24px; }
  .field { margin-bottom: 18px; }
  .field label { display: block; font-size: 12px; font-weight: 700; text-transform: uppercase; color: #8e8e93; margin-bottom: 8px; }
  .field input { width: 100%; padding: 12px 16px; background: #1a1b22; border: 1.5px solid rgba(255,255,255,0.08); border-radius: 10px; color: #fff; font-family: inherit; font-size: 14px; box-sizing: border-box; }
  .field input:focus { border-color: #00C0E8; outline: none; }
  .btn-submit { width: 100%; padding: 14px; background: #00C0E8; color: #0c0d10; border: none; border-radius: 10px; font-weight: 700; font-size: 15px; cursor: pointer; margin-top: 10px; transition: opacity 0.2s; }
  .btn-submit:hover { opacity: 0.9; }
  .error-msg { background: rgba(255, 59, 48, 0.1); border: 1px solid rgba(255, 59, 48, 0.3); color: #ff3b30; padding: 10px; border-radius: 8px; font-size: 13px; margin-bottom: 20px; text-align: center; }
  .tab-row { display: flex; background: #1a1b22; border-radius: 10px; padding: 4px; margin-bottom: 24px; border: 1px solid rgba(255,255,255,0.05); }
  .tab-btn { flex: 1; background: none; border: none; color: #8e8e93; font-family: inherit; font-weight: 700; font-size: 13px; padding: 10px; border-radius: 8px; cursor: pointer; transition: all 0.2s; }
  .tab-btn.active { background: #262833; color: #fff; }
</style>
</head>
<body>

<div class="login-card">
  <a href="index.php" class="login-logo">CADENCE</a>
  <div class="login-sub">Manage your account and view orders</div>

  <div class="tab-row">
    <button type="button" class="tab-btn active" id="tabLogin" onclick="setMode('login')">Sign In</button>
    <button type="button" class="tab-btn" id="tabRegister" onclick="setMode('register')">Register</button>
  </div>

  <?php if (!empty($error)): ?>
    <div class="error-msg"><?= $error ?></div>
  <?php endif; ?>

  <form method="POST" action="login.php" id="authForm">
    <input type="hidden" name="auth_action" id="authAction" value="login">
    <div class="field">
      <label>Username</label>
      <input type="text" name="username" placeholder="Enter your username" required autocomplete="off">
    </div>
    <div class="field">
      <label>Password</label>
      <input type="password" name="password" placeholder="••••••••" required>
    </div>
    <button type="submit" class="btn-submit" id="submitBtn">Sign In</button>
  </form>
</div>

<script>
  function setMode(mode) {
    document.getElementById('authAction').value = mode;
    if (mode === 'login') {
      document.getElementById('tabLogin').classList.add('active');
      document.getElementById('tabRegister').classList.remove('active');
      document.getElementById('submitBtn').textContent = 'Sign In';
    } else {
      document.getElementById('tabRegister').classList.add('active');
      document.getElementById('tabLogin').classList.remove('active');
      document.getElementById('submitBtn').textContent = 'Create Account';
    }
  }
</script>

</body>
</html>