<?php
session_start();
require_once __DIR__ . '/../config/db.php';

// Kiểm tra xem người dùng đã đăng nhập chưa
if (!isset($_SESSION['user_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Lấy thông tin người dùng từ database
try {
    $stmt = $conn->prepare("SELECT nv.*, cv.TenChucVu 
                            FROM nhan_vien nv 
                            LEFT JOIN chuc_vu cv ON nv.MaChucVu = cv.MaChucVu 
                            WHERE nv.MaNhanVien = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    if (!$user) {
        session_destroy();
        header("Location: index.php");
        exit();
    }
} catch(PDOException $e) {
    die("Lỗi truy vấn: " . $e->getMessage());
}

$message = isset($_SESSION['message']) ? $_SESSION['message'] : '';
unset($_SESSION['message']);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nhà hàng 3CE - Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #F5F5DC 0%, #EDE8D0 50%, #E8E4C9 100%);
            min-height: 100vh;
        }
        
        /* Header */
        .header {
            background: linear-gradient(135deg, #001f3f 0%, #003366 100%);
            padding: 15px 0;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
            border-bottom: 3px solid #D4AF37;
        }
        
        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 24px;
            font-weight: bold;
            color: #F5F5DC;
        }
        
        .user-menu {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #F5F5DC;
        }
        
        .user-name {
            font-weight: 600;
            color: #F5F5DC;
        }
        
        .logout-btn {
            padding: 8px 20px;
            background: #dc3545;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: all 0.3s;
            font-weight: 500;
        }
        
        .logout-btn:hover {
            background: #c82333;
            transform: translateY(-2px);
        }
        
        /* Main Content */
        .container {
            max-width: 1200px;
            margin: 50px auto;
            padding: 0 20px;
        }
        
        /* Alert Message */
        .alert {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .alert i {
            font-size: 20px;
        }
        
        /* Welcome Card */
        .welcome-card {
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            margin-bottom: 30px;
        }
        
        .welcome-card h1 {
            color: #001f3f;
            margin-bottom: 10px;
            font-size: 32px;
        }
        
        .welcome-card p {
            color: #003366;
            font-size: 16px;
        }
        
        /* User Profile Card */
        .profile-card {
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        
        .profile-header {
            display: flex;
            align-items: center;
            gap: 30px;
            margin-bottom: 30px;
            padding-bottom: 30px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid #001f3f;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .profile-info h2 {
            color: #001f3f;
            margin-bottom: 10px;
        }
        
        .profile-info p {
            color: #003366;
            font-size: 16px;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }
        
        .info-item {
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
            border-left: 4px solid #001f3f;
        }
        
        .info-item label {
            display: block;
            color: #666;
            font-size: 14px;
            margin-bottom: 5px;
            font-weight: 500;
        }
        
        .info-item .value {
            color: #001f3f;
            font-size: 16px;
            font-weight: 600;
        }
        
        .info-item i {
            margin-right: 8px;
            color: #001f3f;
        }
        
        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        
        .stat-card {
            background: linear-gradient(135deg, #001f3f 0%, #003366 100%);
            color: #F5F5DC;
            padding: 30px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 5px 20px rgba(0,0,0,0.3);
            border: 1px solid rgba(245, 245, 220, 0.3);
        }
        
        .stat-card i {
            font-size: 40px;
            margin-bottom: 15px;
            opacity: 0.9;
        }
        
        .stat-card h3 {
            font-size: 32px;
            margin-bottom: 5px;
        }
        
        .stat-card p {
            opacity: 0.9;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="header-content">
            <div class="logo">
                <i class="fas fa-utensils"></i>
                <span>Nhà hàng 3CE</span>
            </div>
            <div class="user-menu">
                <div class="user-info">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['HoTen']); ?>&background=8b6f47&color=fff" 
                         alt="Avatar" class="user-avatar">
                    <span class="user-name"><?php echo htmlspecialchars($user['HoTen']); ?></span>
                </div>
                <a href="../public/index.php" class="logout-btn" style="background: #28a745;">
                    <i class="fas fa-home"></i> Trang chủ
                </a>
                <a href="../auth/logout.php?type=admin" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Đăng xuất
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container">
        <?php if ($message): ?>
        <div class="alert">
            <i class="fas fa-check-circle"></i>
            <span><?php echo htmlspecialchars($message); ?></span>
        </div>
        <?php endif; ?>

        <div class="welcome-card">
            <h1><i class="fas fa-utensils"></i> Chào mừng, <?php echo htmlspecialchars($user['HoTen']); ?>!</h1>
            <p>Chức vụ: <strong><?php echo htmlspecialchars($user['TenChucVu'] ?? 'Nhân viên'); ?></strong></p>
        </div>

        <!-- Menu quản lý -->
        <div class="stats-grid" style="margin-bottom: 30px;">
            <a href="quan_ly_nhan_vien.php" class="stat-card" style="text-decoration: none; color: white;">
                <i class="fas fa-users"></i>
                <h3>Nhân viên</h3>
                <p>Quản lý nhân viên</p>
            </a>
            <a href="quan_ly_hoa_don.php" class="stat-card" style="text-decoration: none; color: white;">
                <i class="fas fa-file-invoice"></i>
                <h3>Hóa đơn</h3>
                <p>Quản lý hóa đơn</p>
            </a>
            <a href="quan_ly_doanh_thu.php" class="stat-card" style="text-decoration: none; color: white;">
                <i class="fas fa-chart-bar"></i>
                <h3>Doanh thu</h3>
                <p>Báo cáo doanh thu</p>
            </a>
            <a href="quan_ly_ban.php" class="stat-card" style="text-decoration: none; color: white;">
                <i class="fas fa-chair"></i>
                <h3>Bàn ăn</h3>
                <p>Quản lý bàn đặt</p>
            </a>
            <a href="quan_ly_mon_an.php" class="stat-card" style="text-decoration: none; color: white;">
                <i class="fas fa-utensils"></i>
                <h3>Món ăn</h3>
                <p>Quản lý thực đơn</p>
            </a>
            <a href="quan_ly_dat_ban.php" class="stat-card" style="text-decoration: none; color: white;">
                <i class="fas fa-calendar-check"></i>
                <h3>Đặt bàn</h3>
                <p>Quản lý đặt bàn</p>
            </a>
        </div>

        <div class="profile-card">
            <div class="profile-header">
                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['HoTen']); ?>&background=8b6f47&color=fff&size=120" 
                     alt="Avatar" class="profile-avatar">
                <div class="profile-info">
                    <h2><?php echo htmlspecialchars($user['HoTen']); ?></h2>
                    <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars($user['SoDienThoai'] ?? 'Chưa cập nhật'); ?></p>
                </div>
            </div>

            <div class="info-grid">
                <div class="info-item">
                    <label><i class="fas fa-id-badge"></i> Mã nhân viên</label>
                    <div class="value">#<?php echo htmlspecialchars($user['MaNhanVien']); ?></div>
                </div>
                
                <div class="info-item">
                    <label><i class="fas fa-user"></i> Tên nhân viên</label>
                    <div class="value"><?php echo htmlspecialchars($user['HoTen']); ?></div>
                </div>
                
                <div class="info-item">
                    <label><i class="fas fa-phone"></i> Số điện thoại</label>
                    <div class="value"><?php echo htmlspecialchars($user['SoDienThoai'] ?? 'Chưa cập nhật'); ?></div>
                </div>
                
                <div class="info-item">
                    <label><i class="fas fa-briefcase"></i> Chức vụ</label>
                    <div class="value"><?php echo htmlspecialchars($user['TenChucVu'] ?? 'Nhân viên'); ?></div>
                </div>
                
                <div class="info-item">
                    <label><i class="fas fa-money-bill-wave"></i> Lương</label>
                    <div class="value"><?php echo number_format($user['Luong'] ?? 0, 0, ',', '.'); ?>đ</div>
                </div>
                
                <div class="info-item">
                    <label><i class="fas fa-calendar-alt"></i> Ngày vào làm</label>
                    <div class="value"><?php echo date('d/m/Y', strtotime($user['NgayVaoLam'] ?? date('Y-m-d'))); ?></div>
                </div>
            </div>
        </div>

        <?php
        // Lấy thống kê từ database
        try {
            // Đếm số hóa đơn hôm nay
            $stmt = $conn->query("SELECT COUNT(*) as total FROM hoa_don WHERE DATE(ThoiGianVao) = CURDATE()");
            $hoaDonHomNay = $stmt->fetch()['total'];
            
            // Đếm số đơn chờ xử lý
            $stmt = $conn->query("SELECT COUNT(*) as total FROM hoa_don WHERE TrangThai = 'Chưa thanh toán'");
            $donChoXuLy = $stmt->fetch()['total'];
            
            // Đếm số món ăn
            $stmt = $conn->query("SELECT COUNT(*) as total FROM mon_an");
            $tongMonAn = $stmt->fetch()['total'];
            
            // Tính doanh thu hôm nay (chỉ tính đơn đã thanh toán)
            $stmt = $conn->query("SELECT SUM(TongTien) as total FROM hoa_don WHERE DATE(ThoiGianVao) = CURDATE() AND TrangThai = 'Đã thanh toán'");
            $doanhThuHomNay = $stmt->fetch()['total'] ?? 0;
            
            // Lấy 5 đơn hàng mới nhất chờ xử lý
            $stmt = $conn->query("SELECT hd.*, kh.HoTen as TenKH 
                                  FROM hoa_don hd 
                                  LEFT JOIN khach_hang kh ON hd.MaKhachHang = kh.MaKhachHang 
                                  WHERE hd.TrangThai = 'Chưa thanh toán' 
                                  ORDER BY hd.ThoiGianVao DESC 
                                  LIMIT 5");
            $donMoi = $stmt->fetchAll();
        } catch(PDOException $e) {
            $hoaDonHomNay = 0;
            $donChoXuLy = 0;
            $tongMonAn = 0;
            $doanhThuHomNay = 0;
            $donMoi = [];
        }
        ?>
        <div class="stats-grid">
            <div class="stat-card">
                <i class="fas fa-clipboard-list"></i>
                <h3><?php echo $hoaDonHomNay; ?></h3>
                <p>Hóa đơn hôm nay</p>
            </div>
            <div class="stat-card" style="<?php echo $donChoXuLy > 0 ? 'background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);' : ''; ?>">
                <i class="fas fa-clock"></i>
                <h3><?php echo $donChoXuLy; ?></h3>
                <p>Đơn chờ xử lý</p>
            </div>
            <div class="stat-card">
                <i class="fas fa-utensils"></i>
                <h3><?php echo $tongMonAn; ?></h3>
                <p>Tổng món ăn</p>
            </div>
            <div class="stat-card">
                <i class="fas fa-chart-line"></i>
                <h3><?php echo number_format($doanhThuHomNay, 0, ',', '.'); ?>đ</h3>
                <p>Doanh thu hôm nay</p>
            </div>
        </div>

        <?php if (!empty($donMoi)): ?>
        <div class="profile-card" style="margin-top: 30px;">
            <h2 style="color: #001f3f; margin-bottom: 20px;">
                <i class="fas fa-bell"></i> Đơn hàng mới cần xử lý
                <a href="quan_ly_hoa_don.php?filter=pending" style="float: right; font-size: 14px; color: #007bff; text-decoration: none;">
                    Xem tất cả <i class="fas fa-arrow-right"></i>
                </a>
            </h2>
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f8f9fa;">
                        <th style="padding: 12px; text-align: left; border-bottom: 2px solid #001f3f;">Mã HĐ</th>
                        <th style="padding: 12px; text-align: left; border-bottom: 2px solid #001f3f;">Khách hàng</th>
                        <th style="padding: 12px; text-align: left; border-bottom: 2px solid #001f3f;">Thời gian</th>
                        <th style="padding: 12px; text-align: right; border-bottom: 2px solid #001f3f;">Tổng tiền</th>
                        <th style="padding: 12px; text-align: center; border-bottom: 2px solid #001f3f;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($donMoi as $don): ?>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 12px;"><strong>#<?php echo $don['MaHoaDon']; ?></strong></td>
                        <td style="padding: 12px;"><?php echo htmlspecialchars($don['TenKH'] ?? 'Khách vãng lai'); ?></td>
                        <td style="padding: 12px;"><?php echo date('d/m/Y H:i', strtotime($don['ThoiGianVao'])); ?></td>
                        <td style="padding: 12px; text-align: right; font-weight: bold; color: #003366;">
                            <?php echo number_format($don['TongTien'], 0, ',', '.'); ?>đ
                        </td>
                        <td style="padding: 12px; text-align: center;">
                            <a href="quan_ly_hoa_don.php?filter=pending" style="padding: 6px 15px; background: #001f3f; color: #F5F5DC; border-radius: 5px; text-decoration: none; font-size: 13px;">
                                <i class="fas fa-eye"></i> Xử lý
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
