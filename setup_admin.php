<?php
require_once __DIR__ . '/config/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Tạo nhân viên admin mới
        $stmt = $conn->prepare("INSERT INTO nhan_vien (HoTen, NgaySinh, GioiTinh, DiaChi, SoDienThoai, Email, MaChucVu, NgayVaoLam, TrangThai) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->execute([
            'Administrator',
            '1990-01-01',
            'Nam',
            'Hà Nội',
            '0900000000',
            'admin@nhahang.com',
            1,
            date('Y-m-d'),
            'Đang làm việc'
        ]);
        
        $maNhanVien = $conn->lastInsertId();
        
        // Tạo tài khoản đăng nhập
        $matKhau = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO tai_khoan (TenDangNhap, MatKhau, MaNhanVien) VALUES (?, ?, ?)");
        $stmt->execute(['admin', $matKhau, $maNhanVien]);
        
        header("Location: start_app.php?success=1");
        exit();
        
    } catch(PDOException $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thiết lập Admin</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        .card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            max-width: 500px;
            width: 100%;
        }
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
            border-radius: 15px 15px 0 0;
        }
        .card-body {
            padding: 30px;
        }
        .info-box {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .error-box {
            background: #ffebee;
            border-left: 4px solid #f44336;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            color: #c62828;
        }
        .btn {
            width: 100%;
            padding: 15px;
            background: #4caf50;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
        }
        .btn:hover {
            background: #388e3c;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(76, 175, 80, 0.3);
        }
        .back-link {
            display: block;
            text-align: center;
            margin-top: 15px;
            color: #666;
            text-decoration: none;
        }
        .back-link:hover {
            color: #333;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="card-header">
            <h1>⚙️ Thiết lập Admin</h1>
        </div>
        <div class="card-body">
            <?php if(isset($error)): ?>
                <div class="error-box">
                    <strong>❌ Lỗi:</strong> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <div class="info-box">
                <p><strong>ℹ️ Thông tin tài khoản sẽ được tạo:</strong></p>
                <ul style="margin-top: 10px; margin-left: 20px;">
                    <li>Tên đăng nhập: <strong>admin</strong></li>
                    <li>Mật khẩu: <strong>admin123</strong></li>
                    <li>Chức vụ: <strong>Quản lý</strong></li>
                </ul>
            </div>
            
            <form method="POST">
                <button type="submit" class="btn">
                    🚀 Tạo tài khoản Admin
                </button>
            </form>
            
            <a href="start_app.php" class="back-link">← Quay lại</a>
        </div>
    </div>
</body>
</html>
