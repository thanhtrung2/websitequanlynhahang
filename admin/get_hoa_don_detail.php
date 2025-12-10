<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id'])) {
    echo '<p style="color: red;">Không có quyền truy cập</p>';
    exit();
}

$maHoaDon = intval($_GET['id'] ?? 0);

if (!$maHoaDon) {
    echo '<p style="color: red;">Mã hóa đơn không hợp lệ</p>';
    exit();
}

try {
    // Lấy thông tin hóa đơn
    $stmt = $conn->prepare("SELECT hd.*, kh.HoTen as TenKH, kh.SoDienThoai as SDT_KH, nv.HoTen as TenNV 
                            FROM hoa_don hd 
                            LEFT JOIN khach_hang kh ON hd.MaKhachHang = kh.MaKhachHang 
                            LEFT JOIN nhan_vien nv ON hd.MaNhanVien = nv.MaNhanVien 
                            WHERE hd.MaHoaDon = ?");
    $stmt->execute([$maHoaDon]);
    $hoaDon = $stmt->fetch();
    
    if (!$hoaDon) {
        echo '<p style="color: red;">Không tìm thấy hóa đơn</p>';
        exit();
    }
    
    // Lấy chi tiết hóa đơn
    $stmt = $conn->prepare("SELECT ct.*, ma.TenMonAn, ma.HinhAnh 
                            FROM chi_tiet_hoa_don ct 
                            JOIN mon_an ma ON ct.MaMonAn = ma.MaMonAn 
                            WHERE ct.MaHoaDon = ?");
    $stmt->execute([$maHoaDon]);
    $chiTiet = $stmt->fetchAll();
    
} catch(PDOException $e) {
    echo '<p style="color: red;">Lỗi: ' . $e->getMessage() . '</p>';
    exit();
}
?>

<div style="margin-bottom: 20px;">
    <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
        <span style="color: #666;">Mã hóa đơn:</span>
        <strong>#<?php echo $hoaDon['MaHoaDon']; ?></strong>
    </div>
    <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
        <span style="color: #666;">Khách hàng:</span>
        <strong><?php echo htmlspecialchars($hoaDon['TenKH'] ?? 'Khách vãng lai'); ?></strong>
    </div>
    <?php if ($hoaDon['SDT_KH']): ?>
    <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
        <span style="color: #666;">SĐT:</span>
        <strong><?php echo htmlspecialchars($hoaDon['SDT_KH']); ?></strong>
    </div>
    <?php endif; ?>
    <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
        <span style="color: #666;">Thời gian đặt:</span>
        <strong><?php echo date('d/m/Y H:i', strtotime($hoaDon['ThoiGianVao'])); ?></strong>
    </div>
    <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
        <span style="color: #666;">Trạng thái:</span>
        <span style="padding: 3px 10px; border-radius: 10px; font-size: 13px; <?php 
            echo $hoaDon['TrangThai'] === 'Đã thanh toán' ? 'background: #d4edda; color: #155724;' : 
                ($hoaDon['TrangThai'] === 'Đã hủy' ? 'background: #f8d7da; color: #721c24;' : 'background: #fff3cd; color: #856404;'); 
        ?>">
            <?php echo htmlspecialchars($hoaDon['TrangThai']); ?>
        </span>
    </div>
    <?php if ($hoaDon['TenNV']): ?>
    <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
        <span style="color: #666;">Nhân viên xử lý:</span>
        <strong><?php echo htmlspecialchars($hoaDon['TenNV']); ?></strong>
    </div>
    <?php endif; ?>
    <?php if ($hoaDon['GhiChu']): ?>
    <div style="margin-bottom: 10px;">
        <span style="color: #666;">Ghi chú:</span>
        <p style="background: #f8f9fa; padding: 10px; border-radius: 5px; margin-top: 5px;">
            <?php echo htmlspecialchars($hoaDon['GhiChu']); ?>
        </p>
    </div>
    <?php endif; ?>
</div>

<h4 style="margin-bottom: 15px; color: #001f3f;"><i class="fas fa-utensils"></i> Chi tiết món ăn</h4>

<?php foreach ($chiTiet as $item): ?>
<div style="display: flex; align-items: center; gap: 15px; padding: 10px 0; border-bottom: 1px solid #eee;">
    <?php if (!empty($item['HinhAnh'])): ?>
        <img src="<?php echo htmlspecialchars($item['HinhAnh']); ?>" style="width: 50px; height: 50px; border-radius: 8px; object-fit: cover;">
    <?php else: ?>
        <div style="width: 50px; height: 50px; border-radius: 8px; background: linear-gradient(135deg, #001f3f 0%, #003366 100%); display: flex; align-items: center; justify-content: center; color: #F5F5DC;">
            <i class="fas fa-utensils"></i>
        </div>
    <?php endif; ?>
    <div style="flex: 1;">
        <div style="font-weight: 600; color: #001f3f;"><?php echo htmlspecialchars($item['TenMonAn']); ?></div>
        <div style="color: #666; font-size: 13px;">
            <?php echo number_format($item['DonGia'], 0, ',', '.'); ?>đ x <?php echo $item['SoLuong']; ?>
        </div>
    </div>
    <div style="font-weight: bold; color: #003366;">
        <?php echo number_format($item['ThanhTien'], 0, ',', '.'); ?>đ
    </div>
</div>
<?php endforeach; ?>

<div style="margin-top: 20px; padding-top: 15px; border-top: 2px solid #001f3f; text-align: right;">
    <span style="font-size: 18px;">Tổng cộng: </span>
    <span style="font-size: 24px; font-weight: bold; color: #003366;">
        <?php echo number_format($hoaDon['TongTien'], 0, ',', '.'); ?>đ
    </span>
</div>