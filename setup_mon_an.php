<?php
/**
 * Script tự động thêm món ăn vào database
 * Chạy file này một lần để thêm dữ liệu mẫu
 */

require_once __DIR__ . '/config/db.php';

$message = '';
$error = '';
$autoRun = isset($_GET['auto']) && $_GET['auto'] === '1';

// Tự động chạy nếu có parameter auto=1 hoặc có action
if ($autoRun || (isset($_GET['action']) && $_GET['action'] === 'add')) {
    try {
        // Xóa món ăn cũ (tùy chọn)
        if (isset($_GET['reset']) && $_GET['reset'] === '1') {
            $conn->exec("SET FOREIGN_KEY_CHECKS = 0");
            $conn->exec("DELETE FROM chi_tiet_hoa_don");
            $conn->exec("DELETE FROM mon_an");
            $conn->exec("ALTER TABLE mon_an AUTO_INCREMENT = 1");
            $conn->exec("SET FOREIGN_KEY_CHECKS = 1");
        }
        
        // Danh sách món ăn mới
        $monAns = [
            // DANH MỤC 1: MÓN KHAI VỊ
            ['Gỏi cuốn tôm thịt', 1, 60000, 'Phần', 'Gỏi cuốn tươi ngon với tôm, thịt, bún và rau sống, chấm mắm nêm đặc biệt.'],
            ['Chả giò hải sản', 1, 75000, 'Phần', 'Chả giò giòn rụm với nhân hải sản đậm đà, ăn kèm rau sống.'],
            ['Súp cua', 1, 55000, 'Tô', 'Súp cua thơm ngon, béo ngậy với thịt cua tươi và trứng cút.'],
            ['Salad trộn dầu giấm', 1, 45000, 'Phần', 'Salad rau củ tươi mát trộn sốt dầu giấm kiểu Ý.'],
            ['Nem nướng Nha Trang', 1, 70000, 'Phần', 'Nem nướng đặc sản Nha Trang, ăn kèm bánh tráng và rau sống.'],
            ['Bánh mì bơ tỏi', 1, 35000, 'Phần', 'Bánh mì nướng giòn với bơ tỏi thơm lừng.'],
            ['Khoai tây chiên', 1, 40000, 'Phần', 'Khoai tây chiên giòn vàng, ăn kèm sốt mayonnaise.'],
            ['Cánh gà chiên nước mắm', 1, 85000, 'Phần', 'Cánh gà chiên giòn rim nước mắm tỏi ớt đậm đà.'],
            
            // DANH MỤC 2: MÓN CHÍNH
            ['Phở Bò Đặc Biệt', 2, 90000, 'Tô', 'Phở bò gia truyền với thịt nạm, gầu, gân, sách. Nước dùng ninh xương 12 tiếng.'],
            ['Phở Gà Ta', 2, 85000, 'Tô', 'Phở gà ta thả vườn, thịt dai ngọt, nước dùng trong veo.'],
            ['Bún Bò Huế', 2, 95000, 'Tô', 'Bún bò Huế cay nồng đặc trưng với giò heo, chả cua.'],
            ['Bò Bít Tết Sốt Tiêu Xanh', 2, 250000, 'Phần', 'Thịt bò Úc mềm, ăn kèm khoai tây chiên và salad tươi.'],
            ['Bò Lúc Lắc', 2, 220000, 'Phần', 'Bò lúc lắc xào với ớt chuông, hành tây, ăn kèm cơm trắng.'],
            ['Cơm Chiên Dương Châu', 2, 75000, 'Phần', 'Cơm chiên với tôm, lạp xưởng, trứng và rau củ.'],
            ['Cơm Gà Hải Nam', 2, 95000, 'Phần', 'Cơm gà Hải Nam với gà luộc mềm, cơm dầu gà thơm.'],
            ['Cá Diêu Hồng Hấp Xì Dầu', 2, 180000, 'Con', 'Cá diêu hồng tươi sống hấp cùng xì dầu và gừng.'],
            ['Cá Chẽm Chiên Xù', 2, 200000, 'Con', 'Cá chẽm chiên giòn, sốt chua ngọt kiểu Thái.'],
            ['Tôm Sú Nướng Muối Ớt', 2, 280000, 'Phần', 'Tôm sú tươi nướng muối ớt, thơm lừng hấp dẫn.'],
            ['Mực Xào Sa Tế', 2, 190000, 'Phần', 'Mực tươi xào sa tế cay nồng, ăn kèm cơm trắng.'],
            ['Sườn Xào Chua Ngọt', 2, 150000, 'Phần', 'Sườn heo xào chua ngọt với dứa, cà chua.'],
            ['Gà Nướng Mật Ong', 2, 180000, 'Phần', 'Gà nướng mật ong vàng óng, thơm ngọt.'],
            ['Vịt Quay Bắc Kinh', 2, 350000, 'Con', 'Vịt quay da giòn, thịt mềm, ăn kèm bánh tráng.'],
            ['Lẩu Thái Hải Sản', 2, 450000, 'Nồi', 'Lẩu Thái chua cay với tôm, mực, cá, nghêu tươi.'],
            ['Lẩu Bò Nhúng Dấm', 2, 380000, 'Nồi', 'Lẩu bò nhúng dấm với thịt bò tươi và rau sống.'],
            ['Mì Xào Hải Sản', 2, 120000, 'Phần', 'Mì xào giòn với tôm, mực, cá viên và rau củ.'],
            ['Hủ Tiếu Nam Vang', 2, 80000, 'Tô', 'Hủ tiếu Nam Vang với thịt, tôm, gan và trứng cút.'],
            
            // DANH MỤC 3: TRÁNG MIỆNG
            ['Bánh Flan Caramen', 3, 30000, 'Cái', 'Bánh flan mềm mịn, béo ngậy vị caramen.'],
            ['Chè Thái', 3, 35000, 'Ly', 'Chè Thái với nước cốt dừa, thạch, trái cây nhiệt đới.'],
            ['Chè Đậu Đỏ', 3, 30000, 'Ly', 'Chè đậu đỏ nấu nhừ, ngọt thanh mát lạnh.'],
            ['Kem Dừa', 3, 40000, 'Ly', 'Kem dừa béo ngậy trong quả dừa tươi.'],
            ['Kem 3 Vị', 3, 45000, 'Ly', 'Kem 3 vị: socola, vanilla, dâu tây.'],
            ['Trái Cây Thập Cẩm', 3, 50000, 'Đĩa', 'Đĩa trái cây tươi theo mùa.'],
            ['Bánh Tiramisu', 3, 55000, 'Miếng', 'Bánh Tiramisu Ý với cà phê và mascarpone.'],
            ['Sữa Chua Nếp Cẩm', 3, 35000, 'Ly', 'Sữa chua mịn với nếp cẩm dẻo thơm.'],
            
            // DANH MỤC 4: ĐỒ UỐNG
            ['Coca-Cola', 4, 25000, 'Lon', 'Nước ngọt có gas Coca-Cola.'],
            ['Pepsi', 4, 25000, 'Lon', 'Nước ngọt có gas Pepsi.'],
            ['7Up', 4, 25000, 'Lon', 'Nước ngọt có gas 7Up.'],
            ['Sprite', 4, 25000, 'Lon', 'Nước ngọt có gas Sprite.'],
            ['Fanta Cam', 4, 25000, 'Lon', 'Nước ngọt có gas Fanta vị cam.'],
            ['Red Bull', 4, 30000, 'Lon', 'Nước tăng lực Red Bull.'],
            ['Nước Suối Aquafina', 4, 15000, 'Chai', 'Nước suối tinh khiết Aquafina 500ml.'],
            ['Nước Khoáng Lavie', 4, 18000, 'Chai', 'Nước khoáng thiên nhiên Lavie 500ml.'],
            ['Trà Đá', 4, 10000, 'Ly', 'Trà đá mát lạnh.'],
            ['Trà Chanh', 4, 25000, 'Ly', 'Trà chanh tươi mát.'],
            ['Trà Đào', 4, 35000, 'Ly', 'Trà đào cam sả thơm ngon.'],
            ['Trà Sữa Trân Châu', 4, 40000, 'Ly', 'Trà sữa trân châu đường đen.'],
            ['Cà Phê Đen Đá', 4, 30000, 'Ly', 'Cà phê đen đá pha phin truyền thống.'],
            ['Cà Phê Sữa Đá', 4, 35000, 'Ly', 'Cà phê sữa đá béo ngậy.'],
            ['Bạc Xỉu', 4, 35000, 'Ly', 'Bạc xỉu - cà phê sữa nhiều sữa.'],
            ['Cappuccino', 4, 50000, 'Ly', 'Cappuccino Ý với bọt sữa mịn.'],
            ['Latte', 4, 50000, 'Ly', 'Cà phê Latte với sữa tươi.'],
            ['Nước Ép Cam', 4, 40000, 'Ly', 'Nước ép cam tươi 100%.'],
            ['Nước Ép Dưa Hấu', 4, 35000, 'Ly', 'Nước ép dưa hấu mát lạnh.'],
            ['Nước Ép Táo', 4, 40000, 'Ly', 'Nước ép táo tươi nguyên chất.'],
            ['Sinh Tố Bơ', 4, 45000, 'Ly', 'Sinh tố bơ béo ngậy với sữa đặc.'],
            ['Sinh Tố Xoài', 4, 40000, 'Ly', 'Sinh tố xoài chín ngọt lịm.'],
            ['Sinh Tố Dâu', 4, 45000, 'Ly', 'Sinh tố dâu tây tươi mát.'],
            ['Bia Tiger', 4, 30000, 'Lon', 'Bia Tiger lon 330ml.'],
            ['Bia Heineken', 4, 35000, 'Lon', 'Bia Heineken lon 330ml.'],
            ['Bia Saigon Special', 4, 28000, 'Lon', 'Bia Saigon Special lon 330ml.'],
            ['Bia 333', 4, 25000, 'Lon', 'Bia 333 lon 330ml.'],
            ['Bia Budweiser', 4, 40000, 'Lon', 'Bia Budweiser lon 330ml.'],
            ['Rượu Vang Đỏ Chile', 4, 350000, 'Chai', 'Rượu vang đỏ nhập khẩu Chile.'],
            ['Rượu Vang Trắng Pháp', 4, 400000, 'Chai', 'Rượu vang trắng nhập khẩu Pháp.'],
            ['Soju Hàn Quốc', 4, 80000, 'Chai', 'Rượu Soju Hàn Quốc vị original.'],
        ];
        
        $stmt = $conn->prepare("INSERT INTO mon_an (TenMonAn, MaDanhMuc, DonGia, DonViTinh, MoTa, TrangThai) VALUES (?, ?, ?, ?, ?, 'Còn hàng')");
        
        $count = 0;
        foreach ($monAns as $mon) {
            // Kiểm tra món đã tồn tại chưa
            $check = $conn->prepare("SELECT MaMonAn FROM mon_an WHERE TenMonAn = ?");
            $check->execute([$mon[0]]);
            if (!$check->fetch()) {
                $stmt->execute($mon);
                $count++;
            }
        }
        
        $message = "Đã thêm thành công $count món ăn mới!";
        
    } catch(PDOException $e) {
        $error = "Lỗi: " . $e->getMessage();
    }
}

// Đếm số món hiện tại
$tongMon = 0;
$thongKe = [];
try {
    $stmt = $conn->query("SELECT COUNT(*) as total FROM mon_an");
    $tongMon = $stmt->fetch()['total'];
    
    $stmt = $conn->query("SELECT dm.TenDanhMuc, COUNT(ma.MaMonAn) as SoLuong 
                          FROM danh_muc_mon_an dm 
                          LEFT JOIN mon_an ma ON dm.MaDanhMuc = ma.MaDanhMuc 
                          GROUP BY dm.MaDanhMuc, dm.TenDanhMuc 
                          ORDER BY dm.MaDanhMuc");
    $thongKe = $stmt->fetchAll();
} catch(PDOException $e) {}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm món ăn - Nhà hàng 3CE</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #F5F5DC 0%, #EDE8D0 100%);
            min-height: 100vh;
            padding: 40px 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
        }
        .card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        h1 {
            color: #001f3f;
            margin-bottom: 20px;
            text-align: center;
        }
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .alert-success { background: #d4edda; color: #155724; }
        .alert-error { background: #f8d7da; color: #721c24; }
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin: 20px 0;
        }
        .stat-item {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            text-align: center;
        }
        .stat-item h3 {
            color: #003366;
            font-size: 24px;
        }
        .stat-item p {
            color: #666;
            font-size: 14px;
        }
        .btn {
            display: inline-block;
            padding: 15px 30px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            margin: 5px;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }
        .btn-primary {
            background: linear-gradient(135deg, #001f3f 0%, #003366 100%);
            color: #F5F5DC;
        }
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        .btn-group {
            text-align: center;
            margin-top: 20px;
        }
        .warning {
            background: #fff3cd;
            border: 1px solid #ffc107;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
            color: #856404;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <h1><i class="fas fa-utensils"></i> Thêm Món Ăn Mẫu</h1>
            
            <?php if ($message): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $message; ?>
            </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
            <?php endif; ?>
            
            <p style="text-align: center; color: #666; margin-bottom: 20px;">
                Công cụ này sẽ thêm 61 món ăn mẫu vào database, bao gồm:
            </p>
            
            <div class="stat-grid">
                <div class="stat-item">
                    <h3>8</h3>
                    <p>Món Khai Vị</p>
                </div>
                <div class="stat-item">
                    <h3>18</h3>
                    <p>Món Chính</p>
                </div>
                <div class="stat-item">
                    <h3>8</h3>
                    <p>Tráng Miệng</p>
                </div>
                <div class="stat-item">
                    <h3>27</h3>
                    <p>Đồ Uống</p>
                </div>
            </div>
            
            <h3 style="color: #001f3f; margin: 20px 0 10px;">Thống kê hiện tại:</h3>
            <div class="stat-grid">
                <div class="stat-item" style="grid-column: span 2;">
                    <h3><?php echo $tongMon; ?></h3>
                    <p>Tổng số món trong database</p>
                </div>
                <?php foreach ($thongKe as $tk): ?>
                <div class="stat-item">
                    <h3><?php echo $tk['SoLuong']; ?></h3>
                    <p><?php echo htmlspecialchars($tk['TenDanhMuc']); ?></p>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="btn-group">
                <a href="?action=add" class="btn btn-primary" onclick="return confirm('Thêm món ăn mới (giữ lại món cũ)?')">
                    <i class="fas fa-plus"></i> Thêm món mới
                </a>
                <a href="?action=add&reset=1" class="btn btn-danger" onclick="return confirm('⚠️ CẢNH BÁO: Xóa TẤT CẢ món cũ và thêm mới?\n\nHành động này không thể hoàn tác!')">
                    <i class="fas fa-sync"></i> Reset & Thêm mới
                </a>
            </div>
            
            <div class="warning">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Lưu ý:</strong> Nút "Reset & Thêm mới" sẽ xóa tất cả món ăn hiện có và chi tiết hóa đơn liên quan!
            </div>
            
            <div class="btn-group" style="border-top: 1px solid #eee; padding-top: 20px;">
                <a href="public/index.php" class="btn btn-secondary">
                    <i class="fas fa-home"></i> Về trang chủ
                </a>
                <a href="admin/quan_ly_mon_an.php" class="btn btn-secondary">
                    <i class="fas fa-cog"></i> Quản lý món ăn
                </a>
            </div>
        </div>
    </div>
</body>
</html>
