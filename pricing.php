<?php
// ─────────────────────────────────────────────────────────────
// CADENCE — Pricing (PHP + CSS only with Card Hover Effects)
// ─────────────────────────────────────────────────────────────
session_start();

// ---------- 1. DATA ----------
$PLANS = [
  [
    'name'     => 'Tempo',
    'cat'      => 'Everyday Comfort',
    'price'    => 99,
    'was'      => 124,
    'featured' => false,
    'badge'    => '',
    'features' => ['FeatherKnit upper', 'GripLine outsole', '3 color ways', '300-mile lifespan'],
  ],
  [
    'name'     => 'Velo',
    'cat'      => 'Flagship',
    'price'    => 149,
    'was'      => 186,
    'featured' => true,
    'badge'    => 'Most Popular',
    'features' => ['FeatherKnit upper', 'Tempo plate', 'Dialed fit system', 'All 8 colorways', '500-mile lifespan'],
  ],
  [
    'name'     => 'Ridge',
    'cat'      => 'Work Durability',
    'price'    => 139,
    'was'      => 174,
    'featured' => false,
    'badge'    => '',
    'features' => ['FeatherKnit upper', 'GripLine outsole', '5 color ways', '500-mile lifespan'],
  ],
];

// Cost allocation bars (illustrative, not audited figures)
$BARS = [
  [
    'label' => 'Typical Retail Brand',
    'segments' => [
      ['label'=>'Materials',                     'pct'=>22, 'bg'=>'#0A0A0B', 'color'=>'#fff'],
      ['label'=>'Labor',                         'pct'=>14, 'bg'=>'#3a3a40', 'color'=>'#fff'],
      ['label'=>'Markup, Retail & Marketing','pct'=>64, 'bg'=>'#d9d9e0', 'color'=>'#3a3a40'],
    ],
  ],
  [
    'label' => 'Cadence, Direct',
    'segments' => [
      ['label'=>'Materials',       'pct'=>52, 'bg'=>'#00C0E8', 'color'=>'#00161b'],
      ['label'=>'Labor & Testing', 'pct'=>28, 'bg'=>'#0A0A0B', 'color'=>'#fff'],
      ['label'=>'Direct Margin',   'pct'=>20, 'bg'=>'#d9d9e0', 'color'=>'#3a3a40'],
    ],
  ],
];

$GUARANTEES = [
  ['icon'=>'🏃', 'title'=>'30-day trial run',       'body'=>"Wear them on runs, at work, or around town for a full 30 days. Real-world testing, not just a fitting room."],
  ['icon'=>'↩️', 'title'=>'Free returns & exchanges','body'=>"Not the right fit or feel? Send them back or swap sizes at no cost — no restocking fees, no fine print."],
  ['icon'=>'💳', 'title'=>'No middlemen markup',    'body'=>"We sell direct-to-consumer, which is how premium comfort and performance tech reaches you at everyday prices."],
];

// ---------- 2. CART + HELPERS ----------
$cartCount = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;

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
<title>Pricing — Cadence</title>
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
  .pricing-page{ padding: 80px 0 110px; background: linear-gradient(180deg,#0C0D10 0%, #0A0B0D 100%); color:var(--white);}
  .pricing-head{ text-align:center; max-width: 640px; margin: 0 auto 10px;}
  .pricing-head .eyebrow{
    display:block; margin-bottom:14px;
    font-size:11.5px; font-weight:700; letter-spacing:.16em; text-transform:uppercase; color:var(--cyan);
  }
  .pricing-head h1{ font-size: clamp(30px,4vw,46px); font-weight:800; margin:0; }
  .pricing-head .accent{color:var(--cyan);}
  .pricing-head p{ color:#9a9aa2; margin-top:16px; font-size:15px; line-height:1.6;}
  .promo-banner{ max-width: 640px; margin: 30px auto 0; display:flex; align-items:center; justify-content:center; gap:10px; background: rgba(0,192,232,0.1); border:1px solid rgba(0,192,232,0.3); border-radius:999px; padding: 11px 22px; font-size:13px; font-weight:600; color:#cdeef7; flex-wrap:wrap; text-align:center;}
  .promo-banner b{ color: var(--cyan); font-weight:800;}
  
  .price-grid{ display:grid; grid-template-columns: repeat(3,1fr); gap:24px; margin-top: 54px; align-items:end;}
  
  /* Pricing Card Base & Hover Effects */
  .price-card{ 
    background: #131417; 
    border: 1px solid rgba(255,255,255,0.08); 
    border-radius: 20px; 
    padding: 34px 30px; 
    position: relative;
    transition: transform 0.3s ease, border-color 0.3s ease, box-shadow 0.3s ease;
  }
  .price-card:hover {
    transform: translateY(-6px);
    border-color: rgba(255, 255, 255, 0.25);
    box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
  }

  /* Featured Card Styles & Hover Effects */
  .price-card.featured{ 
    background: linear-gradient(180deg, #0F2C33 0%, #0A1E23 100%); 
    border: 1.5px solid var(--cyan); 
    transform: scale(1.04); 
    box-shadow: 0 30px 60px rgba(0,192,232,0.15);
  }
  .price-card.featured:hover {
    transform: scale(1.06) translateY(-4px);
    box-shadow: 0 35px 70px rgba(0,192,232,0.28);
    border-color: #33d3ff;
  }

  .price-tag{ position:absolute; top:-13px; left:50%; transform:translateX(-50%); background: var(--cyan); color:#00161b; font-size:11px; font-weight:800; padding:5px 14px; border-radius:999px; white-space:nowrap;}
  .price-card .p-cat{ color:#9a9aa2; font-size:10.5px; font-weight:700; letter-spacing:.1em; text-transform:uppercase; }
  .price-card h3{ font-family:var(--heading); font-size:26px; font-weight:800; margin: 6px 0 18px;}
  .price-amt{ display:flex; align-items:baseline; gap:10px; margin-bottom: 4px;}
  .price-amt .now{ font-size:34px; font-weight:800; font-family:var(--heading);}
  .price-amt .was{ font-size:16px; color:#6d6e76; text-decoration: line-through;}
  .price-card .per{ font-size:12.5px; color:#8b8c94; margin-bottom:24px;}
  .price-feats{ margin-bottom: 28px; list-style:none; padding:0;}
  .price-feats li{ font-size:13.5px; color:#c6c7cd; padding:8px 0; border-top:1px solid rgba(255,255,255,0.07); display:flex; gap:8px; align-items:flex-start;}
  .price-feats li:first-child{ border-top:none;}
  .price-feats li::before{ content:"✓"; color:var(--cyan); font-weight:800; flex-shrink:0;}
  .trial-note{ text-align:center; margin-top:40px; font-size:13px; color:#8b8c94; font-weight:600;}

  .breakdown{ padding: 100px 0; background: var(--white); color:var(--black); }
  .breakdown-single{ max-width: 640px; margin: 50px auto 0; }
  .bar-compare{ display:flex; flex-direction:column; gap:34px; }
  .bar-block .bar-label{ display:flex; justify-content:space-between; font-size:13px; font-weight:700; margin-bottom:10px; }
  .bar-block .bar-label .amt{ font-family:var(--heading); font-size:18px; font-weight:800; }
  .bar-track2{ height:40px; border-radius:8px; overflow:hidden; display:flex; background: #f0f0f3; }
  .bar-seg{ height:100%; display:flex; align-items:center; justify-content:center; font-size:11px; font-weight:800; color:#fff; min-width: 0; overflow:hidden; }
  .bar-legend{ display:flex; gap:18px; margin-top:12px; flex-wrap:wrap; }
  .bar-legend span{ display:flex; align-items:center; gap:7px; font-size:12px; color:var(--muted); font-weight:600; }
  .bar-legend .dot{ width:10px; height:10px; border-radius:3px; }

  .guarantee{ padding: 100px 0; background: var(--off); }
  .guarantee-grid{ display:grid; grid-template-columns: repeat(3,1fr); gap: 30px; margin-top: 46px; }
  .g-card{ background:var(--white); border:1px solid var(--line); border-radius:16px; padding: 30px; }
  .g-card .icon{ width:44px; height:44px; border-radius:50%; background: rgba(0,192,232,0.12); display:flex; align-items:center; justify-content:center; margin-bottom: 18px; font-size:20px; }
  .g-card h3{ font-size:17px; font-weight:700; margin-bottom:10px; }
  .g-card p{ font-size:13.5px; color:#5c5c64; line-height:1.65; margin:0; }

  @media (max-width: 900px){
    .price-grid{ grid-template-columns:1fr; max-width:420px; margin-left:auto; margin-right:auto;}
    .price-card.featured{ transform:none;}
    .price-card.featured:hover{ transform:translateY(-6px); }
    .guarantee-grid{ grid-template-columns:1fr; }
  }
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
      <a href="pricing.php" class="active">Pricing</a>
      <a href="faq.php">FAQ</a>
    </div>
    <div class="nav-right">
      <a href="cart.php" class="cart-link">
        <svg width="21" height="21" viewBox="0 0 24 24" fill="none"><path d="M6 6H21L19 15H8L6 6Z" stroke="black" stroke-width="1.6" stroke-linejoin="round"/><path d="M6 6L5 3H2" stroke="black" stroke-width="1.6" stroke-linecap="round"/><circle cx="9.5" cy="19" r="1.4" fill="black"/><circle cx="17.5" cy="19" r="1.4" fill="black"/></svg>
        <?php if($cartCount > 0): ?><span class="cart-count"><?= $cartCount ?></span><?php endif; ?>
      </a>
      <a href="shop.php" class="btn btn-dark btn-sm">Shop Now</a>
    </div>
  </div>
</nav>

<div class="breadcrumb">
  <div class="wrap"><a href="index.php">Home</a><span class="sep">/</span><span class="current">Pricing</span></div>
</div>

<section class="pricing-page">
  <div class="wrap">
    <div class="pricing-head">
      <span class="eyebrow">Choose Your Pair</span>
      <h1>Comfort that keeps up, <span class="accent">priced for everyone</span></h1>
      <p>Every model ships free with our 30-day trial guarantee. Wear them to work, the gym, or the airport — love them or return them.</p>
    </div>
    <div class="promo-banner">Limited time — get <b>20% off</b> your first pair, no code needed, applied at checkout. Ends Sunday, 11:59pm.</div>

    <div class="price-grid">
      <?php foreach($PLANS as $p):
        $classes = 'price-card' . ($p['featured'] ? ' featured' : '');
        $btnClass = $p['featured'] ? 'btn btn-primary btn-block' : 'btn btn-outline btn-block';
        $btnStyle = $p['featured'] ? '' : ' style="border-color:rgba(255,255,255,0.3); color:#fff;"';
      ?>
        <div class="<?= $classes ?>">
          <?php if($p['badge']): ?><div class="price-tag"><?= htmlspecialchars($p['badge']) ?></div><?php endif; ?>
          <div class="p-cat"><?= htmlspecialchars($p['cat']) ?></div>
          <h3><?= htmlspecialchars($p['name']) ?></h3>
          <div class="price-amt"><span class="now">$<?= $p['price'] ?></span><span class="was">$<?= $p['was'] ?></span></div>
          <div class="per">per pair</div>
          <ul class="price-feats">
            <?php foreach($p['features'] as $f): ?>
              <li><?= htmlspecialchars($f) ?></li>
            <?php endforeach; ?>
          </ul>
          <a href="product.php?id=<?= urlencode(strtolower($p['name'])) ?>" class="<?= $btnClass ?>"<?= $btnStyle ?>>
            Choose <?= htmlspecialchars($p['name']) ?>
          </a>
        </div>
      <?php endforeach; ?>
    </div>
    <p class="trial-note">30-day trial run · Free returns · Free exchanges</p>
  </div>
</section>

<section class="breakdown">
  <div class="wrap">
    <div class="section-head">
      <span class="eyebrow">Where It Goes</span>
      <h2>What you're actually paying for</h2>
      <p style="color:var(--muted); font-size:14px; margin-top:12px;">No retail markup, no celebrity endorsements — and no hidden costs, either.</p>
    </div>

    <div class="breakdown-single">
      <div class="bar-compare">
        <?php foreach($BARS as $bar): ?>
          <div class="bar-block">
            <div class="bar-label">
              <span><?= htmlspecialchars($bar['label']) ?></span>
              <span class="amt" style="font-size:12px; font-weight:600; color:var(--muted);">cost allocation</span>
            </div>
            <div class="bar-track2">
              <?php foreach($bar['segments'] as $seg): ?>
                <div class="bar-seg" style="width:<?= $seg['pct'] ?>%; background:<?= $seg['bg'] ?>; color:<?= $seg['color'] ?>;">
                  <?= htmlspecialchars($seg['label']) ?>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endforeach; ?>

        <div class="bar-legend">
          <span><span class="dot" style="background:#00C0E8;"></span>Materials</span>
          <span><span class="dot" style="background:#0A0A0B;"></span>Labor & Testing</span>
          <span><span class="dot" style="background:#d9d9e0;"></span>Markup / Margin</span>
        </div>
        <p style="font-size:11.5px; color:var(--muted); margin-top:4px;">Illustrative cost allocation, not audited figures.</p>
      </div>
    </div>
  </div>
</section>

<section class="guarantee">
  <div class="wrap">
    <div class="section-head">
      <span class="eyebrow">Zero Risk</span>
      <h2>Why the trial run works</h2>
    </div>
    <div class="guarantee-grid">
      <?php foreach($GUARANTEES as $g): ?>
        <div class="g-card">
          <div class="icon"><?= $g['icon'] ?></div>
          <h3><?= htmlspecialchars($g['title']) ?></h3>
          <p><?= htmlspecialchars($g['body']) ?></p>
        </div>
      <?php endforeach; ?>
    </div>
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
      <p style="color:#636366; font-size:13px; margin:0;">© <?= date('Y') ?> Cadence Athletics. All rights reserved.</p>
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