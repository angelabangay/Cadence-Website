<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<nav class="main-navbar" style="display: flex; justify-content: space-between; align-items: center; padding: 20px 40px; background: #fff; border-bottom: 1px solid #E5E5EA;">
    <div class="nav-brand">
        <a href="home.php" style="text-decoration: none; font-weight: 800; color: #0C0D10; font-size: 20px;">CADENCE</a>
    </div>
    
    <div class="nav-links" style="display: flex; gap: 20px; align-items: center;">
        <a href="home.php" style="text-decoration: none; color: #6b6b73; font-weight: 600;">Home</a>
        <a href="shop.php" style="text-decoration: none; color: #6b6b73; font-weight: 600;">Shop</a>
        
        <!-- Dynamic User Profile / Login State -->
        <?php if ((isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) || isset($_SESSION['admin_logged'])): ?>
            <a href="profile.php" style="display: flex; align-items: center; gap: 8px; text-decoration: none; color: #0C0D10; font-weight: 700;">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
                <span><?= htmlspecialchars($_SESSION['username'] ?? $_SESSION['admin_user'] ?? 'Admin') ?></span>
            </a>
        <?php else: ?>
            <a href="login.php" style="display: flex; align-items: center; gap: 8px; text-decoration: none; color: #0C0D10; font-weight: 700;">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
                <span>Login</span>
            </a>
        <?php endif; ?>
    </div>
</nav>