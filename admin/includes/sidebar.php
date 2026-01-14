<?php
// Lấy tên file hiện tại để xác định menu active
$currentPage = basename($_SERVER['PHP_SELF']);

// Lấy thông tin user nếu chưa có
if (!isset($user) && isset($_SESSION['user_id'])) {
    try {
        $stmtUser = $conn->prepare("SELECT nv.*, cv.TenChucVu FROM nhan_vien nv LEFT JOIN chuc_vu cv ON nv.MaChucVu = cv.MaChucVu WHERE nv.MaNhanVien = ?");
        $stmtUser->execute([$_SESSION['user_id']]);
        $user = $stmtUser->fetch();
    } catch(PDOException $e) {
        $user = ['HoTen' => 'Admin', 'TenChucVu' => 'Quản lý'];
    }
}
?>
<aside class="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-logo">
            <i class="fas fa-utensils"></i>
            <span>Nhà hàng 3CE</span>
        </div>
    </div>
    
    <div class="sidebar-user">
        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['HoTen'] ?? 'Admin'); ?>&background=D4AF37&color=001f3f&size=50" 
             alt="Avatar" class="sidebar-avatar">
        <div class="sidebar-user-info">
            <h4><?php echo htmlspecialchars($user['HoTen'] ?? 'Admin'); ?></h4>
            <span><?php echo htmlspecialchars($user['TenChucVu'] ?? 'Nhân viên'); ?></span>
        </div>
    </div>
    
    <ul class="sidebar-menu">
        <li><a href="dashboard.php" class="<?php echo $currentPage === 'dashboard.php' ? 'active' : ''; ?>"><i class="fas fa-home"></i> <span>Dashboard</span></a></li>
        
        <div class="sidebar-divider"></div>
        <div class="sidebar-title">Quản lý</div>
        
        <li><a href="quan_ly_mon_an.php" class="<?php echo $currentPage === 'quan_ly_mon_an.php' ? 'active' : ''; ?>"><i class="fas fa-utensils"></i> <span>Món ăn</span></a></li>
        <li><a href="quan_ly_ban.php" class="<?php echo $currentPage === 'quan_ly_ban.php' ? 'active' : ''; ?>"><i class="fas fa-chair"></i> <span>Bàn ăn</span></a></li>
        <li><a href="quan_ly_dat_ban.php" class="<?php echo $currentPage === 'quan_ly_dat_ban.php' ? 'active' : ''; ?>"><i class="fas fa-calendar-check"></i> <span>Đặt bàn</span></a></li>
        <li><a href="quan_ly_hoa_don.php" class="<?php echo $currentPage === 'quan_ly_hoa_don.php' ? 'active' : ''; ?>"><i class="fas fa-file-invoice"></i> <span>Hóa đơn</span></a></li>
        <li><a href="quan_ly_lien_he.php" class="<?php echo $currentPage === 'quan_ly_lien_he.php' ? 'active' : ''; ?>"><i class="fas fa-envelope"></i> <span>Liên hệ</span></a></li>
        
        <div class="sidebar-divider"></div>
        <div class="sidebar-title">Báo cáo</div>
        
        <li><a href="quan_ly_doanh_thu.php" class="<?php echo $currentPage === 'quan_ly_doanh_thu.php' ? 'active' : ''; ?>"><i class="fas fa-chart-line"></i> <span>Doanh thu</span></a></li>
        <li><a href="quan_ly_nhan_vien.php" class="<?php echo $currentPage === 'quan_ly_nhan_vien.php' ? 'active' : ''; ?>"><i class="fas fa-users"></i> <span>Nhân viên</span></a></li>
        
        <div class="sidebar-divider"></div>
        
        <li><a href="../public/index.php"><i class="fas fa-globe"></i> <span>Trang chủ</span></a></li>
        <li><a href="../auth/logout.php?type=admin" style="color: #ff6b6b;"><i class="fas fa-sign-out-alt"></i> <span>Đăng xuất</span></a></li>
    </ul>
</aside>
