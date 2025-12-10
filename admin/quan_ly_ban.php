<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: admin_login.php");
    exit();
}

$message = '';
$error = '';

// Xử lý thay đổi trạng thái bàn
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $maBan = intval($_POST['MaBan']);
    $action = $_POST['action'];
    
    try {
        if ($action === 'change_status') {
            $newStatus = $_POST['TrangThai'];
            $stmt = $conn->prepare("UPDATE ban_an SET TrangThai = ? WHERE MaBan = ?");
            $stmt->execute([$newStatus, $maBan]);
            $message = 'Đã cập nhật trạng thái bàn!';
        }
    } catch(PDOException $e) {
        $error = 'Lỗi: ' . $e->getMessage();
    }
}

try {
    $stmt = $conn->query("SELECT * FROM ban_an ORDER BY MaBan");
    $bans = $stmt->fetchAll();
} catch(PDOException $e) {
    die("Lỗi: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Nhà hàng 3CE - Quản lý bàn ăn</title>
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
        .table-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
        }
        .table-card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        .table-card.available {
            border: 3px solid #28a745;
        }
        .table-card.occupied {
            border: 3px solid #dc3545;
        }
        .table-card i {
            font-size: 50px;
            margin-bottom: 15px;
        }
        .table-card.available i {
            color: #28a745;
        }
        .table-card.occupied i {
            color: #dc3545;
        }
        .table-card h3 {
            color: #001f3f;
            margin-bottom: 10px;
        }
        .status {
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
        }
        .status.available {
            background: #d4edda;
            color: #155724;
        }
        .status.occupied {
            background: #f8d7da;
            color: #721c24;
        }
        .status.reserved {
            background: #fff3cd;
            color: #856404;
        }
        .table-card.reserved {
            border: 3px solid #ffc107;
        }
        .table-card.reserved i {
            color: #ffc107;
        }
        .btn-status {
            margin-top: 15px;
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 13px;
            width: 100%;
        }
        .btn-free { background: #28a745; color: white; }
        .btn-serving { background: #dc3545; color: white; }
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .alert-success { background: #d4edda; color: #155724; }
        .alert-error { background: #f8d7da; color: #721c24; }
        .status-select {
            margin-top: 10px;
            padding: 8px;
            border: 2px solid #ddd;
            border-radius: 5px;
            width: 100%;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <div class="logo">
                <i class="fas fa-chair"></i>
                <span>Quản lý bàn ăn</span>
            </div>
            <a href="dashboard.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
        </div>
    </div>

    <div class="container">
        <div class="page-header">
            <h1><i class="fas fa-chair"></i> Sơ đồ bàn ăn</h1>
            <p>Quản lý trạng thái các bàn trong nhà hàng</p>
        </div>

        <?php if ($message): ?>
        <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $message; ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
        <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div>
        <?php endif; ?>

        <div class="table-grid">
            <?php foreach ($bans as $ban): 
                $statusClass = 'available';
                if ($ban['TrangThai'] == 'Đang phục vụ') $statusClass = 'occupied';
                elseif ($ban['TrangThai'] == 'Đã đặt') $statusClass = 'reserved';
            ?>
            <div class="table-card <?php echo $statusClass; ?>">
                <i class="fas fa-chair"></i>
                <h3><?php echo htmlspecialchars($ban['TenBan'] ?? 'Bàn ' . $ban['MaBan']); ?></h3>
                <p>Sức chứa: <?php echo htmlspecialchars($ban['SoGhe']); ?> người</p>
                <?php if ($ban['ViTri']): ?>
                <p style="font-size: 12px; color: #666;"><?php echo htmlspecialchars($ban['ViTri']); ?></p>
                <?php endif; ?>
                <p class="status <?php echo $statusClass; ?>">
                    <?php echo htmlspecialchars($ban['TrangThai']); ?>
                </p>
                
                <form method="POST">
                    <input type="hidden" name="action" value="change_status">
                    <input type="hidden" name="MaBan" value="<?php echo $ban['MaBan']; ?>">
                    <select name="TrangThai" class="status-select" onchange="this.form.submit()">
                        <option value="Trống" <?php echo $ban['TrangThai'] == 'Trống' ? 'selected' : ''; ?>>Trống</option>
                        <option value="Đang phục vụ" <?php echo $ban['TrangThai'] == 'Đang phục vụ' ? 'selected' : ''; ?>>Đang phục vụ</option>
                        <option value="Đã đặt" <?php echo $ban['TrangThai'] == 'Đã đặt' ? 'selected' : ''; ?>>Đã đặt</option>
                    </select>
                </form>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
