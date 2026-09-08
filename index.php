<?php

// ─────────────────────────────────────────────────────────────
// CADENCE — Home page (PHP + CSS only, no JavaScript)
// ─────────────────────────────────────────────────────────────
session_start();


// ---------- 1. DATA ----------
$PRODUCTS = [
  ['id'=>'velo',    'name'=>'Velo',    'cat'=>'Flagship',         'tag'=>'runners',    'price'=>149, 'rating'=>4.9, 'badge'=>'Best Seller', 'image'=>'images/velo.jpg'],
  ['id'=>'aero',    'name'=>'Aero',    'cat'=>'Everyday Comfort', 'tag'=>'professionals','price'=>99,  'rating'=>4.8, 'badge'=>'', 'image'=>'images/aero.jpg'],
  ['id'=>'ion',     'name'=>'Ion',     'cat'=>'Work Durability',  'tag'=>'runners',    'price'=>139, 'rating'=>4.7, 'badge'=>'', 'image'=>'images/ion.jpg'],
  ['id'=>'tempo',   'name'=>'Tempo',   'cat'=>'Gym Performance',  'tag'=>'gym',        'price'=>129, 'rating'=>4.8, 'badge'=>'New', 'image'=>'images/tempo.jpg'],
  ['id'=>'ridge',   'name'=>'Ridge',   'cat'=>'Gym Performance',  'tag'=>'travelers',  'price'=>119, 'rating'=>4.7, 'badge'=>'', 'image'=>'images/ridge.jpg'],
  ['id'=>'terra',   'name'=>'Terra',   'cat'=>'Everyday Comfort', 'tag'=>'travelers',  'price'=>109, 'rating'=>4.6, 'badge'=>'', 'image'=>'images/terra.jpg'],
  ['id'=>'surge',   'name'=>'Surge',   'cat'=>'Running Pro',      'tag'=>'runners',    'price'=>159, 'rating'=>4.9, 'badge'=>'Pro', 'image'=>'images/surge.jpg'],
  ['id'=>'nova',    'name'=>'Nova',    'cat'=>'Trail & Outdoor',  'tag'=>'professionals','price'=>169, 'rating'=>4.8, 'badge'=>'', 'image'=>'images/nova.jpg'],
  ['id'=>'vantage', 'name'=>'Vantage', 'cat'=>'Everyday Comfort', 'tag'=>'professionals','price'=>99,  'rating'=>4.6, 'badge'=>'', 'image'=>'images/vantage.jpg'],
  ['id'=>'lumen',   'name'=>'Lumen',   'cat'=>'Gym Performance',  'tag'=>'professionals','price'=>129, 'rating'=>4.7, 'badge'=>'', 'image'=>'images/lumen.jpg'],
  ['id'=>'flux',    'name'=>'Flux',    'cat'=>'Travel Ready',     'tag'=>'runners',    'price'=>119, 'rating'=>4.7, 'badge'=>'', 'image'=>'images/flux.jpg'],
  ['id'=>'arc',     'name'=>'Arc',     'cat'=>'Running',          'tag'=>'gym',        'price'=>139, 'rating'=>4.8, 'badge'=>'', 'image'=>'images/arc.jpg'],
  ['id'=>'strato',  'name'=>'Strato',  'cat'=>'Work Durability',  'tag'=>'gym',        'price'=>129, 'rating'=>4.6, 'badge'=>'', 'image'=>'images/strato.jpg'],
  ['id'=>'drift',   'name'=>'Drift',   'cat'=>'Gym Performance',  'tag'=>'gym',        'price'=>149, 'rating'=>4.9, 'badge'=>'New', 'image'=>'images/drift.jpg'],
  ['id'=>'glide',   'name'=>'Glide',   'cat'=>'Everyday Comfort', 'tag'=>'travelers',  'price'=>109, 'rating'=>4.7, 'badge'=>'', 'image'=>'images/glide.jpg'],
];

$FAQS = [
  ['q'=>"How is Cadence priced so competitively?",
   'a'=>"We sell direct-to-consumer and skip the middlemen, retail markups, and celebrity endorsements. That means premium comfort and performance technology reaches you at everyday prices without cutting a single corner on materials or testing."],
  ['q'=>"What is the 30-day trial-run guarantee?",
   'a'=>"Wear your pair for up to 30 days on runs, at work, around town. If they are not the right fit, return or exchange them free, no questions asked."],
  ['q'=>"Which model should I choose?",
   'a'=>"Velo is our flagship all-rounder built for speed and daily wear. Tempo suits fast training days. Ridge is built for durability at work. Terra is made for travel days on your feet."],
  ['q'=>"How long do Cadence shoes last?",
   'a'=>"Depending on the model, Cadence shoes are built for 300 to 500 miles of wear before it is time for a fresh pair."],
  ['q'=>"How fast is shipping?",
   'a'=>"Orders ship within 1-2 business days, with delivery typically arriving in 3-5 business days depending on your location."],
];

// ---------- 2. STATE ----------
$filter      = isset($_GET['filter']) ? preg_replace('/[^a-z]/','',strtolower($_GET['filter'])) : 'all';
$validFilters= ['all','runners','professionals','gym','travelers'];
if(!in_array($filter,$validFilters)) $filter = 'all';

$filtered    = ($filter === 'all')
  ? $PRODUCTS
  : array_filter($PRODUCTS, fn($p) => $p['tag'] === $filter);

// Newsletter
$newsletterOk = false;
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['email']) && filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)){
  $newsletterOk = true;
}

// Cart count
$cartCount = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;

// ---------- 3. HELPERS ----------
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
<title>Cadence — Every Step. Dialed In.</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Afacad+Flux:wght@500;600;700;800&family=Afacad:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/style.css">
<style>
  .brand-logo-img { height: 24px; width: auto; vertical-align: middle; object-fit: contain; }
  
  /* Smooth left-side fade mask for the hero image */
  .hero-photo img {
    -webkit-mask-image: linear-gradient(to right, rgba(0,0,0,0) 0%, rgba(0,0,0,1) 35%, rgba(0,0,0,1) 100%);
    mask-image: linear-gradient(to right, rgba(0,0,0,0) 0%, rgba(0,0,0,1) 35%, rgba(0,0,0,1) 100%);
  }

  /* Gradient updated to push the #f2f2f7 tint further down before transitioning */
  .cta-band-custom {
    padding: 130px 24px 140px;
    text-align: center;
    background: linear-gradient(180deg, #f2f2f7 0%, #f2f2f7 15%, #17b9e8 45%, #064c61 70%, #000000 100%);
    color: #0c0d10;
  }
  .cta-band-custom .wrap {
    max-width: 640px;
    margin: 0 auto;
    display: flex;
    flex-direction: column;
    align-items: center;
  }
  .cta-band-custom .cta-logo-wrap {
    margin-bottom: 20px;
  }
  .cta-band-custom .cta-logo-wrap img {
    height: 32px;
    width: auto;
  }
  .cta-band-custom h2 {
    font-family: var(--heading);
    font-size: clamp(32px, 4.5vw, 48px);
    font-weight: 800;
    line-height: 1.1;
    letter-spacing: -0.02em;
    color: #0c0d10;
    margin-bottom: 16px;
    text-transform: uppercase;
  }
  .cta-band-custom p {
    color: #1a1a1f;
    font-size: 15px;
    line-height: 1.6;
    margin-bottom: 32px;
    max-width: 500px;
  }
  .cta-band-custom .signup-form {
    display: flex;
    gap: 10px;
    width: 100%;
    max-width: 480px;
    margin-bottom: 16px;
  }
  .cta-band-custom .signup-form input {
    flex: 1;
    padding: 14px 18px;
    border-radius: 30px;
    border: 1px solid rgba(12, 13, 16, 0.2);
    background: rgba(255, 255, 255, 0.9);
    color: #0c0d10;
    font-size: 14px;
    outline: none;
  }
  .cta-band-custom .signup-form input::placeholder {
    color: #7a7a85;
  }
  .cta-band-custom .signup-form button {
    padding: 14px 28px;
    border-radius: 30px;
    background: #0c0d10;
    color: #ffffff;
    font-weight: 700;
    border: none;
    cursor: pointer;
    font-size: 14px;
    transition: opacity 0.2s ease;
  }
  .cta-band-custom .signup-form button:hover {
    opacity: 0.85;
  }
  .cta-band-custom .fine {
    font-size: 12.5px;
    color: #26272e;
  }
  .cta-band-custom .success-msg {
    background: #0c0d10;
    color: #ffffff;
    padding: 14px 24px;
    border-radius: 30px;
    font-weight: 600;
    margin-bottom: 16px;
  }
</style>
</head>
<body>

<nav class="nav">
  <div class="wrap">
    <a href="index.php" class="logo"><?= logoImg('light') ?> CADENCE</a>
    <div class="nav-links">
      <a href="index.php" class="active">Home</a>
      <a href="standard.php">Standard</a>
      <a href="shop.php">Shoes</a>
      <a href="reviews.php">Reviews</a>
      <a href="pricing.php">Pricing</a>
      <a href="faq.php">FAQ</a>
    </div>
    <div class="nav-right" style="display: flex; align-items: center; gap: 16px;">
      
      <!-- Dynamic Profile Icon & Username or Login Link -->
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

<header class="hero" id="top">
  <div class="wrap">
    <div class="hero-grid">
      <div class="hero-copy">
        <div class="eyebrow-row"><span class="dot"></span><span>For every body. Every day.</span></div>
        <h1>EVERY STEP.<br><span class="accent">DIALED IN.</span></h1>
        <p class="lede">Premium, lightweight footwear engineered to keep pace from your morning run to a full day on your feet. All-day comfort, timeless design, and performance that never quits — dialed in.</p>
        <div class="cta-row">
          <a href="shop.php" class="btn btn-primary">Shop the Collection</a>
        </div>
        <div class="tag-pills">
          <span>Runners</span><span>Professionals</span><span>Gym-Goers</span><span>Travelers</span>
        </div>
        <div class="hero-stats">
          <div class="stat"><b>185g</b><span>Featherlight</span></div>
          <div class="stat"><b>4.9 ★</b><span>12.4K Reviews</span></div>
          <div class="stat"><b>All-Day</b><span>Comfort Tested</span></div>
        </div>
      </div>
      <div class="hero-visual">
        <div class="hero-photo">
          <img src="images/hero1.jpg" alt="Runner mid-stride at golden hour" loading="eager">         
        </div>
      </div>
    </div>
  </div>
</header>

<section class="rhythm" id="rhythm">
  <div class="wrap rhythm-grid">
    <div class="rhythm-visual">
      <img src="images/standard.jpg" alt="Athlete resting on outdoor stairs mid-routine" loading="lazy">
    </div>
    <div class="rhythm-copy">
      <h2>Made for the full rhythm of your day.</h2>
      <div class="feat-grid">
        <div class="feat-item">
          <div class="feat-icon"><svg width="26" height="26" viewBox="0 0 24 24" fill="none"><path d="M6 8h12l1 12H5L6 8Z" stroke="#00C0E8" stroke-width="1.6" stroke-linejoin="round"/><path d="M9 8a3 3 0 0 1 6 0" stroke="#00C0E8" stroke-width="1.6"/></svg></div>
          <div class="feat-eyebrow">Weight</div><h3>Lightweight feel</h3><p>Barely-there weight for commutes, workouts, errands, and travel days.</p>
        </div>
        <div class="feat-item">
          <div class="feat-icon"><svg width="26" height="26" viewBox="0 0 24 24" fill="none"><path d="M4 16L11 4h2l7 12-8 4-8-4Z" stroke="#00C0E8" stroke-width="1.6" stroke-linejoin="round"/></svg></div>
          <div class="feat-eyebrow">Style</div><h3>Minimal style</h3><p>Clean enough for daily outfits, capable enough for active routines.</p>
        </div>
        <div class="feat-item">
          <div class="feat-icon"><svg width="26" height="26" viewBox="0 0 24 24" fill="none"><rect x="4" y="4" width="7" height="7" rx="1.5" stroke="#00C0E8" stroke-width="1.6"/><rect x="13" y="4" width="7" height="7" rx="1.5" stroke="#00C0E8" stroke-width="1.6"/><rect x="4" y="13" width="7" height="7" rx="1.5" stroke="#00C0E8" stroke-width="1.6"/><rect x="13" y="13" width="7" height="7" rx="1.5" stroke="#00C0E8" stroke-width="1.6"/></svg></div>
          <div class="feat-eyebrow">Material</div><h3>Premium finish</h3><p>Refined materials, precise construction, and details built to last.</p>
        </div>
        <div class="feat-item">
          <div class="feat-icon"><svg width="26" height="26" viewBox="0 0 24 24" fill="none"><path d="M12 20s-7-4.4-7-10a4.5 4.5 0 0 1 7-3.7A4.5 4.5 0 0 1 19 10c0 5.6-7 10-7 10Z" stroke="#00C0E8" stroke-width="1.6" stroke-linejoin="round"/></svg></div>
          <div class="feat-eyebrow">Feel</div><h3>Comfort first</h3><p>Soft underfoot from the first step, supportive through the last hour.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="lineup" id="lineup">
  <div class="wrap">
    <div class="section-head">
      <span class="eyebrow">The Full Lineup</span>
      <h2>Built for every <span class="accent">kind of day</span></h2>
    </div>

    <!-- Filters -->
    <div class="filter-row">
      <?php
        $filterLabels = ['all'=>'All','runners'=>'Runners','professionals'=>'Professionals','gym'=>'Gym-Goers','travelers'=>'Travelers'];
        foreach($filterLabels as $key => $label):
          $active = ($filter === $key) ? ' class="active"' : '';
      ?>
        <a href="index.php?filter=<?= $key ?>"<?= $active ?>><?= htmlspecialchars($label) ?></a>
      <?php endforeach; ?>
    </div>

    <?php 
      $homepageDisplay = array_slice($filtered, 0, 6);
    ?>

    <div class="product-grid">
      <?php foreach($homepageDisplay as $p): ?>
        <a class="p-card" href="product.php?id=<?= urlencode($p['id']) ?>">
          <div class="p-thumb">
            <?php if($p['badge']): ?><div class="p-badge"><?= htmlspecialchars($p['badge']) ?></div><?php endif; ?>
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

    <div style="text-align: center; margin-top: 30px;">
      <p class="add-note" style="margin-bottom: 14px;">
        Showing <?= count($homepageDisplay) ?> of <?= count($PRODUCTS) ?> models
      </p>
      <a href="shop.php" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 8px;">
        View Full Shop &rarr;
      </a>
    </div>
  </div>
</section>

<?php
$SPOTLIGHTS = [
  [
    'img'   => 'https://i.pinimg.com/736x/62/d2/fe/62d2fe5b7cc0736d2ff78be216d0bbf6.jpg',
    'thumb' => 'https://i.pinimg.com/736x/62/d2/fe/62d2fe5b7cc0736d2ff78be216d0bbf6.jpg',
    'text'  => 'The Cadence Velo is the first daily trainer that feels fast enough to race in. I set a personal best and my legs felt fresh at mile 22. These are dialed in.',
    'name'  => 'MARCUS R.',
    'desc'  => 'Marathoner · 2:58 PR'
  ],
  [
    'img'   => 'https://i.pinimg.com/736x/7a/43/fc/7a43fc95d7c2f2df418a7b51cb2ee664.jpg',
    'thumb' => 'https://i.pinimg.com/736x/7a/43/fc/7a43fc95d7c2f2df418a7b51cb2ee664.jpg',
    'text'  => 'Working long hours in healthcare destroys your feet, but switching to Cadence completely eliminated my arch pain. Absolute lifesaver.',
    'name'  => 'PRIYA N.',
    'desc'  => 'ICU Nurse · 12-hr shifts'
  ],
  [
    'img'   => 'https://i.pinimg.com/1200x/cd/ff/43/cdff43a7b42b522899e7a629d2f22046.jpg',
    'thumb' => 'https://i.pinimg.com/1200x/cd/ff/43/cdff43a7b42b522899e7a629d2f22046.jpg',
    'text'  => 'I packed a single pair of these for a two-week trip through Europe. Cobblestones, airports, and museum lines—my feet felt great every step.',
    'name'  => 'DIEGO K.',
    'desc'  => 'Travel Blogger · 14 Countries'
  ],
  [
    'img'   => 'https://i.pinimg.com/736x/51/68/8e/51688e0fcda47cae542c2f48f07435ef.jpg',
    'thumb' => 'https://i.pinimg.com/736x/51/68/8e/51688e0fcda47cae542c2f48f07435ef.jpg',
    'text'  => 'Unmatched grip and stability for heavy lifts and box jumps. They hold their ground and look clean enough to wear casually after.',
    'name'  => 'AMARA P.',
    'desc'  => 'CrossFit Coach · 5x Weekly'
  ]
];

$activeIdx = isset($_GET['review']) ? max(0, min(count($SPOTLIGHTS) - 1, intval($_GET['review']))) : 0;
$current = $SPOTLIGHTS[$activeIdx];
?>

<section class="testimonial" id="reviews">
  <div class="wrap">
    <div class="section-head"><span class="eyebrow">Loved by Thousands</span><h2>Comfort that keeps up</h2></div>
    
    <div class="t-card" style="margin-top:44px; background:#0C0D10; border-radius:20px; padding:32px; display:grid; grid-template-columns: 1fr 1.2fr; gap:36px; align-items:center;">
      
      <div class="t-visual" style="border-radius:12px; overflow:hidden; height:420px;">
        <img src="<?= htmlspecialchars($current['img']) ?>" alt="<?= htmlspecialchars($current['name']) ?>" style="width:100%; height:100%; object-fit:cover;" loading="lazy">
      </div>

      <div class="t-content" style="padding:0; display:flex; flex-direction:column; justify-content:center;">
        <div class="stars" style="color:var(--cyan); font-size:16px; letter-spacing:4px; margin-bottom:18px;">★★★★★</div>
        
        <p class="t-quote" style="font-family:var(--heading); font-size:clamp(18px,1.8vw,21px); line-height:1.5; font-weight:600; color:var(--white); margin-bottom:24px;">
          "<?= htmlspecialchars($current['text']) ?>"
        </p>
        
        <div style="width:100%; height:1px; background:rgba(255,255,255,0.12); margin-bottom:20px;"></div>

        <div class="t-author" style="margin-bottom:24px;">
          <b style="font-size:13px; letter-spacing:0.1em; color:var(--cyan); text-transform:uppercase; margin-bottom:4px; display:block;">— <?= htmlspecialchars($current['name']) ?></b>
          <span style="font-size:12.5px; color:#93949c;"><?= htmlspecialchars($current['desc']) ?></span>
        </div>

        <div class="avatars-row" style="display:flex; gap:12px;">
          <?php foreach($SPOTLIGHTS as $idx => $item): 
            $isActive = ($idx === $activeIdx);
            $border = $isActive ? '2px solid var(--cyan)' : '2px solid rgba(255,255,255,0.15)';
            $opacity = $isActive ? '1' : '0.6';
          ?>
            <a href="index.php?review=<?= $idx ?>#reviews" style="width:54px; height:54px; border-radius:10px; overflow:hidden; border:<?= $border ?>; opacity:<?= $opacity ?>; display:block; transition: all 0.2s ease;" onmouseover="this.style.opacity='1'; this.style.borderColor='var(--cyan)'; this.style.transform='translateY(-2px)';" onmouseout="this.style.opacity='<?= $isActive ? '1' : '0.6' ?>'; this.style.borderColor='<?= $isActive ? 'var(--cyan)' : 'rgba(255,255,255,0.15)' ?>'; this.style.transform='translateY(0)';">
              <img src="<?= htmlspecialchars($item['thumb']) ?>" alt="Thumbnail <?= $idx+1 ?>" style="width:100%; height:100%; object-fit:cover;">
            </a>
          <?php endforeach; ?>
        </div>

      </div>
    </div>
  </div>
</section>

<section class="pricing" id="pricing">
  <div class="wrap">
    <div class="pricing-head">
      <span class="eyebrow">Choose Your Pair</span>
      <h2>Comfort that keeps up, <span class="accent">priced for everyone</span></h2>
      <p>Every model ships free with our 30-day trial guarantee. Wear them to work, the gym, or the airport — love them or return them.</p>
    </div>
    <div class="promo-banner">Limited time — get <b>20% off</b> your first pair, no code needed, applied at checkout. Ends Sunday, 11:59pm.</div>
    <div class="price-grid">
      
      <div class="price-card">
        <div class="p-cat">Everyday Comfort</div><h3>Tempo</h3>
        <div class="price-amt"><span class="now">$99</span><span class="was">$124</span></div><div class="per">per pair</div>
        <ul class="price-feats">
          <li>FeatherKnit upper</li>
          <li>GripLine outsole</li>
          <li>3 color ways</li>
          <li>300-mile lifespan</li>
        </ul>
        <a href="product.php?id=tempo" class="btn btn-outline btn-block">Choose Tempo</a>
      </div>

      <div class="price-card featured">
        <div class="price-tag">Most Popular</div>
        <div class="p-cat">Flagship All-Rounder</div><h3>Velo</h3>
        <div class="price-amt"><span class="now">$149</span><span class="was">$189</span></div><div class="per">per pair</div>
        <ul class="price-feats">
          <li>AeroWeave carbon-plate</li>
          <li>CloudCell dual-foam core</li>
          <li>5 color ways</li>
          <li>500-mile lifespan</li>
          <li>Free 30-day trial runs</li>
        </ul>
        <a href="product.php?id=velo" class="btn btn-primary btn-block">Choose Velo</a>
      </div>

      <div class="price-card">
        <div class="p-cat">Work Durability</div><h3>Ridge</h3>
        <div class="price-amt"><span class="now">$119</span><span class="was">$149</span></div><div class="per">per pair</div>
        <ul class="price-feats">
          <li>ArmorTread outsole</li>
          <li>Reinforced toe box</li>
          <li>3 color ways</li>
          <li>400-mile lifespan</li>
        </ul>
        <a href="product.php?id=ridge" class="btn btn-outline btn-block">Choose Ridge</a>
      </div>

    </div>
    <div class="trial-note">All pairs backed by our 30-day no-risk trial run guarantee. Free shipping & returns worldwide.</div>
  </div>
</section>

<section class="faq" id="faq">
  <div class="wrap">
    <div class="section-head">
      <span class="eyebrow">Got Questions?</span>
      <h2>Frequently Asked <span class="accent">Questions</span></h2>
    </div>
    <div class="faq-list">
      <?php foreach($FAQS as $i => $faq): $num = sprintf('%02d', $i + 1); ?>
        <details class="faq-item">
          <summary>
            <span><span class="num"><?= $num ?></span><?= htmlspecialchars($faq['q']) ?></span>
            <span class="chev">+</span>
          </summary>
          <p><?= htmlspecialchars($faq['a']) ?></p>
        </details>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- FULLY BLENDED CTA BAND -->
<section class="cta-band-custom">
  <div class="wrap">
    <div class="cta-logo-wrap"><?= logoImg('light') ?></div>
    <h2>Lace Up.<br>Dial In.</h2>
    <p>Join 50,000+ runners, professionals, gym-goers, and travelers who've made the switch. Get early access to drops, comfort tips, and members-only pricing.</p>
    
    <?php if($newsletterOk): ?>
      <div class="success-msg">✓ You're on the list! Check your inbox soon for your welcome perks.</div>
    <?php else: ?>
      <form class="signup-form" method="POST" action="#top">
        <input type="email" name="email" placeholder="your@email.com" required>
        <button type="submit">JOIN &rarr;</button>
      </form>
    <?php endif; ?>
    <div class="fine">No spam. Just comfort. Unsubscribe anytime.</div>
  </div>
</section>

<footer style="background:#000000; color:#ffffff; padding:40px 0 35px; margin-top:0;">
  <div class="footer-inner" style="max-width:1200px; margin:0 auto; padding:0 24px;">
    <div class="footer-grid" style="display:grid; grid-template-columns:2fr 1fr 1fr 1fr; gap:40px; align-items:start;">
      <div class="footer-brand">
        <a href="index.php" class="logo" style="display:inline-flex; align-items:center; gap:8px; font-family:var(--heading); font-size:20px; font-weight:800; letter-spacing:0.08em; color:#ffffff; text-decoration:none; margin-bottom:16px;"><?= logoImg('dark') ?> CADENCE</a>
        <p style="color:#8e8e93; font-size:14px; line-height:1.5; max-width:280px;">Comfort-first footwear for every body, every day.</p>
      </div>
      <div class="footer-col">
        <h4 style="font-family:var(--heading); font-size:12px; font-weight:700; letter-spacing:0.14em; text-transform:uppercase; color:#ffffff; margin-bottom:20px;">Shop</h4>
        <ul style="list-style:none; padding:0; margin:0; display:flex; flex-direction:column; gap:12px;">
          <li><a href="shop.php?filter=runners" style="color:#8e8e93; text-decoration:none; font-size:14px;">Runners</a></li>
          <li><a href="shop.php?filter=professionals" style="color:#8e8e93; text-decoration:none; font-size:14px;">Professionals</a></li>
          <li><a href="shop.php?filter=gym" style="color:#8e8e93; text-decoration:none; font-size:14px;">Gym-Goers</a></li>
          <li><a href="shop.php?filter=travelers" style="color:#8e8e93; text-decoration:none; font-size:14px;">Travelers</a></li>
        </ul>
      </div>
      <div class="footer-col">
        <h4 style="font-family:var(--heading); font-size:12px; font-weight:700; letter-spacing:0.14em; text-transform:uppercase; color:#ffffff; margin-bottom:20px;">Company</h4>
        <ul style="list-style:none; padding:0; margin:0; display:flex; flex-direction:column; gap:12px;">
          <li><a href="standard.php" style="color:#8e8e93; text-decoration:none; font-size:14px;">Our Standard</a></li>
          <li><a href="reviews.php" style="color:#8e8e93; text-decoration:none; font-size:14px;">Reviews</a></li>
          <li><a href="pricing.php" style="color:#8e8e93; text-decoration:none; font-size:14px;">Pricing</a></li>
          <li><a href="faq.php" style="color:#8e8e93; text-decoration:none; font-size:14px;">FAQ</a></li>
        </ul>
      </div>
      <div class="footer-col">
        <h4 style="font-family:var(--heading); font-size:12px; font-weight:700; letter-spacing:0.14em; text-transform:uppercase; color:#ffffff; margin-bottom:20px;">Support</h4>
        <ul style="list-style:none; padding:0; margin:0; display:flex; flex-direction:column; gap:12px;">
          <li><a href="cart.php" style="color:#8e8e93; text-decoration:none; font-size:14px;">My Cart</a></li>
          <li><a href="faq.php" style="color:#8e8e93; text-decoration:none; font-size:14px;">Shipping & Returns</a></li>
          <li><a href="faq.php" style="color:#8e8e93; text-decoration:none; font-size:14px;">30-Day Guarantee</a></li>
          <li><a href="mailto:support@cadence.com" style="color:#8e8e93; text-decoration:none; font-size:14px;">Contact Us</a></li>
        </ul>
      </div>
    </div>
    <div class="footer-bottom" style="margin-top:60px; padding-top:24px; border-top:1px solid rgba(255,255,255,0.08); display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:16px;">
      <p style="color:#636366; font-size:13px; margin:0;">&copy; <?= date('Y') ?> Cadence Footwear Inc. All rights reserved.</p>
      <div class="footer-legal" style="display:flex; gap:24px;">
        <a href="#" style="color:#636366; font-size:13px; text-decoration:none;">Privacy Policy</a>
        <a href="#" style="color:#636366; font-size:13px; text-decoration:none;">Terms of Service</a>
      </div>
    </div>
  </div>
</footer>

</body>
</html>