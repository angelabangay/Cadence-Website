<?php
session_start();

// ---------- AUTHENTICATION GUARD ----------
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

$isLoggedIn = true;
$username = $_SESSION['username'] ?? '';

$PRODUCTS = [
  ['id' => 'velo', 'name' => 'Velo', 'cat' => 'Flagship', 'price' => 149, 'image' => 'images/velo.jpg'],
  ['id' => 'aero', 'name' => 'Aero', 'cat' => 'Everyday Comfort', 'price' => 99, 'image' => 'images/aero.jpg'],
  ['id' => 'ion', 'name' => 'Ion', 'cat' => 'Work Durability', 'price' => 139, 'image' => 'images/ion.jpg'],
  ['id' => 'tempo', 'name' => 'Tempo', 'cat' => 'Gym Performance', 'price' => 129, 'image' => 'images/tempo.jpg'],
  ['id' => 'ridge', 'name' => 'Ridge', 'cat' => 'Gym Performance', 'price' => 119, 'image' => 'images/ridge.jpg'],
  ['id' => 'terra', 'name' => 'Terra', 'cat' => 'Everyday Comfort', 'price' => 109, 'image' => 'images/terra.jpg'],
  ['id' => 'surge', 'name' => 'Surge', 'cat' => 'Running Pro', 'price' => 159, 'image' => 'images/surge.jpg'],
  ['id' => 'nova', 'name' => 'Nova', 'cat' => 'Trail & Outdoor', 'price' => 169, 'image' => 'images/nova.jpg'],
  ['id' => 'vantage', 'name' => 'Vantage', 'cat' => 'Everyday Comfort', 'price' => 99, 'image' => 'images/vantage.jpg'],
  ['id' => 'lumen', 'name' => 'Lumen', 'cat' => 'Gym Performance', 'price' => 129, 'image' => 'images/lumen.jpg'],
  ['id' => 'flux', 'name' => 'Flux', 'cat' => 'Travel Ready', 'price' => 119, 'image' => 'images/flux.jpg'],
  ['id' => 'arc', 'name' => 'Arc', 'cat' => 'Running', 'price' => 139, 'image' => 'images/arc.jpg'],
  ['id' => 'strato', 'name' => 'Strato', 'cat' => 'Work Durability', 'price' => 129, 'image' => 'images/strato.jpg'],
  ['id' => 'drift', 'name' => 'Drift', 'cat' => 'Gym Performance', 'price' => 149, 'image' => 'images/drift.jpg'],
  ['id' => 'glide', 'name' => 'Glide', 'cat' => 'Everyday Comfort', 'price' => 109, 'image' => 'images/glide.jpg']
];

// Handle full item removal via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'remove') {
  $id = $_POST['id'] ?? '';
  if (isset($_SESSION['cart'][$id])) {
    unset($_SESSION['cart'][$id]);
  }
  header("Location: cart.php");
  exit;
}

$cart = $_SESSION['cart'] ?? [];
$cartCount = array_sum($cart);
$subtotal = 0;
$cartItems = [];

foreach ($cart as $id => $qty) {
  foreach ($PRODUCTS as $p) {
    if ($p['id'] === $id) {
      $cartItems[] = ['product' => $p, 'qty' => $qty];
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
<title>Your Cart - Cadence</title>
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
  main.cart-main{ padding-bottom: 100px; }
  .cart-title{ font-size: clamp(26px,3.2vw,36px); font-weight:800; margin-bottom: 6px; }
  .cart-sub{ color:var(--muted); font-size:14px; font-weight:600; margin-bottom: 36px; }
  .cart-grid{ display:grid; grid-template-columns: 1.5fr 1fr; gap: 48px; align-items:flex-start; }
  .cart-line{ display:grid; grid-template-columns: 28px 96px 1fr auto; gap: 18px; align-items:center; padding: 22px 0; border-bottom:1px solid var(--line); }
  .cart-checkbox{ width: 18px; height: 18px; accent-color: var(--black); cursor: pointer; }
  .cart-thumb{ width:96px; height:96px; border-radius:12px; overflow:hidden; background:var(--off); }
  .cart-thumb img { width: 100%; height: 100%; object-fit: cover; }
  .cart-line-name{ font-family:var(--heading); font-weight:700; font-size:16px; margin-bottom:4px; }
  .cart-line-meta{ font-size:12.5px; color:var(--muted); font-weight:600; margin-bottom:10px; }
  .cart-line-controls{ display:flex; align-items:center; gap:16px; }
  .cart-qty{ display:flex; align-items:center; border:1.5px solid var(--line); border-radius:999px; overflow:hidden; }
  .cart-qty button{ width:32px; height:32px; font-size:15px; font-weight:700; background:none; border:none; cursor:pointer; }
  .cart-qty span{ min-width:26px; text-align:center; font-size:13.5px; font-weight:700; }
  .cart-remove{ font-size:12.5px; color:var(--muted); font-weight:700; text-decoration:underline; background:none; border:none; cursor:pointer; padding:0; }
  .cart-remove:hover{ color:#e0574a; }
  .cart-line-price{ text-align:right; font-weight:800; font-family:var(--heading); font-size:16px; white-space:nowrap; }
  .summary-card{ background: var(--off); border-radius:18px; padding: 28px; position:sticky; top:96px; }
  .summary-card h3{ font-size:18px; font-weight:800; margin-bottom:20px; }
  .summary-row{ display:flex; justify-content:space-between; font-size:14px; padding:9px 0; color:#3a3a40; }
  .summary-row.total{ border-top:1px solid var(--line); margin-top:10px; padding-top:16px; font-weight:800; font-size:17px; color:var(--black); }
  .trust-row{ display:flex; gap:16px; margin-top:22px; padding-top:20px; border-top:1px solid var(--line); font-size:11.5px; color:var(--muted); font-weight:600; flex-wrap:wrap; }
  .empty-cart{ text-align:center; padding: 100px 20px; }
  .empty-cart svg{ margin:0 auto 22px; opacity:0.3; }
  .empty-cart h2{ font-size:24px; font-weight:800; margin-bottom:10px; }
  .empty-cart p{ color:var(--muted); margin-bottom:26px; }
  .checkout-panel{ display:none; margin-top: 60px; padding-top: 50px; border-top: 1px solid var(--line); }
  .checkout-panel.show{ display:block; }
  .checkout-grid{ display:grid; grid-template-columns: 1.4fr 1fr; gap:48px; align-items:flex-start; }
  .checkout-section{ margin-bottom: 34px; }
  .checkout-section h3{ font-size:15px; font-weight:800; margin-bottom:16px; display:flex; align-items:center; gap:10px; }
  .checkout-section h3 .stepnum{ width:24px; height:24px; border-radius:50%; background:var(--black); color:var(--white); font-size:12px; display:flex; align-items:center; justify-content:center; }
  .pay-methods{ display:flex; gap:10px; margin-bottom:16px; }
  .pay-methods button{ flex:1; padding:12px; border-radius:10px; border:1.5px solid var(--line); font-weight:700; font-size:13px; background:#fff; cursor:pointer; }
  .pay-methods button.active{ border-color:var(--cyan); background:rgba(0,192,232,0.06); }
  .place-order-btn{ width:100%; margin-top:8px; border:none; cursor:pointer; }
  .secure-note{ text-align:center; font-size:12px; color:var(--muted); font-weight:600; margin-top:12px; }
  .field { margin-bottom: 14px; }
  .field label { display: block; font-size: 12px; font-weight: 700; margin-bottom: 6px; text-transform: uppercase; color: var(--muted); }
  .field input { width: 100%; padding: 11px 14px; border-radius: 10px; border: 1.5px solid var(--line); font-family: inherit; font-size: 13.5px; box-sizing: border-box; }
  .field-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
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
  .order-success-content {
    text-align: center;
    padding: 10px 0;
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
  .order-success-content h3 {
    font-family: var(--heading);
    font-size: 22px;
    font-weight: 800;
    margin: 0 0 8px;
  }
  .order-success-content p {
    color: var(--muted);
    font-size: 14.5px;
    margin: 0 0 24px;
    line-height: 1.5;
  }

  @media (max-width: 900px){
    .cart-grid, .checkout-grid{ grid-template-columns:1fr; }
    .summary-card{ position:static; }
    .cart-line{ grid-template-columns: 28px 72px 1fr; }
    .cart-line-price{ grid-column: 3; text-align:left; margin-top:8px; }
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

<div class="breadcrumb" style="padding: 16px 0; font-size: 13.5px; color: #6b6b73; border-bottom: 1px solid #E5E5EA; margin-bottom: 24px;">
  <div class="wrap">
    <a href="index.php" style="color:inherit; text-decoration:none;">Home</a>
    <span class="sep" style="margin: 0 6px;">/</span>
    <span class="current" style="color: #0C0D10; font-weight: 600;">Cart</span>
  </div>
</div>

<main class="cart-main">
  <div class="wrap">
    <h1 class="cart-title">Your cart</h1>
    <p class="cart-sub"><?= $cartCount ?> item<?= $cartCount === 1 ? '' : 's' ?></p>

    <?php if(empty($cartItems)): ?>
      <div class="empty-cart">
        <svg width="64" height="64" viewBox="0 0 24 24" fill="none"><path d="M6 6H21L19 15H8L6 6Z" stroke="black" stroke-width="1.4" stroke-linejoin="round"/><path d="M6 6L5 3H2" stroke="black" stroke-width="1.4" stroke-linecap="round"/><circle cx="9.5" cy="19" r="1.4" fill="black"/><circle cx="17.5" cy="19" r="1.4" fill="black"/></svg>
        <h2>Your cart is empty</h2>
        <p>Looks like you haven't dialed in your pair yet.</p>
        <a href="shop.php" class="btn btn-primary" style="display:inline-block; padding:12px 24px; background:#0C0D10; color:#fff; border-radius:12px; text-decoration:none; font-weight:700;">Shop the Collection</a>
      </div>
    <?php else: ?>
      <div id="cartFilled">
        <div class="cart-grid">
          <div class="cart-lines">
            <?php foreach($cartItems as $item): ?>
              <div class="cart-line" data-price="<?= $item['product']['price'] ?>" data-qty="<?= $item['qty'] ?>">
                <input type="checkbox" class="cart-checkbox item-select" checked>
                <div class="cart-thumb">
                  <img src="<?= htmlspecialchars($item['product']['image']) ?>" alt="<?= htmlspecialchars($item['product']['name']) ?>">
                </div>
                <div>
                  <div class="cart-line-name"><?= htmlspecialchars($item['product']['name']) ?></div>
                  <div class="cart-line-meta"><?= htmlspecialchars($item['product']['cat']) ?> · $<span class="unit-price"><?= $item['product']['price'] ?></span> each</div>
                  <div class="cart-line-controls">
                    <div class="cart-qty">
                      <button type="button" class="qty-btn" data-action="minus">-</button>
                      <span class="qty-display"><?= $item['qty'] ?></span>
                      <button type="button" class="qty-btn" data-action="plus">+</button>
                    </div>
                    <form method="POST" style="display:inline;">
                      <input type="hidden" name="id" value="<?= $item['product']['id'] ?>">
                      <input type="hidden" name="action" value="remove">
                      <button type="submit" class="cart-remove">Remove</button>
                    </form>
                  </div>
                </div>
                <div class="cart-line-price">$<span class="line-total"><?= $item['product']['price'] * $item['qty'] ?></span></div>
              </div>
            <?php endforeach; ?>
          </div>

          <aside class="summary-card">
            <h3>Order summary</h3>
            <div class="summary-row"><span>Subtotal</span><span id="sumSubtotal">$<?= $subtotal ?></span></div>
            <div class="summary-row"><span>Shipping</span><span>Free</span></div>
            <div class="summary-row total"><span>Total</span><span id="sumTotal">$<?= $subtotal ?></span></div>
            
            <button class="btn btn-primary btn-block" id="checkoutBtn" style="width:100%; margin-top:14px; padding:14px; background:#0C0D10; color:#fff; border-radius:12px; font-weight:700; border:none; cursor:pointer;">Proceed to Checkout</button>
            <div class="trust-row">
              <span>30-day trial</span><span>Free returns</span><span>Secure checkout</span>
            </div>
          </aside>
        </div>

        <!-- Checkout Form Panel -->
        <form id="checkoutForm" action="process-order.php" method="POST">
          <div class="checkout-panel" id="checkoutPanel">
            <div class="checkout-grid">
              <div>
                <div class="checkout-section">
                  <h3><span class="stepnum">1</span>Contact & shipping</h3>
                  <div class="field"><label>Email</label><input type="email" name="email" required placeholder="your@email.com"></div>
                  <div class="field-row">
                    <div class="field"><label>First name</label><input type="text" name="first_name" required placeholder="Jordan"></div>
                    <div class="field"><label>Last name</label><input type="text" name="last_name" required placeholder="Lee"></div>
                  </div>
                  <div class="field"><label>Address</label><input type="text" name="address" required placeholder="123 Trail Run Ave"></div>
                  <div class="field-row">
                    <div class="field"><label>City</label><input type="text" name="city" required placeholder="Austin"></div>
                    <div class="field"><label>ZIP / Postal code</label><input type="text" name="zip" required placeholder="78701"></div>
                  </div>
                </div>

                <div class="checkout-section">
                  <h3><span class="stepnum">2</span>Payment</h3>
                  <div class="pay-methods">
                    <button type="button" class="active" data-method="card">Card</button>
                    <button type="button" data-method="paypal">PayPal</button>
                    <button type="button" data-method="applepay">Apple Pay</button>
                  </div>
                  <input type="hidden" name="payment_method" id="paymentMethodInput" value="card">
                  
                  <div class="field"><label>Card number</label><input type="text" name="card_number" required placeholder="•••• •••• •••• ••••"></div>
                  <div class="field-row">
                    <div class="field"><label>Expiry</label><input type="text" name="card_expiry" required placeholder="MM / YY"></div>
                    <div class="field"><label>CVC</label><input type="text" name="card_cvc" required placeholder="•••"></div>
                  </div>
                </div>
              </div>

              <aside class="summary-card">
                <h3>Review order</h3>
                <div id="checkoutLines"></div>
                <div class="summary-row total"><span>Total due</span><span id="checkoutTotal">$<?= $subtotal ?></span></div>
                <button type="submit" class="btn btn-primary place-order-btn" id="placeOrderBtn" style="padding:14px; background:#0C0D10; color:#fff; border-radius:12px; font-weight:700;">Place Order & Save Details</button>
                <p class="secure-note">Your personal details will be securely stored to your account.</p>
              </aside>
            </div>
          </div>
        </form>
      </div>
    <?php endif; ?>
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

  if (urlParams.get('order') === 'success') {
      orderModal.showModal();
      window.history.replaceState({}, document.title, window.location.pathname);
  }

  if(closeOrderModalBtn) {
    closeOrderModalBtn.addEventListener('click', () => {
      orderModal.close();
    });
  }

  if(orderModal) {
    orderModal.addEventListener('click', (event) => {
      const rect = orderModal.getBoundingClientRect();
      if (
        event.clientX < rect.left ||
        event.clientX > rect.right ||
        event.clientY < rect.top ||
        event.clientY > rect.bottom
      ) {
        orderModal.close();
      }
    });
  }

  function updateTotals() {
    let currentSubtotal = 0;
    const lines = document.querySelectorAll('.cart-line');
    const checkoutLinesContainer = document.getElementById('checkoutLines');
    checkoutLinesContainer.innerHTML = '';

    lines.forEach((line) => {
      const checkbox = line.querySelector('.item-select');
      const unitPrice = parseFloat(line.dataset.price);
      const qty = parseInt(line.dataset.qty);
      const name = line.querySelector('.cart-line-name').textContent;

      if (checkbox.checked) {
        currentSubtotal += unitPrice * qty;
        checkoutLinesContainer.innerHTML += `
          <div class="summary-row">
            <span>${name} × ${qty}</span><span>$${unitPrice * qty}</span>
          </div>`;
      }
    });

    document.getElementById('sumSubtotal').textContent = `$${currentSubtotal}`;
    document.getElementById('sumTotal').textContent = `$${currentSubtotal}`;
    document.getElementById('checkoutTotal').textContent = `$${currentSubtotal}`;
  }

  document.querySelectorAll('.cart-line').forEach(line => {
    const qtyDisplay = line.querySelector('.qty-display');
    const lineTotalDisplay = line.querySelector('.line-total');
    const unitPrice = parseFloat(line.dataset.price);
    const checkbox = line.querySelector('.item-select');

    line.querySelectorAll('.qty-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        let currentQty = parseInt(line.dataset.qty);
        const action = btn.dataset.action;

        if (action === 'plus') {
          currentQty++;
        } else if (action === 'minus') {
          currentQty = Math.max(1, currentQty - 1);
        }

        line.dataset.qty = currentQty;
        qtyDisplay.textContent = currentQty;
        lineTotalDisplay.textContent = unitPrice * currentQty;

        updateTotals();
      });
    });

    checkbox.addEventListener('change', updateTotals);
  });

  updateTotals();

  const checkoutBtn = document.getElementById('checkoutBtn');
  if(checkoutBtn){
    checkoutBtn.addEventListener('click', () => {
      document.getElementById('checkoutPanel').classList.add('show');
      document.getElementById('checkoutPanel').scrollIntoView({ behavior:'smooth', block:'start' });
    });
  }

  document.querySelectorAll('.pay-methods button').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('.pay-methods button').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      document.getElementById('paymentMethodInput').value = btn.dataset.method;
    });
  });
</script>
</body>
</html>