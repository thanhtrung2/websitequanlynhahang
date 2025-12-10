<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Lấy danh sách nhân viên
try {
    $stmt = $conn->query("SELECT nv.*, cv.TenChucVu FROM nhan_vien nv LEFT JOIN chuc_vu cv ON nv.MaChucVu = cv.MaChucVu ORDER BY nv.MaNhanVien");
    $nhanViens = $stmt->fetchAll();
} catch(PDOException $e) {
    die("Lỗi: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nhà hàng 3CE - Quản lý nhân viên</title>
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
            transition: all 0.3s;
        }
        .back-btn:hover {
            background: #E8E4C9;
        }
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        .page-header {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            margin-bottom: 30px;
        }
        .page-header h1 {
            color: #001f3f;
            margin-bottom: 10px;
        }
        .table-container {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: linear-gradient(135deg, #001f3f 0%, #003366 100%);
            color: #F5F5DC;
            font-weight: 600;
        }
        tr:hover {
            background: #f5f5f5;
        }
        .btn-add {
            display: inline-block;
            padding: 10px 20px;
            background: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .btn-add:hover {
            background: #218838;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <div class="logo">
                <i class="fas fa-users"></i>
                <span>Quản lý nhân viên</span>
            </div>
            <a href="dashboard.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
        </div>
    </div>

    <div class="container">
        <div class="page-header">
            <h1><i class="fas fa-users"></i> Danh sách nhân viên</h1>
            <p>Quản lý thông tin nhân viên trong hệ thống</p>
        </div>

        <div class="table-container">
            <a href="register.php" class="btn-add">
                <i class="fas fa-plus"></i> Thêm nhân viên mới
            </a>
            
            <table>
                <thead>
                    <tr>
                        <th>Mã NV</th>
                        <th>Tên nhân viên</th>
                        <th>Số điện thoại</th>
                        <th>Chức vụ</th>
                        <th>Lương</th>
                        <th>Ngày vào làm</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($nhanViens as $nv): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($nv['MaNhanVien']); ?></td>
                        <td><?php echo htmlspecialchars($nv['HoTen']); ?></td>
                        <td><?php echo htmlspecialchars($nv['SoDienThoai'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($nv['TenChucVu'] ?? 'N/A'); ?></td>
                        <td>N/A</td>
                        <td><?php echo date('d/m/Y', strtotime($nv['NgayVaoLam'] ?? date('Y-m-d'))); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
