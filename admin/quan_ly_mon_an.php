<?php
session_start();
// Tránh cache
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: admin_login.php");
    exit();
}

$message = isset($_SESSION['message']) ? $_SESSION['message'] : '';
unset($_SESSION['message']);
$error = '';

// Xử lý thêm món ăn
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    if ($action === 'add') {
        $TenMonAn = trim($_POST['TenMonAn']);
        $Gia = $_POST['Gia'];
        $HinhAnh = trim($_POST['HinhAnh'] ?? '');
        
        try {
            // Kiểm tra món ăn đã tồn tại chưa
            $checkStmt = $conn->prepare("SELECT COUNT(*) FROM mon_an WHERE TenMonAn = ?");
            $checkStmt->execute([$TenMonAn]);
            if ($checkStmt->fetchColumn() > 0) {
                $error = "Món ăn '$TenMonAn' đã tồn tại!";
            } else {
                $stmt = $conn->prepare("INSERT INTO mon_an (TenMonAn, DonGia, HinhAnh) VALUES (?, ?, ?)");
                $stmt->execute([$TenMonAn, $Gia, $HinhAnh]);
                $_SESSION['message'] = "Thêm món ăn thành công!";
                header("Location: quan_ly_mon_an.php");
                exit();
            }
        } catch(PDOException $e) {
            $error = "Lỗi: " . $e->getMessage();
        }
    }
    
    if ($action === 'edit') {
        $MaMonAn = trim($_POST['MaMonAn']);
        $TenMonAn = trim($_POST['TenMonAn']);
        $Gia = $_POST['Gia'];
        $HinhAnh = trim($_POST['HinhAnh'] ?? '');
        $TrangThai = $_POST['TrangThai'] ?? 'Còn hàng';
        $MoTa = trim($_POST['MoTa'] ?? '');
        
        try {
            $stmt = $conn->prepare("UPDATE mon_an SET TenMonAn = ?, DonGia = ?, HinhAnh = ?, TrangThai = ?, MoTa = ? WHERE MaMonAn = ?");
            $stmt->execute([$TenMonAn, $Gia, $HinhAnh, $TrangThai, $MoTa, $MaMonAn]);
            $_SESSION['message'] = "Cập nhật món ăn thành công!";
            header("Location: quan_ly_mon_an.php");
            exit();
        } catch(PDOException $e) {
            $error = "Lỗi: " . $e->getMessage();
        }
    }
    
    if ($action === 'toggle_status') {
        $MaMonAn = $_POST['MaMonAn'];
        $newStatus = $_POST['newStatus'];
        
        try {
            $stmt = $conn->prepare("UPDATE mon_an SET TrangThai = ? WHERE MaMonAn = ?");
            $stmt->execute([$newStatus, $MaMonAn]);
            $_SESSION['message'] = "Đã cập nhật trạng thái món ăn!";
            header("Location: quan_ly_mon_an.php");
            exit();
        } catch(PDOException $e) {
            $error = "Lỗi: " . $e->getMessage();
        }
    }
    
    if ($action === 'delete') {
        $MaMonAn = $_POST['MaMonAn'];
        
        try {
            $stmt = $conn->prepare("DELETE FROM mon_an WHERE MaMonAn = ?");
            $stmt->execute([$MaMonAn]);
            $_SESSION['message'] = "Xóa món ăn thành công!";
            header("Location: quan_ly_mon_an.php");
            exit();
        } catch(PDOException $e) {
            $error = "Lỗi: " . $e->getMessage();
        }
    }
}

// Lấy danh sách món ăn
try {
    $stmt = $conn->query("SELECT * FROM mon_an ORDER BY MaMonAn DESC");
    $monAns = $stmt->fetchAll();
    // Map DonGia to Gia for template compatibility
    foreach ($monAns as &$mon) {
        $mon['Gia'] = $mon['DonGia'];
    }
} catch(PDOException $e) {
    die("Lỗi: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Nhà hàng 3CE - Quản lý món ăn</title>
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
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .btn-add {
            display: inline-block;
            padding: 12px 25px;
            background: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            margin-bottom: 20px;
            cursor: pointer;
            border: none;
            font-size: 16px;
        }
        .btn-add:hover {
            background: #218838;
        }
        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        .menu-item {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        .menu-item-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        .menu-item-image:not(img) {
            background: linear-gradient(135deg, #001f3f 0%, #003366 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #F5F5DC;
            font-size: 50px;
        }
        .menu-item-content {
            padding: 20px;
        }
        .menu-item h3 {
            color: #001f3f;
            margin-bottom: 10px;
        }
        .menu-item .price {
            font-size: 20px;
            font-weight: bold;
            color: #003366;
            margin: 10px 0;
        }
        .menu-item .info {
            color: #666;
            font-size: 14px;
            margin-bottom: 15px;
        }
        .menu-item-actions {
            display: flex;
            gap: 10px;
        }
        .btn-edit, .btn-delete {
            flex: 1;
            padding: 8px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
        }
        .btn-edit {
            background: #007bff;
            color: white;
        }
        .btn-edit:hover {
            background: #0056b3;
        }
        .btn-delete {
            background: #dc3545;
            color: white;
        }
        .btn-delete:hover {
            background: #c82333;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 10px;
        }
        .status-available {
            background: #d4edda;
            color: #155724;
        }
        .status-unavailable {
            background: #f8d7da;
            color: #721c24;
        }
        .btn-status {
            padding: 6px 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
            margin-top: 10px;
            width: 100%;
        }
        .btn-status-available {
            background: #28a745;
            color: white;
        }
        .btn-status-unavailable {
            background: #ffc107;
            color: #333;
        }
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            background: white;
        }
        .form-group select:focus {
            outline: none;
            border-color: #001f3f;
        }
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            resize: vertical;
            min-height: 80px;
        }
        .form-group textarea:focus {
            outline: none;
            border-color: #001f3f;
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
            max-width: 500px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
        }
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .modal-header h2 {
            color: #001f3f;
        }
        .close-btn {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #666;
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
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
        }
        .form-group input:focus {
            outline: none;
            border-color: #001f3f;
        }
        .btn-submit {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #001f3f 0%, #003366 100%);
            color: #F5F5DC;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
        }
        .btn-submit:hover {
            background: linear-gradient(135deg, #003366 0%, #004080 100%);
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <div class="logo">
                <i class="fas fa-utensils"></i>
                <span>Quản lý món ăn</span>
            </div>
            <a href="dashboard.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
        </div>
    </div>

    <div class="container">
        <div class="page-header">
            <h1><i class="fas fa-utensils"></i> Quản lý món ăn</h1>
            <p>Thêm, sửa, xóa món ăn trong thực đơn</p>
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

        <button class="btn-add" onclick="openAddModal()">
            <i class="fas fa-plus"></i> Thêm món ăn mới
        </button>

        <div class="menu-grid">
            <?php foreach ($monAns as $mon): ?>
            <div class="menu-item">
                <?php if (!empty($mon['HinhAnh'])): ?>
                    <img src="<?php echo htmlspecialchars($mon['HinhAnh']); ?>" 
                         alt="<?php echo htmlspecialchars($mon['TenMonAn']); ?>" 
                         class="menu-item-image"
                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <div class="menu-item-image" style="display: none;">
                        <i class="fas fa-utensils"></i>
                    </div>
                <?php else: ?>
                    <div class="menu-item-image">
                        <i class="fas fa-utensils"></i>
                    </div>
                <?php endif; ?>
                
                <div class="menu-item-content">
                    <h3><?php echo htmlspecialchars($mon['TenMonAn']); ?></h3>
                    <span class="status-badge <?php echo ($mon['TrangThai'] ?? 'Còn hàng') === 'Còn hàng' ? 'status-available' : 'status-unavailable'; ?>">
                        <?php echo ($mon['TrangThai'] ?? 'Còn hàng') === 'Còn hàng' ? '<i class="fas fa-check-circle"></i> Còn hàng' : '<i class="fas fa-times-circle"></i> Hết hàng'; ?>
                    </span>
                    <div class="price"><?php echo number_format($mon['Gia'], 0, ',', '.'); ?>đ</div>
                    <div class="info">
                        <i class="fas fa-tag"></i> Mã: <?php echo htmlspecialchars($mon['MaMonAn']); ?>
                    </div>
                    
                    <div class="menu-item-actions">
                        <button class="btn-edit" onclick='openEditModal(<?php echo json_encode($mon); ?>)'>
                            <i class="fas fa-edit"></i> Sửa
                        </button>
                        <button class="btn-delete" onclick="confirmDelete('<?php echo htmlspecialchars($mon['MaMonAn']); ?>', '<?php echo htmlspecialchars($mon['TenMonAn']); ?>')">
                            <i class="fas fa-trash"></i> Xóa
                        </button>
                    </div>
                    
                    <!-- Nút đổi trạng thái nhanh -->
                    <form method="POST" style="margin-top: 10px;">
                        <input type="hidden" name="action" value="toggle_status">
                        <input type="hidden" name="MaMonAn" value="<?php echo $mon['MaMonAn']; ?>">
                        <?php if (($mon['TrangThai'] ?? 'Còn hàng') === 'Còn hàng'): ?>
                            <input type="hidden" name="newStatus" value="Hết hàng">
                            <button type="submit" class="btn-status btn-status-unavailable" onclick="return confirm('Đánh dấu món này là HẾT HÀNG?')">
                                <i class="fas fa-times-circle"></i> Đánh dấu hết hàng
                            </button>
                        <?php else: ?>
                            <input type="hidden" name="newStatus" value="Còn hàng">
                            <button type="submit" class="btn-status btn-status-available" onclick="return confirm('Đánh dấu món này là CÒN HÀNG?')">
                                <i class="fas fa-check-circle"></i> Đánh dấu còn hàng
                            </button>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Modal Thêm món ăn -->
    <div id="addModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-plus"></i> Thêm món ăn mới</h2>
                <button class="close-btn" onclick="closeAddModal()">&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="add">
                
                <div class="form-group">
                    <label><i class="fas fa-utensils"></i> Tên món ăn *</label>
                    <input type="text" name="TenMonAn" required placeholder="Nhập tên món ăn">
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-money-bill-wave"></i> Giá *</label>
                    <input type="number" name="Gia" required placeholder="Nhập giá">
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-image"></i> Hình ảnh (URL)</label>
                    <input type="text" name="HinhAnh" placeholder="https://example.com/image.jpg">
                </div>
                
                <button type="submit" class="btn-submit">
                    <i class="fas fa-save"></i> Thêm món ăn
                </button>
            </form>
        </div>
    </div>

    <!-- Modal Sửa món ăn -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-edit"></i> Sửa món ăn</h2>
                <button class="close-btn" onclick="closeEditModal()">&times;</button>
            </div>
            <form method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="MaMonAn" id="edit_MaMonAn">
                
                <div class="form-group">
                    <label><i class="fas fa-tag"></i> Mã món ăn</label>
                    <input type="text" id="edit_MaMonAn_display" disabled>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-utensils"></i> Tên món ăn *</label>
                    <input type="text" name="TenMonAn" id="edit_TenMonAn" required>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-money-bill-wave"></i> Giá *</label>
                    <input type="number" name="Gia" id="edit_Gia" required>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-image"></i> Hình ảnh (URL)</label>
                    <input type="text" name="HinhAnh" id="edit_HinhAnh">
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-toggle-on"></i> Trạng thái *</label>
                    <select name="TrangThai" id="edit_TrangThai" required>
                        <option value="Còn hàng">Còn hàng</option>
                        <option value="Hết hàng">Hết hàng</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-align-left"></i> Mô tả</label>
                    <textarea name="MoTa" id="edit_MoTa" placeholder="Nhập mô tả món ăn..."></textarea>
                </div>
                
                <button type="submit" class="btn-submit">
                    <i class="fas fa-save"></i> Cập nhật
                </button>
            </form>
        </div>
    </div>

    <!-- Form xóa ẩn -->
    <form id="deleteForm" method="POST" style="display: none;">
        <input type="hidden" name="action" value="delete">
        <input type="hidden" name="MaMonAn" id="delete_MaMonAn">
    </form>

    <script>
        function openAddModal() {
            document.getElementById('addModal').classList.add('active');
        }
        
        function closeAddModal() {
            document.getElementById('addModal').classList.remove('active');
        }
        
        function openEditModal(mon) {
            document.getElementById('edit_MaMonAn').value = mon.MaMonAn;
            document.getElementById('edit_MaMonAn_display').value = mon.MaMonAn;
            document.getElementById('edit_TenMonAn').value = mon.TenMonAn;
            document.getElementById('edit_Gia').value = mon.Gia;
            document.getElementById('edit_HinhAnh').value = mon.HinhAnh || '';
            document.getElementById('edit_TrangThai').value = mon.TrangThai || 'Còn hàng';
            document.getElementById('edit_MoTa').value = mon.MoTa || '';
            document.getElementById('editModal').classList.add('active');
        }
        
        function closeEditModal() {
            document.getElementById('editModal').classList.remove('active');
        }
        
        function confirmDelete(maMonAn, tenMonAn) {
            if (confirm('Bạn có chắc chắn muốn xóa món "' + tenMonAn + '"?')) {
                document.getElementById('delete_MaMonAn').value = maMonAn;
                document.getElementById('deleteForm').submit();
            }
        }
        
        // Đóng modal khi click bên ngoài
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.classList.remove('active');
            }
        }
    </script>
</body>
</html>
