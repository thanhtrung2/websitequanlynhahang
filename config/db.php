<?php
// Cấu hình kết nối database
$host = 'localhost';
$dbname = 'quanlynhahang';
$username = 'root';
$password = '';

try {
    // Tạo kết nối PDO
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    
    // Thiết lập chế độ báo lỗi
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    // echo "Kết nối thành công!"; // Bỏ comment dòng này để test kết nối
    
} catch(PDOException $e) {
    die("Lỗi kết nối database: " . $e->getMessage());
}
?>