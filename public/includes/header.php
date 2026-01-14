<?php
// Kiểm tra đăng nhập
$isLoggedIn = false;
$userName = '';
$userType = '';
$notificationCount = 0;

if (isset($_SESSION['user_id'])) {
    $isLoggedIn = true;
    $userType = 'admin';
    $userName = $_SESSION['user_name'] ?? 'Admin';
} elseif (isset($_SESSION['customer_id'])) {
    $isLoggedIn = true;
    $userType = 'customer';
    $userName = $_SESSION['customer_name'] ?? 'Khách hàng';
    
    // Đếm thông báo chưa đọc
    try {
        $stmtNoti = $conn->prepare("SELECT COUNT(*) as total FROM thong_bao WHERE MaKhachHang = ? AND DaDoc = 0");
        $stmtNoti->execute([$_SESSION['customer_id']]);
        $notificationCount = $stmtNoti->fetch()['total'] ?? 0;
    } catch(PDOException $e) {
        $notificationCount = 0;
    }
}

// Xác định trang hiện tại
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<div class="main-header">
    <div class="header-content">
        <a href="index.php" class="logo"><i class="fas fa-utensils"></i> Nhà hàng 3CE</a>
        <ul class="nav-menu">
            <li><a href="index.php" class="<?php echo $currentPage == 'index.php' ? 'active' : ''; ?>"><i class="fas fa-home"></i> Trang chủ</a></li>
            <li><a href="thuc_don.php" class="<?php echo $currentPage == 'thuc_don.php' ? 'active' : ''; ?>"><i class="fas fa-book-open"></i> Thực đơn</a></li>
            <li><a href="dat_ban.php" class="<?php echo $currentPage == 'dat_ban.php' ? 'active' : ''; ?>"><i class="fas fa-calendar-check"></i> Đặt bàn</a></li>
            <li><a href="lien_he.php" class="<?php echo $currentPage == 'lien_he.php' ? 'active' : ''; ?>"><i class="fas fa-envelope"></i> Liên hệ</a></li>
            <?php if ($isLoggedIn): ?>
                <?php if ($userType === 'customer'): ?>
                <li><a href="gio_hang.php" class="<?php echo $currentPage == 'gio_hang.php' ? 'active' : ''; ?>"><i class="fas fa-shopping-cart"></i> Giỏ hàng</a></li>
                <li><a href="thong_bao.php" class="nav-notification <?php echo $currentPage == 'thong_bao.php' ? 'active' : ''; ?>"><i class="fas fa-bell"></i><?php if ($notificationCount > 0): ?><span class="noti-badge"><?php echo $notificationCount; ?></span><?php endif; ?></a></li>
                <?php endif; ?>
                <li><a href="<?php echo $userType === 'admin' ? '../admin/dashboard.php' : 'customer_dashboard.php'; ?>" class="<?php echo $currentPage == 'customer_dashboard.php' ? 'active' : ''; ?>"><i class="fas fa-user"></i> <?php echo htmlspecialchars($userName); ?></a></li>
                <li><a href="../auth/logout.php?type=<?php echo $userType; ?>"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a></li>
            <?php else: ?>
                <li><a href="customer_login.php" class="<?php echo $currentPage == 'customer_login.php' ? 'active' : ''; ?>"><i class="fas fa-sign-in-alt"></i> Đăng nhập</a></li>
                <li><a href="customer_register.php" class="<?php echo $currentPage == 'customer_register.php' ? 'active' : ''; ?>"><i class="fas fa-user-plus"></i> Đăng ký</a></li>
            <?php endif; ?>
        </ul>
        <button class="mobile-menu-btn" onclick="toggleMobileMenu()"><i class="fas fa-bars"></i></button>
    </div>
</div>

<style>
.main-header { background: linear-gradient(135deg, #001f3f 0%, #003366 100%); padding: 12px 0; position: fixed; width: 100%; top: 0; z-index: 1000; box-shadow: 0 2px 10px rgba(0,0,0,0.2); }
.main-header .header-content { max-width: 1400px; margin: 0 auto; padding: 0 20px; display: flex; justify-content: space-between; align-items: center; }
.main-header .logo { display: flex; align-items: center; gap: 8px; font-size: 20px; font-weight: bold; color: #F5F5DC; text-decoration: none; white-space: nowrap; }
.main-header .logo:hover { color: #D4AF37; }
.main-header .nav-menu { display: flex; gap: 5px; list-style: none; margin: 0; padding: 0; flex-wrap: nowrap; align-items: center; }
.main-header .nav-menu li { white-space: nowrap; }
.main-header .nav-menu a { text-decoration: none; color: #F5F5DC; font-weight: 500; transition: all 0.3s; padding: 8px 10px; border-radius: 5px; font-size: 14px; display: flex; align-items: center; gap: 5px; }
.main-header .nav-menu a:hover, .main-header .nav-menu a.active { color: #D4AF37; background: rgba(255,255,255,0.1); }
.main-header .nav-menu .nav-notification { position: relative; }
.main-header .nav-menu .noti-badge { position: absolute; top: -5px; right: -5px; background: #dc3545; color: white; font-size: 10px; font-weight: bold; min-width: 18px; height: 18px; border-radius: 50%; display: flex; align-items: center; justify-content: center; animation: pulse 2s infinite; }
@keyframes pulse { 0%, 100% { transform: scale(1); } 50% { transform: scale(1.1); } }
.main-header .mobile-menu-btn { display: none; background: none; border: none; color: #F5F5DC; font-size: 24px; cursor: pointer; }
@media (max-width: 1100px) {
    .main-header .nav-menu { display: none; position: absolute; top: 100%; left: 0; right: 0; background: #001f3f; flex-direction: column; padding: 20px; gap: 10px; }
    .main-header .nav-menu.show { display: flex; }
    .main-header .nav-menu a { font-size: 16px; padding: 12px 15px; }
    .main-header .mobile-menu-btn { display: block; }
}
</style>
<script>
function toggleMobileMenu() { document.querySelector('.nav-menu').classList.toggle('show'); }
</script>
