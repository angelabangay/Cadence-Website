<?php
session_start();
require_once 'database/config.php';

// ---------- AUTHENTICATION GUARD ----------
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$username = $_SESSION['username'] ?? '';
$userData = [];

// Fetch existing user profile data using username
if ($username) {
    try {
        $stmt = $pdo->prepare("SELECT email, first_name, last_name, address, city, zip FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $userData = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    } catch (PDOException $e) {
        // Handle error silently
    }
}

$PRODUCTS = [
  ['id' => 'velo', 'name' => 'Velo', 'cat' => 'Flagship', 'price' => 149],
  ['id' => 'aero', 'name' => 'Aero', 'cat' => 'Everyday Comfort', 'price' => 99],
  ['id' => 'ion', 'name' => 'Ion', 'cat' => 'Work Durability', 'price' => 139],
  ['id' => 'tempo', 'name' => 'Tempo', 'cat' => 'Gym Performance', 'price' => 129],
  ['id' => 'ridge', 'name' => 'Ridge', 'cat' => 'Gym Performance', 'price' => 119],
  ['id' => 'terra', 'name' => 'Terra', 'cat' => 'Everyday Comfort', 'price' => 109],
  ['id' => 'surge', 'name' => 'Surge', 'cat' => 'Running Pro', 'price' => 159],
  ['id' => 'nova', 'name' => 'Nova', 'cat' => 'Trail & Outdoor', 'price' => 169],
  ['id' => 'vantage', 'name' => 'Vantage', 'cat' => 'Everyday Comfort', 'price' => 99],
  ['id' => 'lumen', 'name' => 'Lumen', 'cat' => 'Gym Performance', 'price' => 129],
  ['id' => 'flux', 'name' => 'Flux', 'cat' => 'Travel Ready', 'price' => 119],
  ['id' => 'arc', 'name' => 'Arc', 'cat' => 'Running', 'price' => 139],
  ['id' => 'strato', 'name' => 'Strato', 'cat' => 'Work Durability', 'price' => 129],
  ['id' => 'drift', 'name' => 'Drift', 'cat' => 'Gym Performance', 'price' => 149],
  ['id' => 'glide', 'name' => 'Glide', 'cat' => 'Everyday Comfort', 'price' => 109]
];

$subtotal = 0;
$checkoutItems = [];

foreach ($cart as $id => $qty) {
  foreach ($PRODUCTS as $p) {
    if ($p['id'] === $id) {
      $checkoutItems[] = ['product' => $p, 'qty' => $qty];
      $subtotal += $p['price'] * $qty;
      break;
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
<title>Checkout - Cadence</title>
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
  body { font-family: var(--body); }
  h1, h2, h3, .logo { font-family: var(--heading); }
  .brand-logo-img { height: 24px; width: auto; vertical-align: middle; object-fit: contain; }
  main.checkout-main{ padding: 40px 0 100px; }
  .checkout-title{ font-size: clamp(26px,3.2vw,36px); font-weight:800; margin-bottom: 30px; }
  .checkout-grid{ display:grid; grid-template-columns: 1.4fr 1fr; gap:48px; align-items:flex-start; }
  .checkout-section{ margin-bottom: 34px; }
  .checkout-section h3{ font-size:16px; font-weight:800; margin-bottom:16px; display:flex; align-items:center; gap:10px; font-family:var(--heading); }
  .checkout-section h3 .stepnum{ width:24px; height:24px; border-radius:50%; background:var(--black); color:var(--white); font-size:12px; display:flex; align-items:center; justify-content:center; }
  .pay-methods{ display:flex; gap:10px; margin-bottom:16px; }
  .pay-methods button{ flex:1; padding:12px; border-radius:10px; border:1.5px solid var(--line); font-weight:700; font-size:13px; background:#fff; cursor:pointer; }
  .pay-methods button.active{ border-color:var(--cyan); background:rgba(0,192,232,0.06); }
  .place-order-btn{ width:100%; margin-top:14px; border:none; cursor:pointer; padding:14px; background:#0C0D10; color:#fff; border-radius:12px; font-weight:700; }
  .place-order-btn:disabled { opacity: 0.5; cursor: not-allowed; background: var(--muted) !important; }
  .secure-note{ text-align:center; font-size:12px; color:var(--muted); font-weight:600; margin-top:12px; }
  .field { margin-bottom: 14px; }
  .field label { display: block; font-size: 12px; font-weight: 700; margin-bottom: 6px; text-transform: uppercase; color: var(--muted); }
  .field input { width: 100%; padding: 11px 14px; border-radius: 10px; border: 1.5px solid var(--line); font-family: inherit; font-size: 13.5px; box-sizing: border-box; }
  .field-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
  .payment-content { display: none; }
  .payment-content.active { display: block; }
  .summary-card{ background: var(--off); border-radius:18px; padding: 28px; position:sticky; top:96px; }
  .summary-card h3{ font-size:18px; font-weight:800; margin-bottom:20px; font-family:var(--heading); }
  .summary-row{ display:flex; justify-content:space-between; font-size:14px; padding:9px 0; color:#3a3a40; }
  .summary-row.total{ border-top:1px solid var(--line); margin-top:10px; padding-top:16px; font-weight:800; font-size:17px; color:var(--black); }

  /* Modal Dialog Styles */
  dialog.order-modal {
    border: none;
    border-radius: 20px;
    padding: 35px;
    width: 100%;
    max-width: 460px;
    background: var(--white);
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    font-family: var(--body);
  }
  dialog.order-modal::backdrop {
    background: rgba(12, 13, 16, 0.6);
    backdrop-filter: blur(4px);
  }
  .order-success-content { text-align: center; padding: 10px 0; }
  .success-icon { width: 56px; height: 56px; background: rgba(0, 192, 232, 0.12); color: var(--cyan); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px; }
  .order-success-content h3 { font-family: var(--heading); font-size: 22px; font-weight: 800; margin: 0 0 8px; }
  .order-success-content p { color: var(--muted); font-size: 14.5px; margin: 0 0 24px; line-height: 1.5; }

  @media (max-width: 900px){
    .checkout-grid{ grid-template-columns:1fr; }
    .summary-card{ position:static; }
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
      <a href="faq.php">FAQ</a>
    </div>
    <div class="nav-right" style="display: flex; align-items: center; gap: 16px;">
      <a href="profile.php" class="user-pill" style="display: flex; align-items: center; gap: 6px; text-decoration: none; color: inherit; font-weight: 600; font-size: 14px;">
        <span><?= htmlspecialchars($_SESSION['username'] ?? 'User') ?></span>
      </a>
      <a href="cart.php" class="btn btn-dark btn-sm">Back to Cart</a>
    </div>
  </div>
</nav>

<main class="checkout-main">
  <div class="wrap">
    <h1 class="checkout-title">Checkout</h1>

    <form id="checkoutForm" action="place-order.php" method="POST">
      <div class="checkout-grid">
        
        <!-- Left Column: Form Inputs -->
        <div>
          <div class="checkout-section">
            <h3><span class="stepnum">1</span>Contact & Shipping</h3>
            <div class="field">
              <label>Email</label>
              <input type="email" name="email" value="<?= htmlspecialchars($userData['email'] ?? '') ?>" required placeholder="your@email.com">
            </div>
            <div class="field-row">
              <div class="field">
                <label>First name</label>
                <input type="text" name="first_name" value="<?= htmlspecialchars($userData['first_name'] ?? '') ?>" required placeholder="Jordan">
              </div>
              <div class="field">
                <label>Last name</label>
                <input type="text" name="last_name" value="<?= htmlspecialchars($userData['last_name'] ?? '') ?>" required placeholder="Lee">
              </div>
            </div>
            <div class="field">
              <label>Address</label>
              <input type="text" name="address" value="<?= htmlspecialchars($userData['address'] ?? '') ?>" required placeholder="123 Trail Run Ave">
            </div>
            <div class="field-row">
              <div class="field">
                <label>City</label>
                <input type="text" name="city" value="<?= htmlspecialchars($userData['city'] ?? '') ?>" required placeholder="Austin">
              </div>
              <div class="field">
                <label>ZIP / Postal code</label>
                <input type="text" name="zip" value="<?= htmlspecialchars($userData['zip'] ?? '') ?>" required placeholder="78701">
              </div>
            </div>
            <div style="margin-top: 12px;">
              <label style="font-size: 13px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px;">
                <input type="checkbox" name="save_info" value="1" checked style="width: 16px; height: 16px; accent-color: var(--black);"> Save this information to my profile for future orders
              </label>
            </div>
          </div>

          <div class="checkout-section">
            <h3><span class="stepnum">2</span>Payment</h3>
            <div class="pay-methods">
              <button type="button" class="active" data-method="card">Card</button>
              <button type="button" data-method="paypal">PayPal</button>
            </div>
            <input type="hidden" name="payment_method" id="paymentMethodInput" value="card">
            
            <!-- Card Payment Fields -->
            <div class="payment-content active" id="cardPaymentContent">
              <div class="field"><label>Card number</label><input type="text" name="card_number" id="cardNumberInput" placeholder="•••• •••• •••• ••••"></div>
              <div class="field-row">
                <div class="field"><label>Expiry</label><input type="text" name="card_expiry" id="cardExpiryInput" placeholder="MM / YY"></div>
                <div class="field"><label>CVC</label><input type="text" name="card_cvc" id="cardCvcInput" placeholder="•••"></div>
              </div>
            </div>

            <!-- PayPal Payment Fields -->
            <div class="payment-content" id="paypalPaymentContent">
              <div class="field">
                <label>PayPal Account Number (11 Digits)</label>
                <input type="text" name="paypal_number" id="paypalNumberInput" maxlength="11" placeholder="Enter 11-digit number">
              </div>
            </div>
          </div>
        </div>

        <!-- Right Column: Summary Card -->
        <aside class="summary-card">
          <h3>Review order</h3>
          <div id="checkoutLines">
            <?php foreach($checkoutItems as $item): ?>
              <div class="summary-row">
                <span><?= htmlspecialchars($item['product']['name']) ?> × <?= $item['qty'] ?></span>
                <span>$<?= $item['product']['price'] * $item['qty'] ?></span>
              </div>
            <?php endforeach; ?>
          </div>
          <div class="summary-row total"><span>Total due</span><span id="checkoutTotal">$<?= $subtotal ?></span></div>
          <button type="submit" class="btn btn-primary place-order-btn" id="placeOrderBtn">Place Order</button>
          <p class="secure-note">Your personal details will be securely stored to your account.</p>
        </aside>

      </div>
    </form>
  </div>
</main>

<!-- Success Dialog Modal -->
<dialog id="orderSuccessModal" class="order-modal">
  <div class="order-success-content">
    <div class="success-icon">
      <svg width="28" height="28" viewBox="0 0 24 24" fill="none"><path d="M5 13L9 17L19 7" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
    </div>
    <h3>Thank you for shopping with us!</h3>
    <p>Your package will be on its way in about 3 days.</p>
    <button type="button" class="btn btn-dark" id="closeOrderModalBtn" style="width:100%; padding: 14px; background: var(--black); color: var(--white); border-radius: 10px; font-weight: 700; border: none; cursor: pointer;">Done</button>
  </div>
</dialog>

<script>
  const urlParams = new URLSearchParams(window.location.search);
  const orderModal = document.getElementById('orderSuccessModal');
  const closeOrderModalBtn = document.getElementById('closeOrderModalBtn');

  if (urlParams.get('order') === 'success' || urlParams.get('success') === '1') {
      orderModal.showModal();
      window.history.replaceState({}, document.title, window.location.pathname);
  }

  if(closeOrderModalBtn) {
    closeOrderModalBtn.addEventListener('click', () => {
      orderModal.close();
      window.location.href = 'shop.php';
    });
  }

  // Payment method toggle and validation logic
  const payButtons = document.querySelectorAll('.pay-methods button');
  const paymentMethodInput = document.getElementById('paymentMethodInput');
  const cardContent = document.getElementById('cardPaymentContent');
  const paypalContent = document.getElementById('paypalPaymentContent');
  const cardNumberInput = document.getElementById('cardNumberInput');
  const cardExpiryInput = document.getElementById('cardExpiryInput');
  const cardCvcInput = document.getElementById('cardCvcInput');
  const paypalNumberInput = document.getElementById('paypalNumberInput');
  const placeOrderBtn = document.getElementById('placeOrderBtn');

  function validateForm() {
    if (!paymentMethodInput || !placeOrderBtn) return;
    const method = paymentMethodInput.value;
    let isValid = true;

    if (method === 'card') {
      if (!cardNumberInput.value.trim() || !cardExpiryInput.value.trim() || !cardCvcInput.value.trim()) {
        isValid = false;
      }
    } else if (method === 'paypal') {
      const val = paypalNumberInput.value.trim();
      if (!/^\d{11}$/.test(val)) {
        isValid = false;
      }
    }

    placeOrderBtn.disabled = !isValid;
  }

  payButtons.forEach(btn => {
    btn.addEventListener('click', () => {
      payButtons.forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      const method = btn.dataset.method;
      paymentMethodInput.value = method;

      if (method === 'card') {
        cardContent.classList.add('active');
        paypalContent.classList.remove('active');
        cardNumberInput.required = true;
        cardExpiryInput.required = true;
        cardCvcInput.required = true;
        paypalNumberInput.required = false;
      } else {
        paypalContent.classList.add('active');
        cardContent.classList.remove('active');
        cardNumberInput.required = false;
        cardExpiryInput.required = false;
        cardCvcInput.required = false;
        paypalNumberInput.required = true;
      }
      validateForm();
    });
  });

  [cardNumberInput, cardExpiryInput, cardCvcInput, paypalNumberInput].forEach(input => {
    if (input) {
      input.addEventListener('input', () => {
        if (input === paypalNumberInput) {
          input.value = input.value.replace(/\D/g, '').slice(0, 11);
        }
        validateForm();
      });
    }
  });

  validateForm();
</script>
</body>
</html>