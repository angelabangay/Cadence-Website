<?php
session_start();

// ---------- AUTHENTICATION GUARD ----------
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// ---------- ADMIN ROLE REDIRECT ----------
// If the user is an admin, send them directly to the admin dashboard
if (($_SESSION['role'] ?? '') === 'admin' || ($_SESSION['username'] ?? '') === 'admin') {
    header('Location: admin.php');
    exit;
}

$username = $_SESSION['username'] ?? 'User';

// Calculate cart count for the nav
$cart = $_SESSION['cart'] ?? [];
$cartCount = array_sum($cart);

function logoImg($variant = 'light'){
  $filename = ($variant === 'dark') ? 'logo2.png' : 'logo1.png';
  return '<img src="images/' . $filename . '" alt="Cadence Logo" class="brand-logo-img">';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Profile — Cadence</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Afacad+Flux:wght@500;600;700;800&family=Afacad:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/style.css">
<style>
  :root{
    --heading:'Afacad Flux','Afacad',system-ui,sans-serif;
    --body:'Afacad',system-ui,-apple-system,Segoe UI,Roboto,sans-serif;
    --black:#0C0D10;
    --cyan:#00C0E8;
    --line:#E5E5EA;
    --off:#F5F5F7;
    --muted:#6b6b73;
    --white:#FFFFFF;
  }
  body { font-family: var(--body); background: var(--white); color: var(--black); margin: 0; }
  h1, h2, h3, .logo { font-family: var(--heading); }
  .brand-logo-img { height: 24px; width: auto; vertical-align: middle; object-fit: contain; }
  
  .profile-main { padding: 60px 0 100px; background: var(--off); min-height: 70vh; }
  .profile-container { max-width: 600px; margin: 0 auto; background: var(--white); border: 1px solid var(--line); border-radius: 20px; padding: 40px; box-shadow: 0 10px 30px rgba(0,0,0,0.03); }
  .profile-header { display: flex; align-items: center; gap: 20px; margin-bottom: 30px; padding-bottom: 24px; border-bottom: 1px solid var(--line); }
  .profile-avatar { width: 64px; height: 64px; border-radius: 50%; background: rgba(0,192,232,0.12); color: var(--cyan); display: flex; align-items: center; justify-content: center; font-family: var(--heading); font-size: 26px; font-weight: 800; }
  .profile-info h1 { font-size: 24px; font-weight: 800; margin: 0 0 4px; }
  .profile-info p { color: var(--muted); font-size: 14px; margin: 0; font-weight: 600; }

  .profile-details { display: flex; flex-direction: column; gap: 16px; margin-bottom: 35px; }
  .detail-row { display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-bottom: 1px dashed var(--line); font-size: 14.5px; }
  .detail-label { color: var(--muted); font-weight: 600; }
  .detail-value { font-weight: 700; color: var(--black); }

  .profile-actions { display: flex; gap: 12px; }
  .profile-actions a { flex: 1; padding: 14px; border-radius: 12px; text-align: center; font-weight: 700; text-decoration: none; font-size: 14px; }
  .btn-primary-custom { background: var(--black); color: var(--white); }
  .btn-outline-custom { background: transparent; border: 1.5px solid #FF3B30; color: #FF3B30; }
  .btn-outline-custom:hover { background: rgba(255, 59, 48, 0.05); }
</style>
</head>
<body>

<nav class="nav">
  <div class="wrap">
    <a href="index.php" class="logo"><?= logoImg('light') ?> CADENCE</a>
    <div class="nav-links">
      <a href="home.php">Home</a>
      <a href="standard.php">Standard</a>
      <a href="shop.php">Shoes</a>
      <a href="reviews.php">Reviews</a>
      <a href="pricing.php">Pricing</a>
      <a href="faq.php">FAQ</a>
    </div>
    <div class="nav-right" style="display: flex; align-items: center; gap: 16px;">
      <a href="profile.php" class="user-pill" style="display: flex; align-items: center; gap: 6px; text-decoration: none; color: inherit; font-weight: 600; font-size: 14px;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle;">
          <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
          <circle cx="12" cy="7" r="4"></circle>
        </svg>
        <span><?= htmlspecialchars($username) ?></span>
      </a>

      <a href="cart.php" class="cart-link" style="position: relative; display: flex; align-items: center;">
        <svg width="21" height="21" viewBox="0 0 24 24" fill="none"><path d="M6 6H21L19 15H8L6 6Z" stroke="black" stroke-width="1.6" stroke-linejoin="round"/><path d="M6 6L5 3H2" stroke="black" stroke-width="1.6" stroke-linecap="round"/><circle cx="9.5" cy="19" r="1.4" fill="black"/><circle cx="17.5" cy="19" r="1.4" fill="black"/></svg>
        <?php if($cartCount > 0): ?><span class="cart-count"><?= $cartCount ?></span><?php endif; ?>
      </a>
      <a href="shop.php" class="btn btn-dark btn-sm">Shop Now</a>
    </div>
  </div>
</nav>

<div class="breadcrumb" style="padding: 16px 0; font-size: 13.5px; color: #6b6b73; border-bottom: 1px solid #E5E5EA;">
  <div class="wrap">
    <a href="index.php" style="color:inherit; text-decoration:none;">Home</a>
    <span class="sep" style="margin: 0 6px;">/</span>
    <span class="current" style="color: #0C0D10; font-weight: 600;">Profile</span>
  </div>
</div>

<main class="profile-main">
  <div class="wrap">
    <div class="profile-container">
      <div class="profile-header">
        <div class="profile-avatar">
          <?= strtoupper(substr($username, 0, 1)) ?>
        </div>
        <div class="profile-info">
          <h1><?= htmlspecialchars($username) ?></h1>
          <p>Cadence Member Account</p>
        </div>
      </div>

      <div class="profile-details">
        <div class="detail-row">
          <span class="detail-label">Username</span>
          <span class="detail-value"><?= htmlspecialchars($username) ?></span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Account Status</span>
          <span class="detail-value" style="color: #00A86B;">Active</span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Authentication Type</span>
          <span class="detail-value">Standard Session</span>
        </div>
      </div>

      <div class="profile-actions">
        <a href="shop.php" class="btn-primary-custom">Browse Shoes</a>
        <a href="logout.php" class="btn-outline-custom">Log Out</a>
      </div>
    </div>
  </div>
</main>

</body>
</html>