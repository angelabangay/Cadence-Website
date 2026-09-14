<?php
// ─────────────────────────────────────────────────────────────
// CADENCE — Registration Page
// ─────────────────────────────────────────────────────────────
session_start();
require_once 'database/config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $role = 'customer'; // Default role for new signups

    if (!empty($username) && !empty($password)) {
        // Check if username already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->fetch()) {
            $error = 'Username is already taken. Please choose another.';
        } else {
            // Insert new user with default empty values for profile fields to avoid database constraint errors
            $stmt = $pdo->prepare("INSERT INTO users (username, password, role, first_name, last_name, address, city, zip) VALUES (?, ?, ?, '', '', '', '', '')");
            if ($stmt->execute([$username, $password, $role])) {
                $success = 'Account created successfully! You can now sign in.';
            } else {
                $error = 'Something went wrong. Please try again.';
            }
        }
    } else {
        $error = 'Please fill in all fields.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Cadence — Register</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Afacad+Flux:wght@500;600;700;800&family=Afacad:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/style.css">
<style>
  body {
    background-color: #0c0d10;
    color: #ffffff;
    font-family: 'Afacad', sans-serif;
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 100vh;
    margin: 0;
  }
  .register-card {
    background: #16181d;
    padding: 40px;
    border-radius: 20px;
    width: 100%;
    max-width: 400px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.5);
    border: 1px solid rgba(255,255,255,0.08);
  }
  .register-card h2 {
    font-family: 'Afacad Flux', sans-serif;
    font-size: 28px;
    font-weight: 800;
    text-transform: uppercase;
    margin-bottom: 8px;
    letter-spacing: -0.02em;
  }
  .register-card p {
    color: #8e8e93;
    font-size: 14px;
    margin-bottom: 24px;
  }
  .form-group {
    margin-bottom: 16px;
  }
  .form-group label {
    display: block;
    font-size: 13px;
    font-weight: 600;
    margin-bottom: 6px;
    color: #c5c5c9;
  }
  .form-group input {
    width: 100%;
    padding: 12px 16px;
    border-radius: 12px;
    border: 1px solid rgba(255,255,255,0.15);
    background: #0c0d10;
    color: #ffffff;
    font-size: 14px;
    outline: none;
    box-sizing: border-box;
    font-family: 'Afacad', sans-serif;
  }
  .form-group input:focus {
    border-color: #00C0E8;
  }
  .btn-submit {
    width: 100%;
    padding: 14px;
    border-radius: 12px;
    background: #00C0E8;
    color: #0c0d10;
    font-weight: 700;
    border: none;
    cursor: pointer;
    font-size: 15px;
    margin-top: 8px;
    transition: opacity 0.2s ease;
  }
  .btn-submit:hover {
    opacity: 0.9;
  }
  .error-msg {
    background: rgba(255, 75, 75, 0.15);
    color: #ff4b4b;
    padding: 10px 14px;
    border-radius: 8px;
    font-size: 13px;
    margin-bottom: 16px;
    font-weight: 600;
  }
  .success-msg {
    background: rgba(0, 192, 232, 0.15);
    color: #00C0E8;
    padding: 10px 14px;
    border-radius: 8px;
    font-size: 13px;
    margin-bottom: 16px;
    font-weight: 600;
    border: 1px solid rgba(0, 192, 232, 0.3);
  }
  .back-link {
    display: block;
    text-align: center;
    margin-top: 20px;
    font-size: 13px;
    color: #8e8e93;
    text-decoration: none;
  }
  .back-link:hover {
    color: #ffffff;
    text-decoration: underline;
  }
</style>
</head>
<body>

<div class="register-card">
  <h2>Create Account</h2>
  <p>Sign up to start shopping with Cadence.</p>

  <?php if (!empty($error)): ?>
    <div class="error-msg"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <?php if (!empty($success)): ?>
    <div class="success-msg"><?= htmlspecialchars($success) ?></div>
  <?php endif; ?>

  <form method="POST" action="">
    <div class="form-group">
      <label>Choose Username</label>
      <input type="text" name="username" placeholder="Enter username" required>
    </div>
    <div class="form-group">
      <label>Choose Password</label>
      <input type="password" name="password" placeholder="Enter password" required>
    </div>
    <button type="submit" class="btn-submit">Register</button>
  </form>

  <a href="index.php" class="back-link">← Back to Sign In</a>
</div>

</body>
</html>