<?php
session_start();
require_once __DIR__ . '/../config/db.php';

// Kiểm tra đăng nhập khách hàng
if (!isset($_SESSION['customer_id'])) {
    header("Location: customer_login.php");
    exit();
}

// Lấy thông tin khách hàng
try {
    $stmt = $conn->prepare("SELECT * FROM khach_hang WHERE MaKhachHang = ?");
    $stmt->execute([$_SESSION['customer_id']]);
    $customer = $stmt->fetch();
    
    if (!$customer) {
        session_destroy();
        header("Location: customer_login.php");
        exit();
    }
    
    // Đếm số đơn hàng
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM hoa_don WHERE MaKhachHang = ?");
    $stmt->execute([$_SESSION['customer_id']]);
    $totalOrders = $stmt->fetch()['total'];
    
    // Đếm đơn chờ xử lý
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM hoa_don WHERE MaKhachHang = ? AND TrangThai = 'Chưa thanh toán'");
    $stmt->execute([$_SESSION['customer_id']]);
    $pendingOrders = $stmt->fetch()['total'];
    
    // Tổng chi tiêu
    $stmt = $conn->prepare("SELECT COALESCE(SUM(ThanhTien), 0) as total FROM hoa_don WHERE MaKhachHang = ? AND TrangThai = 'Đã thanh toán'");
    $stmt->execute([$_SESSION['customer_id']]);
    $totalSpent = $stmt->fetch()['total'];
    
} catch(PDOException $e) {
    die("Lỗi: " . $e->getMessage());
}

$message = $_SESSION['message'] ?? '';
unset($_SESSION['message']);

// Đếm giỏ hàng
$cartCount = 0;
if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cartCount += $item['SoLuong'];
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nhà hàng 3CE - Tài khoản của tôi</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            background: linear-gradient(135deg, #F5F5DC 0%, #EDE8D0 50%, #E8E4C9 100%);
            padding-top: 70px;
        }
        
        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px 20px;
        }
        
        /* Welcome Section */
        .welcome-section {
            background: linear-gradient(135deg, #001f3f 0%, #003366 100%);
            border-radius: 24px;
            padding: 40px;
            margin-bottom: 30px;
            color: #F5F5DC;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(0,31,63,0.3);
        }
        
        .welcome-section::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(212,175,55,0.2) 0%, transparent 70%);
            border-radius: 50%;
        }
        
        .welcome-content {
            position: relative;
            z-index: 1;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 30px;
        }
        
        .welcome-text h1 {
            font-size: 32px;
            margin-bottom: 10px;
        }
        
        .welcome-text h1 span {
            color: #D4AF37;
        }
        
        .welcome-text p {
            opacity: 0.9;
            font-size: 16px;
        }
        
        .welcome-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            border: 4px solid #D4AF37;
            box-shadow: 0 5px 20px rgba(0,0,0,0.3);
        }
        
        /* Stats Cards */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 25px;
            display: flex;
            align-items: center;
            gap: 18px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            transition: all 0.3s;
            border-left: 4px solid #D4AF37;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
        }
        
        .stat-icon {
            width: 55px;
            height: 55px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }
        
        .stat-icon.gold { background: linear-gradient(135deg, #D4AF37, #f7d774); color: #001f3f; }
        .stat-icon.blue { background: linear-gradient(135deg, #001f3f, #003366); color: #F5F5DC; }
        .stat-icon.green { background: linear-gradient(135deg, #28a745, #20c997); color: white; }
        .stat-icon.orange { background: linear-gradient(135deg, #fd7e14, #ffc107); color: white; }
        
        .stat-info h3 {
            font-size: 24px;
            color: #001f3f;
            font-weight: 700;
        }
        
        .stat-info p {
            color: #666;
            font-size: 13px;
        }
        
        /* Menu Grid */
        .menu-section h2 {
            font-size: 22px;
            color: #001f3f;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .menu-section h2 i {
            color: #D4AF37;
        }
        
        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
        }
        
        .menu-card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            text-align: center;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            transition: all 0.3s;
            text-decoration: none;
            color: inherit;
            display: block;
            position: relative;
            overflow: hidden;
            border: 2px solid transparent;
        }
        
        .menu-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, #D4AF37, #f7d774);
            transform: scaleX(0);
            transition: transform 0.3s;
        }
        
        .menu-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 50px rgba(0,31,63,0.2);
            border-color: #D4AF37;
        }
        
        .menu-card:hover::before {
            transform: scaleX(1);
        }
        
        .menu-card-icon {
            width: 80px;
            height: 80px;
            border-radius: 20px;
            background: linear-gradient(135deg, #001f3f 0%, #003366 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            transition: all 0.3s;
        }
        
        .menu-card:hover .menu-card-icon {
            background: linear-gradient(135deg, #D4AF37 0%, #f7d774 100%);
            transform: scale(1.1) rotate(5deg);
        }
        
        .menu-card-icon i {
            font-size: 32px;
            color: #F5F5DC;
            transition: all 0.3s;
        }
        
        .menu-card:hover .menu-card-icon i {
            color: #001f3f;
        }
        
        .menu-card h3 {
            font-size: 18px;
            color: #001f3f;
            margin-bottom: 10px;
        }
        
        .menu-card p {
            color: #666;
            font-size: 14px;
            line-height: 1.5;
        }
        
        .menu-card .badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: linear-gradient(135deg, #dc3545, #e74c3c);
            color: white;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: 700;
            box-shadow: 0 3px 10px rgba(220,53,69,0.4);
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }
        
        /* Points Card */
        .points-card {
            background: linear-gradient(135deg, #D4AF37 0%, #f7d774 100%);
            border-radius: 20px;
            padding: 30px;
            margin-top: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
            box-shadow: 0 10px 40px rgba(212,175,55,0.3);
        }
        
        .points-info h3 {
            color: #001f3f;
            font-size: 20px;
            margin-bottom: 5px;
        }
        
        .points-info p {
            color: #333;
            font-size: 14px;
        }
        
        .points-value {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .points-value i {
            font-size: 40px;
            color: #001f3f;
        }
        
        .points-value span {
            font-size: 36px;
            font-weight: 700;
            color: #001f3f;
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
        @media (max-width: 768px) {
            .welcome-section {
                padding: 30px 20px;
            }
            
            .welcome-text h1 {
                font-size: 24px;
            }
            
            .welcome-avatar {
                width: 70px;
                height: 70px;
            }
            
            .stats-row {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .menu-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="dashboard-container">
        <?php if ($message): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <span><?php echo htmlspecialchars($message); ?></span>
        </div>
        <?php endif; ?>
        
        <!-- Welcome Section -->
        <div class="welcome-section">
            <div class="welcome-content">
                <div class="welcome-text">
                    <h1>Xin chào, <span><?php echo htmlspecialchars($customer['HoTen']); ?></span>!</h1>
                    <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars($customer['SoDienThoai']); ?> &nbsp;|&nbsp; <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($customer['Email'] ?? 'Chưa cập nhật'); ?></p>
                </div>
                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($customer['HoTen']); ?>&background=D4AF37&color=001f3f&size=100&bold=true" 
                     alt="Avatar" class="welcome-avatar">
            </div>
        </div>
        
        <!-- Stats -->
        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-icon gold"><i class="fas fa-star"></i></div>
                <div class="stat-info">
                    <h3><?php echo number_format($customer['DiemTichLuy'] ?? 0); ?></h3>
                    <p>Điểm tích lũy</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon blue"><i class="fas fa-receipt"></i></div>
                <div class="stat-info">
                    <h3><?php echo $totalOrders; ?></h3>
                    <p>Tổng đơn hàng</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon orange"><i class="fas fa-clock"></i></div>
                <div class="stat-info">
                    <h3><?php echo $pendingOrders; ?></h3>
                    <p>Đơn chờ xử lý</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon green"><i class="fas fa-wallet"></i></div>
                <div class="stat-info">
                    <h3><?php echo number_format($totalSpent, 0, ',', '.'); ?>đ</h3>
                    <p>Tổng chi tiêu</p>
                </div>
            </div>
        </div>
        
        <!-- Menu Section -->
        <div class="menu-section">
            <h2><i class="fas fa-th-large"></i> Dịch vụ của bạn</h2>
            
            <div class="menu-grid">
                <a href="index.php#menu" class="menu-card">
                    <div class="menu-card-icon">
                        <i class="fas fa-utensils"></i>
                    </div>
                    <h3>Xem thực đơn</h3>
                    <p>Khám phá các món ăn ngon và đặt món yêu thích của bạn</p>
                </a>
                
                <a href="gio_hang.php" class="menu-card">
                    <?php if ($cartCount > 0): ?>
                    <span class="badge"><?php echo $cartCount; ?></span>
                    <?php endif; ?>
                    <div class="menu-card-icon">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <h3>Giỏ hàng</h3>
                    <p>Xem và quản lý các món ăn bạn đã chọn</p>
                </a>
                
                <a href="dat_ban.php" class="menu-card">
                    <div class="menu-card-icon">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <h3>Đặt bàn</h3>
                    <p>Đặt bàn trước để đảm bảo có chỗ ngồi tốt nhất</p>
                </a>
                
                <a href="thanh_toan.php" class="menu-card">
                    <div class="menu-card-icon">
                        <i class="fas fa-credit-card"></i>
                    </div>
                    <h3>Thanh toán</h3>
                    <p>Thanh toán nhanh chóng và an toàn</p>
                </a>
                
                <a href="lich_su_don_hang.php" class="menu-card">
                    <div class="menu-card-icon">
                        <i class="fas fa-history"></i>
                    </div>
                    <h3>Lịch sử đơn hàng</h3>
                    <p>Xem lại các đơn hàng bạn đã đặt trước đây</p>
                </a>
                
                <a href="customer_profile.php" class="menu-card">
                    <div class="menu-card-icon">
                        <i class="fas fa-user-cog"></i>
                    </div>
                    <h3>Thông tin cá nhân</h3>
                    <p>Cập nhật thông tin và quản lý tài khoản</p>
                </a>
            </div>
        </div>
        
        <!-- Points Card -->
        <div class="points-card">
            <div class="points-info">
                <h3><i class="fas fa-gift"></i> Điểm thưởng của bạn</h3>
                <p>Tích điểm khi đặt món để nhận ưu đãi hấp dẫn!</p>
            </div>
            <div class="points-value">
                <i class="fas fa-coins"></i>
                <span><?php echo number_format($customer['DiemTichLuy'] ?? 0); ?></span>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <footer class="footer">
        <p>&copy; 2024 Nhà hàng 3CE. Tất cả quyền được bảo lưu.</p>
    </footer>
    
    <?php if ($cartCount > 0): ?>
    <!-- Floating Cart Button -->
    <a href="gio_hang.php" class="floating-cart-btn" title="Xem giỏ hàng">
        <i class="fas fa-shopping-cart"></i>
        <span class="cart-count"><?php echo $cartCount; ?></span>
    </a>
    <style>
        .floating-cart-btn {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #001f3f 0%, #003366 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #F5F5DC;
            font-size: 24px;
            text-decoration: none;
            box-shadow: 0 5px 25px rgba(0,31,63,0.4);
            transition: all 0.3s;
            z-index: 999;
        }
        .floating-cart-btn:hover {
            transform: scale(1.1);
            background: linear-gradient(135deg, #D4AF37 0%, #f7d774 100%);
            color: #001f3f;
        }
        .floating-cart-btn .cart-count {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #dc3545;
            color: white;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            font-size: 12px;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid white;
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }
    </style>
    <?php endif; ?>
</body>
</html>
