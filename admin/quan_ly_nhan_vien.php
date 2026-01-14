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
    <link rel="stylesheet" href="includes/admin_layout.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
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
    <div class="admin-layout">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="main-content">
            <!-- Top Bar -->
            <div class="top-bar">
                <h1><i class="fas fa-users"></i> Quản lý nhân viên</h1>
                <span style="color: #666; font-size: 14px;">
                    <i class="fas fa-calendar"></i> <?php echo date('d/m/Y'); ?>
                </span>
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
        </main>
    </div>
</body>
</html>
