-- Cập nhật tên chức vụ Đầu bếp thành Quản lý đầu bếp
-- Chạy file này trong phpMyAdmin hoặc MySQL CLI

USE quanlynhahang_db;

-- Đổi tên chức vụ
UPDATE chuc_vu SET TenChucVu = 'Quản lý đầu bếp' WHERE MaChucVu = 3;

-- Kiểm tra kết quả
SELECT * FROM chuc_vu;

-- Ghi chú phân quyền:
-- MaChucVu = 1: Quản lý (có quyền đăng nhập admin)
-- MaChucVu = 2: Nhân viên Phục vụ (không có quyền đăng nhập admin)
-- MaChucVu = 3: Quản lý đầu bếp (không có quyền đăng nhập admin - là nhân viên)
-- MaChucVu = 4: Thu ngân (không có quyền đăng nhập admin)
