<?php
// ─────────────────────────────────────────────────────────────
// CADENCE — Reviews (Database-Driven Spotlight Carousel with 10 Reviews)
// ─────────────────────────────────────────────────────────────
session_start();

// Database connection
$host = 'localhost';
$db   = 'cadence_db';
$user = 'root';
$pass = 'user123';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

$dbError = '';
try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    $pdo = null;
    $dbError = $e->getMessage();
}

// Fetch all reviews from database to use in the interactive spotlight slider
$SPOTLIGHTS = [];
if ($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM reviews ORDER BY id ASC");
        $dbReviews = $stmt->fetchAll();
        
        // Avatar pool mapped to local 'imges/' directory (10 items total)
        $avatarPool = [
          'images/avatar1.jpg',
          'images/avatar2.jpg',
          'images/avatar3.jpg',
          'images/avatar4.jpg',
          'images/avatar5.jpg',
          'images/avatar6.jpg',
          'images/avatar7.jpg',
          'images/avatar8.jpg',
          'images/avatar9.png',
          'images/avatar10.jpg',
        ];

        foreach($dbReviews as $index => $row) {
            $imgUrl = $avatarPool[$index % count($avatarPool)];
            $SPOTLIGHTS[] = [
                'img'   => $imgUrl,
                'thumb' => $imgUrl,
                'text'  => $row['text'],
                'name'  => strtoupper($row['name']),
                'desc'  => $row['context']
            ];
        }
    } catch (PDOException $e) {
        $dbError = $e->getMessage();
    }
}

// Fallback if database is empty or connection failed (Prepopulated with 10 exact sample reviews matching the design)
if (empty($SPOTLIGHTS)) {
    $SPOTLIGHTS = [
      [
        'img'   => 'imges/avatar1.jpg',
        'thumb' => 'imges/avatar1.jpg',
        'text'  => 'The Cadence Velo is the first daily trainer that feels fast enough to race in. I set a personal best and my legs felt fresh at mile 22. These are dialed in.',
        'name'  => 'MARCUS R.',
        'desc'  => 'Marathoner - 2:58 PR'
      ],
      [
        'img'   => 'imges/avatar2.jpg',
        'thumb' => 'imges/avatar2.jpg',
        'text'  => 'Standing on hospital shifts for 12 hours straight used to wreck my feet. Since switching to Cadence, the heel fatigue is completely gone.',
        'name'  => 'DR. ELENA S.',
        'desc'  => 'Emergency Nurse'
      ],
      [
        'img'   => 'imges/avatar3.jpg',
        'thumb' => 'imges/avatar3.jpg',
        'text'  => 'Incredible energy return. You can genuinely feel the propulsion on long tempo runs without sacrificing any impact protection.',
        'name'  => 'LIAM K.',
        'desc'  => 'Ultramarathoner'
      ],
      [
        'img'   => 'imges/avatar4.jpg',
        'thumb' => 'imges/avatar4.jpg',
        'text'  => 'Super lightweight and breathable. Perfect for high-intensity interval training and lateral movements at the gym.',
        'name'  => 'CHLOE D.',
        'desc'  => 'CrossFit Coach'
      ],
      [
        'img'   => 'imges/avatar5.jpg',
        'thumb' => 'imges/avatar5.jpg',
        'text'  => 'Traveled through three countries last month walking 20,000+ steps a day. Not a single blister or hot spot. Absolute game changer.',
        'name'  => 'SARAH M.',
        'desc'  => 'Travel Blogger'
      ],
      [
        'img'   => 'imges/avatar6.jpg',
        'thumb' => 'imges/avatar6.jpg',
        'text'  => 'The build quality is top tier. After 300 miles on the outsole, the grip and cushioning still feel brand new.',
        'name'  => 'DAVID H.',
        'desc'  => 'Road Runner'
      ],
      [
        'img'   => 'imges/avatar7.jpg',
        'thumb' => 'imges/avatar7.jpg',
        'text'  => 'Sleek design that looks just as good with casual streetwear as it does on the running track. Versatility at its finest.',
        'name'  => 'JASON T.',
        'desc'  => 'Product Designer'
      ],
      [
        'img'   => 'imges/avatar8.jpg',
        'thumb' => 'imges/avatar8.jpg',
        'text'  => 'Recovering from a knee injury, the soft landing transition made all the difference in getting back outside safely.',
        'name'  => 'AMANDA W.',
        'desc'  => 'Physical Therapist'
      ],
      [
        'img'   => 'imges/avatar9.jpg',
        'thumb' => 'imges/avatar9.jpg',
        'text'  => 'The wider toe box lets your feet naturally splay out. Unmatched comfort during peak summer mileage weeks.',
        'name'  => 'BRIAN P.',
        'desc'  => 'Trail Runner'
      ],
      [
        'img'   => 'imges/avatar10.jpg',
        'thumb' => 'imges/avatar10.jpg',
        'text'  => 'Fast shipping, exceptional customer service, and shoes that completely live up to the hype. Will definitely buy again.',
        'name'  => 'NATASHA L.',
        'desc'  => 'Fitness Enthusiast'
      ]
    ];
}

$SUMMARY = [
  'average'    => 4.9,
  'total'      => count($SPOTLIGHTS) > 0 && ! $dbError ? count($SPOTLIGHTS) * 1240 : 12400,
  'breakdown'  => [
    ['stars'=>5, 'pct'=>84],
    ['stars'=>4, 'pct'=>11],
    ['stars'=>3, 'pct'=>3],
    ['stars'=>2, 'pct'=>1],
    ['stars'=>1, 'pct'=>1],
  ],
];

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

  .rev-spotlight { padding: 10px 0 60px; }
  .spotlight-card {
    background: #0C0D10; color: var(--white); border-radius: 20px;
    padding: 40px; display: grid; grid-template-columns: 380px minmax(0, 1fr); gap: 48px; align-items: center;
    border: 1px solid var(--line-dark);
    max-width: 1200px; margin: 0 auto; box-sizing: border-box;
  }
  .spotlight-img { width: 100%; height: 380px; border-radius: 16px; overflow: hidden; position: relative; flex-shrink: 0; }
  .spotlight-img img { width: 100%; height: 100%; object-fit: cover; transition: opacity 0.3s ease; }
  
  .spotlight-content { display: flex; flex-direction: column; justify-content: center; min-width: 0; width: 100%; overflow: hidden; }
  .spotlight-stars { color: var(--cyan); font-size: 16px; letter-spacing: 3px; margin-bottom: 16px; }
  .spotlight-text { font-size: 17px; line-height: 1.6; color: #ececee; margin-bottom: 24px; font-weight: 400; word-break: break-word; overflow-wrap: break-word; }
  .spotlight-author { border-top: 1px solid var(--line-dark); padding-top: 20px; }
  .spotlight-name { font-family: var(--heading); font-size: 15px; font-weight: 700; letter-spacing: 1px; color: var(--white); display: flex; align-items: center; gap: 8px; }
  .spotlight-name::before { content: ""; display: inline-block; width: 12px; height: 2px; background: var(--cyan); }
  .spotlight-desc { font-size: 13px; color: #9a9aa2; margin-top: 4px; font-weight: 600; }
  
  .spotlight-thumbs { display: flex; gap: 10px; margin-top: 28px; overflow-x: auto; padding-bottom: 6px; width: 100%; max-width: 100%; box-sizing: border-box; scrollbar-width: thin; scrollbar-color: var(--cyan) rgba(255,255,255,0.05); }
  .spotlight-thumbs::-webkit-scrollbar { height: 4px; }
  .spotlight-thumbs::-webkit-scrollbar-thumb { background: var(--cyan); border-radius: 99px; }

  .spotlight-thumb {
    width: 50px; height: 50px; border-radius: 10px; overflow: hidden; border: 2px solid transparent;
    cursor: pointer; opacity: 0.5; transition: all .2s ease; background: #222; padding: 0; flex-shrink: 0;
  }
  .spotlight-thumb img { width: 100%; height: 100%; object-fit: cover; }
  .spotlight-thumb.active, .spotlight-thumb:hover { opacity: 1; border-color: var(--cyan); }

  @media (max-width: 900px){
    .summary-panel{ grid-template-columns: 1fr; }
    .summary-score{ border-right:none; padding-right:0; border-bottom:1px solid var(--line-dark); padding-bottom:24px; }
    .spotlight-card { grid-template-columns: 1fr; padding: 24px; }
    .spotlight-img { height: 260px; }
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
      <a href="shop.php">Shoes</a>
      <a href="reviews.php" class="active">Reviews</a>
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

<!-- Interactive Spotlight Section (10 Dynamic Reviews from Database) -->
<section class="rev-spotlight">
  <div class="wrap">
    <div class="spotlight-card">
      <div class="spotlight-img">
        <img id="spotlight-img-elem" src="<?= htmlspecialchars($SPOTLIGHTS[0]['img'], ENT_QUOTES, 'UTF-8') ?>" alt="Spotlight runner">
      </div>
      <div class="spotlight-content">
        <div class="spotlight-stars">★★★★★</div>
        <p class="spotlight-text" id="spotlight-text-elem">"<?= htmlspecialchars($SPOTLIGHTS[0]['text'], ENT_QUOTES, 'UTF-8') ?>"</p>
        <div class="spotlight-author">
          <div class="spotlight-name" id="spotlight-name-elem"><?= htmlspecialchars($SPOTLIGHTS[0]['name'], ENT_QUOTES, 'UTF-8') ?></div>
          <div class="spotlight-desc" id="spotlight-desc-elem"><?= htmlspecialchars($SPOTLIGHTS[0]['desc'], ENT_QUOTES, 'UTF-8') ?></div>
        </div>
        <div class="spotlight-thumbs">
          <?php foreach($SPOTLIGHTS as $i => $s): ?>
            <button class="spotlight-thumb <?= $i === 0 ? 'active' : '' ?>" 
                    data-img="<?= htmlspecialchars($s['img'], ENT_QUOTES, 'UTF-8') ?>"
                    data-text="<?= htmlspecialchars($s['text'], ENT_QUOTES, 'UTF-8') ?>"
                    data-name="<?= htmlspecialchars($s['name'], ENT_QUOTES, 'UTF-8') ?>"
                    data-desc="<?= htmlspecialchars($s['desc'], ENT_QUOTES, 'UTF-8') ?>">
              <img src="<?= htmlspecialchars($s['thumb'], ENT_QUOTES, 'UTF-8') ?>" alt="Thumbnail <?= $i + 1 ?>">
            </button>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </div>
</section>

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
</script>

</body>
</html>