<?php
// ─────────────────────────────────────────────────────────────
// CADENCE — The Cadence Standard (PHP + CSS only)
// ─────────────────────────────────────────────────────────────
session_start();

$cartCount = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;

function logoImg($variant = 'light'){
  $filename = ($variant === 'dark') ? 'logo2.png' : 'logo1.png';
  return '<img src="images/' . $filename . '" alt="Cadence Logo" class="brand-logo-img">';
}

$PILLARS = [
  [
    'icon' => '<svg width="26" height="26" viewBox="0 0 24 24" fill="none"><path d="M6 8h12l1 12H5L6 8Z" stroke="#00C0E8" stroke-width="1.6" stroke-linejoin="round"/><path d="M9 8a3 3 0 0 1 6 0" stroke="#00C0E8" stroke-width="1.6"/></svg>',
    'idx'  => 'WEIGHT',
    'title'=> 'Lightweight feel',
    'body' => 'Barely-there weight for commutes, workouts, errands, and travel days.',
  ],
  [
    'icon' => '<svg width="26" height="26" viewBox="0 0 24 24" fill="none"><path d="M4 16L11 4h2l7 12-8 4-8-4Z" stroke="#00C0E8" stroke-width="1.6" stroke-linejoin="round"/></svg>',
    'idx'  => 'STYLE',
    'title'=> 'Minimal style',
    'body' => 'Clean enough for daily outfits, capable enough for active routines.',
  ],
  [
    'icon' => '<svg width="26" height="26" viewBox="0 0 24 24" fill="none"><rect x="4" y="4" width="7" height="7" rx="1.5" stroke="#00C0E8" stroke-width="1.6"/><rect x="13" y="4" width="7" height="7" rx="1.5" stroke="#00C0E8" stroke-width="1.6"/><rect x="4" y="13" width="7" height="7" rx="1.5" stroke="#00C0E8" stroke-width="1.6"/><rect x="13" y="13" width="7" height="7" rx="1.5" stroke="#00C0E8" stroke-width="1.6"/></svg>',
    'idx'  => 'MATERIAL',
    'title'=> 'Premium finish',
    'body' => 'Refined materials, precise construction, and details built to last.',
  ],
  [
    'icon' => '<svg width="26" height="26" viewBox="0 0 24 24" fill="none"><path d="M12 20s-7-4.4-7-10a4.5 4.5 0 0 1 7-3.7A4.5 4.5 0 0 1 19 10c0 5.6-7 10-7 10Z" stroke="#00C0E8" stroke-width="1.6" stroke-linejoin="round"/></svg>',
    'idx'  => 'FEEL',
    'title'=> 'Comfort first',
    'body' => 'Soft underfoot from the first step, supportive through the last hour.',
  ],
];

$PROCESS = [
  ['step'=>'01', 'title'=>'Material sourcing', 'body'=>'Every FeatherKnit upper and GripLine outsole is sourced against a fixed weight and durability spec before a single unit is cut.'],
  ['step'=>'02', 'title'=>'Wear testing',         'body'=>'Prototypes log real miles with our test community — runners, professionals, gym-goers, and travelers — before a design is approved.'],
  ['step'=>'03', 'title'=>'Comfort certification','body'=>'Each model must clear our All-Day Comfort bar: soft from the first step, still supportive after eight-plus hours on your feet.'],
];

$METRICS = [
  ['value'=>'185g',   'label'=>'Average weight, featherlight class'],
  ['value'=>'500mi',  'label'=>'Max tested lifespan, flagship models'],
  ['value'=>'4.9★',   'label'=>'Average rating, 12.4K+ reviews'],
  ['value'=>'30-day', 'label'=>'Trial run on every pair'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>The Cadence Standard — Cadence</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Afacad+Flux:wght@500;600;700;800&family=Afacad:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/style.css">
<style>
  .std-hero {
    position: relative; color: var(--white); padding: 100px 0 110px; text-align: center; overflow: hidden;
  }
  .std-hero .bg-photo { position: absolute; inset: 0; z-index: 0; }
  .std-hero .bg-photo img { width: 100%; height: 100%; object-fit: cover; }
  .std-hero .bg-photo::after { content: ""; position: absolute; inset: 0; background: linear-gradient(180deg, rgba(9,10,12,0.55) 0%, rgba(9,10,12,0.72) 55%, rgba(9,10,12,0.92) 100%); }
  .std-hero .wrap { position: relative; z-index: 1; }
  .std-hero .eyebrow {
    display: block; margin-bottom: 16px;
    font-size: 11.5px; font-weight: 700; letter-spacing: .16em; text-transform: uppercase; color: var(--cyan);
  }
  .std-hero h1 { font-size: clamp(32px,4.6vw,54px); font-weight: 800; max-width: 780px; margin: 0 auto; line-height: 1.05; }
  .std-hero .accent { color: var(--cyan); }
  .std-hero p.lede { margin: 22px auto 0; max-width: 560px; color: #B9BAC2; font-size: 16px; line-height: 1.65; }

  .std-mission { padding: 90px 0; background: var(--off); }
  .std-mission blockquote {
    max-width: 760px; margin: 0 auto; text-align: center;
    font-family: var(--heading); font-size: clamp(21px,2.6vw,30px); font-weight: 700; line-height: 1.45; color: var(--black);
  }
  .std-mission blockquote::before { content: "“"; color: var(--cyan); }
  .std-mission blockquote::after { content: "”"; color: var(--cyan); }
  .std-mission .attr { text-align: center; margin-top: 22px; font-size: 12.5px; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; color: var(--muted); }

  /* Pillars Split Section */
  .pillars { padding: 110px 0; background: var(--black); color: var(--white); }
  .pillars-container { display: grid; grid-template-columns: 1fr 1fr; gap: 60px; align-items: center; max-width: 1200px; margin: 0 auto; padding: 0 24px; }
  .pillars-image img { width: 100%; height: 640px; object-fit: cover; border-radius: 24px; }
  .pillars-content h2 { font-size: clamp(28px, 3.2vw, 40px); font-weight: 800; margin-bottom: 40px; line-height: 1.15; }
  .pillar-grid-2x2 { display: grid; grid-template-columns: 1fr 1fr; gap: 35px 30px; }
  .pillar { padding-top: 0; border: none; }
  .pillar-icon { margin-bottom: 12px; }
  .pillar .idx { font-family: var(--heading); font-size: 11.5px; font-weight: 700; letter-spacing: .12em; color: var(--cyan); margin-bottom: 8px; text-transform: uppercase; }
  .pillar h3 { font-size: 18px; font-weight: 700; margin-bottom: 8px; }
  .pillar p { font-size: 13px; color: #9a9aa2; line-height: 1.6; }

  /* Logo Image styling */
  .brand-logo-img { height: 24px; width: auto; vertical-align: middle; object-fit: contain; }

  .process { padding: 110px 0; background: var(--white); }
  .process-grid { display: grid; grid-template-columns: repeat(3,1fr); gap: 26px; margin-top: 50px; }
  .process-card { background: var(--off); border-radius: 18px; padding: 30px; }
  .process-card .step-badge {
    width: 36px; height: 36px; border-radius: 50%; background: var(--black); color: var(--white);
    display: flex; align-items: center; justify-content: center; font-family: var(--heading); font-weight: 800; font-size: 14px; margin-bottom: 20px;
  }
  .process-card h3 { font-size: 17px; font-weight: 700; margin-bottom: 10px; color: #1a1a1f; }
  .process-card p { font-size: 13.5px; color: #5c5c64; line-height: 1.65; }

  .metrics { padding: 90px 0; background: linear-gradient(180deg,#0C0D10 0%, #0A0B0D 100%); color: var(--white); }
  .metrics-grid { display: grid; grid-template-columns: repeat(4,1fr); gap: 24px; text-align: center; }
  .metric b { display: block; font-family: var(--heading); font-size: clamp(32px,4vw,44px); font-weight: 800; color: var(--cyan); }
  .metric span { font-size: 12.5px; color: #9a9aa2; font-weight: 600; letter-spacing: .02em; margin-top: 8px; display: block; }

  .std-cta { padding: 90px 0; text-align: center; background: var(--off); color: var(--black); }
  .std-cta h2 { font-size: clamp(26px,3.4vw,38px); font-weight: 800; margin-bottom: 16px; }
  .std-cta p { color: #5c5c64; max-width: 460px; margin: 0 auto 30px; font-size: 15px; line-height: 1.6; }

  @media (max-width: 992px) {
    .pillars-container { grid-template-columns: 1fr; gap: 40px; }
    .pillars-image img { height: 450px; }
    .process-grid { grid-template-columns: 1fr; }
  }
  @media (max-width: 560px) {
    .pillar-grid-2x2 { grid-template-columns: 1fr; }
    .metrics-grid { grid-template-columns: 1fr 1fr; gap: 36px; }
  }
</style>
</head>
<body>

<nav class="nav">
  <div class="wrap">
    <a href="index.php" class="logo"><?= logoImg('light') ?> CADENCE</a>
    <div class="nav-links">
      <a href="index.php">Home</a>
      <a href="standard.php" class="active">Standard</a>
      <a href="shop.php">Shoes</a>
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

<header class="std-hero">
  <div class="bg-photo"><img src="images/standard2.jpg" alt="Runner at golden hour"></div>
  <div class="wrap">
    <span class="eyebrow">The Cadence Standard</span>
    <h1>Every pair is held to <span class="accent">one standard.</span> No exceptions.</h1>
    <p class="lede">From material sourcing to the final stitch, every Cadence shoe passes through the same rigorous bar for weight, comfort, and durability so dialed in means something every time you lace up.</p>
  </div>
</header>

<section class="std-mission">
  <div class="wrap">
    <blockquote>We build footwear that keeps pace with you. Engineered for performance, priced for everyday training, designed to disappear beneath your stride so nothing stands between you and your next mile.</blockquote>
    <p class="attr">— The Cadence Mission</p>
  </div>
</section>

<section class="pillars">
  <div class="pillars-container">
    <div class="pillars-image">
      <img src="images/standard.jpg" alt="Action shot">
    </div>
    <div class="pillars-content">
      <h2>Made for the full rhythm of your day.</h2>
      <div class="pillar-grid-2x2">
        <?php foreach($PILLARS as $p): ?>
          <div class="pillar">
            <div class="pillar-icon"><?= $p['icon'] ?></div>
            <div class="idx"><?= htmlspecialchars($p['idx']) ?></div>
            <h3><?= htmlspecialchars($p['title']) ?></h3>
            <p><?= htmlspecialchars($p['body']) ?></p>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<section class="process">
  <div class="wrap">
    <div class="section-head">
      <span class="eyebrow">How We Test</span>
      <h2>Nothing ships until it earns it</h2>
    </div>
    <div class="process-grid">
      <?php foreach($PROCESS as $step): ?>
        <div class="process-card">
          <div class="step-badge"><?= $step['step'] ?></div>
          <h3><?= htmlspecialchars($step['title']) ?></h3>
          <p><?= htmlspecialchars($step['body']) ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="metrics">
  <div class="wrap">
    <div class="metrics-grid">
      <?php foreach($METRICS as $m): ?>
        <div class="metric"><b><?= $m['value'] ?></b><span><?= htmlspecialchars($m['label']) ?></span></div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="std-cta">
  <div class="wrap">
    <h2>See the standard in every pair</h2>
    <p>Every model in the lineup is held to the same bar — pick the one built for your day.</p>
    <a href="shop.php" class="btn btn-dark">Shop the Collection</a>
  </div>
</section>

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