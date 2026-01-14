-- Thêm nhiều món ăn và thức uống cho nhà hàng 3CE
-- Chạy file này trong phpMyAdmin hoặc MySQL CLI

USE quanlynhahang_db;

-- Xóa dữ liệu cũ (tùy chọn - bỏ comment nếu muốn reset)
-- DELETE FROM chi_tiet_hoa_don;
-- DELETE FROM mon_an;

-- =============================================
-- DANH MỤC 1: MÓN KHAI VỊ (MaDanhMuc = 1)
-- =============================================
INSERT INTO mon_an (TenMonAn, MaDanhMuc, DonGia, DonViTinh, MoTa, TrangThai) VALUES
('Gỏi cuốn tôm thịt', 1, 60000, 'Phần', 'Gỏi cuốn tươi ngon với tôm, thịt, bún và rau sống, chấm mắm nêm đặc biệt.', 'Còn hàng'),
('Chả giò hải sản', 1, 75000, 'Phần', 'Chả giò giòn rụm với nhân hải sản đậm đà, ăn kèm rau sống.', 'Còn hàng'),
('Súp cua', 1, 55000, 'Tô', 'Súp cua thơm ngon, béo ngậy với thịt cua tươi và trứng cút.', 'Còn hàng'),
('Salad trộn dầu giấm', 1, 45000, 'Phần', 'Salad rau củ tươi mát trộn sốt dầu giấm kiểu Ý.', 'Còn hàng'),
('Nem nướng Nha Trang', 1, 70000, 'Phần', 'Nem nướng đặc sản Nha Trang, ăn kèm bánh tráng và rau sống.', 'Còn hàng'),
('Bánh mì bơ tỏi', 1, 35000, 'Phần', 'Bánh mì nướng giòn với bơ tỏi thơm lừng.', 'Còn hàng'),
('Khoai tây chiên', 1, 40000, 'Phần', 'Khoai tây chiên giòn vàng, ăn kèm sốt mayonnaise.', 'Còn hàng'),
('Cánh gà chiên nước mắm', 1, 85000, 'Phần', 'Cánh gà chiên giòn rim nước mắm tỏi ớt đậm đà.', 'Còn hàng');

-- =============================================
-- DANH MỤC 2: MÓN CHÍNH (MaDanhMuc = 2)
-- =============================================
INSERT INTO mon_an (TenMonAn, MaDanhMuc, DonGia, DonViTinh, MoTa, TrangThai) VALUES
('Phở Bò Đặc Biệt', 2, 90000, 'Tô', 'Phở bò gia truyền với thịt nạm, gầu, gân, sách. Nước dùng ninh xương 12 tiếng.', 'Còn hàng'),
('Phở Gà Ta', 2, 85000, 'Tô', 'Phở gà ta thả vườn, thịt dai ngọt, nước dùng trong veo.', 'Còn hàng'),
('Bún Bò Huế', 2, 95000, 'Tô', 'Bún bò Huế cay nồng đặc trưng với giò heo, chả cua.', 'Còn hàng'),
('Bò Bít Tết Sốt Tiêu Xanh', 2, 250000, 'Phần', 'Thịt bò Úc mềm, ăn kèm khoai tây chiên và salad tươi.', 'Còn hàng'),
('Bò Lúc Lắc', 2, 220000, 'Phần', 'Bò lúc lắc xào với ớt chuông, hành tây, ăn kèm cơm trắng.', 'Còn hàng'),
('Cơm Chiên Dương Châu', 2, 75000, 'Phần', 'Cơm chiên với tôm, lạp xưởng, trứng và rau củ.', 'Còn hàng'),
('Cơm Gà Hải Nam', 2, 95000, 'Phần', 'Cơm gà Hải Nam với gà luộc mềm, cơm dầu gà thơm.', 'Còn hàng'),
('Cá Diêu Hồng Hấp Xì Dầu', 2, 180000, 'Con', 'Cá diêu hồng tươi sống hấp cùng xì dầu và gừng.', 'Còn hàng'),
('Cá Chẽm Chiên Xù', 2, 200000, 'Con', 'Cá chẽm chiên giòn, sốt chua ngọt kiểu Thái.', 'Còn hàng'),
('Tôm Sú Nướng Muối Ớt', 2, 280000, 'Phần', 'Tôm sú tươi nướng muối ớt, thơm lừng hấp dẫn.', 'Còn hàng'),
('Mực Xào Sa Tế', 2, 190000, 'Phần', 'Mực tươi xào sa tế cay nồng, ăn kèm cơm trắng.', 'Còn hàng'),
('Sườn Xào Chua Ngọt', 2, 150000, 'Phần', 'Sườn heo xào chua ngọt với dứa, cà chua.', 'Còn hàng'),
('Gà Nướng Mật Ong', 2, 180000, 'Phần', 'Gà nướng mật ong vàng óng, thơm ngọt.', 'Còn hàng'),
('Vịt Quay Bắc Kinh', 2, 350000, 'Con', 'Vịt quay da giòn, thịt mềm, ăn kèm bánh tráng.', 'Còn hàng'),
('Lẩu Thái Hải Sản', 2, 450000, 'Nồi', 'Lẩu Thái chua cay với tôm, mực, cá, nghêu tươi.', 'Còn hàng'),
('Lẩu Bò Nhúng Dấm', 2, 380000, 'Nồi', 'Lẩu bò nhúng dấm với thịt bò tươi và rau sống.', 'Còn hàng'),
('Mì Xào Hải Sản', 2, 120000, 'Phần', 'Mì xào giòn với tôm, mực, cá viên và rau củ.', 'Còn hàng'),
('Hủ Tiếu Nam Vang', 2, 80000, 'Tô', 'Hủ tiếu Nam Vang với thịt, tôm, gan và trứng cút.', 'Còn hàng');

-- =============================================
-- DANH MỤC 3: TRÁNG MIỆNG (MaDanhMuc = 3)
-- =============================================
INSERT INTO mon_an (TenMonAn, MaDanhMuc, DonGia, DonViTinh, MoTa, TrangThai) VALUES
('Bánh Flan Caramen', 3, 30000, 'Cái', 'Bánh flan mềm mịn, béo ngậy vị caramen.', 'Còn hàng'),
('Chè Thái', 3, 35000, 'Ly', 'Chè Thái với nước cốt dừa, thạch, trái cây nhiệt đới.', 'Còn hàng'),
('Chè Đậu Đỏ', 3, 30000, 'Ly', 'Chè đậu đỏ nấu nhừ, ngọt thanh mát lạnh.', 'Còn hàng'),
('Kem Dừa', 3, 40000, 'Ly', 'Kem dừa béo ngậy trong quả dừa tươi.', 'Còn hàng'),
('Kem 3 Vị', 3, 45000, 'Ly', 'Kem 3 vị: socola, vanilla, dâu tây.', 'Còn hàng'),
('Trái Cây Thập Cẩm', 3, 50000, 'Đĩa', 'Đĩa trái cây tươi theo mùa.', 'Còn hàng'),
('Bánh Tiramisu', 3, 55000, 'Miếng', 'Bánh Tiramisu Ý với cà phê và mascarpone.', 'Còn hàng'),
('Sữa Chua Nếp Cẩm', 3, 35000, 'Ly', 'Sữa chua mịn với nếp cẩm dẻo thơm.', 'Còn hàng');

-- =============================================
-- DANH MỤC 4: ĐỒ UỐNG (MaDanhMuc = 4)
-- =============================================
INSERT INTO mon_an (TenMonAn, MaDanhMuc, DonGia, DonViTinh, MoTa, TrangThai) VALUES
-- Nước ngọt
('Coca-Cola', 4, 25000, 'Lon', 'Nước ngọt có gas Coca-Cola.', 'Còn hàng'),
('Pepsi', 4, 25000, 'Lon', 'Nước ngọt có gas Pepsi.', 'Còn hàng'),
('7Up', 4, 25000, 'Lon', 'Nước ngọt có gas 7Up.', 'Còn hàng'),
('Sprite', 4, 25000, 'Lon', 'Nước ngọt có gas Sprite.', 'Còn hàng'),
('Fanta Cam', 4, 25000, 'Lon', 'Nước ngọt có gas Fanta vị cam.', 'Còn hàng'),
('Red Bull', 4, 30000, 'Lon', 'Nước tăng lực Red Bull.', 'Còn hàng'),

-- Nước suối & nước khoáng
('Nước Suối Aquafina', 4, 15000, 'Chai', 'Nước suối tinh khiết Aquafina 500ml.', 'Còn hàng'),
('Nước Khoáng Lavie', 4, 18000, 'Chai', 'Nước khoáng thiên nhiên Lavie 500ml.', 'Còn hàng'),

-- Trà & Cà phê
('Trà Đá', 4, 10000, 'Ly', 'Trà đá mát lạnh.', 'Còn hàng'),
('Trà Chanh', 4, 25000, 'Ly', 'Trà chanh tươi mát.', 'Còn hàng'),
('Trà Đào', 4, 35000, 'Ly', 'Trà đào cam sả thơm ngon.', 'Còn hàng'),
('Trà Sữa Trân Châu', 4, 40000, 'Ly', 'Trà sữa trân châu đường đen.', 'Còn hàng'),
('Cà Phê Đen Đá', 4, 30000, 'Ly', 'Cà phê đen đá pha phin truyền thống.', 'Còn hàng'),
('Cà Phê Sữa Đá', 4, 35000, 'Ly', 'Cà phê sữa đá béo ngậy.', 'Còn hàng'),
('Bạc Xỉu', 4, 35000, 'Ly', 'Bạc xỉu - cà phê sữa nhiều sữa.', 'Còn hàng'),
('Cappuccino', 4, 50000, 'Ly', 'Cappuccino Ý với bọt sữa mịn.', 'Còn hàng'),
('Latte', 4, 50000, 'Ly', 'Cà phê Latte với sữa tươi.', 'Còn hàng'),

-- Nước ép & Sinh tố
('Nước Ép Cam', 4, 40000, 'Ly', 'Nước ép cam tươi 100%.', 'Còn hàng'),
('Nước Ép Dưa Hấu', 4, 35000, 'Ly', 'Nước ép dưa hấu mát lạnh.', 'Còn hàng'),
('Nước Ép Táo', 4, 40000, 'Ly', 'Nước ép táo tươi nguyên chất.', 'Còn hàng'),
('Sinh Tố Bơ', 4, 45000, 'Ly', 'Sinh tố bơ béo ngậy với sữa đặc.', 'Còn hàng'),
('Sinh Tố Xoài', 4, 40000, 'Ly', 'Sinh tố xoài chín ngọt lịm.', 'Còn hàng'),
('Sinh Tố Dâu', 4, 45000, 'Ly', 'Sinh tố dâu tây tươi mát.', 'Còn hàng'),

-- Bia
('Bia Tiger', 4, 30000, 'Lon', 'Bia Tiger lon 330ml.', 'Còn hàng'),
('Bia Heineken', 4, 35000, 'Lon', 'Bia Heineken lon 330ml.', 'Còn hàng'),
('Bia Saigon Special', 4, 28000, 'Lon', 'Bia Saigon Special lon 330ml.', 'Còn hàng'),
('Bia 333', 4, 25000, 'Lon', 'Bia 333 lon 330ml.', 'Còn hàng'),
('Bia Budweiser', 4, 40000, 'Lon', 'Bia Budweiser lon 330ml.', 'Còn hàng'),

-- Rượu
('Rượu Vang Đỏ Chile', 4, 350000, 'Chai', 'Rượu vang đỏ nhập khẩu Chile.', 'Còn hàng'),
('Rượu Vang Trắng Pháp', 4, 400000, 'Chai', 'Rượu vang trắng nhập khẩu Pháp.', 'Còn hàng'),
('Soju Hàn Quốc', 4, 80000, 'Chai', 'Rượu Soju Hàn Quốc vị original.', 'Còn hàng');

-- Kiểm tra kết quả
SELECT MaDanhMuc, COUNT(*) as SoLuong FROM mon_an GROUP BY MaDanhMuc;
SELECT * FROM mon_an ORDER BY MaDanhMuc, MaMonAn;
