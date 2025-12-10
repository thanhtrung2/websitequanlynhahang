<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: admin_login.php");
    exit();
}

$message = '';
$error = '';

// Xử lý cập nhật trạng thái
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $maHoaDon = intval($_POST['MaHoaDon']);
    $action = $_POST['action'];
    
    try {
        if ($action === 'confirm') {
            $stmt = $conn->prepare("UPDATE hoa_don SET TrangThai = 'Đã thanh toán', MaNhanVien = ?, ThoiGianRa = NOW() WHERE MaHoaDon = ?");
            $stmt->execute([$_SESSION['user_id'], $maHoaDon]);
            $message = 'Đã xác nhận thanh toán hóa đơn #' . $maHoaDon;
        } elseif ($action === 'cancel') {
            $stmt = $conn->prepare("UPDATE hoa_don SET TrangThai = 'Đã hủy' WHERE MaHoaDon = ?");
            $stmt->execute([$maHoaDon]);
            $message = 'Đã hủy hóa đơn #' . $maHoaDon;
        }
    } catch(PDOException $e) {
        $error = 'Lỗi: ' . $e->getMessage();
    }
}

// Lọc theo trạng thái
$filter = $_GET['filter'] ?? 'all';
$filterSQL = '';
if ($filter === 'pending') {
    $filterSQL = "WHERE hd.TrangThai = 'Chưa thanh toán'";
} elseif ($filter === 'paid') {
    $filterSQL = "WHERE hd.TrangThai = 'Đã thanh toán'";
} elseif ($filter === 'cancelled') {
    $filterSQL = "WHERE hd.TrangThai = 'Đã hủy'";
}

try {
    $stmt = $conn->query("SELECT hd.*, kh.HoTen as TenKH, nv.HoTen as TenNV 
                          FROM hoa_don hd 
                          LEFT JOIN khach_hang kh ON hd.MaKhachHang = kh.MaKhachHang 
                          LEFT JOIN nhan_vien nv ON hd.MaNhanVien = nv.MaNhanVien 
                          $filterSQL
                          ORDER BY hd.ThoiGianVao DESC");
    $hoaDons = $stmt->fetchAll();
    
    // Đếm số đơn theo trạng thái
    $stmt = $conn->query("SELECT TrangThai, COUNT(*) as total FROM hoa_don GROUP BY TrangThai");
    $counts = [];
    while ($row = $stmt->fetch()) {
        $counts[$row['TrangThai']] = $row['total'];
    }
} catch(PDOException $e) {
    die("Lỗi: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Nhà hàng 3CE - Quản lý hóa đơn</title>
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
        .filter-tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .filter-tab {
            padding: 10px 20px;
            background: white;
            border: 2px solid #001f3f;
            border-radius: 25px;
            text-decoration: none;
            color: #001f3f;
            font-weight: 500;
            transition: all 0.3s;
        }
        .filter-tab:hover, .filter-tab.active {
            background: #001f3f;
            color: #F5F5DC;
        }
        .filter-tab .badge {
            background: #dc3545;
            color: white;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 12px;
            margin-left: 5px;
        }
        .filter-tab.active .badge {
            background: #F5F5DC;
            color: #001f3f;
        }
        .status-badge {
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 13px;
            font-weight: 600;
        }
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        .status-paid {
            background: #d4edda;
            color: #155724;
        }
        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
        }
        .btn-action {
            padding: 6px 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 13px;
            margin: 2px;
        }
        .btn-confirm {
            background: #28a745;
            color: white;
        }
        .btn-cancel {
            background: #dc3545;
            color: white;
        }
        .btn-view {
            background: #007bff;
            color: white;
            text-decoration: none;
            display: inline-block;
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
        .modal.active {
            display: flex;
        }
        .modal-content {
            background: white;
            border-radius: 15px;
            padding: 30px;
            max-width: 600px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
        }
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #eee;
        }
        .close-btn {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
        }
        .detail-item {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <div class="logo">
                <i class="fas fa-file-invoice"></i>
                <span>Quản lý hóa đơn</span>
            </div>
            <a href="dashboard.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
        </div>
    </div>

    <div class="container">
        <div class="page-header">
            <h1><i class="fas fa-file-invoice"></i> Danh sách hóa đơn</h1>
            <p>Quản lý các hóa đơn trong hệ thống</p>
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

        <div class="filter-tabs">
            <a href="?filter=all" class="filter-tab <?php echo $filter === 'all' ? 'active' : ''; ?>">
                <i class="fas fa-list"></i> Tất cả
            </a>
            <a href="?filter=pending" class="filter-tab <?php echo $filter === 'pending' ? 'active' : ''; ?>">
                <i class="fas fa-clock"></i> Chờ thanh toán
                <?php if (($counts['Chưa thanh toán'] ?? 0) > 0): ?>
                <span class="badge"><?php echo $counts['Chưa thanh toán']; ?></span>
                <?php endif; ?>
            </a>
            <a href="?filter=paid" class="filter-tab <?php echo $filter === 'paid' ? 'active' : ''; ?>">
                <i class="fas fa-check"></i> Đã thanh toán
            </a>
            <a href="?filter=cancelled" class="filter-tab <?php echo $filter === 'cancelled' ? 'active' : ''; ?>">
                <i class="fas fa-times"></i> Đã hủy
            </a>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Mã HĐ</th>
                        <th>Khách hàng</th>
                        <th>Nhân viên xử lý</th>
                        <th>Thời gian</th>
                        <th>Tổng tiền</th>
                        <th>Trạng thái</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($hoaDons)): ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 40px; color: #666;">
                            <i class="fas fa-inbox" style="font-size: 40px; margin-bottom: 10px; display: block;"></i>
                            Không có hóa đơn nào
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($hoaDons as $hd): ?>
                    <tr>
                        <td><strong>#<?php echo htmlspecialchars($hd['MaHoaDon']); ?></strong></td>
                        <td><?php echo htmlspecialchars($hd['TenKH'] ?? 'Khách vãng lai'); ?></td>
                        <td><?php echo htmlspecialchars($hd['TenNV'] ?? '<em>Chưa xử lý</em>'); ?></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($hd['ThoiGianVao'])); ?></td>
                        <td style="font-weight: bold; color: #003366;">
                            <?php echo number_format($hd['TongTien'] ?? 0, 0, ',', '.'); ?>đ
                        </td>
                        <td>
                            <span class="status-badge <?php 
                                echo $hd['TrangThai'] === 'Đã thanh toán' ? 'status-paid' : 
                                    ($hd['TrangThai'] === 'Đã hủy' ? 'status-cancelled' : 'status-pending'); 
                            ?>">
                                <?php echo htmlspecialchars($hd['TrangThai']); ?>
                            </span>
                        </td>
                        <td>
                            <button class="btn-action btn-view" onclick="viewDetail(<?php echo $hd['MaHoaDon']; ?>)">
                                <i class="fas fa-eye"></i>
                            </button>
                            <?php if ($hd['TrangThai'] === 'Chưa thanh toán'): ?>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="MaHoaDon" value="<?php echo $hd['MaHoaDon']; ?>">
                                <button type="submit" name="action" value="confirm" class="btn-action btn-confirm" onclick="return confirm('Xác nhận thanh toán hóa đơn này?')">
                                    <i class="fas fa-check"></i>
                                </button>
                                <button type="submit" name="action" value="cancel" class="btn-action btn-cancel" onclick="return confirm('Hủy hóa đơn này?')">
                                    <i class="fas fa-times"></i>
                                </button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Chi tiết hóa đơn -->
    <div id="detailModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-file-invoice"></i> Chi tiết hóa đơn</h2>
                <button class="close-btn" onclick="closeModal()">&times;</button>
            </div>
            <div id="detailContent">
                <p style="text-align: center;"><i class="fas fa-spinner fa-spin"></i> Đang tải...</p>
            </div>
        </div>
    </div>

    <script>
        function viewDetail(maHoaDon) {
            document.getElementById('detailModal').classList.add('active');
            fetch('get_hoa_don_detail.php?id=' + maHoaDon)
                .then(response => response.text())
                .then(html => {
                    document.getElementById('detailContent').innerHTML = html;
                })
                .catch(error => {
                    document.getElementById('detailContent').innerHTML = '<p style="color: red;">Lỗi tải dữ liệu</p>';
                });
        }
        
        function closeModal() {
            document.getElementById('detailModal').classList.remove('active');
        }
        
        document.getElementById('detailModal').addEventListener('click', function(e) {
            if (e.target === this) closeModal();
        });
    </script>
</body>
</html>
