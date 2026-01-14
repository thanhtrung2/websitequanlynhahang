<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';

header('Content-Type: application/json');

// Lấy giỏ hàng từ session
$cart = $_SESSION['cart'] ?? [];

$items = [];
$total = 0;
$count = 0;

foreach ($cart as $index => $item) {
    $items[] = [
        'index' => $index,
        'MaMonAn' => $item['MaMonAn'],
        'TenMonAn' => $item['TenMonAn'],
        'DonGia' => $item['DonGia'],
        'SoLuong' => $item['SoLuong'],
        'ThanhTien' => $item['DonGia'] * $item['SoLuong'],
        'HinhAnh' => getImagePath($item['HinhAnh'] ?? '', 'public')
    ];
    $total += $item['DonGia'] * $item['SoLuong'];
    $count += $item['SoLuong'];
}

echo json_encode([
    'items' => $items,
    'total' => $total,
    'count' => $count,
    'isLoggedIn' => isset($_SESSION['customer_id'])
]);
