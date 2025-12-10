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

// Xử lý thanh toán
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['thanh_toan'])) {
    $maHoaDon = intval($_POST['MaHoaDon']);
    $phuongThuc = $_POST['PhuongThuc'] ?? 'Tiền mặt';
    
    try {
        // Kiểm tra hóa đơn thuộc về khách hàng này
        $stmt = $conn->prepare("SELECT * FROM hoa_don WHERE MaHoaDon = ? AND MaKhachHang = ? AND TrangThai = 'Chưa thanh toán'");
        $stmt->execute([$maHoaDon, $maKhachHang]);
        $hoaDon = $stmt->fetch();
        
        if ($hoaDon) {
            // Cập nhật trạng thái hóa đơn
            $stmt = $conn->prepare("UPDATE hoa_don SET TrangThai = 'Đã thanh toán', ThoiGianRa = NOW(), GhiChu = CONCAT(IFNULL(GhiChu, ''), ' | Thanh toán: ', ?) WHERE MaHoaDon = ?");
            $stmt->execute([$phuongThuc, $maHoaDon]);
            
            // Cộng điểm tích lũy cho khách hàng (1 điểm = 10,000đ)
            $diemCong = floor($hoaDon['TongTien'] / 10000);
            if ($diemCong > 0) {
                $stmt = $conn->prepare("UPDATE khach_hang SET DiemTichLuy = DiemTichLuy + ? WHERE MaKhachHang = ?");
                $stmt->execute([$diemCong, $maKhachHang]);
            }
            
            $message = 'Thanh toán thành công! Bạn được cộng ' . $diemCong . ' điểm tích lũy.';
        } else {
            $error = 'Hóa đơn không hợp lệ hoặc đã được thanh toán!';
        }
    } catch(PDOException $e) {
        $error = 'Lỗi: ' . $e->getMessage();
    }
}

// Lấy danh sách hóa đơn chưa thanh toán
$hoaDonChuaThanhToan = [];
try {
    $stmt = $conn->prepare("SELECT * FROM hoa_don WHERE MaKhachHang = ? AND TrangThai = 'Chưa thanh toán' ORDER BY ThoiGianVao DESC");
    $stmt->execute([$maKhachHang]);
    $hoaDonChuaThanhToan = $stmt->fetchAll();
} catch(PDOException $e) {}

// Lấy lịch sử thanh toán
$lichSuThanhToan = [];
try {
    $stmt = $conn->prepare("SELECT * FROM hoa_don WHERE MaKhachHang = ? AND TrangThai IN ('Đã thanh toán', 'Đã hủy') ORDER BY ThoiGianVao DESC LIMIT 10");
    $stmt->execute([$maKhachHang]);
    $lichSuThanhToan = $stmt->fetchAll();
} catch(PDOException $e) {}

// Xem chi tiết hóa đơn
$chiTietHoaDon = null;
$chiTietMon = [];
if (isset($_GET['view'])) {
    $maHoaDon = intval($_GET['view']);
    try {
        $stmt = $conn->prepare("SELECT * FROM hoa_don WHERE MaHoaDon = ? AND MaKhachHang = ?");
        $stmt->execute([$maHoaDon, $maKhachHang]);
        $chiTietHoaDon = $stmt->fetch();
        
        if ($chiTietHoaDon) {
            $stmt = $conn->prepare("SELECT ct.*, ma.TenMonAn, ma.HinhAnh FROM chi_tiet_hoa_don ct JOIN mon_an ma ON ct.MaMonAn = ma.MaMonAn WHERE ct.MaHoaDon = ?");
            $stmt->execute([$maHoaDon]);
            $chiTietMon = $stmt->fetchAll();
        }
    } catch(PDOException $e) {}
}

// Tính tổng tiền chưa thanh toán
$tongChuaThanhToan = 0;
foreach ($hoaDonChuaThanhToan as $hd) {
    $tongChuaThanhToan += $hd['TongTien'];
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nhà hàng 3CE - Thanh toán</title>
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
        .summary-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .summary-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        .summary-card i {
            font-size: 40px;
            color: #001f3f;
            margin-bottom: 10px;
        }
        .summary-card h3 {
            font-size: 28px;
            color: #003366;
            margin-bottom: 5px;
        }
        .summary-card p { color: #666; }
        .summary-card.warning { border-left: 4px solid #ffc107; }
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
        .invoice-item {
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
        .invoice-item:hover { border-color: #001f3f; }
        .invoice-info h4 { color: #001f3f; margin-bottom: 5px; }
        .invoice-info p { color: #666; font-size: 14px; }
        .invoice-amount {
            font-size: 24px;
            font-weight: bold;
            color: #003366;
        }
        .status-badge {
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 600;
        }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-paid { background: #d4edda; color: #155724; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .btn-view { background: #001f3f; color: #F5F5DC; }
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
        .detail-section {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .detail-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        .detail-item:last-child { border-bottom: none; }
        .detail-item-image {
            width: 50px;
            height: 50px;
            border-radius: 8px;
            object-fit: cover;
        }
        .detail-item-placeholder {
            width: 50px;
            height: 50px;
            border-radius: 8px;
            background: linear-gradient(135deg, #001f3f 0%, #003366 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #F5F5DC;
        }
        .detail-item-info { flex: 1; }
        .detail-item-name { font-weight: 600; color: #001f3f; }
        .detail-item-price { color: #666; font-size: 13px; }
        .detail-item-total { font-weight: bold; color: #003366; }
        .payment-note {
            background: #e7f3ff;
            border-left: 4px solid #007bff;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
        }
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .alert-success { background: #d4edda; color: #155724; }
        .alert-error { background: #f8d7da; color: #721c24; }
        .payment-methods {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        .payment-method {
            padding: 20px;
            border: 2px solid #ddd;
            border-radius: 10px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        .payment-method:hover, .payment-method.selected {
            border-color: #001f3f;
            background: #f0f8ff;
        }
        .payment-method i {
            font-size: 30px;
            color: #001f3f;
            margin-bottom: 10px;
            display: block;
        }
        .payment-method span {
            font-weight: 600;
            color: #001f3f;
        }
        .btn-pay {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 20px;
        }
        .btn-pay:hover {
            background: linear-gradient(135deg, #218838 0%, #1aa179 100%);
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
            padding-bottom: 15px;
            border-bottom: 2px solid #eee;
        }
        .modal-header h3 { color: #001f3f; }
        .close-btn {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <div class="logo">
                <i class="fas fa-credit-card"></i>
                <span>Thanh toán</span>
            </div>
            <a href="customer_dashboard.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
        </div>
    </div>

    <div class="container">
        <div class="page-header">
            <h1><i class="fas fa-credit-card"></i> Thanh toán hóa đơn</h1>
            <p>Xem và thanh toán các hóa đơn của bạn</p>
        </div>

        <?php if ($message): ?>
        <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
        <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="summary-cards">
            <div class="summary-card <?php echo $tongChuaThanhToan > 0 ? 'warning' : ''; ?>">
                <i class="fas fa-file-invoice-dollar"></i>
                <h3><?php echo count($hoaDonChuaThanhToan); ?></h3>
                <p>Hóa đơn chờ thanh toán</p>
            </div>
            <div class="summary-card">
                <i class="fas fa-money-bill-wave"></i>
                <h3><?php echo number_format($tongChuaThanhToan, 0, ',', '.'); ?>đ</h3>
                <p>Tổng tiền cần thanh toán</p>
            </div>
        </div>

        <?php if ($chiTietHoaDon): ?>
        <div class="card">
            <h2><i class="fas fa-file-invoice"></i> Chi tiết hóa đơn #<?php echo $chiTietHoaDon['MaHoaDon']; ?></h2>
            
            <div class="detail-section">
                <p><strong>Thời gian:</strong> <?php echo date('d/m/Y H:i', strtotime($chiTietHoaDon['ThoiGianVao'])); ?></p>
                <p><strong>Trạng thái:</strong> 
                    <span class="status-badge <?php 
                        echo $chiTietHoaDon['TrangThai'] === 'Đã thanh toán' ? 'status-paid' : 
                            ($chiTietHoaDon['TrangThai'] === 'Đã hủy' ? 'status-cancelled' : 'status-pending'); 
                    ?>">
                        <?php echo htmlspecialchars($chiTietHoaDon['TrangThai']); ?>
                    </span>
                </p>
            </div>
            
            <h4 style="margin: 20px 0 15px; color: #001f3f;">Chi tiết món ăn:</h4>
            <?php foreach ($chiTietMon as $mon): ?>
            <div class="detail-item">
                <?php if (!empty($mon['HinhAnh'])): ?>
                    <img src="<?php echo htmlspecialchars($mon['HinhAnh']); ?>" class="detail-item-image">
                <?php else: ?>
                    <div class="detail-item-placeholder"><i class="fas fa-utensils"></i></div>
                <?php endif; ?>
                <div class="detail-item-info">
                    <div class="detail-item-name"><?php echo htmlspecialchars($mon['TenMonAn']); ?></div>
                    <div class="detail-item-price"><?php echo number_format($mon['DonGia'], 0, ',', '.'); ?>đ x <?php echo $mon['SoLuong']; ?></div>
                </div>
                <div class="detail-item-total"><?php echo number_format($mon['ThanhTien'], 0, ',', '.'); ?>đ</div>
            </div>
            <?php endforeach; ?>
            
            <div style="margin-top: 20px; padding-top: 15px; border-top: 2px solid #001f3f; text-align: right;">
                <span style="font-size: 20px;">Tổng cộng: </span>
                <span style="font-size: 28px; font-weight: bold; color: #003366;">
                    <?php echo number_format($chiTietHoaDon['TongTien'], 0, ',', '.'); ?>đ
                </span>
            </div>
            
            <?php if ($chiTietHoaDon['TrangThai'] === 'Chưa thanh toán'): ?>
            <div class="payment-note">
                <i class="fas fa-info-circle"></i> 
                <strong>Thanh toán online:</strong> Chọn phương thức thanh toán bên dưới để thanh toán ngay.
            </div>
            
            <button type="button" class="btn-pay" onclick="openPaymentModal(<?php echo $chiTietHoaDon['MaHoaDon']; ?>, <?php echo $chiTietHoaDon['TongTien']; ?>)">
                <i class="fas fa-credit-card"></i> Thanh toán ngay
            </button>
            <?php endif; ?>
            
            <div style="margin-top: 20px;">
                <a href="thanh_toan.php" class="btn btn-view">
                    <i class="fas fa-arrow-left"></i> Quay lại danh sách
                </a>
            </div>
        </div>
        <?php endif; ?>

        <div class="card">
            <h2><i class="fas fa-clock"></i> Hóa đơn chờ thanh toán</h2>
            <?php if (empty($hoaDonChuaThanhToan)): ?>
            <div class="empty-state">
                <i class="fas fa-check-circle"></i>
                <h3>Không có hóa đơn nào</h3>
                <p>Bạn không có hóa đơn nào cần thanh toán</p>
            </div>
            <?php else: ?>
                <?php foreach ($hoaDonChuaThanhToan as $hd): ?>
                <div class="invoice-item">
                    <div class="invoice-info">
                        <h4>Hóa đơn #<?php echo $hd['MaHoaDon']; ?></h4>
                        <p><i class="fas fa-calendar"></i> <?php echo date('d/m/Y H:i', strtotime($hd['ThoiGianVao'])); ?></p>
                    </div>
                    <div class="invoice-amount"><?php echo number_format($hd['TongTien'], 0, ',', '.'); ?>đ</div>
                    <span class="status-badge status-pending">Chờ thanh toán</span>
                    <a href="?view=<?php echo $hd['MaHoaDon']; ?>" class="btn btn-view">
                        <i class="fas fa-eye"></i> Chi tiết
                    </a>
                    <button type="button" class="btn btn-view" style="background: #28a745;" onclick="openPaymentModal(<?php echo $hd['MaHoaDon']; ?>, <?php echo $hd['TongTien']; ?>)">
                        <i class="fas fa-credit-card"></i> Thanh toán
                    </button>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <div class="card">
            <h2><i class="fas fa-history"></i> Lịch sử thanh toán</h2>
            <?php if (empty($lichSuThanhToan)): ?>
            <div class="empty-state">
                <i class="fas fa-receipt"></i>
                <h3>Chưa có lịch sử</h3>
                <p>Lịch sử thanh toán sẽ hiển thị ở đây</p>
            </div>
            <?php else: ?>
                <?php foreach ($lichSuThanhToan as $hd): ?>
                <div class="invoice-item">
                    <div class="invoice-info">
                        <h4>Hóa đơn #<?php echo $hd['MaHoaDon']; ?></h4>
                        <p><i class="fas fa-calendar"></i> <?php echo date('d/m/Y H:i', strtotime($hd['ThoiGianVao'])); ?></p>
                    </div>
                    <div class="invoice-amount"><?php echo number_format($hd['TongTien'], 0, ',', '.'); ?>đ</div>
                    <span class="status-badge <?php echo $hd['TrangThai'] === 'Đã thanh toán' ? 'status-paid' : 'status-cancelled'; ?>">
                        <?php echo htmlspecialchars($hd['TrangThai']); ?>
                    </span>
                    <a href="?view=<?php echo $hd['MaHoaDon']; ?>" class="btn btn-view">
                        <i class="fas fa-eye"></i> Chi tiết
                    </a>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal Thanh toán -->
    <div id="paymentModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-credit-card"></i> Thanh toán hóa đơn</h3>
                <button class="close-btn" onclick="closePaymentModal()">&times;</button>
            </div>
            
            <form method="POST" id="paymentForm">
                <input type="hidden" name="MaHoaDon" id="pay_MaHoaDon">
                <input type="hidden" name="PhuongThuc" id="pay_PhuongThuc" value="Tiền mặt">
                
                <div style="text-align: center; margin-bottom: 20px;">
                    <p style="color: #666;">Số tiền cần thanh toán:</p>
                    <p style="font-size: 32px; font-weight: bold; color: #003366;" id="pay_Amount">0đ</p>
                </div>
                
                <p style="margin-bottom: 15px; color: #001f3f; font-weight: 600;">
                    <i class="fas fa-wallet"></i> Chọn phương thức thanh toán:
                </p>
                
                <div class="payment-methods">
                    <div class="payment-method selected" onclick="selectPayment(this, 'Tiền mặt')">
                        <i class="fas fa-money-bill-wave"></i>
                        <span>Tiền mặt</span>
                    </div>
                    <div class="payment-method" onclick="selectPayment(this, 'Thẻ ngân hàng')">
                        <i class="fas fa-credit-card"></i>
                        <span>Thẻ ngân hàng</span>
                    </div>
                    <div class="payment-method" onclick="selectPayment(this, 'Ví MoMo')">
                        <i class="fas fa-mobile-alt"></i>
                        <span>Ví MoMo</span>
                    </div>
                    <div class="payment-method" onclick="selectPayment(this, 'ZaloPay')">
                        <i class="fas fa-qrcode"></i>
                        <span>ZaloPay</span>
                    </div>
                </div>
                
                <div class="payment-note" style="margin: 20px 0;">
                    <i class="fas fa-gift"></i> 
                    Bạn sẽ được cộng <strong id="pay_Points">0</strong> điểm tích lũy sau khi thanh toán!
                </div>
                
                <button type="submit" name="thanh_toan" class="btn-pay">
                    <i class="fas fa-check-circle"></i> Xác nhận thanh toán
                </button>
            </form>
        </div>
    </div>

    <script>
        function openPaymentModal(maHoaDon, tongTien) {
            document.getElementById('pay_MaHoaDon').value = maHoaDon;
            document.getElementById('pay_Amount').textContent = formatPrice(tongTien) + 'đ';
            document.getElementById('pay_Points').textContent = Math.floor(tongTien / 10000);
            document.getElementById('paymentModal').classList.add('active');
        }
        
        function closePaymentModal() {
            document.getElementById('paymentModal').classList.remove('active');
        }
        
        function selectPayment(el, method) {
            document.querySelectorAll('.payment-method').forEach(function(item) {
                item.classList.remove('selected');
            });
            el.classList.add('selected');
            document.getElementById('pay_PhuongThuc').value = method;
        }
        
        function formatPrice(price) {
            return new Intl.NumberFormat('vi-VN').format(price);
        }
        
        document.getElementById('paymentModal').addEventListener('click', function(e) {
            if (e.target === this) closePaymentModal();
        });
    </script>
</body>
</html>