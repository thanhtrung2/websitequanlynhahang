<?php
session_start();
require_once __DIR__ . '/../config/db.php';

// Kiểm tra đăng nhập khách hàng
if (!isset($_SESSION['customer_id'])) {
    header("Location: customer_login.php");
    exit();
}

// Lấy thông tin khách hàng
$customer = null;
try {
    $stmt = $conn->prepare("SELECT * FROM khach_hang WHERE MaKhachHang = ?");
    $stmt->execute([$_SESSION['customer_id']]);
    $customer = $stmt->fetch();
} catch(PDOException $e) {
    // Handle error
}

// Xử lý xóa món khỏi giỏ
if (isset($_GET['remove'])) {
    $removeIndex = intval($_GET['remove']);
    if (isset($_SESSION['cart'][$removeIndex])) {
        array_splice($_SESSION['cart'], $removeIndex, 1);
    }
    header("Location: gio_hang.php");
    exit();
}

// Xử lý cập nhật số lượng
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cart'])) {
    foreach ($_POST['quantity'] as $index => $qty) {
        $qty = intval($qty);
        if ($qty > 0 && $qty <= 99 && isset($_SESSION['cart'][$index])) {
            $_SESSION['cart'][$index]['SoLuong'] = $qty;
        }
    }
    header("Location: gio_hang.php");
    exit();
}

// Xử lý xác nhận đặt món
$message = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_order'])) {
    if (empty($_SESSION['cart'])) {
        $error = 'Giỏ hàng trống!';
    } else {
        try {
            $conn->beginTransaction();
            
            // Tạo hóa đơn mới
            $tongTien = 0;
            foreach ($_SESSION['cart'] as $item) {
                $tongTien += $item['DonGia'] * $item['SoLuong'];
            }
            
            $stmt = $conn->prepare("INSERT INTO hoa_don (MaKhachHang, ThoiGianVao, TongTien, ThanhTien, TrangThai, GhiChu) VALUES (?, NOW(), ?, ?, 'Chưa thanh toán', ?)");
            $ghiChu = $_POST['ghi_chu'] ?? '';
            $stmt->execute([$_SESSION['customer_id'], $tongTien, $tongTien, $ghiChu]);
            $maHoaDon = $conn->lastInsertId();

            // Thêm chi tiết hóa đơn
            foreach ($_SESSION['cart'] as $item) {
                $thanhTien = $item['DonGia'] * $item['SoLuong'];
                $stmt = $conn->prepare("INSERT INTO chi_tiet_hoa_don (MaHoaDon, MaMonAn, SoLuong, DonGia, ThanhTien) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$maHoaDon, $item['MaMonAn'], $item['SoLuong'], $item['DonGia'], $thanhTien]);
            }
            
            $conn->commit();
            
            // Xóa giỏ hàng
            $_SESSION['cart'] = [];
            $_SESSION['order_success'] = 'Đặt món thành công! Mã hóa đơn của bạn là: #' . $maHoaDon;
            
            header("Location: gio_hang.php");
            exit();
            
        } catch(PDOException $e) {
            $conn->rollBack();
            $error = 'Lỗi khi đặt món: ' . $e->getMessage();
        }
    }
}

// Lấy thông báo thành công
if (isset($_SESSION['order_success'])) {
    $message = $_SESSION['order_success'];
    unset($_SESSION['order_success']);
}

// Tính tổng tiền
$cart = $_SESSION['cart'] ?? [];
$tongTien = 0;
foreach ($cart as $item) {
    $tongTien += $item['DonGia'] * $item['SoLuong'];
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nhà hàng 3CE - Giỏ hàng</title>
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
        .back-btn {
            padding: 8px 20px;
            background: #F5F5DC;
            color: #001f3f;
            text-decoration: none;
            border-radius: 5px;
        }
        .container {
            max-width: 1000px;
            margin: 30px auto;
            padding: 0 20px;
        }
        .page-header {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .page-header h1 {
            color: #001f3f;
            margin-bottom: 10px;
        }
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
        }
        .alert-error {
            background: #f8d7da;
            color: #721c24;
        }
        .cart-container {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        }
        .cart-item {
            display: flex;
            align-items: center;
            gap: 20px;
            padding: 20px 0;
            border-bottom: 1px solid #eee;
        }
        .cart-item:last-child {
            border-bottom: none;
        }
        .cart-item-image {
            width: 80px;
            height: 80px;
            border-radius: 10px;
            object-fit: cover;
        }
        .cart-item-image-placeholder {
            width: 80px;
            height: 80px;
            border-radius: 10px;
            background: linear-gradient(135deg, #001f3f 0%, #003366 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #F5F5DC;
            font-size: 24px;
        }
        .cart-item-info {
            flex: 1;
        }
        .cart-item-name {
            font-size: 18px;
            font-weight: 600;
            color: #001f3f;
            margin-bottom: 5px;
        }
        .cart-item-price {
            color: #003366;
            font-weight: 500;
        }
        .cart-item-quantity {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .qty-input {
            width: 60px;
            padding: 8px;
            text-align: center;
            border: 2px solid #ddd;
            border-radius: 5px;
        }
        .cart-item-total {
            font-size: 18px;
            font-weight: bold;
            color: #003366;
            min-width: 120px;
            text-align: right;
        }
        .cart-item-remove {
            color: #dc3545;
            text-decoration: none;
            font-size: 20px;
        }
        .cart-summary {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #001f3f;
        }
        .cart-total {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 24px;
            margin-bottom: 20px;
        }
        .cart-total span:last-child {
            font-weight: bold;
            color: #003366;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #001f3f;
            font-weight: 500;
        }
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            resize: vertical;
        }
        .btn-group {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        .btn {
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }
        .btn-primary {
            background: linear-gradient(135deg, #001f3f 0%, #003366 100%);
            color: #F5F5DC;
        }
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        .btn-outline {
            background: white;
            color: #001f3f;
            border: 2px solid #001f3f;
        }
        .empty-cart {
            text-align: center;
            padding: 50px;
            color: #666;
        }
        .empty-cart i {
            font-size: 60px;
            color: #ddd;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <div class="logo">
                <i class="fas fa-shopping-cart"></i>
                <span>Giỏ hàng</span>
            </div>
            <a href="customer_dashboard.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
        </div>
    </div>

    <div class="container">
        <div class="page-header">
            <h1><i class="fas fa-shopping-cart"></i> Giỏ hàng của bạn</h1>
            <p>Xem và quản lý các món đã chọn</p>
        </div>

        <?php if ($message): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($message); ?>
        </div>
        <?php endif; ?>

        <?php if ($error): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>

        <div class="cart-container">
            <?php if (empty($cart)): ?>
            <div class="empty-cart">
                <i class="fas fa-shopping-cart"></i>
                <h3>Giỏ hàng trống</h3>
                <p>Hãy thêm món ăn vào giỏ hàng của bạn</p>
                <br>
                <a href="index.php#menu" class="btn btn-primary">
                    <i class="fas fa-utensils"></i> Xem thực đơn
                </a>
            </div>
            <?php else: ?>
            <form method="POST">
                <?php foreach ($cart as $index => $item): ?>
                <div class="cart-item">
                    <?php if (!empty($item['HinhAnh'])): ?>
                        <img src="<?php echo htmlspecialchars($item['HinhAnh']); ?>" class="cart-item-image" onerror="this.outerHTML='<div class=\'cart-item-image-placeholder\'><i class=\'fas fa-utensils\'></i></div>'">
                    <?php else: ?>
                        <div class="cart-item-image-placeholder">
                            <i class="fas fa-utensils"></i>
                        </div>
                    <?php endif; ?>
                    
                    <div class="cart-item-info">
                        <div class="cart-item-name"><?php echo htmlspecialchars($item['TenMonAn']); ?></div>
                        <div class="cart-item-price"><?php echo number_format($item['DonGia'], 0, ',', '.'); ?>đ</div>
                    </div>
                    
                    <div class="cart-item-quantity">
                        <input type="number" name="quantity[<?php echo $index; ?>]" class="qty-input" 
                               value="<?php echo $item['SoLuong']; ?>" min="1" max="99">
                    </div>
                    
                    <div class="cart-item-total">
                        <?php echo number_format($item['DonGia'] * $item['SoLuong'], 0, ',', '.'); ?>đ
                    </div>
                    
                    <a href="?remove=<?php echo $index; ?>" class="cart-item-remove" onclick="return confirm('Xóa món này khỏi giỏ hàng?')">
                        <i class="fas fa-trash"></i>
                    </a>
                </div>
                <?php endforeach; ?>

                <div class="cart-summary">
                    <div class="cart-total">
                        <span>Tổng cộng:</span>
                        <span><?php echo number_format($tongTien, 0, ',', '.'); ?>đ</span>
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-sticky-note"></i> Ghi chú đơn hàng</label>
                        <textarea name="ghi_chu" rows="3" placeholder="Nhập ghi chú cho đơn hàng (nếu có)..."></textarea>
                    </div>
                    
                    <div class="btn-group">
                        <button type="submit" name="update_cart" class="btn btn-secondary">
                            <i class="fas fa-sync"></i> Cập nhật giỏ hàng
                        </button>
                        <button type="submit" name="confirm_order" class="btn btn-primary">
                            <i class="fas fa-check"></i> Xác nhận đặt món
                        </button>
                        <a href="index.php#menu" class="btn btn-outline">
                            <i class="fas fa-plus"></i> Thêm món
                        </a>
                    </div>
                </div>
            </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>