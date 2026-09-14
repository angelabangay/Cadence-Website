<?php
session_start();

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// SECURITY CHECK: Must be logged in AND have the 'admin' role. 
// If not logged in, or if logged in as a customer (role !== 'admin'), redirect to index.php
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: index.php');
    exit;
}

$username = $_SESSION['username'] ?? 'Admin';

// ---------- DATABASE CONNECTION ----------
$host = 'localhost';
$db   = 'cadence_db';
$user = 'root';
$pass = 'user123';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database Connection Failed: " . $e->getMessage());
}

$success_msg = '';

// Handle Form Submissions (Add product, Edit product, or Update Order Status)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'edit_product') {
        $edit_id = $_POST['product_id'] ?? '';
        $new_name = trim($_POST['name'] ?? '');
        $new_cat = trim($_POST['cat'] ?? '');
        $new_tag = trim($_POST['tag'] ?? '');
        $new_price = floatval($_POST['price'] ?? 0);
        $new_rating = floatval($_POST['rating'] ?? 0);
        $new_stock = intval($_POST['stock'] ?? 0);

        $stmt = $pdo->prepare("UPDATE products SET name = ?, cat = ?, tag = ?, price = ?, rating = ?, stock = ? WHERE id = ?");
        $stmt->execute([$new_name, $new_cat, $new_tag, $new_price, $new_rating, $new_stock, $edit_id]);
        $success_msg = "Product '{$new_name}' updated successfully!";
    } 
    elseif ($action === 'add_product') {
        $new_id = trim($_POST['id'] ?? '');
        $new_name = trim($_POST['name'] ?? '');
        $new_cat = trim($_POST['cat'] ?? '');
        $new_tag = trim($_POST['tag'] ?? '');
        $new_price = floatval($_POST['price'] ?? 0);
        $new_rating = floatval($_POST['rating'] ?? 5.0);
        $new_stock = intval($_POST['stock'] ?? 0);

        $stmt = $pdo->prepare("INSERT INTO products (id, name, cat, tag, price, rating, stock) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$new_id, $new_name, $new_cat, $new_tag, $new_price, $new_rating, $new_stock]);
        $success_msg = "New product '{$new_name}' added successfully!";
    }
    elseif ($action === 'update_order_status') {
        $order_id = intval($_POST['order_id'] ?? 0);
        $new_status = $_POST['status'] ?? 'pending';

        $allowed_statuses = ['pending', 'delivered', 'cancelled'];
        if (in_array($new_status, $allowed_statuses)) {
            $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
            $stmt->execute([$new_status, $order_id]);
            $success_msg = "Order #{$order_id} status updated to " . ucfirst($new_status) . "!";
        }
    }
}

// Fetch all products
$stmt = $pdo->query("SELECT * FROM products");
$PRODUCTS = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all customer orders
$stmtOrders = $pdo->query("SELECT * FROM orders ORDER BY created_at DESC");
$ORDERS = $stmtOrders->fetchAll(PDO::FETCH_ASSOC);

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
<link href="https://fonts.googleapis.com/css2?family=Afacad+Flux:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="css/style.css">
<style>
  :root{
    --heading:'Afacad Flux',sans-serif;
    --body:'Afacad Flux',sans-serif;
    --black:#0C0D10;
    --cyan:#00C0E8;
    --line:#E5E5EA;
    --off:#F5F5F7;
    --muted:#6b6b73;
    --white:#FFFFFF;
  }
  body { font-family: var(--body); background: var(--off); color: var(--black); margin: 0; }
  h1, h2, h3, .logo { font-family: var(--heading); }
  code { font-family: var(--body); }
  .brand-logo-img { height: 24px; width: auto; vertical-align: middle; object-fit: contain; }
  
  .admin-layout { display: flex; min-height: 100vh; }
  .admin-sidebar { width: 260px; background: var(--white); border-right: 1px solid var(--line); padding: 24px; display: flex; flex-direction: column; justify-content: space-between; box-sizing: border-box; }
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

  .data-card { background: var(--white); border: 1px solid var(--line); border-radius: 14px; padding: 24px; margin-bottom: 36px; }
  .data-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; }
  .data-card h3 { margin: 0; font-size: 18px; font-weight: 800; }
  
  .btn-primary { background: var(--cyan); color: var(--black); border: none; padding: 10px 18px; border-radius: 8px; font-weight: 700; cursor: pointer; font-family: inherit; font-size: 13px; }
  .btn-primary:hover { opacity: 0.9; }

  table { width: 100%; border-collapse: collapse; text-align: left; font-size: 14px; }
  th { padding: 12px; border-bottom: 2px solid var(--line); color: var(--muted); font-size: 12px; text-transform: uppercase; }
  td { padding: 14px 12px; border-bottom: 1px solid var(--line); }
  tr:last-child td { border-bottom: none; }
  
  .badge { background: rgba(0, 192, 232, 0.12); color: #0090b0; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 700; text-transform: capitalize; }
  .status-badge { padding: 4px 10px; border-radius: 20px; font-size: 11.5px; font-weight: 700; text-transform: uppercase; display: inline-block; }
  .status-pending { background: rgba(255, 149, 0, 0.12); color: #b26800; }
  .status-delivered { background: rgba(0, 168, 107, 0.12); color: #00a86b; }
  .status-cancelled { background: rgba(255, 59, 48, 0.12); color: #ff3b30; }

  .stock-badge { font-weight: 700; }
  .stock-low { color: #ff3b30; }
  .stock-ok { color: #00a86b; }

  .action-btn { background: none; border: 1.5px solid var(--line); padding: 6px 12px; border-radius: 6px; font-weight: 700; cursor: pointer; font-size: 12px; font-family: inherit; }
  .action-btn:hover { border-color: var(--black); background: var(--off); }
  
  .success-banner { background: rgba(0, 168, 107, 0.1); border: 1px solid rgba(0, 168, 107, 0.3); color: #00a86b; padding: 12px 16px; border-radius: 10px; font-size: 14px; margin-bottom: 24px; font-weight: 600; }

  .logout-box { background: rgba(255, 59, 48, 0.05); border: 1px solid rgba(255, 59, 48, 0.15); padding: 14px; border-radius: 12px; text-align: center; }
  .logout-box .user-info { font-size: 13px; color: var(--muted); margin-bottom: 8px; }
  .btn-logout { display: block; background: #ff3b30; color: #fff; text-decoration: none; font-weight: 700; font-size: 13px; padding: 8px 12px; border-radius: 8px; transition: opacity 0.2s; }
  .btn-logout:hover { opacity: 0.9; }

  /* Modal Styling */
  .modal-overlay { display: none; position: fixed; inset: 0; background: rgba(12, 13, 16, 0.6); align-items: center; justify-content: center; z-index: 1000; }
  .modal-card { background: var(--white); padding: 30px; border-radius: 16px; width: 100%; max-width: 450px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); }
  .modal-card h3 { margin-top: 0; margin-bottom: 20px; font-size: 20px; }
  .form-group { margin-bottom: 16px; }
  .form-group label { display: block; font-size: 12px; font-weight: 700; text-transform: uppercase; color: var(--muted); margin-bottom: 6px; }
  .form-group input, .form-group select { width: 100%; padding: 10px 14px; border: 1.5px solid var(--line); border-radius: 8px; font-family: inherit; font-size: 14px; box-sizing: border-box; background: var(--white); }
  .form-group input:focus, .form-group select:focus { border-color: var(--cyan); outline: none; }
  .modal-actions { display: flex; gap: 10px; margin-top: 24px; }
  .btn-save { flex: 1; background: var(--cyan); color: var(--black); border: none; padding: 12px; border-radius: 8px; font-weight: 700; cursor: pointer; font-family: inherit; }
  .btn-cancel { flex: 1; background: var(--off); color: var(--black); border: 1.5px solid var(--line); padding: 12px; border-radius: 8px; font-weight: 700; cursor: pointer; font-family: inherit; }
  
  .order-details-text { font-size: 13.5px; line-height: 1.6; color: #3a3a40; background: var(--off); padding: 14px; border-radius: 8px; margin-bottom: 16px; white-space: pre-line; }
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
      </div>
    </div>
    
    <div class="logout-box">
      <div class="user-info">Logged in as <b><?= htmlspecialchars($username) ?></b></div>
      <a href="login.php?logout=1" class="btn-logout">Log Out</a>
    </div>
  </aside>

  <!-- Main Content -->
  <main class="admin-content">
    <div class="admin-header">
      <h1 class="admin-title">Dashboard Overview</h1>
    </div>

    <?php if (!empty($success_msg)): ?>
      <div class="success-banner"><?= htmlspecialchars($success_msg) ?></div>
    <?php endif; ?>

    <!-- Quick Stats Grid -->
    <div class="stats-grid">
      <div class="stat-card">
        <h4>Total Orders</h4>
        <p class="stat-value"><?= count($ORDERS) ?></p>
      </div>
      <div class="stat-card">
        <h4>Total Products</h4>
        <p class="stat-value"><?= count($PRODUCTS) ?></p>
      </div>
      <div class="stat-card">
        <h4>Store Status</h4>
        <p class="stat-value" style="font-size: 18px; color: var(--cyan); margin-top: 4px;">Online</p>
      </div>
    </div>

    <!-- Customer Orders Management Table -->
    <div class="data-card">
      <div class="data-header">
        <h3>Customer Orders</h3>
      </div>
      <table>
        <thead>
          <tr>
            <th>Order ID</th>
            <th>Customer</th>
            <th>Total</th>
            <th>Payment</th>
            <th>Current Status</th>
            <th>Actions / Update Status</th>
          </tr>
        </thead>
        <tbody>
          <?php if(empty($ORDERS)): ?>
            <tr>
              <td colspan="6" style="text-align: center; color: var(--muted); padding: 30px;">No customer orders placed yet.</td>
            </tr>
          <?php else: ?>
            <?php foreach($ORDERS as $ord): 
              $statusClass = 'status-' . $ord['status'];
            ?>
              <tr>
                <td><code>#<?= $ord['id'] ?></code></td>
                <td>
                  <strong><?= htmlspecialchars($ord['first_name'] . ' ' . $ord['last_name']) ?></strong><br>
                  <span style="font-size: 12px; color: var(--muted);"><?= htmlspecialchars($ord['email']) ?></span>
                </td>
                <td><strong>$<?= htmlspecialchars($ord['total']) ?></strong></td>
                <td>
                  <span style="text-transform: uppercase; font-size: 12px; font-weight: 700;"><?= htmlspecialchars($ord['payment_method']) ?></span>
                </td>
                <td>
                  <span class="status-badge <?= $statusClass ?>"><?= htmlspecialchars($ord['status']) ?></span>
                </td>
                <td>
                  <div style="display: flex; gap: 8px; align-items: center;">
                    <button type="button" class="action-btn" onclick="openOrderModal(
                      '<?= $ord['id'] ?>',
                      '<?= htmlspecialchars($ord['first_name'] . ' ' . $ord['last_name'], ENT_QUOTES) ?>',
                      '<?= htmlspecialchars($ord['email'], ENT_QUOTES) ?>',
                      '<?= htmlspecialchars($ord['address'] . ', ' . $ord['city'] . ' ' . $ord['zip'], ENT_QUOTES) ?>',
                      '<?= htmlspecialchars($ord['payment_method'], ENT_QUOTES) ?>',
                      '<?= htmlspecialchars($ord['order_items'], ENT_QUOTES) ?>',
                      '<?= $ord['total'] ?>',
                      '<?= $ord['status'] ?>'
                    )">View Details</button>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <!-- Inventory Management Table -->
    <div class="data-card">
      <div class="data-header">
        <h3>Store Inventory Catalog</h3>
        <button type="button" class="btn-primary" onclick="openAddModal()">+ Add New Product</button>
      </div>
      <table>
        <thead>
          <tr>
            <th>Product ID</th>
            <th>Name</th>
            <th>Category</th>
            <th>Filter Tag</th>
            <th>Price</th>
            <th>Rating</th>
            <th>Stock</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($PRODUCTS as $p): 
            $stockVal = intval($p['stock'] ?? 0);
          ?>
            <tr>
              <td><code><?= htmlspecialchars($p['id']) ?></code></td>
              <td><strong><?= htmlspecialchars($p['name']) ?></strong></td>
              <td><?= htmlspecialchars($p['cat']) ?></td>
              <td><span class="badge"><?= htmlspecialchars($p['tag'] ?? 'runners') ?></span></td>
              <td>$<?= htmlspecialchars($p['price']) ?></td>
              <td>⭐ <?= htmlspecialchars($p['rating'] ?? '5.0') ?></td>
              <td>
                <span class="stock-badge <?= $stockVal > 0 ? 'stock-ok' : 'stock-low' ?>">
                  <?= $stockVal ?> units
                </span>
              </td>
              <td>
                <button type="button" class="action-btn" onclick="openEditModal('<?= $p['id'] ?>', '<?= htmlspecialchars($p['name'], ENT_QUOTES) ?>', '<?= htmlspecialchars($p['cat'], ENT_QUOTES) ?>', '<?= htmlspecialchars($p['tag'] ?? 'runners', ENT_QUOTES) ?>', '<?= $p['price'] ?>', '<?= $p['rating'] ?? '5.0' ?>', '<?= $stockVal ?>')">Edit</button>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </main>
</div>

<!-- View & Update Order Modal -->
<div class="modal-overlay" id="orderModal">
  <div class="modal-card">
    <h3>Order Details <span id="modalOrderIdDisplay" style="color: var(--cyan);"></span></h3>
    
    <div class="form-group" style="margin-bottom: 10px;">
      <label>Customer Name & Email</label>
      <div id="modalCustomerInfo" style="font-size: 14px; font-weight: 600;"></div>
    </div>

    <div class="form-group" style="margin-bottom: 10px;">
      <label>Shipping Address</label>
      <div id="modalAddressInfo" style="font-size: 14px; font-weight: 600;"></div>
    </div>

    <div class="form-group" style="margin-bottom: 10px;">
      <label>Payment Type</label>
      <div id="modalPaymentInfo" style="font-size: 14px; font-weight: 600; text-transform: uppercase;"></div>
    </div>

    <div class="form-group">
      <label>Purchased Items</label>
      <div class="order-details-text" id="modalItemsInfo"></div>
    </div>

    <form method="POST" action="admin.php">
      <input type="hidden" name="action" value="update_order_status">
      <input type="hidden" name="order_id" id="modalOrderDatabaseId">

      <div class="form-group">
        <label>Update Order Status</label>
        <select name="status" id="modalOrderStatusSelect" required>
          <option value="pending">Pending</option>
          <option value="delivered">Delivered</option>
          <option value="cancelled">Cancelled</option>
        </select>
      </div>

      <div class="modal-actions">
        <button type="submit" class="btn-save">Save Status</button>
        <button type="button" class="btn-cancel" onclick="closeOrderModal()">Close</button>
      </div>
    </form>
  </div>
</div>

<!-- Edit Product Modal -->
<div class="modal-overlay" id="editModal">
  <div class="modal-card">
    <h3>Edit Product Info</h3>
    <form method="POST" action="admin.php">
      <input type="hidden" name="action" value="edit_product">
      <input type="hidden" name="product_id" id="modalProductId">
      
      <div class="form-group">
        <label>Product Name</label>
        <input type="text" name="name" id="modalProductName" required>
      </div>
      
      <div class="form-group">
        <label>Category Description</label>
        <input type="text" name="cat" id="modalProductCat" required>
      </div>

      <div class="form-group">
        <label>Shop Filter Tag</label>
        <select name="tag" id="modalProductTag" required>
          <option value="runners">Runners</option>
          <option value="professionals">Professionals</option>
          <option value="gym">Gym-Goers</option>
          <option value="travelers">Travelers</option>
        </select>
      </div>
      
      <div class="form-group">
        <label>Price ($)</label>
        <input type="number" step="1" name="price" id="modalProductPrice" required>
      </div>

      <div class="form-group">
        <label>Rating (e.g., 4.80)</label>
        <input type="number" step="0.01" min="1.0" max="5.0" name="rating" id="modalProductRating" required>
      </div>

      <div class="form-group">
        <label>Stock Quantity</label>
        <input type="number" step="1" min="0" name="stock" id="modalProductStock" required>
      </div>

      <div class="modal-actions">
        <button type="submit" class="btn-save">Save Changes</button>
        <button type="button" class="btn-cancel" onclick="closeEditModal()">Cancel</button>
      </div>
    </form>
  </div>
</div>

<!-- Add Product Modal -->
<div class="modal-overlay" id="addModal">
  <div class="modal-card">
    <h3>Add New Product</h3>
    <form method="POST" action="admin.php">
      <input type="hidden" name="action" value="add_product">
      
      <div class="form-group">
        <label>Product ID (e.g., prod_05)</label>
        <input type="text" name="id" required>
      </div>

      <div class="form-group">
        <label>Product Name</label>
        <input type="text" name="name" required>
      </div>
      
      <div class="form-group">
        <label>Category Description</label>
        <input type="text" name="cat" required>
      </div>

      <div class="form-group">
        <label>Shop Filter Tag</label>
        <select name="tag" required>
          <option value="runners">Runners</option>
          <option value="professionals">Professionals</option>
          <option value="gym">Gym-Goers</option>
          <option value="travelers">Travelers</option>
        </select>
      </div>
      
      <div class="form-group">
        <label>Price ($)</label>
        <input type="number" step="1" name="price" required>
      </div>

      <div class="form-group">
        <label>Rating (e.g., 5.0)</label>
        <input type="number" step="0.01" min="1.0" max="5.0" name="rating" value="5.0" required>
      </div>

      <div class="form-group">
        <label>Stock Quantity</label>
        <input type="number" step="1" min="0" name="stock" value="10" required>
      </div>

      <div class="modal-actions">
        <button type="submit" class="btn-save">Add Product</button>
        <button type="button" class="btn-cancel" onclick="closeAddModal()">Cancel</button>
      </div>
    </form>
  </div>
</div>

<script>
  function openOrderModal(id, name, email, address, payment, items, total, status) {
    document.getElementById('modalOrderIdDisplay').textContent = '#' + id;
    document.getElementById('modalOrderDatabaseId').value = id;
    document.getElementById('modalCustomerInfo').textContent = name + ' (' + email + ')';
    document.getElementById('modalAddressInfo').textContent = address;
    document.getElementById('modalPaymentInfo').textContent = payment;
    document.getElementById('modalItemsInfo').textContent = items + '\n\nTotal Paid: $' + total;
    document.getElementById('modalOrderStatusSelect').value = status;
    document.getElementById('orderModal').style.display = 'flex';
  }

  function closeOrderModal() {
    document.getElementById('orderModal').style.display = 'none';
  }

  function openEditModal(id, name, cat, tag, price, rating, stock) {
    document.getElementById('modalProductId').value = id;
    document.getElementById('modalProductName').value = name;
    document.getElementById('modalProductCat').value = cat;
    document.getElementById('modalProductTag').value = tag;
    document.getElementById('modalProductPrice').value = price;
    document.getElementById('modalProductRating').value = rating;
    document.getElementById('modalProductStock').value = stock;
    document.getElementById('editModal').style.display = 'flex';
  }

  function closeEditModal() {
    document.getElementById('editModal').style.display = 'none';
  }

  function openAddModal() {
    document.getElementById('addModal').style.display = 'flex';
  }

  function closeAddModal() {
    document.getElementById('addModal').style.display = 'none';
  }
</script>

</body>
</html>