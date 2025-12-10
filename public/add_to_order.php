<?php
session_start();
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json');

// Kiểm tra đăng nhập khách hàng
if (!isset($_SESSION['customer_id'])) {
    echo json_encode(['success' => false, 'message' => 'Vui lòng đăng nhập để đặt món!']);
    exit();
}

// Lấy dữ liệu từ request
$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['MaMonAn']) || !isset($data['SoLuong'])) {
    echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ!']);
    exit();
}

$maMonAn = intval($data['MaMonAn']);
$soLuong = intval($data['SoLuong']);
$maKhachHang = $_SESSION['customer_id'];

if ($soLuong < 1 || $soLuong > 99) {
    echo json_encode(['success' => false, 'message' => 'Số lượng không hợp lệ!']);
    exit();
}

try {
    // Kiểm tra món ăn có tồn tại và còn hàng không
    $stmt = $conn->prepare("SELECT * FROM mon_an WHERE MaMonAn = ?");
    $stmt->execute([$maMonAn]);
    $monAn = $stmt->fetch();
    
    if (!$monAn) {
        echo json_encode(['success' => false, 'message' => 'Món ăn không tồn tại!']);
        exit();
    }
    
    if ($monAn['TrangThai'] === 'Hết hàng') {
        echo json_encode(['success' => false, 'message' => 'Món ăn này hiện đã hết hàng!']);
        exit();
    }
    
    // Khởi tạo giỏ hàng trong session nếu chưa có
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    
    // Thêm hoặc cập nhật món trong giỏ hàng
    $found = false;
    foreach ($_SESSION['cart'] as &$item) {
        if ($item['MaMonAn'] == $maMonAn) {
            $item['SoLuong'] += $soLuong;
            $found = true;
            break;
        }
    }
    
    if (!$found) {
        $_SESSION['cart'][] = [
            'MaMonAn' => $maMonAn,
            'TenMonAn' => $monAn['TenMonAn'],
            'DonGia' => $monAn['DonGia'],
            'SoLuong' => $soLuong,
            'HinhAnh' => $monAn['HinhAnh']
        ];
    }
    
    // Tính tổng số món trong giỏ
    $totalItems = 0;
    foreach ($_SESSION['cart'] as $item) {
        $totalItems += $item['SoLuong'];
    }
    
    echo json_encode([
        'success' => true, 
        'message' => 'Đã thêm ' . $soLuong . ' ' . $monAn['TenMonAn'] . ' vào giỏ hàng!',
        'cartCount' => $totalItems
    ]);
    
} catch(PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống: ' . $e->getMessage()]);
}
