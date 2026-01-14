<?php
session_start();
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['index']) || !isset($data['quantity'])) {
    echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
    exit();
}

$index = intval($data['index']);
$quantity = intval($data['quantity']);

if (!isset($_SESSION['cart'][$index])) {
    echo json_encode(['success' => false, 'message' => 'Món không tồn tại trong giỏ hàng']);
    exit();
}

if ($quantity < 1) {
    // Xóa món nếu số lượng < 1
    array_splice($_SESSION['cart'], $index, 1);
    $message = 'Đã xóa món khỏi giỏ hàng';
} else {
    $_SESSION['cart'][$index]['SoLuong'] = $quantity;
    $message = 'Đã cập nhật số lượng';
}

// Tính lại tổng
$total = 0;
$count = 0;
foreach ($_SESSION['cart'] as $item) {
    $total += $item['DonGia'] * $item['SoLuong'];
    $count += $item['SoLuong'];
}

echo json_encode([
    'success' => true,
    'message' => $message,
    'total' => $total,
    'count' => $count
]);
