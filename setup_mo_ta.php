<?php
/**
 * Script cập nhật mô tả hấp dẫn cho các món ăn
 * Chạy file này một lần để cập nhật database
 */

require_once __DIR__ . '/config/db.php';

$moTaMonAn = [
    1 => 'Gỏi cuốn tươi mát với tôm hồng căng mọng, thịt heo thơm ngọt, bún trắng mềm mịn cùng rau sống giòn tan. Chấm kèm nước mắm chua ngọt đậm đà - món khai vị hoàn hảo cho bữa ăn!',
    2 => 'Chả giò giòn rụm vàng ươm, nhân hải sản tươi ngon với tôm, mực, cua biển. Lớp vỏ mỏng giòn tan trong miệng, hương vị đậm đà khó quên. Ăn kèm rau sống và nước mắm pha đặc biệt!',
    3 => 'Phở bò gia truyền 3 đời với nước dùng ninh xương 12 tiếng, trong vắt thơm lừng. Thịt bò tái mềm tan, nạm gầu béo ngậy, gân sần sật. Bánh phở mềm dai, ăn kèm rau thơm tươi xanh!',
    4 => 'Thịt bò Úc thượng hạng áp chảo vừa chín tới, mềm mọng nước. Sốt tiêu xanh thơm nồng kích thích vị giác. Ăn kèm khoai tây chiên giòn và salad tươi mát - đẳng cấp nhà hàng 5 sao!',
    5 => 'Cá diêu hồng tươi sống từ bè, thịt trắng ngọt thanh. Hấp cùng xì dầu Nhật, gừng tươi và hành lá thơm phức. Giữ nguyên vị ngọt tự nhiên của cá - món ăn healthy cho cả gia đình!',
    6 => 'Bánh flan mềm mịn như lụa, tan ngay trên đầu lưỡi. Lớp caramen đắng nhẹ hòa quyện vị béo ngậy của trứng và sữa. Ướp lạnh vừa đủ - món tráng miệng hoàn hảo sau bữa ăn!',
    7 => 'Nước suối tinh khiết Aquafina, thanh mát giải khát. Qua 7 bước lọc hiện đại, đảm bảo an toàn tuyệt đối. Chai 500ml tiện lợi, phù hợp mọi bữa ăn!',
    8 => 'Coca-Cola lon lạnh sảng khoái với vị cola đặc trưng, ga mạnh sủi tăm. Cảm giác mát lạnh tê đầu lưỡi, giải khát tức thì. Hoàn hảo khi dùng kèm các món chiên rán!'
];

echo "<h2>Cập nhật mô tả món ăn</h2>";
echo "<pre>";

$updated = 0;
$errors = 0;

foreach ($moTaMonAn as $maMonAn => $moTa) {
    try {
        $stmt = $conn->prepare("UPDATE mon_an SET MoTa = ? WHERE MaMonAn = ?");
        $result = $stmt->execute([$moTa, $maMonAn]);
        
        if ($stmt->rowCount() > 0) {
            echo "✓ Đã cập nhật món ăn #$maMonAn\n";
            $updated++;
        } else {
            echo "- Món ăn #$maMonAn không tồn tại hoặc không thay đổi\n";
        }
    } catch (PDOException $e) {
        echo "✗ Lỗi cập nhật món #$maMonAn: " . $e->getMessage() . "\n";
        $errors++;
    }
}

echo "\n========================================\n";
echo "Hoàn thành! Đã cập nhật: $updated món ăn\n";
if ($errors > 0) {
    echo "Lỗi: $errors\n";
}
echo "</pre>";

echo "<p><a href='public/index.php'>← Về trang chủ xem kết quả</a></p>";
