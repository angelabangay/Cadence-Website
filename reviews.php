<?php
// ─────────────────────────────────────────────────────────────
// CADENCE — Reviews (Single-file with Interactive Spotlight & AJAX Load More)
// ─────────────────────────────────────────────────────────────
session_start();

// ---------- 1. SPOTLIGHT DATA ----------
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

// ---------- 2. REVIEWS DATA (10 Reviews) ----------
$REVIEWS = [
  ['name'=>'Maya O.',     'context'=>'Trail Hiker',                     'rating'=>5, 'text'=>'Tackled muddy inclines and sharp rocks without a single slip or wet foot.'],
  ['name'=>'Jella T.',    'context'=>'Sub-3 marathoner',                'rating'=>5, 'text'=>"Tempo is my easy-day shoe and somehow still my favorite for race-week shakeouts."],
  ['name'=>'James B.',    'context'=>'Founder, remote',                 'rating'=>5, 'text'=>"Vantage looks like a dress sneaker and feels like a recovery shoe. Unfair advantage on travel weeks."],
  ['name'=>'Megan H.',    'context'=>'City Commuter',                   'rating'=>4, 'text'=>"Finding shoes that look high-end but handle a grueling daily commute used to be impossible. These are a total savior—pristine for the office, and pure comfort for miles of walking."],
  ['name'=>'Jordan L.',   'context'=>'Software engineer',               'rating'=>5, 'text'=>"Wear these to the office every single day. Clean enough that no one thinks twice, comfortable enough that I forget I have shoes on."],
  ['name'=>'Chris L.',    'context'=>'Basketball Player',               'rating'=>5, 'text'=>"The Ion stays locked in on hard cuts and disappears when running the floor. Finally, one pair for the whole game."],
  ['name'=>'Nina B.',     'context'=>'Retail store manager',            'rating'=>5, 'text'=>'Durability has genuinely impressed me — six months of daily wear on concrete floors and they still look and feel new.'],
  ['name'=>'Alex M.',     'context'=>'Frequent flyer, consultant',   'rating'=>4, 'text'=>'Slip on and off through security in seconds. Docked one star only because sizing ran slightly small for me.'],
  ['name'=>'Chris D.',    'context'=>'Track club member',               'rating'=>5, 'text'=>'Tempo plate makes a noticeable difference on speed days. This is now my go-to for interval workouts.'],
  ['name'=>'Taylor W.',   'context'=>'Gym-goer, 5x/week',               'rating'=>5, 'text'=>"Finally a training shoe that doesn't feel bulky. Stays planted through squats and still light enough for the treadmill after."],
];

$AVATARS = [
  'https://i.pinimg.com/1200x/a8/2e/95/a82e95aadfe270c979a0af4b2027322c.jpg',
  'https://i.pinimg.com/1200x/71/99/fb/7199fb5651120df24f6777df1d4e01a0.jpg',
  'https://i.pinimg.com/736x/8e/bf/59/8ebf5985815ad7bcf5f549c80adfaa3c.jpg',
  'https://images.unsplash.com/photo-1524504388940-b1c1722653e1?auto=format&fit=crop&w=200&q=80',
  'https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?auto=format&fit=crop&w=200&q=80',
  'https://i.pinimg.com/1200x/47/1d/bd/471dbdaef17840e16881bcde35b879e2.jpg',
  'https://i.pinimg.com/736x/21/88/6b/21886ba2175a1775c6a13e610ac21ceb.jpg',
  'https://i.pinimg.com/736x/63/00/4d/63004d818fcad525514ed43c3a1be4c1.jpg',
  'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=200&q=80',
  'https://i.pinimg.com/736x/ea/3e/11/ea3e112b4f7e7ba65262d6064b17230c.jpg',
];

function starsHtml(int $rating): string {
  return str_repeat('★', $rating) . str_repeat('☆', 5 - $rating);
}

// ---------- 3. HANDLE BACKGROUND AJAX REQUESTS ----------
if (isset($_GET['ajax']) && $_GET['ajax'] === '1') {
    header('Content-Type: application/json');
    $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
    $limit = 4;
    $total = count($REVIEWS);

    $batch = array_slice($REVIEWS, $offset, $limit);
    $html = '';

    foreach($batch as $i => $r) {
        $actualIndex = $offset + $i;
        $avatarUrl = $AVATARS[$actualIndex % count($AVATARS)];
        $stars = starsHtml($r['rating']);
        $name = htmlspecialchars($r['name']);
        $context = htmlspecialchars($r['context']);
        $text = htmlspecialchars($r['text']);

        $html .= "
        <div class=\"rev-card\">
          <div class=\"rev-card-header\">
            <div class=\"rev-avatar\">
              <img src=\"{$avatarUrl}\" alt=\"{$name}\">
            </div>
            <div class=\"rev-author-info\">
              <div class=\"rev-name\">{$name}</div>
              <div class=\"rev-meta\">{$context}</div>
            </div>
          </div>
          <div class=\"rev-stars\">{$stars}</div>
          <p class=\"rev-text\">\"{$text}\"</p>
        </div>";
    }

    $newTotalLoaded = min($offset + count($batch), $total);
    $hasMore = $newTotalLoaded < $total;

    echo json_encode([
        'html' => $html,
        'newTotal' => $newTotalLoaded,
        'nextOffset' => $newTotalLoaded,
        'hasMore' => $hasMore
    ]);
    exit;
}

// ---------- 4. NORMAL PAGE RENDERING ----------
$SUMMARY = [
  'average'    => 4.9,
  'total'      => 12400,
  'breakdown'  => [
    ['stars'=>5, 'pct'=>84],
    ['stars'=>4, 'pct'=>11],
    ['stars'=>3, 'pct'=>3],
    ['stars'=>2, 'pct'=>1],
    ['stars'=>1, 'pct'=>1],
  ],
];

$PAGE_SIZE = 4;
$initialVisible = array_slice($REVIEWS, 0, $PAGE_SIZE);
$total = count($REVIEWS);
$cartCount = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;

$isLoggedIn = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
$username = $_SESSION['username'] ?? '';

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
<title>Reviews — Cadence</title>
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

  .rev-hero{ position:relative; color:var(--white); padding: 90px 0 60px; overflow:hidden; }
  .rev-hero .bg-photo{ position:absolute; inset:0; z-index:0; }
  .rev-hero .bg-photo img{ width:100%; height:100%; object-fit:cover; }
  .rev-hero .bg-photo::after{ content:""; position:absolute; inset:0; background: linear-gradient(180deg, rgba(9,10,12,0.5) 0%, rgba(9,10,12,0.75) 70%, rgba(9,10,12,0.94) 100%); }
  .rev-hero .wrap{ position:relative; z-index:1; }
  .rev-hero h1{ font-size: clamp(30px,4vw,46px); font-weight:800; margin:0; }
  .rev-hero p{ color:#d3d3d8; margin-top:12px; max-width:520px; font-size:15px; line-height:1.6;}

  .rev-summary{ padding: 46px 0 20px; }
  .summary-panel{
    display:grid; grid-template-columns: 260px 1fr; gap: 48px; align-items:center;
    background: #0C0D10; color:var(--white); border-radius:20px; padding: 40px 44px;
  }
  .summary-score{ text-align:center; border-right: 1px solid var(--line-dark); padding-right: 40px; }
  .summary-score .num{ font-family:var(--heading); font-size:56px; font-weight:800; line-height:1; }
  .summary-score .stars{ color:var(--cyan); font-size:18px; letter-spacing:3px; margin:10px 0 6px;}
  .summary-score .count{ font-size:13px; color:#9a9aa2; font-weight:600; }
  .bars{ display:flex; flex-direction:column; gap:10px; }
  .bar-row{ display:grid; grid-template-columns: 44px 1fr 40px; align-items:center; gap:12px; font-size:12.5px; color:#c6c7cd; font-weight:600; }
  .bar-track{ height:8px; border-radius:99px; background: rgba(255,255,255,0.08); overflow:hidden; }
  .bar-fill{ height:100%; background: var(--cyan); border-radius:99px; }

  .rev-spotlight { padding: 10px 0 20px; }
  .spotlight-card {
    background: #0C0D10; color: var(--white); border-radius: 20px;
    padding: 40px; display: grid; grid-template-columns: 380px 1fr; gap: 48px; align-items: center;
    border: 1px solid var(--line-dark);
  }
  .spotlight-img { width: 100%; height: 380px; border-radius: 16px; overflow: hidden; position: relative; }
  .spotlight-img img { width: 100%; height: 100%; object-fit: cover; transition: opacity 0.3s ease; }
  .spotlight-content { display: flex; flex-direction: column; justify-content: center; }
  .spotlight-stars { color: var(--cyan); font-size: 16px; letter-spacing: 3px; margin-bottom: 16px; }
  .spotlight-text { font-size: 18px; line-height: 1.6; color: #ececee; margin-bottom: 24px; font-weight: 400; }
  .spotlight-author { border-top: 1px solid var(--line-dark); padding-top: 20px; }
  .spotlight-name { font-family: var(--heading); font-size: 15px; font-weight: 700; letter-spacing: 1px; color: var(--white); display: flex; align-items: center; gap: 8px; }
  .spotlight-name::before { content: ""; display: inline-block; width: 12px; height: 2px; background: var(--cyan); }
  .spotlight-desc { font-size: 13px; color: #9a9aa2; margin-top: 4px; font-weight: 600; }
  
  .spotlight-thumbs { display: flex; gap: 12px; margin-top: 28px; }
  .spotlight-thumb {
    width: 56px; height: 56px; border-radius: 12px; overflow: hidden; border: 2px solid transparent;
    cursor: pointer; opacity: 0.5; transition: all .2s ease; background: #222; padding: 0;
  }
  .spotlight-thumb img { width: 100%; height: 100%; object-fit: cover; }
  .spotlight-thumb.active, .spotlight-thumb:hover { opacity: 1; border-color: var(--cyan); }

  .rev-list{ display:grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-top: 30px; }
  .rev-card{ 
    background: #FAFAFC; 
    border: 1px solid var(--line); 
    border-radius: 20px; 
    padding: 32px; 
    box-shadow: 0 4px 20px rgba(0,0,0,0.01);
  }
  .rev-card-header{ display: flex; align-items: center; gap: 16px; margin-bottom: 20px; }
  .rev-avatar{ width: 56px; height: 56px; border-radius: 50%; overflow: hidden; flex-shrink: 0; background: #eee; }
  .rev-avatar img{ width: 100%; height: 100%; object-fit: cover; }
  .rev-author-info { display: flex; flex-direction: column; }
  .rev-name{ font-family: var(--heading); font-weight: 700; font-size: 18px; color: var(--black); letter-spacing: 0.2px; }
  .rev-meta{ font-size: 14px; color: var(--muted); font-weight: 400; margin-top: 2px; }
  .rev-stars{ color: var(--cyan); font-size: 16px; letter-spacing: 4px; margin-bottom: 16px; }
  .rev-text{ font-size: 15px; color: #2c2c34; line-height: 1.6; font-weight: 400; }

  .rev-footer{ display:flex; justify-content:space-between; align-items:center; gap:16px; flex-wrap:wrap; padding: 30px 0 90px; border-top:1px solid var(--line); margin-top: 14px; }
  .rev-footer .meta{ font-size:13px; color:var(--muted); font-weight:600; }

  @media (max-width: 900px){
    .summary-panel{ grid-template-columns: 1fr; }
    .summary-score{ border-right:none; padding-right:0; border-bottom:1px solid var(--line-dark); padding-bottom:24px; }
    .spotlight-card { grid-template-columns: 1fr; padding: 24px; }
    .spotlight-img { height: 260px; }
    .rev-list{ grid-template-columns:1fr; }
  }
</style>
</head>
<body>

<nav class="nav">
  <div class="wrap">
    <a href="home.php" class="logo"><?= logoImg('light') ?> CADENCE</a>
    <div class="nav-links">
      <a href="home.php">Home</a>
      <a href="standard.php">Standard</a>
      <a href="shop.php">Shoes</a>
      <a href="reviews.php" class="active">Reviews</a>
      <a href="pricing.php">Pricing</a>
      <a href="faq.php">FAQ</a>
    </div>
    <div class="nav-right">
      <a href="cart.php" class="cart-link">
        <svg width="21" height="21" viewBox="0 0 24 24" fill="none"><path d="M6 6H21L19 15H8L6 6Z" stroke="black" stroke-width="1.6" stroke-linejoin="round"/><path d="M6 6L5 3H2" stroke="black" stroke-width="1.6" stroke-linecap="round"/><circle cx="9.5" cy="19" r="1.4" fill="black"/><circle cx="17.5" cy="19" r="1.4" fill="black"/></svg>
        <?php if($cartCount > 0): ?><span class="cart-count"><?= $cartCount ?></span><?php endif; ?>
      </a>

      <?php if($isLoggedIn): ?>
        <div class="account-menu" title="Logged in as <?= htmlspecialchars($username) ?>">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" stroke="black" stroke-width="1.6" stroke-linecap="round"/><circle cx="12" cy="7" r="4" stroke="black" stroke-width="1.6"/></svg>
          <span><?= htmlspecialchars($username) ?></span>
          <a href="login.php?logout=1" style="color: #ff3b30; margin-left: 6px; font-size: 12px; text-decoration: none;">Logout</a>
        </div>
      <?php else: ?>
        <a href="login.php" class="account-menu">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" stroke="#6b6b73" stroke-width="1.6" stroke-linecap="round"/><circle cx="12" cy="7" r="4" stroke="#6b6b73" stroke-width="1.6"/></svg>
          <span>Sign In</span>
        </a>
      <?php endif; ?>
    </div>
  </div>
</nav>

<div class="breadcrumb">
  <div class="wrap"><a href="home.php">Home</a><span class="sep">/</span><span class="current">Reviews</span></div>
</div>

<header class="rev-hero">
  <div class="bg-photo"><img src="https://images.unsplash.com/photo-1613937574892-25f441264a09?auto=format&fit=crop&w=1600&q=80" alt="Runners"></div>
  <div class="wrap">
    <h1>Comfort that keeps up</h1>
    <p><?= number_format($SUMMARY['total']) ?>+ runners, professionals, gym-goers, and travelers have put Cadence to the test. Here's what they're saying.</p>
  </div>
</header>

<section class="rev-summary">
  <div class="wrap">
    <div class="summary-panel">
      <div class="summary-score">
        <div class="num"><?= $SUMMARY['average'] ?></div>
        <div class="stars">★★★★★</div>
        <div class="count"><?= number_format($SUMMARY['total']) ?> reviews</div>
      </div>
      <div class="bars">
        <?php foreach($SUMMARY['breakdown'] as $row): ?>
          <div class="bar-row">
            <span><?= $row['stars'] ?> star</span>
            <div class="bar-track"><div class="bar-fill" style="width: <?= $row['pct'] ?>%;"></div></div>
            <span><?= $row['pct'] ?>%</span>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<!-- Interactive Spotlight Section -->
<section class="rev-spotlight">
  <div class="wrap">
    <div class="spotlight-card">
      <div class="spotlight-img">
        <img id="spotlight-img-elem" src="<?= htmlspecialchars($SPOTLIGHTS[0]['img']) ?>" alt="Spotlight runner">
      </div>
      <div class="spotlight-content">
        <div class="spotlight-stars">★★★★★</div>
        <p class="spotlight-text" id="spotlight-text-elem">"<?= htmlspecialchars($SPOTLIGHTS[0]['text']) ?>"</p>
        <div class="spotlight-author">
          <div class="spotlight-name" id="spotlight-name-elem"><?= htmlspecialchars($SPOTLIGHTS[0]['name']) ?></div>
          <div class="spotlight-desc" id="spotlight-desc-elem"><?= htmlspecialchars($SPOTLIGHTS[0]['desc']) ?></div>
        </div>
        <div class="spotlight-thumbs">
          <?php foreach($SPOTLIGHTS as $i => $s): ?>
            <button class="spotlight-thumb <?= $i === 0 ? 'active' : '' ?>" 
                    data-img="<?= htmlspecialchars($s['img']) ?>"
                    data-text="<?= htmlspecialchars($s['text']) ?>"
                    data-name="<?= htmlspecialchars($s['name']) ?>"
                    data-desc="<?= htmlspecialchars($s['desc']) ?>">
              <img src="<?= htmlspecialchars($s['thumb']) ?>" alt="Thumbnail <?= $i + 1 ?>">
            </button>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</section>

<main>
  <div class="wrap">
    <div class="rev-list" id="rev-list">
      <?php foreach($initialVisible as $index => $r): 
        $avatarUrl = $AVATARS[$index % count($AVATARS)];
      ?>
        <div class="rev-card">
          <div class="rev-card-header">
            <div class="rev-avatar">
              <img src="<?= htmlspecialchars($avatarUrl) ?>" alt="<?= htmlspecialchars($r['name']) ?>">
            </div>
            <div class="rev-author-info">
              <div class="rev-name"><?= htmlspecialchars($r['name']) ?></div>
              <div class="rev-meta"><?= htmlspecialchars($r['context']) ?></div>
            </div>
          </div>
          <div class="rev-stars"><?= starsHtml($r['rating']) ?></div>
          <p class="rev-text">"<?= htmlspecialchars($r['text']) ?>"</p>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="rev-footer">
      <span class="meta" id="showing-meta">
        Showing <span id="current-count"><?= count($initialVisible) ?></span> of <?= $total ?> reviews
      </span>

      <button id="load-more-btn" class="btn btn-dark" data-offset="<?= count($initialVisible) ?>">
        Load more
      </button>
    </div>
  </div>
</main>

<footer style="background:#000000; color:#ffffff; padding:70px 0 35px; border-top:1px solid rgba(255,255,255,0.06);">
  <div class="footer-inner" style="max-width:1200px; margin:0 auto; padding:0 24px;">
    <div class="footer-grid" style="display:grid; grid-template-columns:2fr 1fr 1fr 1fr; gap:40px; align-items:start;">
      <div class="footer-brand">
        <a href="home.php" class="logo" style="display:inline-flex; align-items:center; gap:8px; font-family:var(--heading); font-size:20px; font-weight:800; letter-spacing:0.08em; color:#ffffff; text-decoration:none; margin-bottom:16px;"><?= logoImg('dark') ?> CADENCE</a>
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

<script>
// --- Interactive Spotlight Script ---
document.querySelectorAll('.spotlight-thumb').forEach(button => {
    button.addEventListener('click', function() {
        document.querySelectorAll('.spotlight-thumb').forEach(btn => btn.classList.remove('active'));
        this.classList.add('active');

        const newImg = this.getAttribute('data-img');
        const newText = this.getAttribute('data-text');
        const newName = this.getAttribute('data-name');
        const newDesc = this.getAttribute('data-desc');

        const imgElem = document.getElementById('spotlight-img-elem');
        imgElem.style.opacity = '0';
        
        setTimeout(() => {
            imgElem.src = newImg;
            document.getElementById('spotlight-text-elem').textContent = '"' + newText + '"';
            document.getElementById('spotlight-name-elem').textContent = newName;
            document.getElementById('spotlight-desc-elem').textContent = newDesc;
            imgElem.style.opacity = '1';
        }, 150);
    });
});

// --- AJAX Load More Script ---
document.getElementById('load-more-btn').addEventListener('click', function() {
    const btn = this;
    const offset = parseInt(btn.getAttribute('data-offset'));
    
    btn.textContent = 'Loading...';
    btn.disabled = true;

    fetch('reviews.php?ajax=1&offset=' + offset)
        .then(response => response.json())
        .then(data => {
            if (data.html) {
                document.getElementById('rev-list').insertAdjacentHTML('beforeend', data.html);
                document.getElementById('current-count').textContent = data.newTotal;
                btn.setAttribute('data-offset', data.nextOffset);

                if (data.hasMore) {
                    btn.textContent = 'Load more';
                    btn.disabled = false;
                } else {
                    btn.remove();
                    document.getElementById('showing-meta').textContent = "You've seen them all ✓";
                }
            }
        })
        .catch(error => {
            console.error('Error loading reviews:', error);
            btn.textContent = 'Load more';
            btn.disabled = false;
        });
});
</script>

</body>
</html>