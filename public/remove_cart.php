<?php
session_start();
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['index'])) {
    echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
    exit();
}

$index = intval($data['index']);

if (!isset($_SESSION['cart'][$index])) {
    echo json_encode(['success' => false, 'message' => 'Món không tồn tại trong giỏ hàng']);
    exit();
}

$tenMon = $_SESSION['cart'][$index]['TenMonAn'];
array_splice($_SESSION['cart'], $index, 1);

// Tính lại tổng
$total = 0;
$count = 0;
foreach ($_SESSION['cart'] ?? [] as $item) {
    $total += $item['DonGia'] * $item['SoLuong'];
    $count += $item['SoLuong'];
}

echo json_encode([
    'success' => true,
    'message' => 'Đã xóa ' . $tenMon . ' khỏi giỏ hàng',
    'total' => $total,
    'count' => $count
]);
