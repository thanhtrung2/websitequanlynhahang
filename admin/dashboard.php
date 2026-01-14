<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Lấy thông tin người dùng
try {
    $stmt = $conn->prepare("SELECT nv.*, cv.TenChucVu FROM nhan_vien nv LEFT JOIN chuc_vu cv ON nv.MaChucVu = cv.MaChucVu WHERE nv.MaNhanVien = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    if (!$user) { session_destroy(); header("Location: admin_login.php"); exit(); }
    
    // Thống kê
    $stmt = $conn->query("SELECT COUNT(*) as total FROM hoa_don WHERE DATE(ThoiGianVao) = CURDATE()");
    $hoaDonHomNay = $stmt->fetch()['total'];
    
    $stmt = $conn->query("SELECT COUNT(*) as total FROM hoa_don WHERE TrangThai = 'Chưa thanh toán'");
    $donChoXuLy = $stmt->fetch()['total'];
    
    $stmt = $conn->query("SELECT COUNT(*) as total FROM mon_an WHERE TrangThai = 'Còn hàng'");
    $tongMonAn = $stmt->fetch()['total'];
    
    $stmt = $conn->query("SELECT COUNT(*) as total FROM ban_an WHERE TrangThai = 'Trống'");
    $banTrong = $stmt->fetch()['total'];
    
    $stmt = $conn->query("SELECT COALESCE(SUM(ThanhTien), 0) as total FROM hoa_don WHERE DATE(ThoiGianVao) = CURDATE() AND TrangThai = 'Đã thanh toán'");
    $doanhThuHomNay = $stmt->fetch()['total'];
    
    $stmt = $conn->query("SELECT COUNT(*) as total FROM dat_ban WHERE TrangThai = 'Chờ xác nhận'");
    $datBanCho = $stmt->fetch()['total'];
    
    // Liên hệ chưa xử lý
    $lienHeChuaXuLy = 0;
    try {
        $stmt = $conn->query("SELECT COUNT(*) as total FROM lien_he WHERE TrangThai = 'Chưa xử lý'");
        $lienHeChuaXuLy = $stmt->fetch()['total'];
    } catch(PDOException $e) {
        $lienHeChuaXuLy = 0;
    }
    
    // Đơn hàng mới
    $stmt = $conn->query("SELECT hd.*, kh.HoTen as TenKH FROM hoa_don hd LEFT JOIN khach_hang kh ON hd.MaKhachHang = kh.MaKhachHang WHERE hd.TrangThai = 'Chưa thanh toán' ORDER BY hd.ThoiGianVao DESC LIMIT 5");
    $donMoi = $stmt->fetchAll();
} catch(PDOException $e) {
    $hoaDonHomNay = $donChoXuLy = $tongMonAn = $banTrong = $doanhThuHomNay = $datBanCho = $lienHeChuaXuLy = 0;
    $donMoi = [];
}

$message = $_SESSION['message'] ?? '';
unset($_SESSION['message']);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Nhà hàng 3CE</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            display: block;
        }
        
        .dashboard-layout {
            display: flex;
            min-height: 100vh;
        }
        
        /* Sidebar */
        .sidebar {
            width: 280px;
            background: linear-gradient(180deg, #001f3f 0%, #003366 100%);
            min-height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            padding: 0;
            box-shadow: 4px 0 25px rgba(0,0,0,0.15);
            z-index: 100;
        }
        
        .sidebar-header {
            padding: 25px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            text-align: center;
        }
        
        .sidebar-logo {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            color: #F5F5DC;
            font-size: 22px;
            font-weight: 700;
        }
        
        .sidebar-logo i {
            font-size: 28px;
            color: #D4AF37;
        }
        
        .sidebar-user {
            padding: 20px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .sidebar-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            border: 3px solid #D4AF37;
        }
        
        .sidebar-user-info h4 {
            color: #F5F5DC;
            font-size: 15px;
            margin-bottom: 3px;
        }
        
        .sidebar-user-info span {
            color: #D4AF37;
            font-size: 12px;
        }
        
        .sidebar-menu {
            padding: 15px 0;
            list-style: none;
        }
        
        .sidebar-menu li {
            margin: 4px 12px;
        }
        
        .sidebar-menu li a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 20px;
            color: rgba(245,245,220,0.8);
            text-decoration: none;
            border-radius: 10px;
            transition: all 0.3s;
            font-weight: 500;
        }
        
        .sidebar-menu li a i {
            width: 22px;
            text-align: center;
            font-size: 18px;
        }
        
        .sidebar-menu li a:hover {
            background: rgba(255,255,255,0.1);
            color: #F5F5DC;
            transform: translateX(5px);
        }
        
        .sidebar-menu li a.active {
            background: linear-gradient(135deg, #D4AF37 0%, #f7d774 100%);
            color: #001f3f;
            box-shadow: 0 4px 15px rgba(212,175,55,0.3);
        }
        
        .sidebar-menu li a.active i {
            color: #001f3f;
        }
        
        .sidebar-divider {
            height: 1px;
            background: rgba(255,255,255,0.1);
            margin: 15px 20px;
        }
        
        .sidebar-title {
            padding: 10px 25px;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: rgba(245,245,220,0.5);
        }
        
        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 280px;
            padding: 30px;
            background: linear-gradient(135deg, #F5F5DC 0%, #EDE8D0 100%);
            min-height: 100vh;
        }
        
        /* Top Bar */
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding: 20px 25px;
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }
        
        .top-bar h1 {
            font-size: 26px;
            color: #001f3f;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .top-bar h1 i {
            color: #D4AF37;
        }
        
        .top-bar-actions {
            display: flex;
            gap: 12px;
        }
        
        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 25px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            display: flex;
            align-items: center;
            gap: 20px;
            transition: all 0.3s;
            position: relative;
            overflow: hidden;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
        }
        
        .stat-card.primary::before { background: linear-gradient(180deg, #001f3f, #003366); }
        .stat-card.success::before { background: linear-gradient(180deg, #28a745, #20c997); }
        .stat-card.warning::before { background: linear-gradient(180deg, #ffc107, #fd7e14); }
        .stat-card.danger::before { background: linear-gradient(180deg, #dc3545, #e74c3c); }
        .stat-card.gold::before { background: linear-gradient(180deg, #D4AF37, #f7d774); }
        .stat-card.info::before { background: linear-gradient(180deg, #17a2b8, #6f42c1); }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
        }
        
        .stat-icon {
            width: 65px;
            height: 65px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 26px;
        }
        
        .stat-card.primary .stat-icon { background: linear-gradient(135deg, #001f3f, #003366); color: #F5F5DC; }
        .stat-card.success .stat-icon { background: linear-gradient(135deg, #28a745, #20c997); color: white; }
        .stat-card.warning .stat-icon { background: linear-gradient(135deg, #ffc107, #fd7e14); color: white; }
        .stat-card.danger .stat-icon { background: linear-gradient(135deg, #dc3545, #e74c3c); color: white; }
        .stat-card.gold .stat-icon { background: linear-gradient(135deg, #D4AF37, #f7d774); color: #001f3f; }
        .stat-card.info .stat-icon { background: linear-gradient(135deg, #17a2b8, #6f42c1); color: white; }
        
        .stat-info h3 {
            font-size: 28px;
            color: #001f3f;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .stat-info p {
            color: #666;
            font-size: 14px;
        }
        
        /* Quick Actions */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .quick-action {
            background: white;
            border-radius: 16px;
            padding: 25px;
            text-align: center;
            text-decoration: none;
            color: #001f3f;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            transition: all 0.3s;
            border: 2px solid transparent;
        }
        
        .quick-action:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
            border-color: #D4AF37;
        }
        
        .quick-action i {
            font-size: 36px;
            color: #D4AF37;
            margin-bottom: 15px;
            display: block;
        }
        
        .quick-action h4 {
            font-size: 16px;
            margin-bottom: 5px;
        }
        
        .quick-action p {
            font-size: 13px;
            color: #666;
        }
        
        /* Recent Orders */
        .card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            overflow: hidden;
        }
        
        .card-header {
            padding: 20px 25px;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .card-header h3 {
            font-size: 18px;
            color: #001f3f;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .card-header h3 i {
            color: #D4AF37;
        }
        
        .card-body {
            padding: 0;
        }
        
        .order-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .order-table th {
            background: #f8f9fa;
            padding: 14px 20px;
            text-align: left;
            font-weight: 600;
            color: #001f3f;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .order-table td {
            padding: 16px 20px;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .order-table tr:last-child td {
            border-bottom: none;
        }
        
        .order-table tr:hover {
            background: #fafafa;
        }
        
        .badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .badge-warning {
            background: #fff3cd;
            color: #856404;
        }
        
        .badge-success {
            background: #d4edda;
            color: #155724;
        }
        
        .badge-danger {
            background: #f8d7da;
            color: #721c24;
        }
        
        /* Alert */
        .alert {
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
            animation: slideIn 0.3s ease;
        }
        
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .alert-success {
            background: linear-gradient(135deg, #d4edda, #c3e6cb);
            color: #155724;
            border-left: 4px solid #28a745;
        }
        
        /* Responsive */
        @media (max-width: 1024px) {
            .sidebar {
                width: 80px;
            }
            
            .sidebar-header, .sidebar-user, .sidebar-title {
                display: none;
            }
            
            .sidebar-menu li a span {
                display: none;
            }
            
            .sidebar-menu li a {
                justify-content: center;
                padding: 16px;
            }
            
            .main-content {
                margin-left: 80px;
            }
        }
        
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .main-content {
                margin-left: 0;
                padding: 20px;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .quick-actions {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body>
    <div class="dashboard-layout">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <div class="sidebar-logo">
                    <i class="fas fa-utensils"></i>
                    <span>Nhà hàng 3CE</span>
                </div>
            </div>
            
            <div class="sidebar-user">
                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['HoTen']); ?>&background=D4AF37&color=001f3f&size=50" 
                     alt="Avatar" class="sidebar-avatar">
                <div class="sidebar-user-info">
                    <h4><?php echo htmlspecialchars($user['HoTen']); ?></h4>
                    <span><?php echo htmlspecialchars($user['TenChucVu'] ?? 'Nhân viên'); ?></span>
                </div>
            </div>
            
            <ul class="sidebar-menu">
                <li><a href="dashboard.php" class="active"><i class="fas fa-home"></i> <span>Dashboard</span></a></li>
                
                <div class="sidebar-divider"></div>
                <div class="sidebar-title">Quản lý</div>
                
                <li><a href="quan_ly_mon_an.php"><i class="fas fa-utensils"></i> <span>Món ăn</span></a></li>
                <li><a href="quan_ly_ban.php"><i class="fas fa-chair"></i> <span>Bàn ăn</span></a></li>
                <li><a href="quan_ly_dat_ban.php"><i class="fas fa-calendar-check"></i> <span>Đặt bàn</span></a></li>
                <li><a href="quan_ly_hoa_don.php"><i class="fas fa-file-invoice"></i> <span>Hóa đơn</span></a></li>
                <li><a href="quan_ly_lien_he.php"><i class="fas fa-envelope"></i> <span>Liên hệ</span></a></li>
                
                <div class="sidebar-divider"></div>
                <div class="sidebar-title">Báo cáo</div>
                
                <li><a href="quan_ly_doanh_thu.php"><i class="fas fa-chart-line"></i> <span>Doanh thu</span></a></li>
                <li><a href="quan_ly_nhan_vien.php"><i class="fas fa-users"></i> <span>Nhân viên</span></a></li>
                
                <div class="sidebar-divider"></div>
                
                <li><a href="../public/index.php"><i class="fas fa-globe"></i> <span>Trang chủ</span></a></li>
                <li><a href="../auth/logout.php?type=admin" style="color: #ff6b6b;"><i class="fas fa-sign-out-alt"></i> <span>Đăng xuất</span></a></li>
            </ul>
        </aside>
        
        <!-- Main Content -->
        <main class="main-content">
            <!-- Top Bar -->
            <div class="top-bar">
                <h1><i class="fas fa-tachometer-alt"></i> Dashboard</h1>
                <div class="top-bar-actions">
                    <span style="color: #666; font-size: 14px;">
                        <i class="fas fa-calendar"></i> <?php echo date('d/m/Y'); ?>
                    </span>
                </div>
            </div>
            
            <?php if ($message): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <span><?php echo htmlspecialchars($message); ?></span>
            </div>
            <?php endif; ?>
            
            <!-- Stats -->
            <div class="stats-grid">
                <div class="stat-card gold">
                    <div class="stat-icon"><i class="fas fa-coins"></i></div>
                    <div class="stat-info">
                        <h3><?php echo number_format($doanhThuHomNay, 0, ',', '.'); ?>đ</h3>
                        <p>Doanh thu hôm nay</p>
                    </div>
                </div>
                
                <div class="stat-card primary">
                    <div class="stat-icon"><i class="fas fa-receipt"></i></div>
                    <div class="stat-info">
                        <h3><?php echo $hoaDonHomNay; ?></h3>
                        <p>Hóa đơn hôm nay</p>
                    </div>
                </div>
                
                <div class="stat-card <?php echo $donChoXuLy > 0 ? 'danger' : 'success'; ?>">
                    <div class="stat-icon"><i class="fas fa-clock"></i></div>
                    <div class="stat-info">
                        <h3><?php echo $donChoXuLy; ?></h3>
                        <p>Đơn chờ xử lý</p>
                    </div>
                </div>
                
                <div class="stat-card info">
                    <div class="stat-icon"><i class="fas fa-chair"></i></div>
                    <div class="stat-info">
                        <h3><?php echo $banTrong; ?></h3>
                        <p>Bàn trống</p>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="quick-actions">
                <a href="quan_ly_mon_an.php" class="quick-action">
                    <i class="fas fa-utensils"></i>
                    <h4>Quản lý món ăn</h4>
                    <p><?php echo $tongMonAn; ?> món</p>
                </a>
                
                <a href="quan_ly_ban.php" class="quick-action">
                    <i class="fas fa-chair"></i>
                    <h4>Quản lý bàn</h4>
                    <p><?php echo $banTrong; ?> bàn trống</p>
                </a>
                
                <a href="quan_ly_dat_ban.php" class="quick-action">
                    <i class="fas fa-calendar-check"></i>
                    <h4>Đặt bàn</h4>
                    <p><?php echo $datBanCho; ?> chờ xác nhận</p>
                </a>
                
                <a href="quan_ly_hoa_don.php" class="quick-action">
                    <i class="fas fa-file-invoice"></i>
                    <h4>Hóa đơn</h4>
                    <p><?php echo $donChoXuLy; ?> chờ xử lý</p>
                </a>
                
                <a href="quan_ly_lien_he.php" class="quick-action" style="<?php echo $lienHeChuaXuLy > 0 ? 'border-color: #dc3545;' : ''; ?>">
                    <i class="fas fa-envelope" style="<?php echo $lienHeChuaXuLy > 0 ? 'color: #dc3545;' : ''; ?>"></i>
                    <h4>Liên hệ</h4>
                    <p><?php echo $lienHeChuaXuLy; ?> chưa xử lý</p>
                </a>
                
                <a href="quan_ly_doanh_thu.php" class="quick-action">
                    <i class="fas fa-chart-line"></i>
                    <h4>Báo cáo</h4>
                    <p>Xem doanh thu</p>
                </a>
                
                <a href="quan_ly_nhan_vien.php" class="quick-action">
                    <i class="fas fa-users"></i>
                    <h4>Nhân viên</h4>
                    <p>Quản lý nhân sự</p>
                </a>
            </div>
            
            <!-- Recent Orders -->
            <?php if (!empty($donMoi)): ?>
            <div class="card">
                <div class="card-header">
                    <h3><i class="fas fa-bell"></i> Đơn hàng mới cần xử lý</h3>
                    <a href="quan_ly_hoa_don.php?filter=pending" class="btn btn-sm btn-primary">
                        Xem tất cả <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
                <div class="card-body">
                    <table class="order-table">
                        <thead>
                            <tr>
                                <th>Mã HĐ</th>
                                <th>Khách hàng</th>
                                <th>Thời gian</th>
                                <th>Tổng tiền</th>
                                <th>Trạng thái</th>
                                <th>Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($donMoi as $don): ?>
                            <tr>
                                <td><strong>#<?php echo $don['MaHoaDon']; ?></strong></td>
                                <td><?php echo htmlspecialchars($don['TenKH'] ?? 'Khách vãng lai'); ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($don['ThoiGianVao'])); ?></td>
                                <td style="font-weight: 600; color: #003366;">
                                    <?php echo number_format($don['TongTien'], 0, ',', '.'); ?>đ
                                </td>
                                <td><span class="badge badge-warning"><i class="fas fa-clock"></i> Chờ xử lý</span></td>
                                <td>
                                    <a href="quan_ly_hoa_don.php?id=<?php echo $don['MaHoaDon']; ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye"></i> Xử lý
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php else: ?>
            <div class="card">
                <div class="card-body" style="padding: 40px; text-align: center;">
                    <i class="fas fa-check-circle" style="font-size: 48px; color: #28a745; margin-bottom: 15px;"></i>
                    <h3 style="color: #001f3f; margin-bottom: 10px;">Tuyệt vời!</h3>
                    <p style="color: #666;">Không có đơn hàng nào đang chờ xử lý.</p>
                </div>
            </div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>
