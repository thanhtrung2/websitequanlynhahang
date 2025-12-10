<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['customer_id'])) {
    header("Location: customer_login.php");
    exit();
}

$maKhachHang = $_SESSION['customer_id'];
$message = '';
$error = '';

// Lấy thông tin khách hàng
$customer = null;
try {
    $stmt = $conn->prepare("SELECT * FROM khach_hang WHERE MaKhachHang = ?");
    $stmt->execute([$maKhachHang]);
    $customer = $stmt->fetch();
} catch(PDOException $e) {
    die("Lỗi: " . $e->getMessage());
}

// Xử lý cập nhật thông tin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $hoTen = trim($_POST['HoTen']);
    $email = trim($_POST['Email'] ?? '');
    
    if (empty($hoTen)) {
        $error = 'Vui lòng nhập họ tên!';
    } else {
        try {
            $stmt = $conn->prepare("UPDATE khach_hang SET HoTen = ?, Email = ? WHERE MaKhachHang = ?");
            $stmt->execute([$hoTen, $email, $maKhachHang]);
            
            $_SESSION['customer_name'] = $hoTen;
            $message = 'Cập nhật thông tin thành công!';
            
            // Refresh customer data
            $stmt = $conn->prepare("SELECT * FROM khach_hang WHERE MaKhachHang = ?");
            $stmt->execute([$maKhachHang]);
            $customer = $stmt->fetch();
        } catch(PDOException $e) {
            $error = 'Lỗi: ' . $e->getMessage();
        }
    }
}

// Xử lý đổi số điện thoại
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_phone'])) {
    $newPhone = trim($_POST['NewPhone']);
    $confirmPhone = trim($_POST['ConfirmPhone']);
    
    if (empty($newPhone)) {
        $error = 'Vui lòng nhập số điện thoại mới!';
    } elseif ($newPhone !== $confirmPhone) {
        $error = 'Số điện thoại xác nhận không khớp!';
    } elseif (!preg_match('/^[0-9]{10,11}$/', $newPhone)) {
        $error = 'Số điện thoại không hợp lệ!';
    } else {
        try {
            // Kiểm tra số điện thoại đã tồn tại chưa
            $stmt = $conn->prepare("SELECT MaKhachHang FROM khach_hang WHERE SoDienThoai = ? AND MaKhachHang != ?");
            $stmt->execute([$newPhone, $maKhachHang]);
            if ($stmt->fetch()) {
                $error = 'Số điện thoại này đã được sử dụng!';
            } else {
                $stmt = $conn->prepare("UPDATE khach_hang SET SoDienThoai = ? WHERE MaKhachHang = ?");
                $stmt->execute([$newPhone, $maKhachHang]);
                $message = 'Đổi số điện thoại thành công!';
                
                // Refresh customer data
                $stmt = $conn->prepare("SELECT * FROM khach_hang WHERE MaKhachHang = ?");
                $stmt->execute([$maKhachHang]);
                $customer = $stmt->fetch();
            }
        } catch(PDOException $e) {
            $error = 'Lỗi: ' . $e->getMessage();
        }
    }
}

// Lấy thống kê
$tongDonHang = 0;
$tongChiTieu = 0;
try {
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM hoa_don WHERE MaKhachHang = ?");
    $stmt->execute([$maKhachHang]);
    $tongDonHang = $stmt->fetch()['total'];
    
    $stmt = $conn->prepare("SELECT SUM(TongTien) as total FROM hoa_don WHERE MaKhachHang = ? AND TrangThai = 'Đã thanh toán'");
    $stmt->execute([$maKhachHang]);
    $tongChiTieu = $stmt->fetch()['total'] ?? 0;
} catch(PDOException $e) {}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nhà hàng 3CE - Thông tin cá nhân</title>
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
            max-width: 800px;
            margin: 30px auto;
            padding: 0 20px;
        }
        .profile-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .profile-header {
            background: linear-gradient(135deg, #001f3f 0%, #003366 100%);
            padding: 40px;
            text-align: center;
            color: #F5F5DC;
        }
        .profile-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: #F5F5DC;
            color: #001f3f;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            font-weight: bold;
            margin: 0 auto 15px;
            border: 4px solid #D4AF37;
        }
        .profile-header h2 {
            margin-bottom: 5px;
        }
        .profile-header p {
            opacity: 0.9;
        }
        .profile-body {
            padding: 30px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-item {
            text-align: center;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
        }
        .stat-item i {
            font-size: 30px;
            color: #001f3f;
            margin-bottom: 10px;
        }
        .stat-item h3 {
            font-size: 24px;
            color: #003366;
            margin-bottom: 5px;
        }
        .stat-item p {
            color: #666;
            font-size: 14px;
        }
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .alert-success { background: #d4edda; color: #155724; }
        .alert-error { background: #f8d7da; color: #721c24; }
        .form-section h3 {
            color: #001f3f;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #eee;
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
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
        }
        .form-group input:focus {
            outline: none;
            border-color: #001f3f;
        }
        .form-group input:disabled {
            background: #f5f5f5;
            color: #666;
        }
        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
        }
        .btn-primary {
            background: linear-gradient(135deg, #001f3f 0%, #003366 100%);
            color: #F5F5DC;
        }
        .btn-primary:hover {
            background: linear-gradient(135deg, #003366 0%, #004080 100%);
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }
        .info-row:last-child { border-bottom: none; }
        .info-label {
            color: #666;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .info-value {
            font-weight: 600;
            color: #001f3f;
        }
        .loyalty-card {
            background: linear-gradient(135deg, #D4AF37 0%, #B8960C 100%);
            border-radius: 15px;
            padding: 25px;
            color: #001f3f;
            margin-top: 20px;
        }
        .loyalty-card h4 {
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .loyalty-points {
            font-size: 36px;
            font-weight: bold;
        }
        .form-group small {
            display: block;
            margin-top: 5px;
            color: #666;
        }
        .btn-secondary {
            background: #6c757d;
            color: white;
            margin-left: 10px;
        }
        .btn-link {
            background: none;
            border: none;
            color: #007bff;
            cursor: pointer;
            font-size: 14px;
            padding: 0;
            text-decoration: underline;
        }
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        .modal.active { display: flex; }
        .modal-content {
            background: white;
            border-radius: 15px;
            padding: 30px;
            max-width: 450px;
            width: 90%;
        }
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #eee;
        }
        .modal-header h3 { color: #001f3f; }
        .close-btn {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
        }
        @media (max-width: 600px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <div class="logo">
                <i class="fas fa-id-card"></i>
                <span>Thông tin cá nhân</span>
            </div>
            <a href="customer_dashboard.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
        </div>
    </div>

    <div class="container">
        <div class="profile-card">
            <div class="profile-header">
                <div class="profile-avatar">
                    <?php echo strtoupper(mb_substr($customer['HoTen'], 0, 1, 'UTF-8')); ?>
                </div>
                <h2><?php echo htmlspecialchars($customer['HoTen']); ?></h2>
                <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars($customer['SoDienThoai']); ?></p>
            </div>
            
            <div class="profile-body">
                <div class="stats-grid">
                    <div class="stat-item">
                        <i class="fas fa-shopping-bag"></i>
                        <h3><?php echo $tongDonHang; ?></h3>
                        <p>Đơn hàng</p>
                    </div>
                    <div class="stat-item">
                        <i class="fas fa-money-bill-wave"></i>
                        <h3><?php echo number_format($tongChiTieu, 0, ',', '.'); ?>đ</h3>
                        <p>Tổng chi tiêu</p>
                    </div>
                    <div class="stat-item">
                        <i class="fas fa-star"></i>
                        <h3><?php echo $customer['DiemTichLuy'] ?? 0; ?></h3>
                        <p>Điểm tích lũy</p>
                    </div>
                </div>

                <?php if ($message): ?>
                <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $message; ?></div>
                <?php endif; ?>
                <?php if ($error): ?>
                <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div>
                <?php endif; ?>

                <div class="form-section">
                    <h3><i class="fas fa-user-edit"></i> Cập nhật thông tin</h3>
                    <form method="POST">
                        <div class="form-group">
                            <label><i class="fas fa-id-badge"></i> Mã khách hàng</label>
                            <input type="text" value="#<?php echo $customer['MaKhachHang']; ?>" disabled>
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-user"></i> Họ tên *</label>
                            <input type="text" name="HoTen" value="<?php echo htmlspecialchars($customer['HoTen']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-phone"></i> Số điện thoại</label>
                            <div style="display: flex; gap: 10px; align-items: center;">
                                <input type="text" value="<?php echo htmlspecialchars($customer['SoDienThoai']); ?>" disabled style="flex: 1;">
                                <button type="button" class="btn btn-secondary" onclick="openPhoneModal()" style="white-space: nowrap;">
                                    <i class="fas fa-edit"></i> Đổi SĐT
                                </button>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label><i class="fas fa-envelope"></i> Email</label>
                            <input type="email" name="Email" value="<?php echo htmlspecialchars($customer['Email'] ?? ''); ?>" placeholder="example@email.com">
                        </div>
                        
                        <button type="submit" name="update_profile" class="btn btn-primary">
                            <i class="fas fa-save"></i> Lưu thay đổi
                        </button>
                    </form>
                </div>

                <div class="loyalty-card">
                    <h4><i class="fas fa-crown"></i> Thẻ thành viên</h4>
                    <p>Điểm tích lũy hiện tại:</p>
                    <div class="loyalty-points"><?php echo number_format($customer['DiemTichLuy'] ?? 0); ?> điểm</div>
                    <p style="margin-top: 10px; font-size: 14px;">
                        <i class="fas fa-info-circle"></i> Tích lũy điểm khi thanh toán để nhận ưu đãi (1 điểm = 10,000đ)
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Đổi số điện thoại -->
    <div id="phoneModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-phone"></i> Đổi số điện thoại</h3>
                <button class="close-btn" onclick="closePhoneModal()">&times;</button>
            </div>
            
            <form method="POST">
                <div class="form-group">
                    <label>Số điện thoại hiện tại</label>
                    <input type="text" value="<?php echo htmlspecialchars($customer['SoDienThoai']); ?>" disabled>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-phone-alt"></i> Số điện thoại mới *</label>
                    <input type="text" name="NewPhone" required placeholder="Nhập số điện thoại mới" pattern="[0-9]{10,11}">
                    <small>Số điện thoại phải có 10-11 chữ số</small>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-check-double"></i> Xác nhận số điện thoại *</label>
                    <input type="text" name="ConfirmPhone" required placeholder="Nhập lại số điện thoại mới">
                </div>
                
                <button type="submit" name="change_phone" class="btn btn-primary" style="width: 100%;">
                    <i class="fas fa-save"></i> Xác nhận đổi số điện thoại
                </button>
            </form>
        </div>
    </div>

    <script>
        function openPhoneModal() {
            document.getElementById('phoneModal').classList.add('active');
        }
        
        function closePhoneModal() {
            document.getElementById('phoneModal').classList.remove('active');
        }
        
        document.getElementById('phoneModal').addEventListener('click', function(e) {
            if (e.target === this) closePhoneModal();
        });
    </script>
</body>
</html>