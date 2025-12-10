<?php
require_once __DIR__ . '/config/db.php';

try {
    // Kiểm tra kết nối database
    $stmt = $conn->query("SELECT COUNT(*) as count FROM nhan_vien");
    $result = $stmt->fetch();
    
    $stmt_admin = $conn->query("SELECT nv.*, tk.TenDangNhap FROM nhan_vien nv LEFT JOIN tai_khoan tk ON nv.MaNhanVien = tk.MaNhanVien WHERE nv.MaChucVu = 1 LIMIT 1");
    $admin = $stmt_admin->fetch();
    
    $hasAdmin = $admin ? true : false;
    
} catch(PDOException $e) {
    die("Lỗi kết nối database: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Khởi động dự án - Nhà hàng 3CE</title>
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
        .container {
            max-width: 900px;
            width: 100%;
        }
        .card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
            margin-bottom: 20px;
        }
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .card-header h1 {
            font-size: 32px;
            margin-bottom: 10px;
        }
        .card-body {
            padding: 30px;
        }
        .status-box {
            background: #e8f5e9;
            border-left: 4px solid #4caf50;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .status-box.warning {
            background: #fff3e0;
            border-left-color: #ff9800;
        }
        .status-box h3 {
            color: #2e7d32;
            margin-bottom: 10px;
        }
        .status-box.warning h3 {
            color: #e65100;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        .info-item {
            background: #f5f5f5;
            padding: 15px;
            border-radius: 8px;
        }
        .info-item strong {
            color: #666;
            display: block;
            margin-bottom: 5px;
        }
        .info-item span {
            color: #333;
            font-size: 16px;
        }
        .btn-group {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        .btn {
            display: inline-block;
            padding: 15px 25px;
            border-radius: 8px;
            text-decoration: none;
            text-align: center;
            font-weight: bold;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
        }
        .btn-primary {
            background: #2196f3;
            color: white;
        }
        .btn-primary:hover {
            background: #1976d2;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(33, 150, 243, 0.3);
        }
        .btn-success {
            background: #4caf50;
            color: white;
        }
        .btn-success:hover {
            background: #388e3c;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(76, 175, 80, 0.3);
        }
        .btn-warning {
            background: #ff9800;
            color: white;
        }
        .btn-warning:hover {
            background: #f57c00;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 152, 0, 0.3);
        }
        .highlight {
            background: #ffeb3b;
            padding: 3px 8px;
            border-radius: 3px;
            font-weight: bold;
        }
        .links-section {
            background: #fafafa;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
        }
        .links-section h3 {
            margin-bottom: 15px;
            color: #333;
        }
        .link-list {
            list-style: none;
        }
        .link-list li {
            padding: 10px 0;
            border-bottom: 1px solid #e0e0e0;
        }
        .link-list li:last-child {
            border-bottom: none;
        }
        .link-list a {
            color: #2196f3;
            text-decoration: none;
        }
        .link-list a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h1>🍽️ Nhà hàng 3CE</h1>
                <p>Chào mừng đến với hệ thống quản lý</p>
            </div>
            <div class="card-body">
                <div class="status-box <?php echo $hasAdmin ? '' : 'warning'; ?>">
                    <h3>
                        <?php if($hasAdmin): ?>
                            ✅ Hệ thống đã sẵn sàng!
                        <?php else: ?>
                            ⚠️ Cần tạo tài khoản Admin
                        <?php endif; ?>
                    </h3>
                    <p>
                        <?php if($hasAdmin): ?>
                            Database đã được thiết lập và có tài khoản admin.
                        <?php else: ?>
                            Chưa có tài khoản admin. Vui lòng tạo tài khoản admin trước khi sử dụng.
                        <?php endif; ?>
                    </p>
                </div>

                <?php if($hasAdmin): ?>
                    <div class="info-grid">
                        <div class="info-item">
                            <strong>Mã nhân viên:</strong>
                            <span class="highlight"><?php echo $admin['MaNhanVien']; ?></span>
                        </div>
                        <div class="info-item">
                            <strong>Họ tên:</strong>
                            <span><?php echo $admin['HoTen']; ?></span>
                        </div>
                        <div class="info-item">
                            <strong>Tên đăng nhập:</strong>
                            <span><?php echo $admin['TenDangNhap'] ?? 'Chưa có'; ?></span>
                        </div>
                        <div class="info-item">
                            <strong>Mật khẩu:</strong>
                            <span>admin123</span>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="btn-group">
                    <?php if($hasAdmin): ?>
                        <a href="admin/admin_login.php" class="btn btn-primary">
                            🔐 Đăng nhập Admin
                        </a>
                        <a href="public/index.php" class="btn btn-success">
                            🏠 Trang chủ
                        </a>
                        <a href="public/customer_login.php" class="btn btn-success">
                            👤 Đăng nhập Khách hàng
                        </a>
                    <?php else: ?>
                        <a href="setup_admin.php" class="btn btn-warning">
                            ⚙️ Tạo tài khoản Admin
                        </a>
                    <?php endif; ?>
                </div>

                <div class="links-section">
                    <h3>📋 Các trang quan trọng:</h3>
                    <ul class="link-list">
                        <li>🏠 <a href="public/index.php">Trang chủ</a></li>
                        <li>🔐 <a href="admin/admin_login.php">Đăng nhập Admin</a></li>
                        <li>👤 <a href="public/customer_login.php">Đăng nhập Khách hàng</a></li>
                        <li>📝 <a href="public/customer_register.php">Đăng ký Khách hàng</a></li>
                        <?php if(!$hasAdmin): ?>
                        <li>⚙️ <a href="setup_admin.php">Thiết lập Admin</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
