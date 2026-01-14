-- Tạo bảng liên hệ
CREATE TABLE IF NOT EXISTS lien_he (
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
    MaKhachHang INT,
    FOREIGN KEY (MaKhachHang) REFERENCES khach_hang(MaKhachHang) ON DELETE SET NULL,
    FOREIGN KEY (NguoiXuLy) REFERENCES nhan_vien(MaNhanVien) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Thêm dữ liệu mẫu
INSERT INTO lien_he (HoTen, Email, SoDienThoai, ChuDe, NoiDung, TrangThai) VALUES
('Nguyễn Văn A', 'nguyenvana@email.com', '0901234567', 'Hỏi về thực đơn', 'Cho tôi hỏi nhà hàng có món chay không ạ?', 'Chưa xử lý'),
('Trần Thị B', 'tranthib@email.com', '0912345678', 'Đặt tiệc sinh nhật', 'Tôi muốn đặt tiệc sinh nhật cho 20 người vào cuối tuần này', 'Đang xử lý'),
('Lê Văn C', 'levanc@email.com', '0923456789', 'Góp ý dịch vụ', 'Nhân viên phục vụ rất nhiệt tình, cảm ơn nhà hàng!', 'Đã xử lý');
