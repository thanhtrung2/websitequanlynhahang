<?php
/**
 * MIGRATION: Thêm cột MatKhau vào bảng nhan_vien
 */
require_once __DIR__ . '/../config/db.php';

echo '<html><head><meta charset="UTF-8"><title>Database Migration</title></head><body>';
echo '<div style="max-width:800px;margin:50px auto;font-family:Arial,sans-serif;">';
echo '<h2>🔧 DATABASE MIGRATION</h2>';
echo '<hr>';

try {
    // Kiểm tra cột MatKhau đã tồn tại chưa
    $stmt = $conn->query("SHOW COLUMNS FROM nhan_vien LIKE 'MatKhau'");
    $columnExists = $stmt->fetch();
    
    if (!$columnExists) {
        echo "<p>📝 Đang thêm cột MatKhau vào bảng nhan_vien...</p>";
        
        // Thêm cột MatKhau
        $conn->exec("ALTER TABLE `nhan_vien` ADD COLUMN `MatKhau` VARCHAR(255) NULL AFTER `TrangThai`");
        
        echo "<div style='background:#d4edda;padding:15px;margin:10px 0;border-radius:8px;'>";
        echo "<p style='color:#155724;'><b>✅ Đã thêm cột MatKhau thành công!</b></p>";
        echo "</div>";
        
        // Cập nhật mật khẩu mặc định (123456) cho nhân viên hiện có
        $defaultPassword = password_hash('123456', PASSWORD_DEFAULT);
        $conn->exec("UPDATE `nhan_vien` SET `MatKhau` = '$defaultPassword' WHERE `MatKhau` IS NULL");
        
        echo "<div style='background:#cce5ff;padding:15px;margin:10px 0;border-radius:8px;'>";
        echo "<p style='color:#004085;'><b>✅ Đã cập nhật mật khẩu mặc định cho nhân viên hiện có!</b></p>";
        echo "<p>Mật khẩu mặc định: <strong>123456</strong></p>";
        echo "</div>";
        
    } else {
        echo "<div style='background:#fff3cd;padding:15px;margin:10px 0;border-radius:8px;'>";
        echo "<p style='color:#856404;'><b>ℹ️ Cột MatKhau đã tồn tại!</b></p>";
        echo "</div>";
    }
    
    // Hiển thị cấu trúc bảng sau khi migration
    echo "<h3>📋 Cấu trúc bảng nhan_vien sau khi cập nhật:</h3>";
    echo "<table border='1' cellpadding='10' style='border-collapse:collapse;width:100%;'>";
    echo "<tr style='background:#f0f0f0;'><th>Field</th><th>Type</th><th>Null</th><th>Key</th></tr>";
    
    $stmt = $conn->query("SHOW COLUMNS FROM nhan_vien");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>";
        echo "<td><strong>" . $row['Field'] . "</strong></td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . ($row['Key'] ?: '-') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<div style='background:#d1ecf1;padding:15px;margin:20px 0;border-radius:8px;'>";
    echo "<h4 style='color:#0c5460;'>🎯 HƯỚNG DẪN TIẾP THEO:</h4>";
    echo "<ol>";
    echo "<li><a href='../admin/register.php'>Đăng ký nhân viên mới</a> (có hỗ trợ mật khẩu)</li>";
    echo "<li><a href='../admin/admin_login.php'>Đăng nhập với nhân viên hiện có</a> (mật khẩu: 123456)</li>";
    echo "<li><a href='../test_connection.php'>Kiểm tra kết nối database</a></li>";
    echo "</ol></div>";
    
} catch (PDOException $e) {
    echo "<div style='background:#f8d7da;padding:15px;margin:10px 0;border-radius:8px;'>";
    echo "<p style='color:#721c24;'><b>❌ Lỗi:</b> " . $e->getMessage() . "</p>";
    echo "</div>";
}

echo '</div></body></html>';
?>
