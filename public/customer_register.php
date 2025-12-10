<?php
session_start();
require_once __DIR__ . '/../config/db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $HoTen = trim($_POST['HoTen']);
    $SoDienThoai = trim($_POST['SoDienThoai']);
    $Email = trim($_POST['Email'] ?? '');
    
    if (empty($HoTen) || empty($SoDienThoai)) {
        $error = 'Vui lòng điền đầy đủ thông tin bắt buộc!';
    } else {
        try {
            // Kiểm tra số điện thoại đã tồn tại
            $stmt = $conn->prepare("SELECT MaKhachHang FROM khach_hang WHERE SoDienThoai = ?");
            $stmt->execute([$SoDienThoai]);
            if ($stmt->fetch()) {
                $error = 'Số điện thoại đã được đăng ký!';
            } else {
                // Thêm khách hàng mới (MaKhachHang tự động tăng)
                $stmt = $conn->prepare("INSERT INTO khach_hang (HoTen, SoDienThoai, Email, DiemTichLuy) VALUES (?, ?, ?, 0)");
                $stmt->execute([$HoTen, $SoDienThoai, $Email]);
                
                $success = 'Đăng ký thành công! Bạn có thể đăng nhập bằng số điện thoại.';
            }
        } catch(PDOException $e) {
            $error = 'Lỗi: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nhà hàng 3CE - Đăng ký</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #F5F5DC 0%, #EDE8D0 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .register-container {
            background: white;
            border-radius: 15px;
            padding: 40px;
            max-width: 500px;
            width: 100%;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        .register-container h2 {
            text-align: center;
            color: #001f3f;
            margin-bottom: 30px;
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
        .form-group input, .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
        }
        .form-group input:focus, .form-group textarea:focus {
            outline: none;
            border-color: #001f3f;
        }
        .btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #001f3f 0%, #003366 100%);
            color: #F5F5DC;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        .btn:hover {
            background: linear-gradient(135deg, #003366 0%, #004080 100%);
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .success {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .links {
            text-align: center;
            margin-top: 20px;
        }
        .links a {
            color: #001f3f;
            text-decoration: none;
        }
        .links a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <h2><i class="fas fa-user-plus"></i> Đăng ký khách hàng thân thiết</h2>
        
        <?php if ($error): ?>
        <div class="error">
            <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
        <div class="success">
            <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
        </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label><i class="fas fa-user"></i> Tên khách hàng *</label>
                <input type="text" name="HoTen" required placeholder="Nhập họ tên đầy đủ">
            </div>
            
            <div class="form-group">
                <label><i class="fas fa-phone"></i> Số điện thoại *</label>
                <input type="text" name="SoDienThoai" required placeholder="Ví dụ: 0123456789">
            </div>
            
            <div class="form-group">
                <label><i class="fas fa-envelope"></i> Email (tùy chọn)</label>
                <input type="email" name="Email" placeholder="example@email.com">
            </div>
            
            <button type="submit" class="btn">
                <i class="fas fa-user-plus"></i> Đăng ký
            </button>
        </form>
        
        <div class="links">
            Đã có tài khoản? <a href="customer_login.php">Đăng nhập ngay</a> |
            <a href="index.php">Về trang chủ</a>
        </div>
    </div>
</body>
</html>
