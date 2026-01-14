<?php
/**
 * Script tạo bảng liên hệ
 * Chạy file này một lần để tạo bảng trong database
 */

require_once __DIR__ . '/config/db.php';

echo "<h2>Tạo bảng liên hệ</h2>";

try {
    // Tạo bảng lien_he
    $sql = "CREATE TABLE IF NOT EXISTS lien_he (
        MaLienHe INT AUTO_INCREMENT PRIMARY KEY,
        HoTen VARCHAR(100) NOT NULL,
        Email VARCHAR(100),
        SoDienThoai VARCHAR(20) NOT NULL,
        ChuDe VARCHAR(200) NOT NULL,
        NoiDung TEXT NOT NULL,
        TrangThai ENUM('Chưa xử lý', 'Đang xử lý', 'Đã xử lý') DEFAULT 'Chưa xử lý',
        PhanHoi TEXT,
        NguoiXuLy INT,
        ThoiGianGui DATETIME DEFAULT CURRENT_TIMESTAMP,
        ThoiGianXuLy DATETIME,
        MaKhachHang INT
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $conn->exec($sql);
    echo "<p style='color: green;'>✓ Tạo bảng lien_he thành công!</p>";
    
    // Kiểm tra xem bảng đã có dữ liệu chưa
    $stmt = $conn->query("SELECT COUNT(*) as total FROM lien_he");
    $count = $stmt->fetch()['total'];
    
    if ($count == 0) {
        // Thêm dữ liệu mẫu
        $sql = "INSERT INTO lien_he (HoTen, Email, SoDienThoai, ChuDe, NoiDung, TrangThai) VALUES
            ('Nguyễn Văn A', 'nguyenvana@email.com', '0901234567', 'Hỏi về thực đơn', 'Cho tôi hỏi nhà hàng có món chay không ạ?', 'Chưa xử lý'),
            ('Trần Thị B', 'tranthib@email.com', '0912345678', 'Đặt tiệc sinh nhật', 'Tôi muốn đặt tiệc sinh nhật cho 20 người vào cuối tuần này', 'Đang xử lý'),
            ('Lê Văn C', 'levanc@email.com', '0923456789', 'Góp ý dịch vụ', 'Nhân viên phục vụ rất nhiệt tình, cảm ơn nhà hàng!', 'Đã xử lý')";
        $conn->exec($sql);
        echo "<p style='color: green;'>✓ Thêm dữ liệu mẫu thành công!</p>";
    } else {
        echo "<p style='color: blue;'>ℹ Bảng đã có $count liên hệ</p>";
    }
    
    echo "<hr>";
    echo "<p><a href='public/lien_he.php'>→ Đi đến trang Liên hệ (Khách hàng)</a></p>";
    echo "<p><a href='admin/quan_ly_lien_he.php'>→ Đi đến trang Quản lý liên hệ (Admin)</a></p>";
    
} catch(PDOException $e) {
    echo "<p style='color: red;'>✗ Lỗi: " . $e->getMessage() . "</p>";
}
?>
