<?php
// ─────────────────────────────────────────────────────────────
// CADENCE — Shop All Shoes (PHP + CSS only)
// Filter + sort via ?filter= and ?sort= query params.
// ─────────────────────────────────────────────────────────────
session_start();

// ---------- 1. DATA (Loaded from Database) ----------
require_once 'database/config.php';

try {
    $stmt = $pdo->query("SELECT * FROM products");
    $PRODUCTS = $stmt->fetchAll();
} catch (\PDOException $e) {
    $PRODUCTS = []; // Fallback if query fails
}

// ---------- 2. STATE ----------
$filter       = isset($_GET['filter']) ? preg_replace('/[^a-z]/','',strtolower($_GET['filter'])) : 'all';
$sort         = isset($_GET['sort'])   ? preg_replace('/[^a-z\-]/','',$_GET['sort'])            : 'featured';
$validFilters = ['all','runners','professionals','gym','travelers'];
$validSorts   = ['featured','rating']; 
if(!in_array($filter,$validFilters)) $filter = 'all';
if(!in_array($sort,  $validSorts))   $sort   = 'featured';

// ---------- 3. FILTER + SORT ----------
$filtered = ($filter === 'all') ? $PRODUCTS : array_filter($PRODUCTS, fn($p) => $p['tag'] === $filter);
$filtered = array_values($filtered); // reindex

if($sort !== 'featured'){
    usort($filtered, function($a, $b) use ($sort){
        if ($sort === 'rating') {
            return $b['rating'] <=> $a['rating'];
        }
        return 0;
    });
}

// ---------- 4. URL HELPERS ----------
function shopUrl(array $params, string $currentFilter, string $currentSort): string {
    $merged = array_merge(['filter' => $currentFilter, 'sort' => $currentSort], $params);
    $clean  = array_filter($merged, fn($v, $k) => !($k==='filter' && $v==='all') && !($k==='sort' && $v==='featured'), ARRAY_FILTER_USE_BOTH);
    return 'shop.php' . (count($clean) ? '?' . http_build_query($clean) : '');
}

// ---------- 5. CART + AUTH + HELPERS ----------
$cartCount = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;
$isLoggedIn = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
$username = $_SESSION['username'] ?? '';

function logoImg($variant = 'light'){
    $filename = ($variant === 'dark') ? 'logo2.png' : 'logo1.png';
    return '<img src="images/' . $filename . '" alt="Cadence Logo" class="brand-logo-img">';
}

$filterLabels = ['all'=>'All','runners'=>'Runners','professionals'=>'Professionals','gym'=>'Gym-Goers','travelers'=>'Travelers'];
$sortLabels   = ['featured'=>'Sort: Featured','rating'=>'Highest Rated']; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Shop All Shoes — Cadence</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Afacad+Flux:wght@500;600;700;800&family=Afacad:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/style.css">
<style>
    :root{
        --black:#0C0D10;--white:#ffffff;--off:#F5F5F7;--cyan:#00C0E8;
        --line:#E5E5EA;--line-dark:rgba(255,255,255,0.12);--muted:#6b6b73;
        --heading:'Afacad Flux','Afacad',system-ui,sans-serif;
        --body:'Afacad',system-ui,-apple-system,Segoe UI,Roboto,sans-serif;
    }
    .brand-logo-img { height: 24px; width: auto; vertical-align: middle; object-fit: contain; }
    .shop-hero {
        position: relative;
        background: url('images/hero-bg.jpg') center/cover no-repeat;
        padding: 80px 0;
        margin-top: 50px;
        color: var(--white);
        text-align: center;
    }
    .shop-hero::before {
        content: '';
        position: absolute;
        inset: 0;
        background: rgba(0, 0, 0, 0.65); 
        z-index: 1;
    }
    .shop-hero .wrap {
        position: relative;
        z-index: 2;
        display: flex;
        flex-direction: column;
        align-items: center;
    }
    .shop-hero h1 {
        font-size: clamp(32px, 4.5vw, 48px);
        font-weight: 800;
        margin: 0;
        color: var(--white);
    }
    .shop-hero p {
        color: rgba(255, 255, 255, 0.85);
        margin-top: 12px;
        max-width: 520px;
        font-size: 15px;
        line-height: 1.6;
        text-align: center;
    }
    .sort-select{
        padding:10px 14px; border-radius:10px; border:1.5px solid var(--line); font-family:inherit;
        font-size:13.5px; font-weight:600; background:var(--white); color:var(--black); cursor:pointer;
    }
    .result-count{ font-size:13px; color:var(--muted); font-weight:600; }
    main.shop-main{ padding-bottom:100px; }
    .empty-state{ text-align:center; padding: 70px 20px; color:var(--muted); }
    .empty-state a{ color:var(--cyan); font-weight:700; }
    .p-thumb img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }
    .account-menu {
        position: relative;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: var(--black);
        font-size: 14px;
        font-weight: 600;
        text-decoration: none;
        cursor: pointer;
        background: var(--off);
        padding: 6px 12px;
        border-radius: 20px;
        border: 1px solid var(--line);
    }
    .account-menu:hover {
        border-color: var(--cyan);
    }
</style>
</head>
<body>

<nav class="nav">
  <div class="wrap">
    <a href="index.php" class="logo"><?= logoImg('light') ?> CADENCE</a>
    <div class="nav-links">
      <a href="index.php">Home</a>
      <a href="standard.php">Standard</a>
      <a href="shop.php" class="active">Shoes</a>
      <a href="reviews.php">Reviews</a>
      <a href="pricing.php">Pricing</a>
      <a href="faq.php">FAQ</a>
    </div>
    <div class="nav-right" style="display: flex; align-items: center; gap: 16px;">
      <?php if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true): ?>
        <a href="profile.php" class="user-pill" style="display: flex; align-items: center; gap: 6px; text-decoration: none; color: inherit; font-weight: 600; font-size: 14px;">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle;">
            <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
            <circle cx="12" cy="7" r="4"></circle>
          </svg>
          <span><?= htmlspecialchars($_SESSION['username'] ?? 'User') ?></span>
        </a>
      <?php else: ?>
        <a href="login.php" class="user-pill" style="display: flex; align-items: center; gap: 6px; text-decoration: none; color: inherit; font-weight: 600; font-size: 14px;" title="Login">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle;">
            <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
            <circle cx="12" cy="7" r="4"></circle>
          </svg>
          <span>Login</span>
        </a>
      <?php endif; ?>

      <a href="cart.php" class="cart-link">
        <svg width="21" height="21" viewBox="0 0 24 24" fill="none"><path d="M6 6H21L19 15H8L6 6Z" stroke="black" stroke-width="1.6" stroke-linejoin="round"/><path d="M6 6L5 3H2" stroke="black" stroke-width="1.6" stroke-linecap="round"/><circle cx="9.5" cy="19" r="1.4" fill="black"/><circle cx="17.5" cy="19" r="1.4" fill="black"/></svg>
        <?php if($cartCount > 0): ?><span class="cart-count"><?= $cartCount ?></span><?php endif; ?>
      </a>
      <a href="shop.php" class="btn btn-dark btn-sm">Shop Now</a>
    </div>
  </div>
</nav>

<div class="breadcrumb">
  <div class="wrap"><a href="home.php">Home</a><span class="sep">/</span><span class="current">Shop</span></div>
</div>

<header class="shop-hero">
  <div class="wrap">
    <h1>Built for every kind of day</h1>
    <p>Fifteen models, one philosophy: lightweight, all-day comfort that keeps pace with whatever your day looks like.</p>
  </div>
</header>

<main class="shop-main">
  <div class="wrap">

    <!-- Filters -->
    <div class="filter-row left">
      <?php foreach($filterLabels as $key => $label):
        $active = ($filter === $key) ? ' class="active"' : '';
      ?>
        <a href="<?= htmlspecialchars(shopUrl(['filter' => $key], $filter, $sort)) ?>"<?= $active ?>>
          <?= htmlspecialchars($label) ?>
        </a>
      <?php endforeach; ?>
    </div>

    <!-- Toolbar -->
    <div class="shop-toolbar">
      <span class="result-count">
        <?= count($filtered) ?> shoe<?= count($filtered) === 1 ? '' : 's' ?>
      </span>
      <form method="GET" action="shop.php" class="sort-form">
        <?php if($filter !== 'all'): ?>
          <input type="hidden" name="filter" value="<?= htmlspecialchars($filter) ?>">
        <?php endif; ?>
        <select class="sort-select" name="sort" onchange="this.form.submit()">
          <?php foreach($sortLabels as $key => $label): ?>
            <option value="<?= $key ?>" <?= $sort === $key ? 'selected' : '' ?>>
              <?= htmlspecialchars($label) ?>
            </option>
          <?php endforeach; ?>
        </select>
        <button type="submit" class="sort-submit" style="display:none;">Apply</button>
      </form>
    </div>

    <?php if(count($filtered) === 0): ?>
      <div class="empty-state">
        <p>No shoes match that filter yet — <a href="shop.php">see the full lineup →</a></p>
      </div>
    <?php else: ?>
      <div class="product-grid">
        <?php foreach($filtered as $p): ?>
          <a class="p-card" href="product.php?id=<?= urlencode($p['id']) ?>">
            <div class="p-thumb">
              <?php if(!empty($p['badge'])): ?><div class="p-badge"><?= htmlspecialchars($p['badge']) ?></div><?php endif; ?>
              <img src="<?= htmlspecialchars($p['image']) ?>" alt="<?= htmlspecialchars($p['name']) ?>" loading="lazy">
            </div>
            <div class="p-body">
              <div class="p-cat"><?= htmlspecialchars($p['cat']) ?></div>
              <div class="p-row"><span class="p-name"><?= htmlspecialchars($p['name']) ?></span></div>
              <div class="p-row"><span class="p-price">$<?= $p['price'] ?></span><span class="p-rating"><span class="star">★</span> <?= $p['rating'] ?></span></div>
            </div>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

  </div>
</main>

<footer style="background:#000000; color:#ffffff; padding:70px 0 35px; border-top:1px solid rgba(255,255,255,0.06);">
  <div class="footer-inner" style="max-width:1200px; margin:0 auto; padding:0 24px;">
    <div class="footer-grid" style="display:grid; grid-template-columns:2fr 1fr 1fr 1fr; gap:40px; align-items:start;">
      <div class="footer-brand">
        <a href="index.php" class="logo" style="display:inline-flex; align-items:center; gap:8px; font-family:var(--heading); font-size:20px; font-weight:800; letter-spacing:0.08em; color:#ffffff; text-decoration:none; margin-bottom:16px;"><?= logoImg('dark') ?> CADENCE</a>
        <p style="color:#8e8e93; font-size:14px; line-height:1.5; max-width:280px;">Comfort-first footwear for every body, every day.</p>
      </div>
      <div class="footer-col">
        <h4 style="font-family:var(--heading); font-size:12px; font-weight:700; letter-spacing:0.14em; text-transform:uppercase; color:#ffffff; margin-bottom:20px;">Shop</h4>
        <ul style="list-style:none; padding:0; margin:0; display:flex; flex-direction:column; gap:12px;">
          <li><a href="shop.php" style="color:#8e8e93; text-decoration:none; font-size:14px;">All Shoes</a></li>
          <li><a href="cart.php" style="color:#8e8e93; text-decoration:none; font-size:14px;">Cart</a></li>
        </ul>
      </div>
      <div class="footer-col">
        <h4 style="font-family:var(--heading); font-size:12px; font-weight:700; letter-spacing:0.14em; text-transform:uppercase; color:#ffffff; margin-bottom:20px;">Support</h4>
        <ul style="list-style:none; padding:0; margin:0; display:flex; flex-direction:column; gap:12px;">
          <li><a href="#" style="color:#8e8e93; text-decoration:none; font-size:14px;">Size Guide</a></li>
          <li><a href="#" style="color:#8e8e93; text-decoration:none; font-size:14px;">Shipping</a></li>
          <li><a href="#" style="color:#8e8e93; text-decoration:none; font-size:14px;">Returns</a></li>
        </ul>
      </div>
      <div class="footer-col">
        <h4 style="font-family:var(--heading); font-size:12px; font-weight:700; letter-spacing:0.14em; text-transform:uppercase; color:#ffffff; margin-bottom:20px;">Follow</h4>
        <ul style="list-style:none; padding:0; margin:0; display:flex; flex-direction:column; gap:12px;">
          <li><a href="#" style="color:#8e8e93; text-decoration:none; font-size:14px; display:flex; align-items:center; gap:8px;">Instagram</a></li>
          <li><a href="#" style="color:#8e8e93; text-decoration:none; font-size:14px; display:flex; align-items:center; gap:8px;">TikTok</a></li>
          <li><a href="#" style="color:#8e8e93; text-decoration:none; font-size:14px; display:flex; align-items:center; gap:8px;">Strava</a></li>
        </ul>
      </div>
    </div>
    <div class="footer-bottom" style="margin-top:60px; padding-top:24px; border-top:1px solid rgba(255,255,255,0.08); display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px;">
      <p style="color:#636366; font-size:13px; margin:0;">&copy; <?= date('Y') ?> Cadence Athletics. All rights reserved.</p>
      <div class="footer-legal" style="display:flex; gap:24px;">
        <a href="#" style="color:#636366; font-size:13px; text-decoration:none;">Privacy</a>
        <a href="#" style="color:#636366; font-size:13px; text-decoration:none;">Terms</a>
        <a href="#" style="color:#636366; font-size:13px; text-decoration:none;">Cookies</a>
      </div>
    </div>
  </div>
</footer>

</body>
</html>