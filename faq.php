<?php
// ─────────────────────────────────────────────────────────────
// CADENCE — FAQ (PHP + CSS only)
// Category tabs, accordion, and interactive Support Modal without Close Button.
// ─────────────────────────────────────────────────────────────
session_start();

// ---------- 1. DATA ----------
$CATEGORIES = [
  'general' => [
    'label'     => 'General',
    'questions' => [
      ['q'=>"How is Cadence priced so competitively?",
       'a'=>"We sell direct-to-consumer and skip the middlemen, retail markups, and celebrity endorsements. That means premium comfort and performance technology reaches you at everyday prices — without cutting a single corner on materials or testing."],
      ['q'=>"Which model should I choose?",
       'a'=>"Velo is our flagship all-rounder built for speed and daily wear. Tempo suits fast training days on a budget. Ridge is built for durability at work. Aero is designed for the office. Ion is made for the gym floor. Terra is built for travel days."],
      ['q'=>"How long do Cadence shoes last?",
       'a'=>"Depending on the model, Cadence shoes are built for 300 to 500 miles of wear before it's time for a fresh pair."],
      ['q'=>"Are Cadence shoes vegan?",
       'a'=>"Yes. All Cadence uppers and outsoles use synthetic FeatherKnit and GripLine materials — no animal-derived components."],
    ],
  ],
  'orders' => [
    'label'     => 'Orders & Shipping',
    'questions' => [
      ['q'=>"How fast is shipping?",
       'a'=>"Orders ship within 1-2 business days, with delivery typically arriving in 3-5 business days depending on your location."],
      ['q'=>"Do you ship internationally?",
       'a'=>"We currently ship within the US and Canada, with more countries added regularly. Enter your address at checkout to confirm availability."],
      ['q'=>"Can I change or cancel my order?",
       'a'=>"You can modify or cancel an order within 2 hours of placing it by contacting support — after that, it's already in fulfillment."],
      ['q'=>"How do I track my order?",
       'a'=>"You'll receive a tracking link by email as soon as your order ships, usually within 1-2 business days."],
    ],
  ],
  'returns' => [
    'label'     => 'Returns & Warranty',
    'questions' => [
      ['q'=>"What is the 30-day trial-run guarantee?",
       'a'=>"Wear your pair for up to 30 days — on runs, at work, around town. If they're not the right fit, return or exchange them free, no questions asked."],
      ['q'=>"How do I start a return?",
       'a'=>"Head to our Returns Center with your order number and email — we'll email a prepaid shipping label within minutes."],
      ['q'=>"Do you offer a warranty against defects?",
       'a'=>"Yes — every pair is covered against manufacturing defects for 12 months from the delivery date."],
    ]],
  'sizing' => [
    'label'     => 'Sizing & Fit',
    'questions' => [
      ['q'=>"Do Cadence shoes run true to size?",
       'a'=>"Most models run true to standard US sizing. If you're between sizes, we generally recommend sizing up half a size for a roomier toe box."],
      ['q'=>"What if I order the wrong size?",
       'a'=>"Free exchanges within 30 days — swap sizes as many times as you need until the fit is right."],
      ['q'=>"Is there a width option?",
       'a'=>"Currently all models are offered in standard width only. Wide-width options are in development."],
    ],
  ],
];

// ---------- 2. STATE ----------
$cat      = isset($_GET['cat']) ? preg_replace('/[^a-z]/','',strtolower($_GET['cat'])) : 'general';
$validCats= array_keys($CATEGORIES);
if(!in_array($cat, $validCats)) $cat = 'general';

// ---------- 3. URL HELPERS ----------
function faqCatUrl(string $catId): string {
  return 'faq.php' . ($catId !== 'general' ? '?cat=' . $catId : '');
}

// ---------- 4. CART + HELPERS ----------
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
<title>FAQ — Cadence</title>
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
  
  /* Immersive Hero Section */
  .faq-hero {
    position: relative;
    background: #0C0D10 url('https://i.pinimg.com/1200x/68/98/2a/68982a411feb1860d3276c75c7c56dc3.jpg') center/cover no-repeat;
    color: var(--white);
    padding: 90px 0 80px;
    text-align: center;
  }
  .faq-hero::before {
    content: '';
    position: absolute;
    inset: 0;
    background: rgba(12, 13, 16, 0.82);
    z-index: 1;
  }
  .faq-hero .wrap {
    position: relative;
    z-index: 2;
  }
  .faq-hero .eyebrow {
    display: block;
    margin-bottom: 12px;
    font-size: 11.5px;
    font-weight: 700;
    letter-spacing: .16em;
    text-transform: uppercase;
    color: var(--cyan);
  }
  .faq-hero h1{ font-size: clamp(32px,4.5vw,50px); font-weight:800; margin:0; }
  .faq-hero p{ color:#b0b0ba; margin: 14px auto 0; max-width:540px; font-size:15.5px; line-height:1.6;}

  main.faq-main{ padding: 60px 0 110px; }
  .faq-layout{ display:grid; grid-template-columns: 220px 1fr; gap: 50px; align-items:flex-start; }

  .cat-nav{ position:sticky; top: 100px; display:flex; flex-direction:column; gap:6px; }
  .cat-nav a{
    display:block; text-align:left; padding: 10px 14px; border-radius:10px;
    font-size:14px; font-weight:600; color:#5c5c64;
    transition: background .15s ease, color .15s ease;
  }
  .cat-nav a:hover{ background: rgba(0,0,0,0.04); }
  .cat-nav a.active{ background: var(--black); color:var(--white); font-weight:700; }

  .faq-category{ margin-bottom: 50px; }
  .faq-category h2{ font-size:20px; font-weight:800; margin: 0 0 8px; }
  .faq-category .cat-count{ font-size:12.5px; color:var(--muted); font-weight:600; margin-bottom:8px; }
  .faq-list{ margin-top: 18px; }
  .faq-item{ border-bottom:1px solid var(--line); }
  .faq-item summary{
    list-style:none; cursor:pointer;
    display:flex; align-items:center; justify-content:space-between;
    padding: 20px 4px; text-align:left; font-family:var(--heading); font-weight:700; font-size:15.5px;
  }
  .faq-item summary::-webkit-details-marker{ display:none; }
  .faq-item summary .chev{ transition: transform .25s ease; color:var(--muted); font-size:18px; flex-shrink:0; margin-left:16px; }
  .faq-item[open] summary .chev{ transform: rotate(45deg); color: var(--cyan); }
  .faq-item p{ padding: 0 4px 22px 0; font-size:14px; color:#5c5c64; line-height:1.7; max-width:640px; margin:0; }

  .contact-cta{ margin-top: 60px; background: var(--off); border-radius:18px; padding: 40px; text-align:center; }
  .contact-cta h3{ font-size:19px; font-weight:800; margin:0 0 10px; }
  .contact-cta p{ color:#5c5c64; font-size:14px; margin: 0 0 20px; }

  /* Support Modal Dialog Styles */
  dialog.support-modal {
    border: none;
    border-radius: 20px;
    padding: 35px;
    width: 100%;
    max-width: 460px;
    background: var(--white);
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    font-family: var(--body);
  }
  dialog.support-modal::backdrop {
    background: rgba(12, 13, 16, 0.6);
    backdrop-filter: blur(4px);
  }
  .modal-header {
    margin-bottom: 20px;
  }
  .modal-header h3 {
    font-family: var(--heading);
    font-size: 20px;
    font-weight: 800;
    margin: 0;
  }
  .support-form {
    display: flex;
    flex-direction: column;
    gap: 14px;
  }
  .form-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
    text-align: left;
  }
  .form-group label {
    font-size: 13px;
    font-weight: 600;
    color: var(--black);
  }
  .form-group input, .form-group textarea {
    width: 100%;
    padding: 12px 16px;
    border-radius: 10px;
    border: 1.5px solid var(--line);
    font-family: inherit;
    font-size: 14px;
    background: var(--off);
    color: var(--black);
  }
  .form-group input:focus, .form-group textarea:focus {
    outline: none;
    border-color: var(--black);
    background: var(--white);
  }
  .support-form button[type="submit"] {
    margin-top: 6px;
    width: 100%;
    padding: 14px;
    border-radius: 10px;
    background: var(--black);
    color: var(--white);
    border: none;
    font-weight: 700;
    font-size: 14.5px;
    cursor: pointer;
    transition: background 0.2s ease;
  }
  .support-form button[type="submit"]:hover { background: #252830; }

  /* Success State Styling */
  .support-success {
    text-align: center;
    padding: 20px 0;
    display: none;
  }
  .success-icon {
    width: 56px;
    height: 56px;
    background: rgba(0, 192, 232, 0.12);
    color: var(--cyan);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 16px;
  }
  .support-success h3 {
    font-family: var(--heading);
    font-size: 22px;
    font-weight: 800;
    margin: 0 0 8px;
  }
  .support-success p {
    color: var(--muted);
    font-size: 14.5px;
    margin: 0 0 24px;
    line-height: 1.5;
  }

  @media (max-width: 900px){
    .faq-layout{ grid-template-columns: 1fr; }
    .cat-nav{ position:static; flex-direction:row; overflow-x:auto; padding-bottom:4px; }
    .cat-nav a{ white-space:nowrap; }
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
      <a href="pricing.php">Pricing</a>
      <a href="faq.php" class="active">FAQ</a>
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
  <div class="wrap"><a href="index.php">Home</a><span class="sep">/</span><span class="current">FAQ</span></div>
</div>

<header class="faq-hero">
  <div class="wrap">
    <span class="eyebrow">Questions</span>
    <h1>Dialed in, head to toe</h1>
    <p>Everything you need to know about Cadence — pricing, the trial guarantee, sizing, shipping, and which model fits your day.</p>
  </div>
</header>

<main class="faq-main">
  <div class="wrap faq-layout">

    <nav class="cat-nav">
      <?php foreach($CATEGORIES as $id => $c):
        $active = ($id === $cat) ? ' class="active"' : '';
      ?>
        <a href="<?= htmlspecialchars(faqCatUrl($id)) ?>"<?= $active ?>>
          <?= htmlspecialchars($c['label']) ?>
        </a>
      <?php endforeach; ?>
    </nav>

    <div>
      <?php if(isset($CATEGORIES[$cat])): 
        $currentCat = $CATEGORIES[$cat];
      ?>
        <section class="faq-category" id="cat-<?= $cat ?>">
          <h2><?= htmlspecialchars($currentCat['label']) ?></h2>
          <div class="cat-count"><?= count($currentCat['questions']) ?> question<?= count($currentCat['questions']) === 1 ? '' : 's' ?></div>
          <div class="faq-list">
            <?php foreach($currentCat['questions'] as $q): ?>
              <details class="faq-item">
                <summary>
                  <span><?= htmlspecialchars($q['q']) ?></span>
                  <span class="chev">+</span>
                </summary>
                <p><?= htmlspecialchars($q['a']) ?></p>
              </details>
            <?php endforeach; ?>
          </div>
        </section>
      <?php endif; ?>
    </div>

  </div>

  <div class="wrap">
    <div class="contact-cta">
      <h3>Still have questions?</h3>
      <p>Our support team typically replies within one business day.</p>
      <button type="button" class="btn btn-dark" id="openSupportModal">Contact Support</button>
    </div>
  </div>
</main>

<dialog id="supportModal" class="support-modal">
  <div class="modal-header">
    <h3 id="modalTitle">Contact Support</h3>
  </div>

  <form class="support-form" id="supportForm">
    <div class="form-group">
      <label for="supportName">Your Name</label>
      <input type="text" id="supportName" name="name" required placeholder="Jane Doe">
    </div>
    <div class="form-group">
      <label for="supportEmail">Email Address</label>
      <input type="email" id="supportEmail" name="email" required placeholder="jane@example.com">
    </div>
    <div class="form-group">
      <label for="supportMessage">How can we help?</label>
      <textarea id="supportMessage" name="message" rows="4" required placeholder="Tell us about your order or question..."></textarea>
    </div>
    <button type="submit">Send Message</button>
  </form>

  <div class="support-success" id="supportSuccess">
    <div class="success-icon">
      <svg width="28" height="28" viewBox="0 0 24 24" fill="none"><path d="M5 13L9 17L19 7" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
    </div>
    <h3>Message Sent!</h3>
    <p>Thank you for reaching out. Our support team has received your message and will email you back within 24 hours.</p>
    <button type="button" class="btn btn-dark" id="closeSuccessBtn" style="width:100%;">Done</button>
  </div>
</dialog>

<script>
  const modal = document.getElementById('supportModal');
  const openBtn = document.getElementById('openSupportModal');
  const closeSuccessBtn = document.getElementById('closeSuccessBtn');
  const supportForm = document.getElementById('supportForm');
  const supportSuccess = document.getElementById('supportSuccess');
  const modalTitle = document.getElementById('modalTitle');

  openBtn.addEventListener('click', () => {
    supportForm.style.display = 'flex';
    supportSuccess.style.display = 'none';
    modalTitle.style.display = 'block';
    supportForm.reset();
    modal.showModal();
  });

  const closeModal = () => {
    modal.close();
  };

  closeSuccessBtn.addEventListener('click', closeModal);

  supportForm.addEventListener('submit', (e) => {
    e.preventDefault();
    supportForm.style.display = 'none';
    modalTitle.style.display = 'none';
    supportSuccess.style.display = 'block';
  });

  modal.addEventListener('click', (event) => {
    const rect = modal.getBoundingClientRect();
    if (
      event.clientX < rect.left ||
      event.clientX > rect.right ||
      event.clientY < rect.top ||
      event.clientY > rect.bottom
    ) {
      modal.close();
    }
  });
</script>

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