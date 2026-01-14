<?php
/**
 * HƯỚNG DẪN THÊM MÓN ĂN MỚI VÀO DATABASE
 * =====================================
 * 
 * Cách 1: Chạy file PHP này trên trình duyệt
 * ------------------------------------------
 * 1. Mở trình duyệt web (Chrome, Firefox, Edge...)
 * 2. Truy cập: http://localhost/setup_them_mon_an.php
 *    (hoặc đường dẫn tương ứng của bạn)
 * 3. Nhấn nút "Thêm món ăn" để thêm tất cả món ăn mới
 * 
 * Cách 2: Chạy file SQL trực tiếp trong phpMyAdmin
 * ------------------------------------------------
 * 1. Mở phpMyAdmin (http://localhost/phpmyadmin)
 * 2. Chọn database của bạn (ví dụ: nha_hang_3ce)
 * 3. Click tab "SQL" hoặc "Import"
 * 4. Copy nội dung file database/them_mon_an_moi.sql và paste vào
 * 5. Nhấn "Go" hoặc "Thực thi"
 * 
 * Cách 3: Dùng MySQL Command Line
 * -------------------------------
 * 1. Mở Command Prompt / Terminal
 * 2. Chạy lệnh: mysql -u root -p ten_database < database/them_mon_an_moi.sql
 * 3. Nhập mật khẩu MySQL khi được yêu cầu
 */

require_once __DIR__ . '/config/db.php';

$message = '';
$error = '';
$addedCount = 0;

// Xử lý thêm món ăn
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_foods'])) {
    
    $monAnMoi = [
        // DANH MỤC 1: MÓN KHAI VỊ
        ['Gỏi cuốn tôm thịt', 45000, 1, '🌿 Cuốn tươi mát với tôm hồng căng mọng, thịt heo thơm mềm, bún trắng tinh, rau sống giòn ngọt. Chấm cùng mắm nêm gia truyền - hương vị khó quên!', 'Phần'],
        ['Chả giò hải sản', 55000, 1, '🦐 Giòn rụm tan ngay đầu lưỡi! Nhân tôm tươi, cua biển, mực dày thịt cuộn trong lớp vỏ vàng óng. Ăn kèm rau thơm và nước mắm chua ngọt đặc biệt.', 'Phần'],
        ['Súp cua', 65000, 1, '🦀 Tô súp nóng hổi với thịt cua xé sợi thơm ngọt, trứng cút béo bùi, nấm hương đậm đà. Món khai vị hoàn hảo cho bữa tiệc sang trọng!', 'Tô'],
        ['Salad trộn dầu giấm', 50000, 1, '🥗 Tươi mát, thanh nhẹ! Rau xà lách giòn tan, cà chua chín mọng, dưa leo mát lành, tất cả hòa quyện trong sốt dầu giấm chua ngọt dịu dàng.', 'Phần'],
        ['Đậu hũ chiên giòn', 35000, 1, '✨ Bên ngoài vàng giòn, bên trong mềm mịn như lụa! Đậu hũ non chiên hoàn hảo, chấm nước tương gừng thơm nồng - món chay tuyệt vời.', 'Phần'],
        ['Bánh tôm Hồ Tây', 60000, 1, '🏆 Đặc sản Hà Nội chính hiệu! Bánh giòn tan với tôm tươi nguyên con, ăn kèm rau sống và nước mắm pha theo công thức bí truyền.', 'Phần'],
        ['Nem chua rán', 45000, 1, '🔥 Giòn rụm, chua thanh, ngọt dịu! Nem chua Thanh Hóa rán vàng óng, vị chua lên men đặc trưng kết hợp vỏ giòn tan - gây nghiện từ miếng đầu tiên!', 'Phần'],
        ['Cánh gà chiên nước mắm', 75000, 1, '🍗 Best seller! Cánh gà chiên vàng giòn, rim nước mắm tỏi ớt sánh đặc, thơm lừng gian bếp. Vị mặn ngọt cân bằng hoàn hảo, ăn là ghiền!', 'Phần'],
        
        // DANH MỤC 2: MÓN CHÍNH
        ['Cơm chiên Dương Châu', 65000, 2, '🍚 Hạt cơm tơi xốp, vàng óng ánh! Tôm tươi, lạp xưởng thơm, trứng bông mềm, đậu Hà Lan xanh mướt - tất cả hòa quyện trong từng thìa cơm đầy đặn.', 'Phần'],
        ['Phở bò tái chín', 55000, 2, '🍜 Hương vị Việt Nam đích thực! Nước dùng trong veo, ngọt thanh từ xương bò hầm 12 tiếng. Thịt bò tái hồng, chín mềm tan trong miệng. Phở ngon nhất phố!', 'Tô'],
        ['Bún bò Huế', 60000, 2, '🌶️ Cay nồng đậm đà kiểu Huế! Nước lèo đỏ au thơm sả, giò heo mềm rục, chả cua thơm ngọt, huyết đông mịn. Một tô đủ ấm lòng cả ngày!', 'Tô'],
        ['Cá kho tộ', 85000, 2, '🐟 Đậm đà hương vị miền Tây! Cá lóc đồng kho trong tộ đất, thấm đẫm nước màu dừa, thơm nức mùi tiêu. Ăn với cơm trắng nóng - ngon khó cưỡng!', 'Phần'],
        ['Sườn xào chua ngọt', 95000, 2, '🍖 Sườn non mềm tan, sốt chua ngọt sánh mịn! Dứa thơm, cà chua chín, ớt chuông giòn ngọt tạo nên bản giao hưởng hương vị tuyệt vời.', 'Phần'],
        ['Gà nướng mật ong', 180000, 2, '🐔 Gà ta thả vườn nướng than hoa! Da vàng giòn phủ lớp mật ong óng ánh, thịt mềm ngọt thấm gia vị. Thơm lừng, hấp dẫn từ cái nhìn đầu tiên!', 'Con'],
        ['Bò lúc lắc', 150000, 2, '🥩 Thịt bò Úc thượng hạng cắt hạt lựu! Xào lửa lớn với ớt chuông đủ màu, hành tây caramel hóa. Ăn kèm khoai tây chiên giòn - đẳng cấp nhà hàng 5 sao!', 'Phần'],
        ['Tôm sú nướng muối ớt', 220000, 2, '🦐 Tôm sú size khủng, tươi rói từ biển! Nướng than hoa với muối ớt cay nồng, thịt tôm chắc ngọt tự nhiên. Chấm muối tiêu chanh - ngon xuất sắc!', 'Phần'],
        ['Cua rang me', 350000, 2, '🦀 Siêu phẩm hải sản! Cua biển tươi sống rang với sốt me chua ngọt sánh đặc. Thịt cua chắc, ngọt lịm, gạch cua béo ngậy - món ăn đáng thử nhất!', 'Con'],
        ['Lẩu Thái hải sản', 280000, 2, '🍲 Chua cay đúng điệu Thái Lan! Nước lẩu Tom Yum thơm lừng sả, riềng, lá chanh. Tôm, mực, cá, nghêu tươi rói. Ăn xong toát mồ hôi, sảng khoái vô cùng!', 'Nồi'],
        ['Lẩu gà lá é', 250000, 2, '🌿 Thanh mát, bổ dưỡng! Gà ta thả vườn nấu với lá é Đà Lạt thơm dịu. Nước dùng trong veo, ngọt thanh tự nhiên. Món lẩu healthy cho cả gia đình!', 'Nồi'],
        ['Vịt quay Bắc Kinh', 320000, 2, '🦆 Đẳng cấp ẩm thực Trung Hoa! Da vịt giòn tan như bánh, thịt mềm ngọt thấm gia vị. Cuốn bánh tráng với hành, dưa leo - trải nghiệm hoàng gia!', 'Con'],
        ['Cá chẽm hấp Hồng Kông', 280000, 2, '🐟 Tinh hoa ẩm thực Quảng Đông! Cá chẽm biển tươi hấp xì dầu, gừng thái chỉ, hành lá xanh. Thịt cá trắng phau, ngọt lịm, tan ngay đầu lưỡi!', 'Con'],
        ['Mì xào hải sản', 75000, 2, '🍝 Sợi mì vàng giòn, hải sản tươi ngon! Tôm, mực, cải thìa xanh, nấm đông cô xào lửa lớn. Thơm phức, đậm đà - món ăn no bụng, vừa túi tiền!', 'Phần'],
        ['Cơm gà Hải Nam', 70000, 2, '🍗 Huyền thoại Singapore! Gà luộc da vàng óng, thịt mềm mịn. Cơm nấu nước luộc gà thơm béo. Chấm sốt gừng, sốt ớt đặc biệt - ngon không thể tả!', 'Phần'],
        ['Bò bít tết', 165000, 2, '🥩 Steak chuẩn Âu! Thịt bò Úc áp chảo medium rare, bên ngoài cháy xém, bên trong hồng mềm. Kèm khoai tây nghiền mịn, rau củ nướng - fine dining tại bàn!', 'Phần'],
        
        // DANH MỤC 3: TRÁNG MIỆNG
        ['Chè khúc bạch', 35000, 3, '🍮 Mát lạnh, ngọt dịu! Khúc bạch mềm mịn như lụa, vải thiều thơm, nhãn lồng ngọt lịm, thạch dừa giòn sần sật. Tráng miệng hoàn hảo cho ngày hè!', 'Ly'],
        ['Bánh flan caramel', 30000, 3, '🍮 Mềm mịn như mây! Bánh flan trứng béo ngậy với lớp caramel đắng nhẹ sánh mịn. Vị ngọt thanh, tan chảy trong miệng - dessert kinh điển!', 'Phần'],
        ['Kem dừa trái dừa', 45000, 3, '🥥 Độc đáo & thơm ngon! Kem dừa béo ngậy phục vụ ngay trong trái dừa tươi. Cơm dừa non giòn ngọt, nước dừa mát lành - trải nghiệm nhiệt đới!', 'Trái'],
        ['Chè đậu đỏ', 25000, 3, '❤️ Ngọt ngào, ấm áp! Đậu đỏ nấu nhừ bùi bùi, nước cốt dừa béo thơm. Món chè truyền thống mang may mắn, hạnh phúc đến cho bạn!', 'Ly'],
        ['Trái cây tươi theo mùa', 55000, 3, '🍉 Tươi mát, vitamin dồi dào! Xoài chín vàng, dưa hấu đỏ mọng, thanh long ngọt mát, ổi giòn tan. Đĩa trái cây đầy màu sắc cho sức khỏe!', 'Đĩa'],
        ['Bánh tiramisu', 50000, 3, '☕ Hương vị nước Ý! Lớp kem mascarpone mềm mịn, bánh lady finger thấm cà phê espresso, phủ bột cacao đắng nhẹ. Ngọt ngào, quyến rũ!', 'Miếng'],
        ['Sữa chua dẻo', 28000, 3, '🥛 Mịn màng, thanh mát! Sữa chua dẻo homemade với men vi sinh tốt cho tiêu hóa. Ăn kèm mứt trái cây tươi - healthy dessert cho mọi lứa tuổi!', 'Hũ'],
        ['Chè thái', 32000, 3, '🇹🇭 Ngọt mát kiểu Thái! Mít thơm, thạch đủ màu, hạt lựu giòn sần sật, nước cốt dừa béo ngậy, đá bào mát lạnh. Giải nhiệt tức thì!', 'Ly'],
        
        // DANH MỤC 4: ĐỒ UỐNG
        ['Trà đào cam sả', 35000, 4, '🍑 Best seller mùa hè! Trà oolong thơm dịu, đào ngọt mọng, cam tươi chua thanh, sả thơm mát. Giải khát tuyệt vời, uống là ghiền!', 'Ly'],
        ['Sinh tố bơ', 40000, 4, '🥑 Béo ngậy, thơm lừng! Bơ sáp Đắk Lắk xay nhuyễn với sữa đặc Ông Thọ. Đặc sánh, ngọt dịu - nguồn năng lượng dồi dào cho ngày mới!', 'Ly'],
        ['Nước ép cam tươi', 35000, 4, '🍊 100% cam tươi nguyên chất! Không đường, không chất bảo quản. Vitamin C dồi dào, tăng cường sức đề kháng. Tươi mát, khỏe mạnh!', 'Ly'],
        ['Cà phê sữa đá', 25000, 4, '☕ Đậm đà hương vị Việt! Cà phê phin pha chậm, sữa đặc béo ngọt, đá lạnh sảng khoái. Thức tỉnh mọi giác quan, năng lượng cả ngày dài!', 'Ly'],
        ['Trà sữa trân châu', 38000, 4, '🧋 Trend không bao giờ lỗi mốt! Trà đen thơm, sữa tươi béo, trân châu đường đen dẻo dai. Ngọt ngào, vui vẻ trong từng ngụm!', 'Ly'],
        ['Nước dừa tươi', 30000, 4, '🥥 Thanh mát tự nhiên! Dừa xiêm Bến Tre tươi nguyên trái, nước ngọt thanh, cơm dừa non mềm. Giải nhiệt, đẹp da, tốt cho sức khỏe!', 'Trái'],
        ['Soda chanh', 28000, 4, '🍋 Sảng khoái tức thì! Soda tăm mát lạnh, chanh tươi chua thanh, bạc hà thơm mát. Đánh bay cơn khát, tỉnh táo tinh thần!', 'Ly'],
        ['Bia Tiger', 25000, 4, '🍺 Bia số 1 Việt Nam! Vị đắng nhẹ, thanh mát, độ cồn vừa phải. Lon 330ml lạnh sảng khoái - bạn đồng hành của mọi bữa tiệc!', 'Lon'],
        ['Bia Heineken', 30000, 4, '🍺 Premium beer từ Hà Lan! Hương thơm đặc trưng, vị đắng thanh, màu vàng óng ánh. Lon 330ml - nâng tầm đẳng cấp!', 'Lon'],
        ['Rượu vang đỏ Chile', 450000, 4, '🍷 Tinh hoa vùng đất Chile! Hương nho chín, vị tannin mềm mại, hậu vị dài. Kết hợp hoàn hảo với steak và pasta. Chai 750ml.', 'Chai'],
        ['Nước suối', 15000, 4, '💧 Tinh khiết, an toàn! Nước khoáng thiên nhiên Aquafina 500ml. Giải khát đơn giản, tốt cho sức khỏe mỗi ngày!', 'Chai'],
        ['Coca Cola', 18000, 4, '🥤 Huyền thoại giải khát! Vị cola đặc trưng, ga mạnh sảng khoái. Lon 330ml lạnh - hoàn hảo cho mọi bữa ăn!', 'Lon'],
        ['Trà atiso', 30000, 4, '🌸 Thanh nhiệt, giải độc! Trà atiso Đà Lạt nguyên chất, vị đắng nhẹ, hậu ngọt thanh. Tốt cho gan, đẹp da - thức uống healthy!', 'Ly'],
        ['Sinh tố xoài', 38000, 4, '🥭 Ngọt ngào nhiệt đới! Xoài cát Hòa Lộc chín vàng xay nhuyễn với sữa tươi. Thơm lừng, ngọt tự nhiên - vitamin A dồi dào!', 'Ly'],
    ];
    
    try {
        $conn->beginTransaction();
        
        $stmt = $conn->prepare("INSERT INTO mon_an (TenMonAn, DonGia, MaDanhMuc, MoTa, TrangThai, DonViTinh) VALUES (?, ?, ?, ?, 'Còn hàng', ?)");
        
        foreach ($monAnMoi as $mon) {
            // Kiểm tra món đã tồn tại chưa
            $check = $conn->prepare("SELECT MaMonAn FROM mon_an WHERE TenMonAn = ?");
            $check->execute([$mon[0]]);
            
            if (!$check->fetch()) {
                $stmt->execute($mon);
                $addedCount++;
            }
        }
        
        $conn->commit();
        $message = "Đã thêm thành công $addedCount món ăn mới!";
        
    } catch(PDOException $e) {
        $conn->rollBack();
        $error = "Lỗi: " . $e->getMessage();
    }
}

// Đếm số món hiện có
$totalMon = 0;
try {
    $stmt = $conn->query("SELECT COUNT(*) as total FROM mon_an");
    $totalMon = $stmt->fetch()['total'];
} catch(PDOException $e) {}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thêm món ăn mới - Nhà hàng 3CE</title>
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
            max-width: 800px;
            margin: 0 auto;
        }
        .card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15);
            margin-bottom: 30px;
        }
        h1 {
            color: #001f3f;
            text-align: center;
            margin-bottom: 10px;
        }
        h1 i { color: #D4AF37; }
        .subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 30px;
        }
        .stats {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-bottom: 30px;
        }
        .stat-item {
            text-align: center;
            padding: 20px 30px;
            background: linear-gradient(135deg, #001f3f, #003366);
            border-radius: 15px;
            color: white;
        }
        .stat-item h3 {
            font-size: 36px;
            color: #D4AF37;
        }
        .stat-item p { opacity: 0.9; }
        .alert {
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border-left: 5px solid #28a745;
        }
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border-left: 5px solid #dc3545;
        }
        .alert i { font-size: 24px; }
        .btn {
            display: block;
            width: 100%;
            padding: 18px;
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(40,167,69,0.4);
        }
        .btn i { margin-right: 10px; }
        .instructions {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 25px;
            margin-top: 20px;
        }
        .instructions h3 {
            color: #001f3f;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .instructions h3 i { color: #D4AF37; }
        .instructions ol {
            margin-left: 20px;
            color: #555;
            line-height: 2;
        }
        .instructions code {
            background: #e9ecef;
            padding: 3px 8px;
            border-radius: 5px;
            font-family: monospace;
        }
        .menu-preview {
            margin-top: 30px;
        }
        .menu-preview h3 {
            color: #001f3f;
            margin-bottom: 15px;
        }
        .menu-category {
            margin-bottom: 20px;
        }
        .menu-category h4 {
            color: #D4AF37;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 2px solid #eee;
        }
        .menu-items {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }
        .menu-item {
            background: #f0f0f0;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 13px;
            color: #333;
        }
        .links {
            text-align: center;
            margin-top: 20px;
        }
        .links a {
            color: #001f3f;
            text-decoration: none;
            margin: 0 15px;
        }
        .links a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <h1><i class="fas fa-utensils"></i> Thêm Món Ăn Mới</h1>
            <p class="subtitle">Nhà hàng 3CE - Hệ thống quản lý thực đơn</p>
            
            <div class="stats">
                <div class="stat-item">
                    <h3><?php echo $totalMon; ?></h3>
                    <p>Món hiện có</p>
                </div>
                <div class="stat-item">
                    <h3>46</h3>
                    <p>Món sẽ thêm</p>
                </div>
            </div>
            
            <?php if ($message): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <div>
                    <strong>Thành công!</strong><br>
                    <?php echo $message; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <div>
                    <strong>Lỗi!</strong><br>
                    <?php echo $error; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <form method="POST">
                <button type="submit" name="add_foods" class="btn">
                    <i class="fas fa-plus-circle"></i> Thêm tất cả món ăn mới
                </button>
            </form>
            
            <div class="menu-preview">
                <h3><i class="fas fa-list"></i> Danh sách món sẽ được thêm:</h3>
                
                <div class="menu-category">
                    <h4><i class="fas fa-seedling"></i> Món khai vị (8 món)</h4>
                    <div class="menu-items">
                        <span class="menu-item">Gỏi cuốn tôm thịt</span>
                        <span class="menu-item">Chả giò hải sản</span>
                        <span class="menu-item">Súp cua</span>
                        <span class="menu-item">Salad trộn dầu giấm</span>
                        <span class="menu-item">Đậu hũ chiên giòn</span>
                        <span class="menu-item">Bánh tôm Hồ Tây</span>
                        <span class="menu-item">Nem chua rán</span>
                        <span class="menu-item">Cánh gà chiên nước mắm</span>
                    </div>
                </div>
                
                <div class="menu-category">
                    <h4><i class="fas fa-drumstick-bite"></i> Món chính (16 món)</h4>
                    <div class="menu-items">
                        <span class="menu-item">Cơm chiên Dương Châu</span>
                        <span class="menu-item">Phở bò tái chín</span>
                        <span class="menu-item">Bún bò Huế</span>
                        <span class="menu-item">Cá kho tộ</span>
                        <span class="menu-item">Sườn xào chua ngọt</span>
                        <span class="menu-item">Gà nướng mật ong</span>
                        <span class="menu-item">Bò lúc lắc</span>
                        <span class="menu-item">Tôm sú nướng muối ớt</span>
                        <span class="menu-item">Cua rang me</span>
                        <span class="menu-item">Lẩu Thái hải sản</span>
                        <span class="menu-item">Lẩu gà lá é</span>
                        <span class="menu-item">Vịt quay Bắc Kinh</span>
                        <span class="menu-item">Cá chẽm hấp Hồng Kông</span>
                        <span class="menu-item">Mì xào hải sản</span>
                        <span class="menu-item">Cơm gà Hải Nam</span>
                        <span class="menu-item">Bò bít tết</span>
                    </div>
                </div>
                
                <div class="menu-category">
                    <h4><i class="fas fa-ice-cream"></i> Tráng miệng (8 món)</h4>
                    <div class="menu-items">
                        <span class="menu-item">Chè khúc bạch</span>
                        <span class="menu-item">Bánh flan caramel</span>
                        <span class="menu-item">Kem dừa trái dừa</span>
                        <span class="menu-item">Chè đậu đỏ</span>
                        <span class="menu-item">Trái cây tươi theo mùa</span>
                        <span class="menu-item">Bánh tiramisu</span>
                        <span class="menu-item">Sữa chua dẻo</span>
                        <span class="menu-item">Chè thái</span>
                    </div>
                </div>
                
                <div class="menu-category">
                    <h4><i class="fas fa-glass-cheers"></i> Đồ uống (14 món)</h4>
                    <div class="menu-items">
                        <span class="menu-item">Trà đào cam sả</span>
                        <span class="menu-item">Sinh tố bơ</span>
                        <span class="menu-item">Nước ép cam tươi</span>
                        <span class="menu-item">Cà phê sữa đá</span>
                        <span class="menu-item">Trà sữa trân châu</span>
                        <span class="menu-item">Nước dừa tươi</span>
                        <span class="menu-item">Soda chanh</span>
                        <span class="menu-item">Bia Tiger</span>
                        <span class="menu-item">Bia Heineken</span>
                        <span class="menu-item">Rượu vang đỏ Chile</span>
                        <span class="menu-item">Nước suối</span>
                        <span class="menu-item">Coca Cola</span>
                        <span class="menu-item">Trà atiso</span>
                        <span class="menu-item">Sinh tố xoài</span>
                    </div>
                </div>
            </div>
            
            <div class="links">
                <a href="public/thuc_don.php"><i class="fas fa-book-open"></i> Xem thực đơn</a>
                <a href="admin/quan_ly_mon_an.php"><i class="fas fa-cog"></i> Quản lý món ăn</a>
                <a href="public/index.php"><i class="fas fa-home"></i> Trang chủ</a>
            </div>
        </div>
    </div>
</body>
</html>
