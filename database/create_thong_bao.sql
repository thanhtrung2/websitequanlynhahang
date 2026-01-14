-- Tạo bảng thông báo
CREATE TABLE IF NOT EXISTS thong_bao (
    MaThongBao INT AUTO_INCREMENT PRIMARY KEY,
    MaKhachHang INT NOT NULL,
    TieuDe VARCHAR(200) NOT NULL,
    NoiDung TEXT NOT NULL,
    LoaiThongBao ENUM('lien_he', 'dat_ban', 'don_hang', 'he_thong') DEFAULT 'he_thong',
    MaLienKet INT,
    DaDoc TINYINT(1) DEFAULT 0,
    ThoiGianTao DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (MaKhachHang) REFERENCES khach_hang(MaKhachHang) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
