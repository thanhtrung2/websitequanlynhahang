<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: admin_login.php");
    exit();
}

$message = '';
$error = '';

// Xử lý cập nhật trạng thái đặt bàn
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $maDatBan = intval($_POST['MaDatBan']);
    $action = $_POST['action'];
    
    try {
        if ($action === 'confirm') {
            $maBan = $_POST['MaBan'] ?? null;
            $stmt = $conn->prepare("UPDATE dat_ban SET TrangThai = 'Đã xác nhận', MaBan = ? WHERE MaDatBan = ?");
            $stmt->execute([$maBan, $maDatBan]);
            
            // Cập nhật trạng thái bàn
            if ($maBan) {
                $stmt = $conn->prepare("UPDATE ban_an SET TrangThai = 'Đã đặt' WHERE MaBan = ?");
                $stmt->execute([$maBan]);
            }
            $message = 'Đã xác nhận đặt bàn!';
        } elseif ($action === 'cancel') {
            $stmt = $conn->prepare("UPDATE dat_ban SET TrangThai = 'Đã hủy' WHERE MaDatBan = ?");
            $stmt->execute([$maDatBan]);
            $message = 'Đã hủy đặt bàn!';
        } elseif ($action === 'done') {
            // Lấy mã bàn trước khi cập nhật
            $stmt = $conn->prepare("SELECT MaBan FROM dat_ban WHERE MaDatBan = ?");
            $stmt->execute([$maDatBan]);
            $datBan = $stmt->fetch();
            
            $stmt = $conn->prepare("UPDATE dat_ban SET TrangThai = 'Đã đến' WHERE MaDatBan = ?");
            $stmt->execute([$maDatBan]);
            
            // Cập nhật trạng thái bàn
            if ($datBan && $datBan['MaBan']) {
                $stmt = $conn->prepare("UPDATE ban_an SET TrangThai = 'Đang phục vụ' WHERE MaBan = ?");
                $stmt->execute([$datBan['MaBan']]);
            }
            $message = 'Đã cập nhật trạng thái!';
        }
    } catch(PDOException $e) {
        $error = 'Lỗi: ' . $e->getMessage();
    }
}

// Lọc theo trạng thái
$filter = $_GET['filter'] ?? 'all';
$filterSQL = '';
if ($filter === 'pending') {
    $filterSQL = "WHERE db.TrangThai = 'Chờ xác nhận'";
} elseif ($filter === 'confirmed') {
    $filterSQL = "WHERE db.TrangThai = 'Đã xác nhận'";
} elseif ($filter === 'done') {
    $filterSQL = "WHERE db.TrangThai = 'Đã đến'";
} elseif ($filter === 'cancelled') {
    $filterSQL = "WHERE db.TrangThai = 'Đã hủy'";
}

// Lấy danh sách đặt bàn
$datBans = [];
$counts = [];
try {
    $stmt = $conn->query("SELECT db.*, kh.HoTen, kh.SoDienThoai, ba.TenBan, ba.ViTri 
                          FROM dat_ban db 
                          LEFT JOIN khach_hang kh ON db.MaKhachHang = kh.MaKhachHang 
                          LEFT JOIN ban_an ba ON db.MaBan = ba.MaBan 
                          $filterSQL
                          ORDER BY db.ThoiGianDat DESC");
    $datBans = $stmt->fetchAll();
    
    // Đếm số lượng theo trạng thái
    $stmt = $conn->query("SELECT TrangThai, COUNT(*) as total FROM dat_ban GROUP BY TrangThai");
    while ($row = $stmt->fetch()) {
        $counts[$row['TrangThai']] = $row['total'];
    }
} catch(PDOException $e) {
    $error = 'Lỗi: ' . $e->getMessage();
}

// Lấy danh sách bàn trống
$bans = [];
try {
    $stmt = $conn->query("SELECT * FROM ban_an WHERE TrangThai = 'Trống' ORDER BY MaBan");
    $bans = $stmt->fetchAll();
} catch(PDOException $e) {}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Nhà hàng 3CE - Quản lý đặt bàn</title>
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
            max-width: 1200px;
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
        .card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        }
        .booking-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            border: 2px solid #eee;
            border-radius: 10px;
            margin-bottom: 15px;
            flex-wrap: wrap;
            gap: 15px;
        }
        .booking-item:hover {
            border-color: #001f3f;
        }
        .booking-info h4 {
            color: #001f3f;
            margin-bottom: 5px;
        }
        .booking-info p {
            color: #666;
            font-size: 14px;
            margin: 3px 0;
        }
        .status-badge {
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 600;
        }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-confirmed { background: #d4edda; color: #155724; }
        .status-done { background: #cce5ff; color: #004085; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
        .btn-action {
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 13px;
            margin: 2px;
        }
        .btn-confirm { background: #28a745; color: white; }
        .btn-done { background: #007bff; color: white; }
        .btn-cancel { background: #dc3545; color: white; }
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
            padding: 30px;
            max-width: 500px;
            width: 90%;
        }
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .modal-header h3 { color: #001f3f; }
        .close-btn {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
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
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
        }
        .btn-submit {
            width: 100%;
            padding: 12px;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
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
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <div class="logo">
                <i class="fas fa-calendar-check"></i>
                <span>Quản lý đặt bàn</span>
            </div>
            <a href="dashboard.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
        </div>
    </div>

    <div class="container">
        <div class="page-header">
            <h1><i class="fas fa-calendar-check"></i> Quản lý đặt bàn</h1>
            <p>Xem và xử lý các yêu cầu đặt bàn từ khách hàng</p>
        </div>

        <?php if ($message): ?>
        <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $message; ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
        <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div>
        <?php endif; ?>

        <div class="filter-tabs">
            <a href="?filter=all" class="filter-tab <?php echo $filter === 'all' ? 'active' : ''; ?>">
                <i class="fas fa-list"></i> Tất cả
            </a>
            <a href="?filter=pending" class="filter-tab <?php echo $filter === 'pending' ? 'active' : ''; ?>">
                <i class="fas fa-clock"></i> Chờ xác nhận
                <?php if (($counts['Chờ xác nhận'] ?? 0) > 0): ?>
                <span class="badge"><?php echo $counts['Chờ xác nhận']; ?></span>
                <?php endif; ?>
            </a>
            <a href="?filter=confirmed" class="filter-tab <?php echo $filter === 'confirmed' ? 'active' : ''; ?>">
                <i class="fas fa-check"></i> Đã xác nhận
            </a>
            <a href="?filter=done" class="filter-tab <?php echo $filter === 'done' ? 'active' : ''; ?>">
                <i class="fas fa-user-check"></i> Đã đến
            </a>
            <a href="?filter=cancelled" class="filter-tab <?php echo $filter === 'cancelled' ? 'active' : ''; ?>">
                <i class="fas fa-times"></i> Đã hủy
            </a>
        </div>

        <div class="card">
            <?php if (empty($datBans)): ?>
            <div class="empty-state">
                <i class="fas fa-calendar-times"></i>
                <h3>Không có đặt bàn nào</h3>
            </div>
            <?php else: ?>
                <?php foreach ($datBans as $dat): ?>
                <div class="booking-item">
                    <div class="booking-info">
                        <h4><i class="fas fa-user"></i> <?php echo htmlspecialchars($dat['HoTen']); ?></h4>
                        <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars($dat['SoDienThoai']); ?></p>
                        <p><i class="fas fa-calendar"></i> <?php echo date('d/m/Y H:i', strtotime($dat['ThoiGianDat'])); ?></p>
                        <p><i class="fas fa-users"></i> <?php echo $dat['SoLuongKhach']; ?> người</p>
                        <?php if ($dat['TenBan']): ?>
                        <p><i class="fas fa-chair"></i> <?php echo htmlspecialchars($dat['TenBan']); ?> - <?php echo htmlspecialchars($dat['ViTri'] ?? ''); ?></p>
                        <?php endif; ?>
                        <?php if ($dat['GhiChu']): ?>
                        <p><i class="fas fa-sticky-note"></i> <?php echo htmlspecialchars($dat['GhiChu']); ?></p>
                        <?php endif; ?>
                    </div>
                    <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 10px;">
                        <span class="status-badge <?php 
                            echo $dat['TrangThai'] === 'Chờ xác nhận' ? 'status-pending' : 
                                ($dat['TrangThai'] === 'Đã xác nhận' ? 'status-confirmed' : 
                                ($dat['TrangThai'] === 'Đã đến' ? 'status-done' : 'status-cancelled')); 
                        ?>">
                            <?php echo $dat['TrangThai']; ?>
                        </span>
                        <div>
                            <?php if ($dat['TrangThai'] === 'Chờ xác nhận'): ?>
                            <button class="btn-action btn-confirm" onclick="openConfirmModal(<?php echo $dat['MaDatBan']; ?>, <?php echo $dat['SoLuongKhach']; ?>)">
                                <i class="fas fa-check"></i> Xác nhận
                            </button>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="MaDatBan" value="<?php echo $dat['MaDatBan']; ?>">
                                <button type="submit" name="action" value="cancel" class="btn-action btn-cancel" onclick="return confirm('Hủy đặt bàn này?')">
                                    <i class="fas fa-times"></i> Hủy
                                </button>
                            </form>
                            <?php elseif ($dat['TrangThai'] === 'Đã xác nhận'): ?>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="MaDatBan" value="<?php echo $dat['MaDatBan']; ?>">
                                <button type="submit" name="action" value="done" class="btn-action btn-done">
                                    <i class="fas fa-user-check"></i> Khách đã đến
                                </button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal xác nhận đặt bàn -->
    <div id="confirmModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-check-circle"></i> Xác nhận đặt bàn</h3>
                <button class="close-btn" onclick="closeModal()">&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="confirm">
                <input type="hidden" name="MaDatBan" id="confirm_MaDatBan">
                
                <div class="form-group">
                    <label><i class="fas fa-chair"></i> Chọn bàn cho khách</label>
                    <select name="MaBan" id="confirm_MaBan">
                        <option value="">-- Chọn bàn --</option>
                        <?php foreach ($bans as $ban): ?>
                        <option value="<?php echo $ban['MaBan']; ?>" data-seats="<?php echo $ban['SoGhe']; ?>">
                            <?php echo htmlspecialchars($ban['TenBan']); ?> (<?php echo $ban['SoGhe']; ?> chỗ) - <?php echo htmlspecialchars($ban['ViTri'] ?? ''); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <button type="submit" class="btn-submit">
                    <i class="fas fa-check"></i> Xác nhận đặt bàn
                </button>
            </form>
        </div>
    </div>

    <script>
        function openConfirmModal(maDatBan, soKhach) {
            document.getElementById('confirm_MaDatBan').value = maDatBan;
            document.getElementById('confirmModal').classList.add('active');
        }
        
        function closeModal() {
            document.getElementById('confirmModal').classList.remove('active');
        }
        
        document.getElementById('confirmModal').addEventListener('click', function(e) {
            if (e.target === this) closeModal();
        });
    </script>
</body>
</html>