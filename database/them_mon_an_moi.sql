-- Thêm món ăn mới vào database Nhà hàng 3CE
-- Chạy script này để thêm các món ăn đa dạng

-- =============================================
-- DANH MỤC 1: MÓN KHAI VỊ
-- =============================================
INSERT INTO mon_an (TenMonAn, DonGia, MaDanhMuc, MoTa, TrangThai, DonViTinh) VALUES
('Gỏi cuốn tôm thịt', 45000, 1, 'Gỏi cuốn tươi với tôm, thịt heo, bún, rau sống, chấm mắm nêm đặc biệt', 'Còn hàng', 'Phần'),
('Chả giò hải sản', 55000, 1, 'Chả giò giòn rụm với nhân tôm, cua, mực, ăn kèm rau sống và nước mắm chua ngọt', 'Còn hàng', 'Phần'),
('Súp cua', 65000, 1, 'Súp cua thơm ngon với thịt cua tươi, trứng cút, nấm hương', 'Còn hàng', 'Tô'),
('Salad trộn dầu giấm', 50000, 1, 'Salad tươi mát với rau xà lách, cà chua, dưa leo, trộn dầu giấm', 'Còn hàng', 'Phần'),
('Đậu hũ chiên giòn', 35000, 1, 'Đậu hũ chiên vàng giòn, ăn kèm nước tương gừng', 'Còn hàng', 'Phần'),
('Bánh tôm Hồ Tây', 60000, 1, 'Bánh tôm giòn tan kiểu Hà Nội, ăn kèm rau sống và nước mắm', 'Còn hàng', 'Phần'),
('Nem chua rán', 45000, 1, 'Nem chua rán giòn, vị chua ngọt đặc trưng', 'Còn hàng', 'Phần'),
('Cánh gà chiên nước mắm', 75000, 1, 'Cánh gà chiên giòn, rim nước mắm tỏi ớt thơm lừng', 'Còn hàng', 'Phần');

-- =============================================
-- DANH MỤC 2: MÓN CHÍNH
-- =============================================
INSERT INTO mon_an (TenMonAn, DonGia, MaDanhMuc, MoTa, TrangThai, DonViTinh) VALUES
('Cơm chiên Dương Châu', 65000, 2, 'Cơm chiên với tôm, lạp xưởng, trứng, đậu Hà Lan, cà rốt', 'Còn hàng', 'Phần'),
('Phở bò tái chín', 55000, 2, 'Phở bò truyền thống với thịt bò tái, chín, nước dùng đậm đà', 'Còn hàng', 'Tô'),
('Bún bò Huế', 60000, 2, 'Bún bò Huế cay nồng với giò heo, chả cua, huyết', 'Còn hàng', 'Tô'),
('Cá kho tộ', 85000, 2, 'Cá lóc kho tộ đậm đà, thơm mùi tiêu và nước màu', 'Còn hàng', 'Phần'),
('Sườn xào chua ngọt', 95000, 2, 'Sườn heo xào với dứa, cà chua, ớt chuông, vị chua ngọt hài hòa', 'Còn hàng', 'Phần'),
('Gà nướng mật ong', 180000, 2, 'Gà ta nướng mật ong, da giòn thịt mềm, thơm lừng', 'Còn hàng', 'Con'),
('Bò lúc lắc', 150000, 2, 'Thịt bò Úc xào với ớt chuông, hành tây, khoai tây chiên', 'Còn hàng', 'Phần'),
('Tôm sú nướng muối ớt', 220000, 2, 'Tôm sú tươi nướng muối ớt, ăn kèm muối tiêu chanh', 'Còn hàng', 'Phần'),
('Cua rang me', 350000, 2, 'Cua biển rang me chua ngọt, thịt cua chắc ngọt', 'Còn hàng', 'Con'),
('Lẩu Thái hải sản', 280000, 2, 'Lẩu Thái chua cay với tôm, mực, cá, nghêu, rau các loại', 'Còn hàng', 'Nồi'),
('Lẩu gà lá é', 250000, 2, 'Lẩu gà ta với lá é thơm, nước dùng thanh ngọt', 'Còn hàng', 'Nồi'),
('Vịt quay Bắc Kinh', 320000, 2, 'Vịt quay da giòn, thịt mềm, ăn kèm bánh tráng và hành', 'Còn hàng', 'Con'),
('Cá chẽm hấp Hồng Kông', 280000, 2, 'Cá chẽm tươi hấp xì dầu, gừng, hành, thịt cá trắng ngọt', 'Còn hàng', 'Con'),
('Mì xào hải sản', 75000, 2, 'Mì xào giòn với tôm, mực, cải thìa, nấm', 'Còn hàng', 'Phần'),
('Cơm gà Hải Nam', 70000, 2, 'Cơm gà truyền thống Singapore, gà luộc mềm, cơm thơm dầu gà', 'Còn hàng', 'Phần'),
('Bò bít tết', 165000, 2, 'Bò Úc áp chảo medium, ăn kèm khoai tây nghiền và rau củ', 'Còn hàng', 'Phần');

-- =============================================
-- DANH MỤC 3: TRÁNG MIỆNG
-- =============================================
INSERT INTO mon_an (TenMonAn, DonGia, MaDanhMuc, MoTa, TrangThai, DonViTinh) VALUES
('Chè khúc bạch', 35000, 3, 'Chè khúc bạch mát lạnh với vải, nhãn, thạch dừa', 'Còn hàng', 'Ly'),
('Bánh flan caramel', 30000, 3, 'Bánh flan mềm mịn với lớp caramel đắng nhẹ', 'Còn hàng', 'Phần'),
('Kem dừa trái dừa', 45000, 3, 'Kem dừa béo ngậy trong trái dừa tươi', 'Còn hàng', 'Trái'),
('Chè đậu đỏ', 25000, 3, 'Chè đậu đỏ nấu với nước cốt dừa, thơm ngọt', 'Còn hàng', 'Ly'),
('Trái cây tươi theo mùa', 55000, 3, 'Đĩa trái cây tươi theo mùa: xoài, dưa hấu, thanh long, ổi', 'Còn hàng', 'Đĩa'),
('Bánh tiramisu', 50000, 3, 'Bánh tiramisu Ý với lớp kem mascarpone và cà phê', 'Còn hàng', 'Miếng'),
('Sữa chua dẻo', 28000, 3, 'Sữa chua dẻo mịn, ăn kèm mứt trái cây', 'Còn hàng', 'Hũ'),
('Chè thái', 32000, 3, 'Chè Thái với mít, thạch, nước cốt dừa, đá bào', 'Còn hàng', 'Ly');

-- =============================================
-- DANH MỤC 4: ĐỒ UỐNG
-- =============================================
INSERT INTO mon_an (TenMonAn, DonGia, MaDanhMuc, MoTa, TrangThai, DonViTinh) VALUES
('Trà đào cam sả', 35000, 4, 'Trà đào thơm mát với cam tươi và sả', 'Còn hàng', 'Ly'),
('Sinh tố bơ', 40000, 4, 'Sinh tố bơ béo ngậy với sữa đặc', 'Còn hàng', 'Ly'),
('Nước ép cam tươi', 35000, 4, 'Nước cam tươi nguyên chất, giàu vitamin C', 'Còn hàng', 'Ly'),
('Cà phê sữa đá', 25000, 4, 'Cà phê phin truyền thống với sữa đặc', 'Còn hàng', 'Ly'),
('Trà sữa trân châu', 38000, 4, 'Trà sữa thơm ngon với trân châu đường đen', 'Còn hàng', 'Ly'),
('Nước dừa tươi', 30000, 4, 'Nước dừa xiêm tươi mát, ngọt thanh', 'Còn hàng', 'Trái'),
('Soda chanh', 28000, 4, 'Soda chanh tươi mát, giải khát', 'Còn hàng', 'Ly'),
('Bia Tiger', 25000, 4, 'Bia Tiger lon 330ml', 'Còn hàng', 'Lon'),
('Bia Heineken', 30000, 4, 'Bia Heineken lon 330ml', 'Còn hàng', 'Lon'),
('Rượu vang đỏ Chile', 450000, 4, 'Rượu vang đỏ Chile, hương vị đậm đà', 'Còn hàng', 'Chai'),
('Nước suối', 15000, 4, 'Nước suối Aquafina 500ml', 'Còn hàng', 'Chai'),
('Coca Cola', 18000, 4, 'Coca Cola lon 330ml', 'Còn hàng', 'Lon'),
('Trà atiso', 30000, 4, 'Trà atiso Đà Lạt, thanh mát giải nhiệt', 'Còn hàng', 'Ly'),
('Sinh tố xoài', 38000, 4, 'Sinh tố xoài chín ngọt với sữa tươi', 'Còn hàng', 'Ly');

-- Thông báo hoàn thành
SELECT 'Đã thêm thành công các món ăn mới!' as ThongBao;
