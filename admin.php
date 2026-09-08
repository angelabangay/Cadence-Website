<?php
session_start();

// ---------- AUTHENTICATION & ADMIN GUARD ----------
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Optional: You can check if the user is an admin, e.g.:
// if ($_SESSION['role'] !== 'admin') { header('Location: home.php'); exit; }

$username = $_SESSION['username'] ?? 'Admin';

// Sample inventory/products array or database fetch mockup
$PRODUCTS = [
  ['id' => 'velo', 'name' => 'Velo', 'cat' => 'Flagship', 'price' => 149],
  ['id' => 'aero', 'name' => 'Aero', 'cat' => 'Everyday Comfort', 'price' => 99],
  ['id' => 'ion', 'name' => 'Ion', 'cat' => 'Work Durability', 'price' => 139],
  ['id' => 'tempo', 'name' => 'Tempo', 'cat' => 'Gym Performance', 'price' => 129],
  ['id' => 'ridge', 'name' => 'Ridge', 'cat' => 'Gym Performance', 'price' => 119],
  ['id' => 'terra', 'name' => 'Terra', 'cat' => 'Everyday Comfort', 'price' => 109],
  ['id' => 'surge', 'name' => 'Surge', 'cat' => 'Running Pro', 'price' => 159],
  ['id' => 'nova', 'name' => 'Nova', 'cat' => 'Trail & Outdoor', 'price' => 169]
];

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
<title>Admin Dashboard - Cadence</title>
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
  body { font-family: var(--body); background: var(--off); color: var(--black); margin: 0; }
  h1, h2, h3, .logo { font-family: var(--heading); }
  .brand-logo-img { height: 24px; width: auto; vertical-align: middle; object-fit: contain; }
  
  .admin-layout { display: flex; min-height: 100vh; }
  .admin-sidebar { width: 260px; background: var(--white); border-right: 1px solid var(--line); padding: 24px; display: flex; flex-direction: column; justify-content: space-between; }
  .sidebar-nav { margin-top: 30px; display: flex; flex-direction: column; gap: 8px; }
  .sidebar-nav a { padding: 10px 14px; border-radius: 8px; color: var(--muted); text-decoration: none; font-weight: 600; font-size: 14.5px; }
  .sidebar-nav a.active, .sidebar-nav a:hover { background: var(--off); color: var(--black); }
  
  .admin-content { flex: 1; padding: 40px; box-sizing: border-box; overflow-y: auto; }
  .admin-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px; }
  .admin-title { font-size: 28px; font-weight: 800; margin: 0; }
  
  .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 36px; }
  .stat-card { background: var(--white); border: 1px solid var(--line); border-radius: 14px; padding: 22px; }
  .stat-card h4 { margin: 0 0 8px; font-size: 13px; color: var(--muted); text-transform: uppercase; }
  .stat-card .stat-value { font-size: 26px; font-weight: 800; font-family: var(--heading); margin: 0; }

  .data-card { background: var(--white); border: 1px solid var(--line); border-radius: 14px; padding: 24px; }
  .data-card h3 { margin-top: 0; font-size: 18px; font-weight: 800; margin-bottom: 16px; }
  
  table { width: 100%; border-collapse: collapse; text-align: left; font-size: 14px; }
  th { padding: 12px; border-bottom: 2px solid var(--line); color: var(--muted); font-size: 12px; text-transform: uppercase; }
  td { padding: 14px 12px; border-bottom: 1px solid var(--line); }
  tr:last-child td { border-bottom: none; }
  
  .badge { background: rgba(0, 192, 232, 0.12); color: #0090b0; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 700; }
  .action-btn { background: none; border: 1.5px solid var(--line); padding: 6px 12px; border-radius: 6px; font-weight: 700; cursor: pointer; font-size: 12px; }
  .action-btn:hover { border-color: var(--black); }
</style>
</head>
<body>

<div class="admin-layout">
  <!-- Sidebar -->
  <aside class="admin-sidebar">
    <div>
      <a href="index.php" class="logo" style="text-decoration:none; color:var(--black); font-weight:800; display:flex; align-items:center; gap:8px;">
        <?= logoImg('light') ?> CADENCE ADMIN
      </a>
      <div class="sidebar-nav">
        <a href="admin.php" class="active">Dashboard</a>
        <a href="shop.php" target="_blank">View Live Store</a>
        <a href="home.php">Return to App</a>
      </div>
    </div>
    <div>
      <div style="font-size: 13px; color: var(--muted); margin-bottom: 8px;">Logged in as <b><?= htmlspecialchars($username) ?></b></div>
      <a href="login.php?logout=1" style="color: #ff3b30; text-decoration: none; font-weight: 700; font-size: 13px;">Logout</a>
    </div>
  </aside>

  <!-- Main Content Area -->
  <main class="admin-content">
    <div class="admin-header">
      <h1 class="admin-title">Dashboard Overview</h1>
    </div>

    <!-- Quick Stats Grid -->
    <div class="stats-grid">
      <div class="stat-card">
        <h4>Total Products</h4>
        <p class="stat-value"><?= count($PRODUCTS) ?></p>
      </div>
      <div class="stat-card">
        <h4>Active Session</h4>
        <p class="stat-value" style="font-size: 18px; color: #00a86b; margin-top: 4px;">Secure & Active</p>
      </div>
      <div class="stat-card">
        <h4>Store Status</h4>
        <p class="stat-value" style="font-size: 18px; color: var(--cyan); margin-top: 4px;">Online</p>
      </div>
    </div>

    <!-- Inventory Management Table -->
    <div class="data-card">
      <h3>Store Inventory Catalog</h3>
      <table>
        <thead>
          <tr>
            <th>Product ID</th>
            <th>Name</th>
            <th>Category</th>
            <th>Price</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($PRODUCTS as $p): ?>
            <tr>
              <td><code><?= htmlspecialchars($p['id']) ?></code></td>
              <td><strong><?= htmlspecialchars($p['name']) ?></strong></td>
              <td><span class="badge"><?= htmlspecialchars($p['cat']) ?></span></td>
              <td>$<?= htmlspecialchars($p['price']) ?></td>
              <td>
                <button type="button" class="action-btn" onclick="alert('Product edit capability ready for integration.')">Edit</button>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </main>
</div>

</body>
</html>