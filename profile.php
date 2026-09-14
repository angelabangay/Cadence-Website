<?php
session_start();
require_once 'database/config.php';

// ---------- AUTHENTICATION GUARD ----------
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// ---------- ADMIN ROLE REDIRECT ----------
if (($_SESSION['role'] ?? '') === 'admin' || ($_SESSION['username'] ?? '') === 'admin') {
    header('Location: admin.php');
    exit;
}

$username = $_SESSION['username'] ?? 'User';
$userId = $_SESSION['user_id'] ?? null;

$successMessage = '';
$errorMessage = '';
$showThankYouModal = false;

// Check if redirected from a successful checkout order
if (isset($_GET['success']) && $_GET['success'] == '1') {
    $showThankYouModal = true;
}

// Handle Profile Update Submission from form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName  = trim($_POST['last_name'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $address   = trim($_POST['address'] ?? '');
    $city      = trim($_POST['city'] ?? '');
    $zip       = trim($_POST['zip'] ?? '');

    if (empty($email)) {
        $errorMessage = 'Email address cannot be empty.';
    } else {
        try {
            $stmtCheck = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmtCheck->execute([$email, $userId]);
            if ($stmtCheck->rowCount() > 0) {
                $errorMessage = 'This email address is already in use by another account.';
            } else {
                $stmtUpdate = $pdo->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ?, address = ?, city = ?, zip = ? WHERE id = ?");
                $stmtUpdate->execute([$firstName, $lastName, $email, $address, $city, $zip, $userId]);
                $successMessage = 'Your profile details have been successfully updated!';
            }
        } catch (\PDOException $e) {
            $errorMessage = 'Database update failed: ' . $e->getMessage();
        }
    }
}

// Fetch user details and orders
$user = [];
$orders = [];

try {
    $stmtUser = $pdo->prepare("SELECT * FROM users WHERE id = ? OR username = ?");
    $stmtUser->execute([$userId, $username]);
    $user = $stmtUser->fetch(PDO::FETCH_ASSOC);

    $userEmail = $user['email'] ?? '';
    if (!empty($userEmail)) {
        $stmtOrders = $pdo->prepare("SELECT * FROM orders WHERE email = ? ORDER BY created_at DESC");
        $stmtOrders->execute([$userEmail]);
        $orders = $stmtOrders->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (\PDOException $e) {
    // Handle query error gracefully
}

$cart = $_SESSION['cart'] ?? [];
$cartCount = array_sum($cart);

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
<title>My Profile - Cadence</title>
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
  body { font-family: var(--body); background: var(--white); color: var(--black); margin: 0; }
  h1, h2, h3, .logo { font-family: var(--heading); }
  .brand-logo-img { height: 24px; width: auto; vertical-align: middle; object-fit: contain; }
  
  .profile-main { padding: 60px 0 100px; background: var(--off); min-height: 70vh; }
  .profile-container { max-width: 800px; margin: 0 auto; background: var(--white); border: 1px solid var(--line); border-radius: 20px; padding: 40px; box-shadow: 0 10px 30px rgba(0,0,0,0.03); }
  .profile-header { display: flex; align-items: center; gap: 20px; margin-bottom: 30px; padding-bottom: 24px; border-bottom: 1px solid var(--line); }
  .profile-avatar { width: 64px; height: 64px; border-radius: 50%; background: rgba(0,192,232,0.12); color: var(--cyan); display: flex; align-items: center; justify-content: center; font-family: var(--heading); font-size: 26px; font-weight: 800; }
  .profile-info h1 { font-size: 24px; font-weight: 800; margin: 0 0 4px; }
  .profile-info p { color: var(--muted); font-size: 14px; margin: 0; font-weight: 600; }

  .profile-section-title { font-size: 18px; font-weight: 800; margin-top: 35px; margin-bottom: 16px; font-family: var(--heading); display: flex; justify-content: space-between; align-items: center; }
  .profile-details { display: flex; flex-direction: column; gap: 12px; margin-bottom: 25px; }
  .detail-row { display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px dashed var(--line); font-size: 14.5px; }
  .detail-label { color: var(--muted); font-weight: 600; }
  .detail-value { font-weight: 700; color: var(--black); }

  .edit-form { display: none; margin-top: 15px; background: var(--off); padding: 24px; border-radius: 14px; border: 1px solid var(--line); }
  .edit-form.active { display: block; }
  .field { margin-bottom: 14px; }
  .field label { display: block; font-size: 12px; font-weight: 700; margin-bottom: 6px; text-transform: uppercase; color: var(--muted); }
  .field input { width: 100%; padding: 11px 14px; border-radius: 10px; border: 1.5px solid var(--line); font-family: inherit; font-size: 13.5px; box-sizing: border-box; background: #fff; }
  .field-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }

  .orders-table-wrapper { overflow-x: auto; margin-top: 10px; border: 1px solid var(--line); border-radius: 12px; }
  .orders-table { width: 100%; border-collapse: collapse; text-align: left; font-size: 13.5px; }
  .orders-table th { background: var(--off); padding: 12px 16px; font-weight: 700; color: var(--muted); border-bottom: 1px solid var(--line); }
  .orders-table td { padding: 14px 16px; border-bottom: 1px solid var(--line); color: var(--black); vertical-align: top; }
  .orders-table tr:last-child td { border-bottom: none; }
  .badge-status { display: inline-block; padding: 4px 10px; border-radius: 99px; font-size: 11.5px; font-weight: 700; text-transform: uppercase; background: #fff3cd; color: #856404; }
  .badge-status.completed { background: #d4edda; color: #155724; }
  .no-orders { text-align: center; color: var(--muted); padding: 24px; font-size: 14px; }

  .profile-actions { display: flex; gap: 12px; margin-top: 35px; }
  .profile-actions a, .profile-actions button { flex: 1; padding: 14px; border-radius: 12px; text-align: center; font-weight: 700; text-decoration: none; font-size: 14px; cursor: pointer; border: none; }
  .btn-primary-custom { background: var(--black); color: var(--white); }
  .btn-secondary-custom { background: var(--cyan); color: var(--black); }
  .btn-outline-custom { background: transparent; border: 1.5px solid #FF3B30 !important; color: #FF3B30; }
  .btn-outline-custom:hover { background: rgba(255, 59, 48, 0.05); }

  /* Center Modal Alert Popup Style */
  .modal-overlay {
    position: fixed;
    top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(12, 13, 16, 0.7);
    backdrop-filter: blur(4px);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 99999;
    animation: fadeIn 0.3s ease;
  }
  .modal-overlay.active {
    display: flex;
  }
  .modal-box {
    background: #ffffff;
    color: var(--black);
    width: 90%;
    max-width: 420px;
    padding: 32px;
    border-radius: 20px;
    text-align: center;
    box-shadow: 0 20px 40px rgba(0,0,0,0.3);
    animation: scaleUp 0.3s cubic-bezier(0.16, 1, 0.3, 1);
  }
  .modal-icon {
    width: 60px; height: 60px;
    background: rgba(0, 192, 232, 0.12);
    color: var(--cyan);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
  }
  .modal-icon.danger {
    background: rgba(255, 59, 48, 0.12);
    color: #FF3B30;
  }
  .modal-box h3 {
    font-size: 22px;
    font-weight: 800;
    margin: 0 0 10px;
    font-family: var(--heading);
    text-transform: uppercase;
  }
  .modal-box p {
    color: var(--muted);
    font-size: 14.5px;
    line-height: 1.5;
    margin: 0 0 24px;
  }
  .modal-btn-group {
    display: flex;
    gap: 10px;
  }
  .modal-btn {
    flex: 1;
    padding: 14px;
    background: var(--black);
    color: var(--white);
    border: none;
    border-radius: 12px;
    font-weight: 700;
    font-size: 15px;
    cursor: pointer;
    transition: opacity 0.2s;
  }
  .modal-btn:hover { opacity: 0.9; }
  .modal-btn-danger {
    background: #FF3B30;
    color: var(--white);
  }
  .modal-btn-secondary {
    background: #E5E5EA;
    color: var(--black);
  }

  .alert { padding: 12px 16px; border-radius: 10px; font-size: 14px; font-weight: 600; margin-bottom: 20px; }
  .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
  .alert-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }

  @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
  @keyframes scaleUp { from { transform: scale(0.9); opacity: 0; } to { transform: scale(1); opacity: 1; } }
</style>
</head>
<body>

<!-- Center Modal Popup for Thank You / Order Success -->
<?php if ($showThankYouModal): ?>
  <div class="modal-overlay active" id="thankYouModal">
    <div class="modal-box">
      <div class="modal-icon">
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
      </div>
      <h3>Thank You for Shopping!</h3>
      <p>Your order has been successfully placed and saved to your profile history.</p>
      <button type="button" class="modal-btn" onclick="closeModal()">OK</button>
    </div>
  </div>
<?php endif; ?>

<!-- Custom Cancel Confirmation Modal -->
<div class="modal-overlay" id="cancelConfirmModal">
  <div class="modal-box">
    <div class="modal-icon danger">
      <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
    </div>
    <h3>Cancel Order</h3>
    <p id="cancelModalText">Are you sure you want to cancel this order? This action cannot be undone.</p>
    <form id="cancelOrderForm" action="cancel-order.php" method="POST">
      <input type="hidden" name="order_id" id="modalOrderId" value="">
      <div class="modal-btn-group">
        <button type="button" class="modal-btn modal-btn-secondary" onclick="closeCancelModal()">Keep Order</button>
        <button type="submit" class="modal-btn modal-btn-danger">Yes, Cancel</button>
      </div>
    </form>
  </div>
</div>

<nav class="nav">
  <div class="wrap">
    <a href="index.php" class="logo"><?= logoImg('light') ?> CADENCE</a>
    <div class="nav-links">
      <a href="index.php">Home</a>
      <a href="standard.php">Standard</a>
      <a href="shop.php">Shoes</a>
      <a href="reviews.php">Reviews</a>
      <a href="pricing.php">Pricing</a>
      <a href="faq.php">FAQ</a>
    </div>
    <div class="nav-right" style="display: flex; align-items: center; gap: 16px;">
      <a href="profile.php" class="user-pill" style="display: flex; align-items: center; gap: 6px; text-decoration: none; color: inherit; font-weight: 600; font-size: 14px;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle;">
          <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
          <circle cx="12" cy="7" r="4"></circle>
        </svg>
        <span><?= htmlspecialchars($username) ?></span>
      </a>

      <a href="cart.php" class="cart-link" style="position: relative; display: flex; align-items: center;">
        <svg width="21" height="21" viewBox="0 0 24 24" fill="none"><path d="M6 6H21L19 15H8L6 6Z" stroke="black" stroke-width="1.6" stroke-linejoin="round"/><path d="M6 6L5 3H2" stroke="black" stroke-width="1.6" stroke-linecap="round"/><circle cx="9.5" cy="19" r="1.4" fill="black"/><circle cx="17.5" cy="19" r="1.4" fill="black"/></svg>
        <?php if($cartCount > 0): ?><span class="cart-count"><?= $cartCount ?></span><?php endif; ?>
      </a>
      <a href="shop.php" class="btn btn-dark btn-sm">Shop Now</a>
    </div>
  </div>
</nav>

<div class="breadcrumb" style="padding: 16px 0; font-size: 13.5px; color: #6b6b73; border-bottom: 1px solid #E5E5EA;">
  <div class="wrap">
    <a href="index.php" style="color:inherit; text-decoration:none;">Home</a>
    <span class="sep" style="margin: 0 6px;">/</span>
    <span class="current" style="color: #0C0D10; font-weight: 600;">Profile</span>
  </div>
</div>

<main class="profile-main">
  <div class="wrap">
    <div class="profile-container">

      <?php if (!empty($successMessage)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($successMessage) ?></div>
      <?php endif; ?>
      <?php if (!empty($errorMessage)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($errorMessage) ?></div>
      <?php endif; ?>

      <div class="profile-header">
        <div class="profile-avatar">
          <?= strtoupper(substr($username, 0, 1)) ?>
        </div>
        <div class="profile-info">
          <h1><?= htmlspecialchars($username) ?></h1>
          <p>Cadence Member Account</p>
        </div>
      </div>

      <div class="profile-section-title" style="margin-top:0;">
        <span>Account Information</span>
        <button type="button" id="toggleEditBtn" style="background: none; border: none; color: var(--cyan); font-weight: 700; cursor: pointer; font-size: 14px;">Edit Profile</button>
      </div>

      <div class="profile-details" id="profileDetailsView">
        <div class="detail-row">
          <span class="detail-label">Username</span>
          <span class="detail-value"><?= htmlspecialchars($username) ?></span>
        </div>
        <div class="detail-row">
          <span class="detail-label">First Name</span>
          <span class="detail-value"><?= htmlspecialchars($user['first_name'] ?? 'Not provided') ?></span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Last Name</span>
          <span class="detail-value"><?= htmlspecialchars($user['last_name'] ?? 'Not provided') ?></span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Email</span>
          <span class="detail-value"><?= htmlspecialchars($user['email'] ?? 'Not provided') ?></span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Shipping Address</span>
          <span class="detail-value"><?= htmlspecialchars($user['address'] ?? 'No address saved') ?></span>
        </div>
        <div class="detail-row">
          <span class="detail-label">City</span>
          <span class="detail-value"><?= htmlspecialchars($user['city'] ?? 'Not provided') ?></span>
        </div>
        <div class="detail-row">
          <span class="detail-label">ZIP / Postal Code</span>
          <span class="detail-value"><?= htmlspecialchars($user['zip'] ?? 'Not provided') ?></span>
        </div>
        <div class="detail-row">
          <span class="detail-label">Account Status</span>
          <span class="detail-value" style="color: #00A86B;">Active</span>
        </div>
      </div>

      <form action="profile.php" method="POST" class="edit-form" id="profileEditForm">
        <input type="hidden" name="update_profile" value="1">
        <div class="field-row">
          <div class="field">
            <label>First Name</label>
            <input type="text" name="first_name" value="<?= htmlspecialchars($user['first_name'] ?? '') ?>" required>
          </div>
          <div class="field">
            <label>Last Name</label>
            <input type="text" name="last_name" value="<?= htmlspecialchars($user['last_name'] ?? '') ?>" required>
          </div>
        </div>
        <div class="field">
          <label>Email Address</label>
          <input type="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
        </div>
        <div class="field">
          <label>Shipping Address</label>
          <input type="text" name="address" value="<?= htmlspecialchars($user['address'] ?? '') ?>">
        </div>
        <div class="field-row">
          <div class="field">
            <label>City</label>
            <input type="text" name="city" value="<?= htmlspecialchars($user['city'] ?? '') ?>">
          </div>
          <div class="field">
            <label>ZIP / Postal Code</label>
            <input type="text" name="zip" value="<?= htmlspecialchars($user['zip'] ?? '') ?>">
          </div>
        </div>
        <div style="display: flex; gap: 10px; margin-top: 16px;">
          <button type="submit" class="btn-primary-custom" style="padding: 10px 20px; border-radius: 8px; border: none; font-weight: 700; cursor: pointer;">Save Changes</button>
          <button type="button" id="cancelEditBtn" style="background: transparent; border: 1.5px solid var(--line); padding: 10px 20px; border-radius: 8px; font-weight: 700; cursor: pointer;">Cancel</button>
        </div>
      </form>

      <div class="profile-section-title">Order History</div>
      <div class="orders-table-wrapper">
        <table class="orders-table">
          <thead>
            <tr>
              <th>Order ID</th>
              <th>Items</th>
              <th>Total</th>
              <th>Status</th>
              <th>Date</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($orders)): ?>
              <tr>
                <td colspan="6" class="no-orders">You haven't placed any orders yet.</td>
              </tr>
            <?php else: ?>
              <?php foreach ($orders as $order): ?>
                <tr>
                  <td><strong>#<?= htmlspecialchars($order['id']) ?></strong></td>
                  <td style="white-space: pre-line; font-size: 13px;"><?= htmlspecialchars($order['order_items']) ?></td>
                  <td><strong>$<?= number_format($order['total'], 2) ?></strong></td>
                  <td>
                    <?php 
                      $status = strtolower($order['status']);
                      $statusClass = ($status === 'completed' || $status === 'delivered') ? 'completed' : '';
                    ?>
                    <span class="badge-status <?= $statusClass ?>"><?= htmlspecialchars($order['status']) ?></span>
                  </td>
                  <td style="font-size: 12.5px; color: var(--muted);"><?= htmlspecialchars($order['created_at']) ?></td>
                  <td>
                    <?php if ($status === 'pending'): ?>
                      <button type="button" onclick="openCancelModal('<?= $order['id'] ?>')" style="background: #FF3B30; color: white; border: none; padding: 6px 10px; border-radius: 6px; font-size: 11.5px; font-weight: bold; cursor: pointer;">Cancel</button>
                    <?php else: ?>
                      <span style="font-size: 12px; color: var(--muted);">N/A</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <div class="profile-actions">
        <a href="shop.php" class="btn-primary-custom" style="display:inline-block;">Browse Shoes</a>
        <a href="logout.php" class="btn-outline-custom" style="display:inline-block;">Log Out</a>
      </div>
    </div>
  </div>
</main>

<script>
  const toggleEditBtn = document.getElementById('toggleEditBtn');
  const cancelEditBtn = document.getElementById('cancelEditBtn');
  const profileDetailsView = document.getElementById('profileDetailsView');
  const profileEditForm = document.getElementById('profileEditForm');

  toggleEditBtn.addEventListener('click', () => {
    profileDetailsView.style.display = 'none';
    profileEditForm.classList.add('active');
    toggleEditBtn.style.display = 'none';
  });

  cancelEditBtn.addEventListener('click', () => {
    profileEditForm.classList.remove('active');
    profileDetailsView.style.display = 'flex';
    toggleEditBtn.style.display = 'inline-block';
  });

  function closeModal() {
    const modal = document.getElementById('thankYouModal');
    if (modal) {
      modal.classList.remove('active');
      window.history.replaceState({}, document.title, window.location.pathname);
    }
  }

  function openCancelModal(orderId) {
    const modal = document.getElementById('cancelConfirmModal');
    const modalText = document.getElementById('cancelModalText');
    const modalInput = document.getElementById('modalOrderId');
    
    modalText.textContent = 'Are you sure you want to cancel order ? This action cannot be undone and the order will be removed from your history.';
    modalInput.value = orderId;
    modal.classList.add('active');
  }

  function closeCancelModal() {
    const modal = document.getElementById('cancelConfirmModal');
    if (modal) {
      modal.classList.remove('active');
    }
  }
</script>

</body>
</html>