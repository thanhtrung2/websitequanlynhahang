<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['customer_id'])) {
    header("Location: customer_login.php");
    exit();
}

$maKhachHang = $_SESSION['customer_id'];

// Tự động tạo bảng nếu chưa tồn tại
try {
    $conn->exec("CREATE TABLE IF NOT EXISTS thong_bao (
        MaThongBao INT AUTO_INCREMENT PRIMARY KEY,
        MaKhachHang INT NOT NULL,
        TieuDe VARCHAR(200) NOT NULL,
        NoiDung TEXT NOT NULL,
        LoaiThongBao ENUM('lien_he', 'dat_ban', 'don_hang', 'he_thong') DEFAULT 'he_thong',
        MaLienKet INT,
        DaDoc TINYINT(1) DEFAULT 0,
        ThoiGianTao DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
} catch(PDOException $e) {}

// Đánh dấu đã đọc
if (isset($_GET['read'])) {
    $maThongBao = intval($_GET['read']);
    try {
        $stmt = $conn->prepare("UPDATE thong_bao SET DaDoc = 1 WHERE MaThongBao = ? AND MaKhachHang = ?");
        $stmt->execute([$maThongBao, $maKhachHang]);
    } catch(PDOException $e) {}
}

// Đánh dấu tất cả đã đọc
if (isset($_GET['read_all'])) {
    try {
        $stmt = $conn->prepare("UPDATE thong_bao SET DaDoc = 1 WHERE MaKhachHang = ?");
        $stmt->execute([$maKhachHang]);
    } catch(PDOException $e) {}
    header("Location: thong_bao.php");
    exit();
}

// Xóa thông báo
if (isset($_GET['delete'])) {
    $maThongBao = intval($_GET['delete']);
    try {
        $stmt = $conn->prepare("DELETE FROM thong_bao WHERE MaThongBao = ? AND MaKhachHang = ?");
        $stmt->execute([$maThongBao, $maKhachHang]);
    } catch(PDOException $e) {}
    header("Location: thong_bao.php");
    exit();
}

// Lấy danh sách thông báo
$thongBaos = [];
$chuaDoc = 0;
try {
    $stmt = $conn->prepare("SELECT * FROM thong_bao WHERE MaKhachHang = ? ORDER BY ThoiGianTao DESC");
    $stmt->execute([$maKhachHang]);
    $thongBaos = $stmt->fetchAll();
    
    // Đếm chưa đọc
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM thong_bao WHERE MaKhachHang = ? AND DaDoc = 0");
    $stmt->execute([$maKhachHang]);
    $chuaDoc = $stmt->fetch()['total'];
} catch(PDOException $e) {}

// Xem chi tiết
$chiTiet = null;
if (isset($_GET['view'])) {
    $maThongBao = intval($_GET['view']);
    try {
        $stmt = $conn->prepare("SELECT * FROM thong_bao WHERE MaThongBao = ? AND MaKhachHang = ?");
        $stmt->execute([$maThongBao, $maKhachHang]);
        $chiTiet = $stmt->fetch();
        
        // Đánh dấu đã đọc
        if ($chiTiet && !$chiTiet['DaDoc']) {
            $stmt = $conn->prepare("UPDATE thong_bao SET DaDoc = 1 WHERE MaThongBao = ?");
            $stmt->execute([$maThongBao]);
        }
    } catch(PDOException $e) {}
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thông báo - Nhà hàng 3CE</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #F5F5DC 0%, #EDE8D0 100%);
            min-height: 100vh;
            padding-top: 70px;
        }
        .container { max-width: 900px; margin: 30px auto; padding: 0 20px; }
        
        .page-header {
            background: white;
            border-radius: 15px;
            padding: 25px 30px;
            margin-bottom: 25px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        .page-header h1 { color: #001f3f; font-size: 24px; }
        .page-header h1 i { color: #D4AF37; }
        .header-actions { display: flex; gap: 10px; }
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .btn-primary { background: linear-gradient(135deg, #001f3f, #003366); color: #F5F5DC; }
        .btn-outline { background: white; color: #001f3f; border: 2px solid #001f3f; }
        
        .notification-list {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .notification-item {
            padding: 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            gap: 15px;
            align-items: flex-start;
            transition: background 0.3s;
            cursor: pointer;
        }
        .notification-item:hover { background: #f8f9fa; }
        .notification-item:last-child { border-bottom: none; }
        .notification-item.unread { background: #fff8e6; border-left: 4px solid #D4AF37; }
        
        .notification-icon {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }
        .notification-icon.lien_he { background: #e3f2fd; color: #1976d2; }
        .notification-icon.dat_ban { background: #e8f5e9; color: #388e3c; }
        .notification-icon.don_hang { background: #fff3e0; color: #f57c00; }
        .notification-icon.he_thong { background: #fce4ec; color: #c2185b; }
        
        .notification-content { flex: 1; }
        .notification-title {
            font-weight: 600;
            color: #001f3f;
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .notification-title .badge-new {
            background: #dc3545;
            color: white;
            font-size: 10px;
            padding: 2px 8px;
            border-radius: 10px;
        }
        .notification-preview {
            color: #666;
            font-size: 14px;
            line-height: 1.5;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .notification-time {
            color: #999;
            font-size: 12px;
            margin-top: 8px;
        }
        
        .notification-actions {
            display: flex;
            gap: 8px;
        }
        .btn-icon {
            width: 35px;
            height: 35px;
            border: none;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            transition: all 0.3s;
        }
        .btn-icon.view { background: #e3f2fd; color: #1976d2; }
        .btn-icon.delete { background: #ffebee; color: #c62828; }
        .btn-icon:hover { transform: scale(1.1); }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }
        .empty-state i { font-size: 60px; color: #ddd; margin-bottom: 20px; }
        
        /* Modal */
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
            max-width: 500px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
        }
        .modal-header {
            padding: 20px;
            border-bottom: 2px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .modal-header h3 { color: #001f3f; font-size: 18px; }
        .close-btn {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #666;
        }
        .modal-body { padding: 25px; }
        .modal-body .detail-time {
            color: #999;
            font-size: 13px;
            margin-bottom: 15px;
        }
        .modal-body .detail-content {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            line-height: 1.7;
            color: #333;
        }
        
        .stats-bar {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }
        .stat-item {
            background: white;
            padding: 15px 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .stat-item span { font-size: 24px; font-weight: bold; color: #001f3f; }
        .stat-item p { color: #666; font-size: 13px; }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container">
        <div class="stats-bar">
            <div class="stat-item">
                <span><?php echo count($thongBaos); ?></span>
                <p>Tổng thông báo</p>
            </div>
            <div class="stat-item">
                <span style="color: #D4AF37;"><?php echo $chuaDoc; ?></span>
                <p>Chưa đọc</p>
            </div>
        </div>
        
        <div class="page-header">
            <h1><i class="fas fa-bell"></i> Thông báo của bạn</h1>
            <div class="header-actions">
                <?php if ($chuaDoc > 0): ?>
                <a href="?read_all=1" class="btn btn-outline"><i class="fas fa-check-double"></i> Đánh dấu tất cả đã đọc</a>
                <?php endif; ?>
                <a href="customer_dashboard.php" class="btn btn-primary"><i class="fas fa-arrow-left"></i> Quay lại</a>
            </div>
        </div>

        <div class="notification-list">
            <?php if (empty($thongBaos)): ?>
            <div class="empty-state">
                <i class="fas fa-bell-slash"></i>
                <h3>Không có thông báo</h3>
                <p>Bạn chưa có thông báo nào</p>
            </div>
            <?php else: ?>
                <?php foreach ($thongBaos as $tb): ?>
                <div class="notification-item <?php echo !$tb['DaDoc'] ? 'unread' : ''; ?>" onclick="window.location='?view=<?php echo $tb['MaThongBao']; ?>'">
                    <div class="notification-icon <?php echo $tb['LoaiThongBao']; ?>">
                        <i class="fas fa-<?php 
                            echo $tb['LoaiThongBao'] === 'lien_he' ? 'envelope' : 
                                ($tb['LoaiThongBao'] === 'dat_ban' ? 'calendar-check' : 
                                ($tb['LoaiThongBao'] === 'don_hang' ? 'shopping-cart' : 'bell')); 
                        ?>"></i>
                    </div>
                    <div class="notification-content">
                        <div class="notification-title">
                            <?php echo htmlspecialchars($tb['TieuDe']); ?>
                            <?php if (!$tb['DaDoc']): ?>
                            <span class="badge-new">Mới</span>
                            <?php endif; ?>
                        </div>
                        <div class="notification-preview"><?php echo htmlspecialchars($tb['NoiDung']); ?></div>
                        <div class="notification-time">
                            <i class="fas fa-clock"></i> <?php echo date('d/m/Y H:i', strtotime($tb['ThoiGianTao'])); ?>
                        </div>
                    </div>
                    <div class="notification-actions" onclick="event.stopPropagation();">
                        <a href="?view=<?php echo $tb['MaThongBao']; ?>" class="btn-icon view" title="Xem"><i class="fas fa-eye"></i></a>
                        <a href="?delete=<?php echo $tb['MaThongBao']; ?>" class="btn-icon delete" title="Xóa" onclick="return confirm('Xóa thông báo này?')"><i class="fas fa-trash"></i></a>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal Chi tiết -->
    <?php if ($chiTiet): ?>
    <div class="modal active" id="detailModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-bell"></i> <?php echo htmlspecialchars($chiTiet['TieuDe']); ?></h3>
                <a href="thong_bao.php" class="close-btn">&times;</a>
            </div>
            <div class="modal-body">
                <div class="detail-time">
                    <i class="fas fa-clock"></i> <?php echo date('d/m/Y H:i', strtotime($chiTiet['ThoiGianTao'])); ?>
                </div>
                <div class="detail-content">
                    <?php echo nl2br(htmlspecialchars($chiTiet['NoiDung'])); ?>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.getElementById('detailModal').addEventListener('click', function(e) {
            if (e.target === this) window.location.href = 'thong_bao.php';
        });
    </script>
    <?php endif; ?>
</body>
</html>
