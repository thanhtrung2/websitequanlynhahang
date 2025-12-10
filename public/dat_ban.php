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

// Xử lý đặt bàn
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dat_ban'])) {
    $maBan = !empty($_POST['MaBan']) ? $_POST['MaBan'] : null;
    $thoiGianDat = $_POST['ThoiGianDat'] ?? '';
    $soLuongKhach = intval($_POST['SoLuongKhach'] ?? 0);
    $ghiChu = trim($_POST['GhiChu'] ?? '');
    
    if (empty($thoiGianDat) || $soLuongKhach < 1) {
        $error = 'Vui lòng điền đầy đủ thông tin!';
    } else {
        try {
            $stmt = $conn->prepare("INSERT INTO dat_ban (MaKhachHang, MaBan, ThoiGianDat, SoLuongKhach, GhiChu, TrangThai) VALUES (?, ?, ?, ?, ?, 'Chờ xác nhận')");
            $stmt->execute([$maKhachHang, $maBan, $thoiGianDat, $soLuongKhach, $ghiChu]);
            $message = 'Đặt bàn thành công! Chúng tôi sẽ liên hệ xác nhận sớm nhất.';
        } catch(PDOException $e) {
            $error = 'Lỗi: ' . $e->getMessage();
        }
    }
}

// Xử lý hủy đặt bàn
if (isset($_GET['cancel'])) {
    $maDatBan = intval($_GET['cancel']);
    try {
        $stmt = $conn->prepare("UPDATE dat_ban SET TrangThai = 'Đã hủy' WHERE MaDatBan = ? AND MaKhachHang = ? AND TrangThai = 'Chờ xác nhận'");
        $stmt->execute([$maDatBan, $maKhachHang]);
        if ($stmt->rowCount() > 0) {
            $message = 'Đã hủy đặt bàn thành công!';
        }
    } catch(PDOException $e) {
        $error = 'Lỗi: ' . $e->getMessage();
    }
}

// Lấy danh sách bàn trống
$bans = [];
try {
    $stmt = $conn->query("SELECT * FROM ban_an WHERE TrangThai = 'Trống' ORDER BY MaBan");
    $bans = $stmt->fetchAll();
} catch(PDOException $e) {
    $bans = [];
}

// Lấy lịch sử đặt bàn của khách hàng
$lichSuDatBan = [];
try {
    $stmt = $conn->prepare("SELECT db.*, ba.TenBan, ba.ViTri FROM dat_ban db LEFT JOIN ban_an ba ON db.MaBan = ba.MaBan WHERE db.MaKhachHang = ? ORDER BY db.ThoiGianDat DESC");
    $stmt->execute([$maKhachHang]);
    $lichSuDatBan = $stmt->fetchAll();
} catch(PDOException $e) {
    $lichSuDatBan = [];
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nhà hàng 3CE - Đặt bàn</title>
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
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .alert-success { background: #d4edda; color: #155724; }
        .alert-error { background: #f8d7da; color: #721c24; }
        .card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .card h2 {
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
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
        }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
            outline: none;
            border-color: #001f3f;
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
        .table-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        .table-item {
            padding: 15px;
            border: 2px solid #ddd;
            border-radius: 10px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        .table-item:hover, .table-item.selected {
            border-color: #001f3f;
            background: #f0f8ff;
        }
        .table-item i {
            font-size: 30px;
            color: #001f3f;
            margin-bottom: 10px;
            display: block;
        }
        .table-item h4 { color: #001f3f; margin-bottom: 5px; }
        .table-item p { color: #666; font-size: 12px; margin: 0; }
        .history-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            border-bottom: 1px solid #eee;
            flex-wrap: wrap;
            gap: 10px;
        }
        .history-item:last-child { border-bottom: none; }
        .status-badge {
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 600;
        }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-confirmed { background: #d4edda; color: #155724; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
        .status-done { background: #cce5ff; color: #004085; }
        .btn-cancel {
            padding: 5px 15px;
            background: #dc3545;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 12px;
            cursor: pointer;
            text-decoration: none;
        }
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        .empty-state i {
            font-size: 50px;
            color: #ddd;
            margin-bottom: 15px;
            display: block;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <div class="logo">
                <i class="fas fa-chair"></i>
                <span>Đặt bàn</span>
            </div>
            <a href="customer_dashboard.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
        </div>
    </div>

    <div class="container">
        <div class="page-header">
            <h1><i class="fas fa-chair"></i> Đặt bàn trước</h1>
            <p>Đặt bàn để đảm bảo chỗ ngồi khi đến nhà hàng</p>
        </div>

        <?php if ($message): ?>
        <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
        <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="card">
            <h2><i class="fas fa-calendar-plus"></i> Đặt bàn mới</h2>
            <form method="POST">
                <div class="form-group">
                    <label><i class="fas fa-clock"></i> Thời gian đến *</label>
                    <input type="datetime-local" name="ThoiGianDat" required min="<?php echo date('Y-m-d\TH:i'); ?>">
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-users"></i> Số lượng khách *</label>
                    <input type="number" name="SoLuongKhach" min="1" max="50" required placeholder="Nhập số người">
                </div>
                
                <?php if (!empty($bans)): ?>
                <div class="form-group">
                    <label><i class="fas fa-chair"></i> Chọn bàn (tùy chọn)</label>
                    <div class="table-list">
                        <?php foreach ($bans as $ban): ?>
                        <div class="table-item" onclick="selectTable(this, <?php echo $ban['MaBan']; ?>)">
                            <input type="radio" name="MaBan" value="<?php echo $ban['MaBan']; ?>" style="display: none;">
                            <i class="fas fa-chair"></i>
                            <h4><?php echo htmlspecialchars($ban['TenBan']); ?></h4>
                            <p><?php echo $ban['SoGhe']; ?> chỗ</p>
                            <p><?php echo htmlspecialchars($ban['ViTri'] ?? ''); ?></p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <small style="color: #666;">* Nếu không chọn, nhà hàng sẽ sắp xếp bàn phù hợp</small>
                </div>
                <?php endif; ?>
                
                <div class="form-group">
                    <label><i class="fas fa-sticky-note"></i> Ghi chú</label>
                    <textarea name="GhiChu" rows="3" placeholder="Yêu cầu đặc biệt (ghế trẻ em, vị trí yêu thích...)"></textarea>
                </div>
                
                <button type="submit" name="dat_ban" class="btn btn-primary">
                    <i class="fas fa-check"></i> Xác nhận đặt bàn
                </button>
            </form>
        </div>

        <div class="card">
            <h2><i class="fas fa-history"></i> Lịch sử đặt bàn</h2>
            <?php if (empty($lichSuDatBan)): ?>
            <div class="empty-state">
                <i class="fas fa-calendar-times"></i>
                <h3>Chưa có lịch sử đặt bàn</h3>
                <p>Hãy đặt bàn để đảm bảo chỗ ngồi</p>
            </div>
            <?php else: ?>
                <?php foreach ($lichSuDatBan as $dat): ?>
                <div class="history-item">
                    <div>
                        <strong><?php echo htmlspecialchars($dat['TenBan'] ?? 'Chưa xếp bàn'); ?></strong>
                        <p style="color: #666; font-size: 14px; margin-top: 5px;">
                            <i class="fas fa-calendar"></i> <?php echo date('d/m/Y H:i', strtotime($dat['ThoiGianDat'])); ?>
                            | <i class="fas fa-users"></i> <?php echo $dat['SoLuongKhach']; ?> người
                        </p>
                        <?php if (!empty($dat['GhiChu'])): ?>
                        <p style="color: #888; font-size: 13px; margin-top: 3px;">
                            <i class="fas fa-sticky-note"></i> <?php echo htmlspecialchars($dat['GhiChu']); ?>
                        </p>
                        <?php endif; ?>
                    </div>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span class="status-badge <?php 
                            echo $dat['TrangThai'] === 'Chờ xác nhận' ? 'status-pending' : 
                                ($dat['TrangThai'] === 'Đã xác nhận' ? 'status-confirmed' : 
                                ($dat['TrangThai'] === 'Đã hủy' ? 'status-cancelled' : 'status-done')); 
                        ?>">
                            <?php echo htmlspecialchars($dat['TrangThai']); ?>
                        </span>
                        <?php if ($dat['TrangThai'] === 'Chờ xác nhận'): ?>
                        <a href="?cancel=<?php echo $dat['MaDatBan']; ?>" class="btn-cancel" onclick="return confirm('Bạn có chắc muốn hủy đặt bàn này?')">
                            <i class="fas fa-times"></i> Hủy
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function selectTable(el, maBan) {
            // Bỏ chọn tất cả
            document.querySelectorAll('.table-item').forEach(function(item) {
                item.classList.remove('selected');
                item.querySelector('input[type="radio"]').checked = false;
            });
            // Chọn bàn này
            el.classList.add('selected');
            el.querySelector('input[type="radio"]').checked = true;
        }
    </script>
</body>
</html>