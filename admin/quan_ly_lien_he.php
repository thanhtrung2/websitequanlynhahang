<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$message = '';
$error = '';

// Tự động tạo bảng nếu chưa tồn tại
try {
    $conn->exec("CREATE TABLE IF NOT EXISTS lien_he (
        MaLienHe INT AUTO_INCREMENT PRIMARY KEY,
        HoTen VARCHAR(100) NOT NULL,
        Email VARCHAR(100),
        SoDienThoai VARCHAR(20) NOT NULL,
        ChuDe VARCHAR(200) NOT NULL,
        NoiDung TEXT NOT NULL,
        TrangThai ENUM('Chưa xử lý', 'Đang xử lý', 'Đã xử lý') DEFAULT 'Chưa xử lý',
        PhanHoi TEXT,
        NguoiXuLy INT,
        ThoiGianGui DATETIME DEFAULT CURRENT_TIMESTAMP,
        ThoiGianXuLy DATETIME,
        MaKhachHang INT
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    
    // Tạo bảng thông báo
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

// Xử lý cập nhật trạng thái
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $maLienHe = intval($_POST['MaLienHe']);
    $trangThai = $_POST['TrangThai'];
    $phanHoi = trim($_POST['PhanHoi'] ?? '');
    
    try {
        // Lấy thông tin liên hệ trước khi cập nhật
        $stmt = $conn->prepare("SELECT * FROM lien_he WHERE MaLienHe = ?");
        $stmt->execute([$maLienHe]);
        $lienHeInfo = $stmt->fetch();
        
        // Cập nhật trạng thái
        $stmt = $conn->prepare("UPDATE lien_he SET TrangThai = ?, PhanHoi = ?, NguoiXuLy = ?, ThoiGianXuLy = NOW() WHERE MaLienHe = ?");
        $stmt->execute([$trangThai, $phanHoi, $_SESSION['user_id'], $maLienHe]);
        
        // Gửi thông báo cho khách hàng nếu có MaKhachHang và có phản hồi
        if ($lienHeInfo && $lienHeInfo['MaKhachHang'] && !empty($phanHoi)) {
            $tieuDe = "Phản hồi liên hệ: " . $lienHeInfo['ChuDe'];
            $noiDungTB = "Nhà hàng 3CE đã phản hồi yêu cầu của bạn:\n\n" . $phanHoi . "\n\n---\nYêu cầu ban đầu: " . $lienHeInfo['NoiDung'];
            
            $stmt = $conn->prepare("INSERT INTO thong_bao (MaKhachHang, TieuDe, NoiDung, LoaiThongBao, MaLienKet) VALUES (?, ?, ?, 'lien_he', ?)");
            $stmt->execute([$lienHeInfo['MaKhachHang'], $tieuDe, $noiDungTB, $maLienHe]);
        }
        
        $message = 'Cập nhật thành công!' . ($lienHeInfo['MaKhachHang'] && !empty($phanHoi) ? ' Đã gửi thông báo cho khách hàng.' : '');
    } catch(PDOException $e) {
        $error = 'Lỗi: ' . $e->getMessage();
    }
}

// Xử lý xóa liên hệ
if (isset($_GET['delete'])) {
    $maLienHe = intval($_GET['delete']);
    try {
        $stmt = $conn->prepare("DELETE FROM lien_he WHERE MaLienHe = ?");
        $stmt->execute([$maLienHe]);
        $message = 'Đã xóa liên hệ!';
    } catch(PDOException $e) {
        $error = 'Lỗi: ' . $e->getMessage();
    }
}

// Lọc theo trạng thái
$filterStatus = $_GET['status'] ?? '';
$searchQuery = $_GET['search'] ?? '';

// Lấy danh sách liên hệ
$lienHes = [];
try {
    $sql = "SELECT lh.*, kh.HoTen as TenKhachHang, nv.HoTen as TenNguoiXuLy 
            FROM lien_he lh 
            LEFT JOIN khach_hang kh ON lh.MaKhachHang = kh.MaKhachHang 
            LEFT JOIN nhan_vien nv ON lh.NguoiXuLy = nv.MaNhanVien 
            WHERE 1=1";
    $params = [];
    
    if (!empty($filterStatus)) {
        $sql .= " AND lh.TrangThai = ?";
        $params[] = $filterStatus;
    }
    
    if (!empty($searchQuery)) {
        $sql .= " AND (lh.HoTen LIKE ? OR lh.SoDienThoai LIKE ? OR lh.ChuDe LIKE ?)";
        $params[] = "%$searchQuery%";
        $params[] = "%$searchQuery%";
        $params[] = "%$searchQuery%";
    }
    
    $sql .= " ORDER BY 
              CASE lh.TrangThai 
                WHEN 'Chưa xử lý' THEN 1 
                WHEN 'Đang xử lý' THEN 2 
                ELSE 3 
              END, 
              lh.ThoiGianGui DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $lienHes = $stmt->fetchAll();
} catch(PDOException $e) {
    $error = 'Lỗi: ' . $e->getMessage();
}

// Đếm theo trạng thái
$counts = ['all' => 0, 'Chưa xử lý' => 0, 'Đang xử lý' => 0, 'Đã xử lý' => 0];
try {
    $stmt = $conn->query("SELECT TrangThai, COUNT(*) as total FROM lien_he GROUP BY TrangThai");
    while ($row = $stmt->fetch()) {
        $counts[$row['TrangThai']] = $row['total'];
        $counts['all'] += $row['total'];
    }
} catch(PDOException $e) {}

// Xem chi tiết
$chiTiet = null;
if (isset($_GET['view'])) {
    $maLienHe = intval($_GET['view']);
    try {
        $stmt = $conn->prepare("SELECT lh.*, kh.HoTen as TenKhachHang, nv.HoTen as TenNguoiXuLy 
                                FROM lien_he lh 
                                LEFT JOIN khach_hang kh ON lh.MaKhachHang = kh.MaKhachHang 
                                LEFT JOIN nhan_vien nv ON lh.NguoiXuLy = nv.MaNhanVien 
                                WHERE lh.MaLienHe = ?");
        $stmt->execute([$maLienHe]);
        $chiTiet = $stmt->fetch();
    } catch(PDOException $e) {}
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý liên hệ - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f4f6f9; min-height: 100vh; }
        
        /* Sidebar */
        .sidebar {
            width: 260px;
            background: linear-gradient(180deg, #001f3f 0%, #003366 100%);
            min-height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            padding: 20px 0;
            box-shadow: 4px 0 15px rgba(0,0,0,0.1);
            z-index: 100;
        }
        .sidebar-header { padding: 0 20px 20px; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .sidebar-logo { display: flex; align-items: center; gap: 10px; color: #F5F5DC; font-size: 20px; font-weight: bold; }
        .sidebar-logo i { color: #D4AF37; font-size: 24px; }
        .sidebar-user { padding: 20px; display: flex; align-items: center; gap: 12px; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .sidebar-avatar { width: 45px; height: 45px; border-radius: 50%; border: 2px solid #D4AF37; }
        .sidebar-user-info h4 { color: #F5F5DC; font-size: 14px; }
        .sidebar-user-info span { color: #D4AF37; font-size: 12px; }
        .sidebar-menu { list-style: none; padding: 15px 0; }
        .sidebar-menu li a { display: flex; align-items: center; gap: 12px; padding: 12px 25px; color: #ccc; text-decoration: none; transition: all 0.3s; }
        .sidebar-menu li a:hover { background: rgba(255,255,255,0.1); color: #F5F5DC; }
        .sidebar-menu li a.active { background: rgba(212,175,55,0.2); color: #D4AF37; border-left: 3px solid #D4AF37; }
        .sidebar-menu li a i { width: 20px; text-align: center; }
        .sidebar-divider { height: 1px; background: rgba(255,255,255,0.1); margin: 10px 20px; }
        .sidebar-title { padding: 10px 25px; color: #888; font-size: 11px; text-transform: uppercase; letter-spacing: 1px; }
        
        .admin-layout { display: flex; min-height: 100vh; }
        .main-content { flex: 1; margin-left: 260px; padding: 20px; min-height: 100vh; }
        .page-header { 
            background: white; 
            padding: 25px; 
            border-radius: 15px; 
            margin-bottom: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .page-header h1 { color: #001f3f; margin-bottom: 10px; }
        
        .stats-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 25px;
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            cursor: pointer;
            transition: all 0.3s;
            border-left: 4px solid #ddd;
        }
        .stat-card:hover, .stat-card.active { transform: translateY(-3px); }
        .stat-card.all { border-left-color: #001f3f; }
        .stat-card.pending { border-left-color: #ffc107; }
        .stat-card.processing { border-left-color: #17a2b8; }
        .stat-card.done { border-left-color: #28a745; }
        .stat-card h3 { font-size: 28px; color: #001f3f; }
        .stat-card p { color: #666; font-size: 14px; }
        
        .filter-bar {
            background: white;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            display: flex;
            gap: 15px;
            align-items: center;
            flex-wrap: wrap;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .filter-bar input, .filter-bar select {
            padding: 10px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
        }
        .filter-bar input { flex: 1; min-width: 200px; }
        .filter-bar button {
            padding: 10px 20px;
            background: #001f3f;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }
        
        .contact-list {
            background: white;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .contact-item {
            padding: 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            gap: 20px;
            align-items: flex-start;
            transition: background 0.3s;
        }
        .contact-item:hover { background: #f8f9fa; }
        .contact-item:last-child { border-bottom: none; }
        
        .contact-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, #001f3f, #003366);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #F5F5DC;
            font-size: 20px;
            font-weight: bold;
        }
        .contact-info { flex: 1; }
        .contact-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 8px;
        }
        .contact-name { font-weight: 600; color: #001f3f; font-size: 16px; }
        .contact-subject { color: #D4AF37; font-weight: 500; }
        .contact-meta { color: #666; font-size: 13px; margin-bottom: 8px; }
        .contact-preview { color: #444; font-size: 14px; line-height: 1.5; }
        
        .contact-actions {
            display: flex;
            flex-direction: column;
            gap: 8px;
            align-items: flex-end;
        }
        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-processing { background: #d1ecf1; color: #0c5460; }
        .status-done { background: #d4edda; color: #155724; }
        
        .btn-action {
            padding: 6px 12px;
            border: none;
            border-radius: 5px;
            font-size: 12px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        .btn-view { background: #001f3f; color: white; }
        .btn-delete { background: #dc3545; color: white; }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .alert-success { background: #d4edda; color: #155724; }
        .alert-error { background: #f8d7da; color: #721c24; }
        
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
            max-width: 600px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
        }
        .modal-header {
            padding: 20px;
            border-bottom: 2px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .modal-header h3 { color: #001f3f; }
        .close-btn {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
        }
        .modal-body { padding: 20px; }
        
        .detail-row {
            display: flex;
            padding: 12px 0;
            border-bottom: 1px solid #eee;
        }
        .detail-row:last-child { border-bottom: none; }
        .detail-label {
            width: 120px;
            color: #666;
            font-weight: 500;
        }
        .detail-value { flex: 1; color: #001f3f; }
        .detail-content {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
            line-height: 1.6;
        }
        
        .form-group { margin-bottom: 15px; }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #001f3f;
            font-weight: 500;
        }
        .form-group select, .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 14px;
        }
        .form-group textarea { resize: vertical; min-height: 100px; }
        
        .btn-submit {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #001f3f, #003366);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }
        .empty-state i {
            font-size: 60px;
            color: #ddd;
            margin-bottom: 20px;
        }
        
        @media (max-width: 1024px) {
            .main-content { margin-left: 0; }
            .stats-row { grid-template-columns: repeat(2, 1fr); }
        }
    </style>
</head>
<body>
    <div class="admin-layout">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="main-content">
        <div class="page-header">
            <h1><i class="fas fa-envelope"></i> Quản lý liên hệ</h1>
            <p>Xem và xử lý các yêu cầu liên hệ từ khách hàng</p>
        </div>

        <?php if ($message): ?>
        <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $message; ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
        <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div>
        <?php endif; ?>

        <div class="stats-row">
            <a href="?status=" class="stat-card all <?php echo empty($filterStatus) ? 'active' : ''; ?>" style="text-decoration:none;">
                <h3><?php echo $counts['all']; ?></h3>
                <p><i class="fas fa-inbox"></i> Tất cả</p>
            </a>
            <a href="?status=Chưa xử lý" class="stat-card pending <?php echo $filterStatus === 'Chưa xử lý' ? 'active' : ''; ?>" style="text-decoration:none;">
                <h3><?php echo $counts['Chưa xử lý']; ?></h3>
                <p><i class="fas fa-clock"></i> Chưa xử lý</p>
            </a>
            <a href="?status=Đang xử lý" class="stat-card processing <?php echo $filterStatus === 'Đang xử lý' ? 'active' : ''; ?>" style="text-decoration:none;">
                <h3><?php echo $counts['Đang xử lý']; ?></h3>
                <p><i class="fas fa-spinner"></i> Đang xử lý</p>
            </a>
            <a href="?status=Đã xử lý" class="stat-card done <?php echo $filterStatus === 'Đã xử lý' ? 'active' : ''; ?>" style="text-decoration:none;">
                <h3><?php echo $counts['Đã xử lý']; ?></h3>
                <p><i class="fas fa-check"></i> Đã xử lý</p>
            </a>
        </div>

        <form method="GET" class="filter-bar">
            <input type="text" name="search" placeholder="Tìm kiếm theo tên, SĐT, chủ đề..." value="<?php echo htmlspecialchars($searchQuery); ?>">
            <input type="hidden" name="status" value="<?php echo htmlspecialchars($filterStatus); ?>">
            <button type="submit"><i class="fas fa-search"></i> Tìm kiếm</button>
            <?php if (!empty($searchQuery) || !empty($filterStatus)): ?>
            <a href="quan_ly_lien_he.php" style="padding: 10px 20px; background: #6c757d; color: white; border-radius: 8px; text-decoration: none;">
                <i class="fas fa-times"></i> Xóa lọc
            </a>
            <?php endif; ?>
        </form>

        <div class="contact-list">
            <?php if (empty($lienHes)): ?>
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <h3>Không có liên hệ nào</h3>
                <p>Chưa có yêu cầu liên hệ nào từ khách hàng</p>
            </div>
            <?php else: ?>
                <?php foreach ($lienHes as $lh): ?>
                <div class="contact-item">
                    <div class="contact-avatar">
                        <?php echo strtoupper(mb_substr($lh['HoTen'], 0, 1, 'UTF-8')); ?>
                    </div>
                    <div class="contact-info">
                        <div class="contact-header">
                            <div>
                                <span class="contact-name"><?php echo htmlspecialchars($lh['HoTen']); ?></span>
                                <span class="contact-subject"> - <?php echo htmlspecialchars($lh['ChuDe']); ?></span>
                            </div>
                        </div>
                        <div class="contact-meta">
                            <i class="fas fa-phone"></i> <?php echo htmlspecialchars($lh['SoDienThoai']); ?>
                            <?php if (!empty($lh['Email'])): ?>
                            &nbsp;|&nbsp; <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($lh['Email']); ?>
                            <?php endif; ?>
                            &nbsp;|&nbsp; <i class="fas fa-clock"></i> <?php echo date('d/m/Y H:i', strtotime($lh['ThoiGianGui'])); ?>
                        </div>
                        <div class="contact-preview">
                            <?php echo htmlspecialchars(mb_substr($lh['NoiDung'], 0, 150, 'UTF-8')); ?>
                            <?php if (mb_strlen($lh['NoiDung'], 'UTF-8') > 150) echo '...'; ?>
                        </div>
                    </div>
                    <div class="contact-actions">
                        <span class="status-badge <?php 
                            echo $lh['TrangThai'] === 'Chưa xử lý' ? 'status-pending' : 
                                ($lh['TrangThai'] === 'Đang xử lý' ? 'status-processing' : 'status-done'); 
                        ?>">
                            <?php echo htmlspecialchars($lh['TrangThai']); ?>
                        </span>
                        <div>
                            <a href="?view=<?php echo $lh['MaLienHe']; ?>" class="btn-action btn-view">
                                <i class="fas fa-eye"></i> Xem
                            </a>
                            <a href="?delete=<?php echo $lh['MaLienHe']; ?>" class="btn-action btn-delete" onclick="return confirm('Xác nhận xóa liên hệ này?')">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        </main>
    </div>

    <!-- Modal Chi tiết -->
    <?php if ($chiTiet): ?>
    <div class="modal active" id="detailModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-envelope-open"></i> Chi tiết liên hệ #<?php echo $chiTiet['MaLienHe']; ?></h3>
                <a href="quan_ly_lien_he.php<?php echo !empty($filterStatus) ? '?status='.urlencode($filterStatus) : ''; ?>" class="close-btn">&times;</a>
            </div>
            <div class="modal-body">
                <div class="detail-row">
                    <span class="detail-label">Họ tên:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($chiTiet['HoTen']); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Điện thoại:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($chiTiet['SoDienThoai']); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Email:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($chiTiet['Email'] ?? 'Không có'); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Chủ đề:</span>
                    <span class="detail-value" style="color: #D4AF37; font-weight: 600;"><?php echo htmlspecialchars($chiTiet['ChuDe']); ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Thời gian:</span>
                    <span class="detail-value"><?php echo date('d/m/Y H:i', strtotime($chiTiet['ThoiGianGui'])); ?></span>
                </div>
                <?php if ($chiTiet['TenKhachHang']): ?>
                <div class="detail-row">
                    <span class="detail-label">Khách hàng:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($chiTiet['TenKhachHang']); ?> (Thành viên)</span>
                </div>
                <?php endif; ?>
                
                <h4 style="margin: 20px 0 10px; color: #001f3f;"><i class="fas fa-comment"></i> Nội dung:</h4>
                <div class="detail-content"><?php echo nl2br(htmlspecialchars($chiTiet['NoiDung'])); ?></div>
                
                <?php if (!empty($chiTiet['PhanHoi'])): ?>
                <h4 style="margin: 20px 0 10px; color: #28a745;"><i class="fas fa-reply"></i> Phản hồi:</h4>
                <div class="detail-content" style="background: #d4edda; border-left: 4px solid #28a745;">
                    <?php echo nl2br(htmlspecialchars($chiTiet['PhanHoi'])); ?>
                    <p style="margin-top: 10px; font-size: 12px; color: #666;">
                        <i class="fas fa-user"></i> <?php echo htmlspecialchars($chiTiet['TenNguoiXuLy'] ?? 'Admin'); ?>
                        - <?php echo $chiTiet['ThoiGianXuLy'] ? date('d/m/Y H:i', strtotime($chiTiet['ThoiGianXuLy'])) : ''; ?>
                    </p>
                </div>
                <?php endif; ?>
                
                <hr style="margin: 25px 0; border: none; border-top: 2px solid #eee;">
                
                <form method="POST">
                    <input type="hidden" name="MaLienHe" value="<?php echo $chiTiet['MaLienHe']; ?>">
                    
                    <div class="form-group">
                        <label><i class="fas fa-flag"></i> Trạng thái</label>
                        <select name="TrangThai">
                            <option value="Chưa xử lý" <?php echo $chiTiet['TrangThai'] === 'Chưa xử lý' ? 'selected' : ''; ?>>Chưa xử lý</option>
                            <option value="Đang xử lý" <?php echo $chiTiet['TrangThai'] === 'Đang xử lý' ? 'selected' : ''; ?>>Đang xử lý</option>
                            <option value="Đã xử lý" <?php echo $chiTiet['TrangThai'] === 'Đã xử lý' ? 'selected' : ''; ?>>Đã xử lý</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-reply"></i> Phản hồi</label>
                        <textarea name="PhanHoi" placeholder="Nhập nội dung phản hồi cho khách hàng..."><?php echo htmlspecialchars($chiTiet['PhanHoi'] ?? ''); ?></textarea>
                    </div>
                    
                    <button type="submit" name="update_status" class="btn-submit">
                        <i class="fas fa-save"></i> Cập nhật
                    </button>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script>
        // Close modal when clicking outside
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('click', function(e) {
                if (e.target === this) {
                    window.location.href = 'quan_ly_lien_he.php';
                }
            });
        });
    </script>
</body>
</html>
