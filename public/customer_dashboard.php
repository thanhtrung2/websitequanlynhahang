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
} catch(PDOException $e) {
    die("Lỗi: " . $e->getMessage());
}

$message = $_SESSION['message'] ?? '';
unset($_SESSION['message']);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nhà hàng 3CE - Khách hàng</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #F5F5DC 0%, #EDE8D0 100%);
            min-height: 100vh;
        }
        .header {
            background: linear-gradient(135deg, #001f3f 0%, #003366 100%);
            padding: 15px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
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
            color: #F5F5DC;
        }
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #F5F5DC;
            color: #001f3f;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }
        .logout-btn {
            padding: 8px 20px;
            background: #dc3545;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: all 0.3s;
        }
        .logout-btn:hover {
            background: #c82333;
        }
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        .welcome-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            margin-bottom: 30px;
        }
        .welcome-card h1 {
            color: #001f3f;
            margin-bottom: 10px;
        }
        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }
        .menu-card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            transition: transform 0.3s;
            cursor: pointer;
            text-decoration: none;
            color: inherit;
            display: block;
        }
        .menu-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 31, 63, 0.2);
        }
        .menu-card i {
            font-size: 50px;
            color: #001f3f;
            margin-bottom: 15px;
        }
        .menu-card h3 {
            color: #001f3f;
            margin-bottom: 10px;
        }
        .menu-card p {
            color: #666;
            font-size: 14px;
        }
        .alert {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <div class="logo">
                <i class="fas fa-utensils"></i>
                <span>Nhà hàng 3CE - Khách hàng</span>
            </div>
            <div class="user-menu">
                <div class="user-info">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($customer['HoTen'], 0, 1)); ?>
                    </div>
                    <span><?php echo htmlspecialchars($customer['HoTen']); ?></span>
                </div>
                <a href="index.php" class="logout-btn" style="background: #28a745;">
                    <i class="fas fa-home"></i> Trang chủ
                </a>
                <a href="../auth/logout.php?type=customer" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i> Đăng xuất
                </a>
            </div>
        </div>
    </div>

    <div class="container">
        <?php if ($message): ?>
        <div class="alert">
            <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($message); ?>
        </div>
        <?php endif; ?>

        <div class="welcome-card">
            <h1><i class="fas fa-user"></i> Chào mừng, <?php echo htmlspecialchars($customer['HoTen']); ?>!</h1>
            <p>Chọn chức năng bên dưới để sử dụng dịch vụ</p>
        </div>

        <div class="menu-grid">
            <a href="index.php#menu" class="menu-card">
                <i class="fas fa-utensils"></i>
                <h3>Xem thực đơn</h3>
                <p>Xem và đặt món ăn yêu thích</p>
            </a>

            <a href="gio_hang.php" class="menu-card" style="position: relative;">
                <i class="fas fa-shopping-cart"></i>
                <h3>Giỏ hàng</h3>
                <p>Xem các món đã chọn</p>
                <?php 
                $cartCount = 0;
                if (isset($_SESSION['cart'])) {
                    foreach ($_SESSION['cart'] as $item) {
                        $cartCount += $item['SoLuong'];
                    }
                }
                if ($cartCount > 0): ?>
                <span style="position: absolute; top: 15px; right: 15px; background: #dc3545; color: white; border-radius: 50%; width: 25px; height: 25px; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: bold;">
                    <?php echo $cartCount; ?>
                </span>
                <?php endif; ?>
            </a>

            <a href="dat_ban.php" class="menu-card">
                <i class="fas fa-chair"></i>
                <h3>Đặt bàn</h3>
                <p>Đặt bàn trước để đảm bảo chỗ ngồi</p>
            </a>

            <a href="thanh_toan.php" class="menu-card">
                <i class="fas fa-credit-card"></i>
                <h3>Thanh toán</h3>
                <p>Thanh toán hóa đơn của bạn</p>
            </a>

            <a href="lich_su_don_hang.php" class="menu-card">
                <i class="fas fa-history"></i>
                <h3>Lịch sử đơn hàng</h3>
                <p>Xem các đơn hàng đã đặt</p>
            </a>

            <a href="customer_profile.php" class="menu-card">
                <i class="fas fa-id-card"></i>
                <h3>Thông tin cá nhân</h3>
                <p>Xem và cập nhật thông tin</p>
            </a>
        </div>
    </div>
</body>
</html>
