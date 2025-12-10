<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['customer_id'])) {
    header("Location: customer_login.php");
    exit();
}

$maKhachHang = $_SESSION['customer_id'];

// Lấy danh sách hóa đơn của khách hàng
$hoaDons = [];
try {
    $stmt = $conn->prepare("SELECT * FROM hoa_don WHERE MaKhachHang = ? ORDER BY ThoiGianVao DESC");
    $stmt->execute([$maKhachHang]);
    $hoaDons = $stmt->fetchAll();
} catch(PDOException $e) {
    // Handle error
}

// Xem chi tiết hóa đơn
$chiTiet = [];
$selectedHoaDon = null;
if (isset($_GET['view'])) {
    $maHoaDon = intval($_GET['view']);
    try {
        $stmt = $conn->prepare("SELECT * FROM hoa_don WHERE MaHoaDon = ? AND MaKhachHang = ?");
        $stmt->execute([$maHoaDon, $maKhachHang]);
        $selectedHoaDon = $stmt->fetch();
        
        if ($selectedHoaDon) {
            $stmt = $conn->prepare("SELECT ct.*, ma.TenMonAn, ma.HinhAnh FROM chi_tiet_hoa_don ct JOIN mon_an ma ON ct.MaMonAn = ma.MaMonAn WHERE ct.MaHoaDon = ?");
            $stmt->execute([$maHoaDon]);
            $chiTiet = $stmt->fetchAll();
        }
    } catch(PDOException $e) {
        // Handle error
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nhà hàng 3CE - Lịch sử đơn hàng</title>
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
        .page-header h1 { color: #001f3f; }
        .order-list {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        }
        .order-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            border-bottom: 1px solid #eee;
            flex-wrap: wrap;
            gap: 15px;
        }
        .order-item:last-child { border-bottom: none; }
        .order-info h3 {
            color: #001f3f;
            margin-bottom: 5px;
        }
        .order-info p {
            color: #666;
            font-size: 14px;
        }
        .order-total {
            font-size: 20px;
            font-weight: bold;
            color: #003366;
        }
        .order-status {
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
        }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-paid { background: #d4edda; color: #155724; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
        .btn-view {
            padding: 8px 20px;
            background: #001f3f;
            color: #F5F5DC;
            text-decoration: none;
            border-radius: 5px;
            font-size: 14px;
        }
        .detail-section {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .detail-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 2px solid #eee;
        }
        .detail-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }
        .detail-item-image {
            width: 60px;
            height: 60px;
            border-radius: 8px;
            object-fit: cover;
        }
        .detail-item-placeholder {
            width: 60px;
            height: 60px;
            border-radius: 8px;
            background: linear-gradient(135deg, #001f3f 0%, #003366 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #F5F5DC;
        }
        .detail-item-info { flex: 1; }
        .detail-item-name { font-weight: 600; color: #001f3f; }
        .detail-item-price { color: #666; font-size: 14px; }
        .detail-item-total { font-weight: bold; color: #003366; }
        .empty-state {
            text-align: center;
            padding: 50px;
            color: #666;
        }
        .empty-state i {
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
                <i class="fas fa-history"></i>
                <span>Lịch sử đơn hàng</span>
            </div>
            <a href="customer_dashboard.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
        </div>
    </div>

    <div class="container">
        <?php if ($selectedHoaDon): ?>
        <div class="detail-section">
            <div class="detail-header">
                <div>
                    <h2>Đơn hàng #<?php echo $selectedHoaDon['MaHoaDon']; ?></h2>
                    <p style="color: #666;"><?php echo date('d/m/Y H:i', strtotime($selectedHoaDon['ThoiGianVao'])); ?></p>
                </div>
                <a href="lich_su_don_hang.php" class="back-btn">
                    <i class="fas fa-list"></i> Xem tất cả
                </a>
            </div>
            
            <?php foreach ($chiTiet as $item): ?>
            <div class="detail-item">
                <?php if (!empty($item['HinhAnh'])): ?>
                    <img src="<?php echo htmlspecialchars($item['HinhAnh']); ?>" class="detail-item-image">
                <?php else: ?>
                    <div class="detail-item-placeholder"><i class="fas fa-utensils"></i></div>
                <?php endif; ?>
                <div class="detail-item-info">
                    <div class="detail-item-name"><?php echo htmlspecialchars($item['TenMonAn']); ?></div>
                    <div class="detail-item-price"><?php echo number_format($item['DonGia'], 0, ',', '.'); ?>đ x <?php echo $item['SoLuong']; ?></div>
                </div>
                <div class="detail-item-total"><?php echo number_format($item['ThanhTien'], 0, ',', '.'); ?>đ</div>
            </div>
            <?php endforeach; ?>
            
            <div style="margin-top: 20px; padding-top: 20px; border-top: 2px solid #001f3f; text-align: right;">
                <span style="font-size: 20px;">Tổng cộng: </span>
                <span style="font-size: 24px; font-weight: bold; color: #003366;"><?php echo number_format($selectedHoaDon['TongTien'], 0, ',', '.'); ?>đ</span>
            </div>
        </div>
        <?php endif; ?>

        <div class="page-header">
            <h1><i class="fas fa-history"></i> Lịch sử đơn hàng</h1>
            <p>Xem các đơn hàng bạn đã đặt</p>
        </div>

        <div class="order-list">
            <?php if (empty($hoaDons)): ?>
            <div class="empty-state">
                <i class="fas fa-receipt"></i>
                <h3>Chưa có đơn hàng nào</h3>
                <p>Hãy đặt món để bắt đầu</p>
            </div>
            <?php else: ?>
                <?php foreach ($hoaDons as $hd): ?>
                <div class="order-item">
                    <div class="order-info">
                        <h3>Đơn hàng #<?php echo $hd['MaHoaDon']; ?></h3>
                        <p><i class="fas fa-calendar"></i> <?php echo date('d/m/Y H:i', strtotime($hd['ThoiGianVao'])); ?></p>
                    </div>
                    <div class="order-total"><?php echo number_format($hd['TongTien'], 0, ',', '.'); ?>đ</div>
                    <span class="order-status <?php 
                        echo $hd['TrangThai'] === 'Đã thanh toán' ? 'status-paid' : 
                            ($hd['TrangThai'] === 'Đã hủy' ? 'status-cancelled' : 'status-pending'); 
                    ?>">
                        <?php echo htmlspecialchars($hd['TrangThai']); ?>
                    </span>
                    <a href="?view=<?php echo $hd['MaHoaDon']; ?>" class="btn-view">
                        <i class="fas fa-eye"></i> Chi tiết
                    </a>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>