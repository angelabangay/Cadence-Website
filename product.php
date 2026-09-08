<?php
// ─────────────────────────────────────────────────────────────
// CADENCE — Product Detail Page (Database-Powered)
// ─────────────────────────────────────────────────────────────
session_start();
require_once 'database/config.php';

// ---------- 1. GET CURRENT PRODUCT ID FROM URL ----------
$id = isset($_GET['id']) ? trim($_GET['id']) : 'velo';

// ---------- 2. FETCH PRODUCT FROM DATABASE ----------
try {
    $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch();

    // Fallback to the first product if ID doesn't exist in database
    if (!$product) {
        $stmt = $pdo->query("SELECT * FROM products LIMIT 1");
        $product = $stmt->fetch();
    }
} catch (\PDOException $e) {
    // Fallback empty structure if query fails
    $product = [
        'id' => 'velo', 'name' => 'Velo', 'cat' => 'Flagship', 'tag' => 'runners', 'price' => 149, 
        'was' => 179, 'rating' => 4.9, 'reviews' => 1240, 'image' => 'images/velo.jpg',
        'stock' => 5
    ];
}

// ---------- 3. TAG-BASED DESCRIPTIONS ----------
$tagDescriptions = [
    'runners' => 'Built with lightweight, high-rebound cushioning designed specifically to maximize your running stride.',
    'professionals' => 'Sleek, polished comfort engineered for long hours on your feet in professional environments.',
    'gym' => 'Engineered with a flat, stable base and lateral support for cross-training and heavy lifting.',
    'travelers' => 'Ultra-packable, slip-on comfort made for jet-setting, walking airports, and exploring new cities.'
];

$shoeTag = strtolower(trim($product['tag'] ?? ''));
$shoeDesc = $tagDescriptions[$shoeTag] ?? 'Comfortable, stylish, and built for daily wear.';

// Default feature list
$features = [
    'Nitrogen-infused featherlight midsole',
    'Breathable engineered knit upper',
    'High-traction rubber outsole pods',
    'Targeted arch support structure'
];

// ---------- 4. HANDLE ADD TO CART POST (WITH LOGIN CHECK) ----------
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_to_cart'){
    
    // GUARD: Verify if user is logged in before adding items
    if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        header('Location: login.php');
        exit;
    }

    $addId  = preg_replace('/[^a-z\-]/', '', $_POST['id'] ?? '');
    $qty    = max(1, (int)($_POST['qty'] ?? 1));
    
    if(!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    $_SESSION['cart'][$addId] = ($_SESSION['cart'][$addId] ?? 0) + $qty;
    
    header("Location: product.php?id=" . urlencode($addId) . "&added=1");
    exit;
}

// ---------- 5. SAFE CART COUNT CALCULATION ----------
$cartCount = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $itemQty) {
        if (is_numeric($itemQty)) {
            $cartCount += (int)$itemQty;
        }
    }
}

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
<title><?= htmlspecialchars($product['name']) ?> — Cadence</title>
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
    }
    body {
        font-family: var(--body);
    }
    h1, h2, h3, .pdp-price .now, .logo {
        font-family: var(--heading);
    }
    .brand-logo-img { height: 24px; width: auto; vertical-align: middle; object-fit: contain; }
    main.pdp{ padding: 20px 0 100px; }
    
    .cart-link {
        position: relative;
        display: inline-flex;
        align-items: center;
        text-decoration: none;
    }
    .cart-count {
        position: absolute;
        top: -8px;
        right: -10px;
        background: var(--cyan);
        color: var(--black);
        font-size: 11px;
        font-weight: 800;
        padding: 1px 5px;
        border-radius: 999px;
        line-height: 1.2;
        box-shadow: 0 2px 5px rgba(0,0,0,0.15);
    }

    .back-btn-wrap {
        margin-bottom: 20px;
    }
    .back-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 13.5px;
        font-weight: 600;
        color: #6b6b73;
        text-decoration: none;
        transition: color 0.2s ease;
    }
    .back-btn svg {
        transition: transform 0.2s ease;
    }
    .back-btn:hover {
        color: #0C0D10;
    }
    .back-btn:hover svg {
        transform: translateX(-4px);
    }

    .pdp-grid{ display:grid; grid-template-columns: 1.05fr 0.95fr; gap: 60px; align-items:flex-start; }
    
    .pdp-gallery-main{ aspect-ratio: 1/1; border-radius: 20px; overflow:hidden; background:#F5F5F7; margin-bottom:14px; }
    .pdp-gallery-main img { width: 100%; height: 100%; object-fit: cover; }
    
    .pdp-info .p-cat{ margin-bottom:10px; font-size:13px; font-weight:700; text-transform:uppercase; color:#6b6b73; }
    .pdp-info h1{ font-size: clamp(28px,3.4vw,38px); font-weight:800; margin-bottom:10px; }
    .pdp-rating{ display:flex; align-items:center; gap:8px; font-size:13.5px; color:#6b6b73; font-weight:600; margin-bottom:16px;}
    .pdp-rating .stars{ color:#00C0E8; letter-spacing:2px; }
    
    .stock-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 12.5px;
        font-weight: 700;
        padding: 5px 12px;
        border-radius: 20px;
        margin-bottom: 20px;
    }
    .stock-in { background: #e8fbf1; color: #0a7a44; }
    .stock-low { background: #fff4e5; color: #b7791f; }
    .stock-out { background: #fde8e8; color: #c53030; }

    .pdp-price{ display:flex; align-items:baseline; gap:12px; margin-bottom: 22px; }
    .pdp-price .now{ font-size:28px; font-weight:800; }
    .pdp-price .was{ font-size:16px; color:#a9a9b0; text-decoration:line-through; }
    
    .pdp-desc{ font-size:14.5px; color:#5c5c64; line-height:1.7; margin-bottom: 30px; max-width:480px; }

    .option-block{ margin-bottom: 26px; }
    .option-label{ font-size:12.5px; font-weight:700; text-transform:uppercase; letter-spacing:.06em; margin-bottom:12px; display:flex; justify-content:space-between; }
    .option-label span.sel{ text-transform:none; font-weight:600; color:#6b6b73; }

    .size-row{ display:flex; gap:10px; flex-wrap:wrap; }
    .size-btn{
        min-width:52px; padding:11px 10px; text-align:center; border-radius:10px; border:1.5px solid #E5E5EA;
        font-size:13.5px; font-weight:700; background: #fff; cursor:pointer; transition: all .15s ease;
    }
    .size-btn.active{ background:#0C0D10; color:#fff; border-color:#0C0D10; }

    .qty-row{ display:flex; align-items:center; gap:14px; }
    .qty-control{ display:flex; align-items:center; border:1.5px solid #E5E5EA; border-radius:999px; overflow:hidden; }
    .qty-control button{ width:38px; height:38px; font-size:16px; font-weight:700; background:none; border:none; cursor:pointer; }
    .qty-control span{ min-width:32px; text-align:center; font-weight:700; font-size:14.5px; }

    .pdp-actions{ display:flex; gap:14px; margin-top: 30px; }
    .pdp-actions .btn{ flex:1; text-align:center; padding:14px 20px; border-radius:12px; font-weight:700; text-decoration:none; display:inline-flex; align-items:center; justify-content:center; }
    .btn-primary{ background:#0C0D10; color:#fff; border:none; cursor:pointer; }
    .btn-primary:disabled { background: #a9a9b0; cursor: not-allowed; }
    .btn-outline{ background:transparent; color:#0C0D10; border:1.5px solid #E5E5EA; }

    .added-toast{ margin-top:14px; padding:12px 16px; border-radius:10px; background:#e8fbf1; color:#0a7a44; font-size:13.5px; font-weight:700; display:none; align-items:center; gap:8px; }
    .added-toast.show{ display:flex; }

    .pdp-feat-list{ margin-top: 34px; padding-top: 28px; border-top:1px solid #E5E5EA; list-style:none; padding-left:0; }
    .pdp-feat-list li{ font-size:13.5px; color:#3a3a40; padding:9px 0; display:flex; gap:10px; align-items:flex-start; }
    .pdp-feat-list li::before{ content:"✓"; color:#00C0E8; font-weight:800; }

    @media (max-width: 900px){ .pdp-grid{ grid-template-columns:1fr; gap:34px; } }
</style>
</head>
<body>

<!-- Navigation Bar -->
<nav class="nav">
  <div class="wrap">
    <a href="index.php" class="logo"><?= logoImg('light') ?> CADENCE</a>
    <div class="nav-links">
      <a href="home.php">Home</a>
      <a href="standard.php">Standard</a>
      <a href="shop.php" class="active">Shoes</a>
      <a href="reviews.php">Reviews</a>
      <a href="pricing.php">Pricing</a>
      <a href="faq.php">FAQ</a>
    </div>
    <div class="nav-right">
      <a href="cart.php" class="cart-link">
        <svg width="21" height="21" viewBox="0 0 24 24" fill="none"><path d="M6 6H21L19 15H8L6 6Z" stroke="black" stroke-width="1.6" stroke-linejoin="round"/><path d="M6 6L5 3H2" stroke="black" stroke-width="1.6" stroke-linecap="round"/><circle cx="9.5" cy="19" r="1.4" fill="black"/><circle cx="17.5" cy="19" r="1.4" fill="black"/></svg>
        <?php if($cartCount > 0): ?>
          <span class="cart-count"><?= $cartCount ?></span>
        <?php endif; ?>
      </a>
      <a href="shop.php" class="btn btn-dark btn-sm">Shop Now</a>
    </div>
  </div>
</nav>

<!-- Breadcrumb Row -->
<div class="breadcrumb" style="padding: 16px 0; font-size: 13.5px; color: #6b6b73; border-bottom: 1px solid #E5E5EA; margin-bottom: 24px;">
  <div class="wrap">
    <a href="index.php" style="color:inherit; text-decoration:none;">Home</a>
    <span class="sep" style="margin: 0 6px;">/</span>
    <a href="shop.php" style="color:inherit; text-decoration:none;">Shop</a>
    <span class="sep" style="margin: 0 6px;">/</span>
    <span class="current" style="color: #0C0D10; font-weight: 600;"><?= htmlspecialchars($product['name']) ?></span>
  </div>
</div>

<main class="pdp">
  <div class="wrap">
    
    <div class="back-btn-wrap">
      <a href="shop.php" class="back-btn">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none"><path d="M19 12H5M5 12L12 19M5 12L12 5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
        Back to shoes
      </a>
    </div>

    <div class="pdp-grid">
      <!-- Gallery Main Image -->
      <div class="pdp-gallery">
        <div class="pdp-gallery-main">
          <img src="<?= htmlspecialchars($product['image']) ?>" alt="<?= htmlspecialchars($product['name']) ?>">
        </div>
      </div>

      <!-- Product Info -->
      <div class="pdp-info">
        <div class="p-cat"><?= htmlspecialchars($product['cat']) ?></div>
        <h1><?= htmlspecialchars($product['name']) ?></h1>
        
        <div class="pdp-rating">
          <span class="stars">★★★★★</span>
          <span><?= htmlspecialchars($product['rating'] ?? '4.8') ?> · <?= number_format($product['reviews'] ?? 500) ?> reviews</span>
        </div>

        <?php 
          $stock = isset($product['stock']) ? (int)$product['stock'] : 10;
          if ($stock > 5) {
              echo '<div class="stock-badge stock-in">● In Stock (' . $stock . ' available)</div>';
          } elseif ($stock > 0) {
              echo '<div class="stock-badge stock-low">● Low Stock - Only ' . $stock . ' left!</div>';
          } else {
              echo '<div class="stock-badge stock-out">● Out of Stock</div>';
          }
        ?>
        
        <div class="pdp-price">
          <span class="now">$<?= htmlspecialchars($product['price']) ?></span>
          <?php if(isset($product['was']) && $product['was'] > 0): ?>
            <span class="was">$<?= htmlspecialchars($product['was']) ?></span>
          <?php endif; ?>
        </div>
        
        <p class="pdp-desc"><?= htmlspecialchars($shoeDesc) ?></p>

        <!-- Actions Form Container -->
        <form method="POST" action="product.php?id=<?= htmlspecialchars($product['id']) ?>">
          <input type="hidden" name="action" value="add_to_cart">
          <input type="hidden" name="id" value="<?= htmlspecialchars($product['id']) ?>">
          <input type="hidden" name="size" id="formSizeInput" value="">
          <input type="hidden" name="qty" id="formQtyInput" value="1">

          <!-- Size Selector -->
          <div class="option-block">
            <div class="option-label"><span>Size (US)</span><span class="sel" id="sizeSel">Select a size</span></div>
            <div class="size-row" id="sizeRow">
              <?php foreach(["6","7","8","9","10","11","12","13"] as $sz): ?>
                <button type="button" class="size-btn" data-size="<?= $sz ?>" onclick="selectSize(this)"><?= $sz ?></button>
              <?php endforeach; ?>
            </div>
          </div>

          <!-- Quantity -->
          <div class="option-block">
            <div class="option-label"><span>Quantity</span></div>
            <div class="qty-row">
              <div class="qty-control">
                <button type="button" onclick="updateQty(-1)">−</button>
                <span id="qtyVal">1</span>
                <button type="button" onclick="updateQty(1)">+</button>
              </div>
            </div>
          </div>

          <!-- Actions -->
          <div class="pdp-actions">
            <?php if($stock > 0): ?>
              <button class="btn btn-primary" type="submit" onclick="return validateSize(event)">Add to Cart</button>
            <?php else: ?>
              <button class="btn btn-primary" type="button" disabled>Out of Stock</button>
            <?php endif; ?>
            <a href="cart.php" class="btn btn-outline">View Cart</a>
          </div>
        </form>

        <?php if(isset($_GET['added'])): ?>
          <div class="added-toast show">✓ Added to your cart</div>
        <?php endif; ?>

        <!-- Features List -->
        <ul class="pdp-feat-list">
          <?php foreach($features as $feat): ?>
            <li><?= htmlspecialchars($feat) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    </div>

  </div>
</main>

<script>
  let selectedSize = null;
  let quantity = 1;
  const maxStock = <?= $stock ?>;

  function selectSize(btn){
    document.querySelectorAll('.size-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    selectedSize = btn.dataset.size;
    
    document.getElementById('sizeSel').textContent = `US ${selectedSize}`;
    document.getElementById('sizeSel').style.color = '#6b6b73';
    document.getElementById('formSizeInput').value = selectedSize;
  }

  function updateQty(change){
    quantity = Math.max(1, Math.min(maxStock > 0 ? maxStock : 1, quantity + change));
    document.getElementById('qtyVal').textContent = quantity;
    document.getElementById('formQtyInput').value = quantity;
  }

  function validateSize(event){
    if(!selectedSize){
      event.preventDefault();
      const sizeSel = document.getElementById('sizeSel');
      sizeSel.textContent = 'Please select a size';
      sizeSel.style.color = '#e0574a';
      return false;
    }
    return true;
  }
</script>
</body>
</html>