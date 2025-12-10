<?php
/**
 * SETUP.PHP - TRANG THIẾT LẬP & KIỂM TRA HỆ THỐNG
 * File tổng hợp: Tạo admin, kiểm tra kết nối, dashboard
 */

$action = $_GET['action'] ?? 'dashboard';

// Load database connection
try {
    require_once __DIR__ . '/config/db.php';
    $dbConnected = true;
} catch (Exception $e) {
    $dbConnected = false;
    $dbError = $e->getMessage();
}

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup - Nhà hàng 3CE</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .header {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
            margin-bottom: 20px;
            text-align: center;
        }
        .tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .tab {
            flex: 1;
            min-width: 200px;
            padding: 15px 20px;
            background: white;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s;
            text-decoration: none;
            display: block;
            text-align: center;
            color: #333;
        }
        .tab:hover { background: #667eea; color: white; transform: translateY(-2px); }
        .tab.active { background: #667eea; color: white; }
        .content {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
        }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin: 15px 0; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin: 15px 0; }
        .info { background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 8px; margin: 15px 0; }
        .warning { background: #fff3cd; color: #856404; padding: 15px; border-radius: 8px; margin: 15px 0; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #667eea; color: white; }
        .btn { display: inline-block; padding: 12px 24px; background: #667eea; color: white; text-decoration: none; border-radius: 8px; margin: 10px 5px; border: none; cursor: pointer; }
        .btn:hover { background: #5568d3; }
        code { background: #ffffcc; padding: 5px 10px; border-radius: 3px; font-size: 16px; font-weight: bold; color: #d63384; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin: 20px 0; }
        .card { background: #f8f9fa; padding: 20px; border-radius: 10px; border-left: 4px solid #667eea; }
        .card h3 { margin-bottom: 15px; color: #333; }
        .card ul { list-style: none; padding: 0; }
        .card li { padding: 8px 0; }
        .card a { color: #667eea; text-decoration: none; font-weight: 500; }
        .card a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><i class="fas fa-utensils"></i> NHÀ HÀNG 3CE</h1>
            <p>Setup & Dashboard Dự Án</p>
            <?php if ($dbConnected): ?>
                <span style="display: inline-block; padding: 8px 15px; background: #28a745; color: white; border-radius: 20px; margin-top: 10px;">
                    <i class="fas fa-check-circle"></i> Database đã kết nối
                </span>
            <?php else: ?>
                <span style="display: inline-block; padding: 8px 15px; background: #dc3545; color: white; border-radius: 20px; margin-top: 10px;">
                    <i class="fas fa-times-circle"></i> Lỗi kết nối
                </span>
            <?php endif; ?>
        </div>

        <div class="tabs">
            <a href="?action=dashboard" class="tab <?= $action === 'dashboard' ? 'active' : '' ?>">
                <i class="fas fa-home"></i> Dashboard
            </a>
            <a href="?action=test" class="tab <?= $action === 'test' ? 'active' : '' ?>">
                <i class="fas fa-database"></i> Kiểm Tra DB
            </a>
            <a href="?action=create_admin" class="tab <?= $action === 'create_admin' ? 'active' : '' ?>">
                <i class="fas fa-user-shield"></i> Tạo Admin
            </a>
        </div>

        <div class="content">
            <?php
            // ==================== DASHBOARD ====================
            if ($action === 'dashboard'):
            ?>
                <h2><i class="fas fa-tachometer-alt"></i> Dashboard Dự Án</h2>
                <hr style="margin: 20px 0;">
                
                <div class="grid">
                    <div class="card">
                        <h3><i class="fas fa-home"></i> Trang Công Khai</h3>
                        <ul>
                            <li><i class="fas fa-globe"></i> <a href="public/index.php" target="_blank">Trang Chủ</a></li>
                            <li><i class="fas fa-user-plus"></i> <a href="public/customer_register.php" target="_blank">Đăng Ký KH</a></li>
                            <li><i class="fas fa-sign-in-alt"></i> <a href="public/customer_login.php" target="_blank">Đăng Nhập KH</a></li>
                        </ul>
                    </div>

                    <div class="card">
                        <h3><i class="fas fa-user-tie"></i> Quản Lý Admin</h3>
                        <ul>
                            <li><i class="fas fa-sign-in-alt"></i> <a href="admin/admin_login.php" target="_blank">Đăng Nhập Admin</a></li>
                            <li><i class="fas fa-tachometer-alt"></i> <a href="admin/dashboard.php" target="_blank">Dashboard</a></li>
                            <li><i class="fas fa-users"></i> <a href="admin/quan_ly_nhan_vien.php" target="_blank">QL Nhân Viên</a></li>
                            <li><i class="fas fa-utensils"></i> <a href="admin/quan_ly_mon_an.php" target="_blank">QL Món Ăn</a></li>
                            <li><i class="fas fa-table"></i> <a href="admin/quan_ly_ban.php" target="_blank">QL Bàn Ăn</a></li>
                            <li><i class="fas fa-receipt"></i> <a href="admin/quan_ly_hoa_don.php" target="_blank">QL Hóa Đơn</a></li>
                            <li><i class="fas fa-chart-line"></i> <a href="admin/quan_ly_doanh_thu.php" target="_blank">Doanh Thu</a></li>
                        </ul>
                    </div>

                    <div class="card">
                        <h3><i class="fas fa-key"></i> Tài Khoản Mặc Định</h3>
                        <ul>
                            <li><i class="fas fa-user-shield"></i> <strong>Admin:</strong> Mã NV (xem tab Tạo Admin)</li>
                            <li><i class="fas fa-lock"></i> <strong>Mật khẩu:</strong> admin123</li>
                            <li><i class="fas fa-user"></i> <strong>Khách 1:</strong> 0988776655</li>
                            <li><i class="fas fa-user"></i> <strong>Khách 2:</strong> 0911223344</li>
                        </ul>
                    </div>

                    <?php if ($dbConnected): ?>
                    <div class="card">
                        <h3><i class="fas fa-database"></i> Thống Kê</h3>
                        <ul>
                            <?php
                            $tables = ['nhan_vien' => 'Nhân viên', 'khach_hang' => 'Khách hàng', 
                                      'mon_an' => 'Món ăn', 'ban_an' => 'Bàn ăn', 'hoa_don' => 'Hóa đơn'];
                            foreach ($tables as $table => $name) {
                                try {
                                    $stmt = $conn->query("SELECT COUNT(*) as count FROM $table");
                                    $count = $stmt->fetch()['count'];
                                    echo "<li><i class='fas fa-check' style='color: green;'></i> $name: <strong>$count</strong></li>";
                                } catch (Exception $e) {
                                    echo "<li><i class='fas fa-times' style='color: red;'></i> $name: Lỗi</li>";
                                }
                            }
                            ?>
                        </ul>
                    </div>
                    <?php endif; ?>
                </div>

            <?php
            // ==================== TEST CONNECTION ====================
            elseif ($action === 'test'):
            ?>
                <h2><i class="fas fa-database"></i> Kiểm Tra Kết Nối Database</h2>
                <hr style="margin: 20px 0;">

                <?php if ($dbConnected): ?>
                    <div class="success">
                        <h3>✅ KẾT NỐI THÀNH CÔNG!</h3>
                        <p>Database <code>quanlynhahang_db</code> đã sẵn sàng.</p>
                    </div>

                    <h3>📊 Danh sách bảng:</h3>
                    <table>
                        <tr><th>Bảng</th><th>Tên</th><th>Số dòng</th><th>Trạng thái</th></tr>
                        <?php
                        $tables = [
                            'nhan_vien' => 'Nhân viên', 'khach_hang' => 'Khách hàng',
                            'mon_an' => 'Món ăn', 'ban_an' => 'Bàn ăn', 'hoa_don' => 'Hóa đơn',
                            'chi_tiet_hoa_don' => 'Chi tiết HĐ', 'chuc_vu' => 'Chức vụ',
                            'danh_muc_mon_an' => 'Danh mục', 'dat_ban' => 'Đặt bàn', 'tai_khoan' => 'Tài khoản'
                        ];
                        foreach ($tables as $table => $name) {
                            try {
                                $stmt = $conn->query("SELECT COUNT(*) as count FROM $table");
                                $count = $stmt->fetch()['count'];
                                echo "<tr><td><code>$table</code></td><td>$name</td><td><strong>$count</strong></td><td style='color:green;'>✅ OK</td></tr>";
                            } catch (Exception $e) {
                                echo "<tr><td><code>$table</code></td><td>$name</td><td>-</td><td style='color:red;'>❌ Lỗi</td></tr>";
                            }
                        }
                        ?>
                    </table>
                <?php else: ?>
                    <div class="error">
                        <h3>❌ LỖI KẾT NỐI!</h3>
                        <p><strong>Chi tiết:</strong> <?= htmlspecialchars($dbError ?? 'Không thể kết nối') ?></p>
                        <h4>Giải pháp:</h4>
                        <ol>
                            <li>Mở Laragon và click <strong>Start All</strong></li>
                            <li>Tạo database <code>quanlynhahang_db</code> trong phpMyAdmin</li>
                            <li>Import file <code>database/quanlynhahang_db.sql</code></li>
                            <li>Kiểm tra file <code>config/db.php</code></li>
                        </ol>
                    </div>
                <?php endif; ?>

            <?php
            // ==================== CREATE ADMIN ====================
            elseif ($action === 'create_admin' && $dbConnected):
                try {
                    // Thêm cột MatKhau nếu chưa có
                    $stmt = $conn->query("SHOW COLUMNS FROM nhan_vien LIKE 'MatKhau'");
                    if (!$stmt->fetch()) {
                        $conn->exec("ALTER TABLE `nhan_vien` ADD COLUMN `MatKhau` VARCHAR(255) NULL");
                        echo "<div class='info'>✅ Đã thêm cột MatKhau</div>";
                    }

                    // Xóa admin cũ và tạo mới
                    $conn->exec("DELETE FROM nhan_vien WHERE SoDienThoai = '0999999999'");
                    $matKhau = password_hash('admin123', PASSWORD_DEFAULT);
                    
                    $stmt = $conn->prepare("INSERT INTO nhan_vien (HoTen, SoDienThoai, Email, DiaChi, MaChucVu, NgayVaoLam, TrangThai, MatKhau) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute(['ADMIN - Quản Trị Viên', '0999999999', 'admin@nhahang.com', 'Trụ sở chính', 1, date('Y-m-d'), 'Đang làm việc', $matKhau]);
                    
                    $adminId = $conn->lastInsertId();
                    
                    // Cập nhật mật khẩu cho nhân viên cũ
                    $conn->exec("UPDATE nhan_vien SET MatKhau = '$matKhau' WHERE MatKhau IS NULL OR MatKhau = ''");
            ?>
                <h2><i class="fas fa-user-shield"></i> Tạo Tài Khoản Admin</h2>
                <hr style="margin: 20px 0;">

                <div class="success">
                    <h3>✅ TẠO THÀNH CÔNG!</h3>
                </div>

                <div class="info">
                    <h3>📋 THÔNG TIN ĐĂNG NHẬP:</h3>
                    <table>
                        <tr><th>Thông tin</th><th>Giá trị</th></tr>
                        <tr><td><strong>Mã nhân viên:</strong></td><td><code><?= $adminId ?></code></td></tr>
                        <tr><td><strong>Mật khẩu:</strong></td><td><code>admin123</code></td></tr>
                        <tr><td><strong>Họ tên:</strong></td><td>ADMIN - Quản Trị Viên</td></tr>
                        <tr><td><strong>SĐT:</strong></td><td>0999999999</td></tr>
                        <tr><td><strong>Chức vụ:</strong></td><td>Quản lý (Cao nhất)</td></tr>
                    </table>
                </div>

                <div class="warning">
                    <h3>⚠️ LƯU Ý:</h3>
                    <ul>
                        <li>Lưu lại <strong>Mã nhân viên: <?= $adminId ?></strong></li>
                        <li>Mật khẩu mặc định: <code>admin123</code></li>
                        <li>Đã cập nhật mật khẩu cho tất cả nhân viên</li>
                    </ul>
                </div>

                <h3>👥 Danh Sách Nhân Viên:</h3>
                <table>
                    <tr><th>Mã NV</th><th>Họ tên</th><th>SĐT</th><th>Chức vụ</th></tr>
                    <?php
                    $stmt = $conn->query("SELECT nv.MaNhanVien, nv.HoTen, nv.SoDienThoai, cv.TenChucVu FROM nhan_vien nv LEFT JOIN chuc_vu cv ON nv.MaChucVu = cv.MaChucVu ORDER BY nv.MaNhanVien");
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $highlight = ($row['MaNhanVien'] == $adminId) ? "style='background:#ffffcc;font-weight:bold;'" : "";
                        echo "<tr $highlight><td>{$row['MaNhanVien']}</td><td>{$row['HoTen']}</td><td>{$row['SoDienThoai']}</td><td>{$row['TenChucVu']}</td></tr>";
                    }
                    ?>
                </table>

                <div style="text-align: center; margin-top: 30px;">
                    <a href="admin/admin_login.php" class="btn"><i class="fas fa-sign-in-alt"></i> Đăng nhập ngay</a>
                    <a href="?action=dashboard" class="btn"><i class="fas fa-home"></i> Về Dashboard</a>
                </div>

            <?php
                } catch (PDOException $e) {
                    echo "<div class='error'><h3>❌ LỖI:</h3><p>" . htmlspecialchars($e->getMessage()) . "</p></div>";
                }
            elseif ($action === 'create_admin' && !$dbConnected):
                echo "<div class='error'><h3>❌ Không thể tạo admin!</h3><p>Vui lòng kiểm tra kết nối database ở tab <strong>Kiểm Tra DB</strong></p></div>";
            endif;
            ?>
        </div>
    </div>
</body>
</html>
