-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 26, 2025 at 04:25 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `quanlynhahang_db`
--

-- Tạo database
CREATE DATABASE IF NOT EXISTS `quanlynhahang_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_vietnamese_ci;
USE `quanlynhahang_db`;

-- --------------------------------------------------------

--
-- Table structure for table `ban_an`
--

CREATE TABLE `ban_an` (
  `MaBan` int(11) NOT NULL,
  `TenBan` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `ViTri` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `SoGhe` int(11) NOT NULL,
  `TrangThai` enum('Trống','Đang phục vụ','Đã đặt') DEFAULT 'Trống'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Dumping data for table `ban_an`
--

INSERT INTO `ban_an` (`MaBan`, `TenBan`, `ViTri`, `SoGhe`, `TrangThai`) VALUES
(1, 'Bàn 1', 'Tầng 1, gần cửa sổ', 4, 'Trống'),
(2, 'Bàn 2', 'Tầng 1, trung tâm', 4, 'Đang phục vụ'),
(3, 'Bàn 3', 'Tầng 1, góc riêng tư', 2, 'Trống'),
(4, 'Bàn 5', 'Tầng 2, phòng VIP', 8, 'Trống'),
(5, 'Bàn 6', 'Tầng 2', 6, 'Đã đặt');

-- --------------------------------------------------------

--
-- Table structure for table `chi_tiet_hoa_don`
--

CREATE TABLE `chi_tiet_hoa_don` (
  `MaChiTiet` int(11) NOT NULL,
  `MaHoaDon` int(11) NOT NULL,
  `MaMonAn` int(11) NOT NULL,
  `SoLuong` int(11) NOT NULL,
  `DonGia` decimal(10,2) NOT NULL,
  `ThanhTien` decimal(12,2) NOT NULL,
  `GhiChu` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Dumping data for table `chi_tiet_hoa_don`
--

INSERT INTO `chi_tiet_hoa_don` (`MaChiTiet`, `MaHoaDon`, `MaMonAn`, `SoLuong`, `DonGia`, `ThanhTien`, `GhiChu`) VALUES
(1, 1, 1, 1, 60000.00, 60000.00, NULL),
(2, 1, 3, 2, 90000.00, 180000.00, NULL),
(3, 1, 8, 3, 25000.00, 75000.00, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `chuc_vu`
--

CREATE TABLE `chuc_vu` (
  `MaChucVu` int(11) NOT NULL,
  `TenChucVu` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Dumping data for table `chuc_vu`
--

INSERT INTO `chuc_vu` (`MaChucVu`, `TenChucVu`) VALUES
(3, 'Đầu bếp'),
(2, 'Nhân viên Phục vụ'),
(1, 'Quản lý'),
(4, 'Thu ngân');

-- --------------------------------------------------------

--
-- Table structure for table `danh_muc_mon_an`
--

CREATE TABLE `danh_muc_mon_an` (
  `MaDanhMuc` int(11) NOT NULL,
  `TenDanhMuc` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `MoTa` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Dumping data for table `danh_muc_mon_an`
--

INSERT INTO `danh_muc_mon_an` (`MaDanhMuc`, `TenDanhMuc`, `MoTa`) VALUES
(1, 'Món Khai Vị', 'Các món ăn nhẹ để bắt đầu bữa ăn'),
(2, 'Món Chính', 'Các món ăn chính, đặc sắc của nhà hàng'),
(3, 'Tráng Miệng', 'Các món ngọt sau bữa ăn'),
(4, 'Đồ Uống', 'Bao gồm nước ngọt, bia, rượu và nước ép');

-- --------------------------------------------------------

--
-- Table structure for table `dat_ban`
--

CREATE TABLE `dat_ban` (
  `MaDatBan` int(11) NOT NULL,
  `MaKhachHang` int(11) NOT NULL,
  `MaBan` int(11) DEFAULT NULL,
  `ThoiGianDat` datetime NOT NULL,
  `SoLuongKhach` int(11) NOT NULL,
  `GhiChu` text DEFAULT NULL,
  `TrangThai` enum('Chờ xác nhận','Đã xác nhận','Đã hủy','Đã đến') DEFAULT 'Chờ xác nhận',
  `NgayTao` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Dumping data for table `dat_ban`
--

INSERT INTO `dat_ban` (`MaDatBan`, `MaKhachHang`, `MaBan`, `ThoiGianDat`, `SoLuongKhach`, `GhiChu`, `TrangThai`, `NgayTao`) VALUES
(1, 1, 5, '2024-06-15 19:00:00', 6, 'Vui lòng chuẩn bị ghế trẻ em.', 'Đã xác nhận', '2025-11-14 12:50:04');

-- --------------------------------------------------------

--
-- Table structure for table `hoa_don`
--

CREATE TABLE `hoa_don` (
  `MaHoaDon` int(11) NOT NULL,
  `MaKhachHang` int(11) DEFAULT NULL,
  `MaNhanVien` int(11) DEFAULT NULL,
  `MaBan` int(11) DEFAULT NULL,
  `ThoiGianVao` datetime NOT NULL DEFAULT current_timestamp(),
  `ThoiGianRa` datetime DEFAULT NULL,
  `TongTien` decimal(12,2) DEFAULT 0.00,
  `GiamGia` decimal(12,2) DEFAULT 0.00,
  `ThanhTien` decimal(12,2) DEFAULT 0.00,
  `TrangThai` enum('Chưa thanh toán','Đã thanh toán','Đã hủy') DEFAULT 'Chưa thanh toán',
  `GhiChu` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Dumping data for table `hoa_don`
--

INSERT INTO `hoa_don` (`MaHoaDon`, `MaKhachHang`, `MaNhanVien`, `MaBan`, `ThoiGianVao`, `ThoiGianRa`, `TongTien`, `GiamGia`, `ThanhTien`, `TrangThai`, `GhiChu`) VALUES
(1, NULL, 2, 2, '2025-11-14 19:49:47', NULL, 315000.00, 0.00, 315000.00, 'Chưa thanh toán', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `khach_hang`
--

CREATE TABLE `khach_hang` (
  `MaKhachHang` int(11) NOT NULL,
  `HoTen` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `SoDienThoai` varchar(15) NOT NULL,
  `Email` varchar(100) DEFAULT NULL,
  `DiemTichLuy` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Dumping data for table `khach_hang`
--

INSERT INTO `khach_hang` (`MaKhachHang`, `HoTen`, `SoDienThoai`, `Email`, `DiemTichLuy`) VALUES
(1, 'Trần Văn Khách', '0988776655', 'khach.tran@email.com', 150),
(2, 'Nguyễn Thị Quý', '0911223344', 'quy.nguyen@email.com', 75);

-- --------------------------------------------------------

--
-- Table structure for table `mon_an`
--

CREATE TABLE `mon_an` (
  `MaMonAn` int(11) NOT NULL,
  `TenMonAn` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `MaDanhMuc` int(11) DEFAULT NULL,
  `DonGia` decimal(10,2) NOT NULL,
  `DonViTinh` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT 'Phần',
  `HinhAnh` varchar(255) DEFAULT NULL,
  `MoTa` text DEFAULT NULL,
  `TrangThai` enum('Còn hàng','Hết hàng') DEFAULT 'Còn hàng'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Dumping data for table `mon_an`
--

INSERT INTO `mon_an` (`MaMonAn`, `TenMonAn`, `MaDanhMuc`, `DonGia`, `DonViTinh`, `HinhAnh`, `MoTa`, `TrangThai`) VALUES
(1, 'Gỏi cuốn tôm thịt', 1, 60000.00, 'Phần', 'images/goi_cuon.jpg', 'Gỏi cuốn tươi ngon với tôm, thịt, bún và rau sống.', 'Còn hàng'),
(2, 'Chả giò hải sản', 1, 75000.00, 'Phần', 'images/cha_gio.jpg', 'Chả giò giòn rụm với nhân hải sản đậm đà.', 'Còn hàng'),
(3, 'Phở Bò Đặc Biệt', 2, 90000.00, 'Tô', 'images/pho_bo.jpg', 'Phở bò gia truyền với thịt nạm, gầu, gân, sách.', 'Còn hàng'),
(4, 'Bò Bít Tết Sốt Tiêu Xanh', 2, 250000.00, 'Phần', 'images/bo_bit_tet.jpg', 'Thịt bò Úc mềm, ăn kèm khoai tây chiên và salad.', 'Còn hàng'),
(5, 'Cá diêu hồng hấp xì dầu', 2, 180000.00, 'Con', 'images/ca_hap.jpg', 'Cá diêu hồng tươi sống hấp cùng xì dầu và gừng.', 'Hết hàng'),
(6, 'Bánh flan caramen', 3, 30000.00, 'Cái', 'images/banh_flan.jpg', 'Bánh flan mềm mịn, béo ngậy vị caramen.', 'Còn hàng'),
(7, 'Nước suối Aquafina', 4, 20000.00, 'Chai', 'images/nuoc_suoi.jpg', 'Nước suối tinh khiết.', 'Còn hàng'),
(8, 'Coca-Cola', 4, 25000.00, 'Lon', 'images/coca.jpg', 'Nước ngọt có gas.', 'Còn hàng');

-- --------------------------------------------------------

--
-- Table structure for table `nhan_vien`
--

CREATE TABLE `nhan_vien` (
  `MaNhanVien` int(11) NOT NULL,
  `HoTen` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `NgaySinh` date DEFAULT NULL,
  `GioiTinh` enum('Nam','Nữ','Khác') DEFAULT 'Khác',
  `DiaChi` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `SoDienThoai` varchar(15) DEFAULT NULL,
  `Email` varchar(100) DEFAULT NULL,
  `MaChucVu` int(11) DEFAULT NULL,
  `NgayVaoLam` date NOT NULL,
  `TrangThai` enum('Đang làm việc','Đã nghỉ việc') DEFAULT 'Đang làm việc'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Dumping data for table `nhan_vien`
--

INSERT INTO `nhan_vien` (`MaNhanVien`, `HoTen`, `NgaySinh`, `GioiTinh`, `DiaChi`, `SoDienThoai`, `Email`, `MaChucVu`, `NgayVaoLam`, `TrangThai`) VALUES
(1, 'Nguyễn Văn An', '1990-05-15', 'Nam', '123 Đường ABC, Quận 1, TP.HCM', '0901234567', 'an.nguyen@nhahang.com', 1, '2022-01-10', 'Đang làm việc'),
(2, 'Trần Thị Bình', '1995-08-20', 'Nữ', '456 Đường XYZ, Quận 3, TP.HCM', '0912345678', 'binh.tran@nhahang.com', 2, '2022-03-15', 'Đang làm việc'),
(3, 'Lê Văn Cường', '1988-11-30', 'Nam', '789 Đường KLM, Quận 5, TP.HCM', '0987654321', 'cuong.le@nhahang.com', 3, '2021-12-01', 'Đang làm việc'),
(4, 'Phạm Thị Dung', '1998-02-25', 'Nữ', '101 Đường PQR, Quận 10, TP.HCM', '0934567890', 'dung.pham@nhahang.com', 4, '2023-01-20', 'Đang làm việc');

-- --------------------------------------------------------

--
-- Table structure for table `tai_khoan`
--

CREATE TABLE `tai_khoan` (
  `MaTaiKhoan` int(11) NOT NULL,
  `TenDangNhap` varchar(50) NOT NULL,
  `MatKhau` varchar(255) NOT NULL,
  `MaNhanVien` int(11) NOT NULL,
  `NgayTao` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_vietnamese_ci;

--
-- Dumping data for table `tai_khoan`
--

INSERT INTO `tai_khoan` (`MaTaiKhoan`, `TenDangNhap`, `MatKhau`, `MaNhanVien`, `NgayTao`) VALUES
(1, 'admin', '$2y$10$xxxxxxxxxxxxxxxxxxxxxxxxxxxx', 1, '2025-11-14 12:49:25'),
(2, 'phucvu1', '$2y$10$yyyyyyyyyyyyyyyyyyyyyyyyyyyy', 2, '2025-11-14 12:49:25'),
(3, 'bepchinh', '$2y$10$zzzzzzzzzzzzzzzzzzzzzzzzzzzz', 3, '2025-11-14 12:49:25'),
(4, 'thungan1', '$2y$10$aaaaaaaaaaaaaaaaaaaaaaaaaaaa', 4, '2025-11-14 12:49:25');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `ban_an`
--
ALTER TABLE `ban_an`
  ADD PRIMARY KEY (`MaBan`);

--
-- Indexes for table `chi_tiet_hoa_don`
--
ALTER TABLE `chi_tiet_hoa_don`
  ADD PRIMARY KEY (`MaChiTiet`),
  ADD KEY `MaHoaDon` (`MaHoaDon`),
  ADD KEY `MaMonAn` (`MaMonAn`);

--
-- Indexes for table `chuc_vu`
--
ALTER TABLE `chuc_vu`
  ADD PRIMARY KEY (`MaChucVu`),
  ADD UNIQUE KEY `TenChucVu` (`TenChucVu`);

--
-- Indexes for table `danh_muc_mon_an`
--
ALTER TABLE `danh_muc_mon_an`
  ADD PRIMARY KEY (`MaDanhMuc`),
  ADD UNIQUE KEY `TenDanhMuc` (`TenDanhMuc`);

--
-- Indexes for table `dat_ban`
--
ALTER TABLE `dat_ban`
  ADD PRIMARY KEY (`MaDatBan`),
  ADD KEY `MaKhachHang` (`MaKhachHang`),
  ADD KEY `MaBan` (`MaBan`);

--
-- Indexes for table `hoa_don`
--
ALTER TABLE `hoa_don`
  ADD PRIMARY KEY (`MaHoaDon`),
  ADD KEY `MaKhachHang` (`MaKhachHang`),
  ADD KEY `MaNhanVien` (`MaNhanVien`),
  ADD KEY `MaBan` (`MaBan`);

--
-- Indexes for table `khach_hang`
--
ALTER TABLE `khach_hang`
  ADD PRIMARY KEY (`MaKhachHang`),
  ADD UNIQUE KEY `SoDienThoai` (`SoDienThoai`),
  ADD UNIQUE KEY `Email` (`Email`);

--
-- Indexes for table `mon_an`
--
ALTER TABLE `mon_an`
  ADD PRIMARY KEY (`MaMonAn`),
  ADD KEY `MaDanhMuc` (`MaDanhMuc`);

--
-- Indexes for table `nhan_vien`
--
ALTER TABLE `nhan_vien`
  ADD PRIMARY KEY (`MaNhanVien`),
  ADD UNIQUE KEY `SoDienThoai` (`SoDienThoai`),
  ADD UNIQUE KEY `Email` (`Email`),
  ADD KEY `MaChucVu` (`MaChucVu`);

--
-- Indexes for table `tai_khoan`
--
ALTER TABLE `tai_khoan`
  ADD PRIMARY KEY (`MaTaiKhoan`),
  ADD UNIQUE KEY `TenDangNhap` (`TenDangNhap`),
  ADD UNIQUE KEY `MaNhanVien` (`MaNhanVien`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `ban_an`
--
ALTER TABLE `ban_an`
  MODIFY `MaBan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `chi_tiet_hoa_don`
--
ALTER TABLE `chi_tiet_hoa_don`
  MODIFY `MaChiTiet` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `chuc_vu`
--
ALTER TABLE `chuc_vu`
  MODIFY `MaChucVu` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `danh_muc_mon_an`
--
ALTER TABLE `danh_muc_mon_an`
  MODIFY `MaDanhMuc` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `dat_ban`
--
ALTER TABLE `dat_ban`
  MODIFY `MaDatBan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `hoa_don`
--
ALTER TABLE `hoa_don`
  MODIFY `MaHoaDon` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `khach_hang`
--
ALTER TABLE `khach_hang`
  MODIFY `MaKhachHang` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `mon_an`
--
ALTER TABLE `mon_an`
  MODIFY `MaMonAn` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `nhan_vien`
--
ALTER TABLE `nhan_vien`
  MODIFY `MaNhanVien` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tai_khoan`
--
ALTER TABLE `tai_khoan`
  MODIFY `MaTaiKhoan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `chi_tiet_hoa_don`
--
ALTER TABLE `chi_tiet_hoa_don`
  ADD CONSTRAINT `chi_tiet_hoa_don_ibfk_1` FOREIGN KEY (`MaHoaDon`) REFERENCES `hoa_don` (`MaHoaDon`) ON DELETE CASCADE,
  ADD CONSTRAINT `chi_tiet_hoa_don_ibfk_2` FOREIGN KEY (`MaMonAn`) REFERENCES `mon_an` (`MaMonAn`);

--
-- Constraints for table `dat_ban`
--
ALTER TABLE `dat_ban`
  ADD CONSTRAINT `dat_ban_ibfk_1` FOREIGN KEY (`MaKhachHang`) REFERENCES `khach_hang` (`MaKhachHang`) ON DELETE CASCADE,
  ADD CONSTRAINT `dat_ban_ibfk_2` FOREIGN KEY (`MaBan`) REFERENCES `ban_an` (`MaBan`) ON DELETE SET NULL;

--
-- Constraints for table `hoa_don`
--
ALTER TABLE `hoa_don`
  ADD CONSTRAINT `hoa_don_ibfk_1` FOREIGN KEY (`MaKhachHang`) REFERENCES `khach_hang` (`MaKhachHang`) ON DELETE SET NULL,
  ADD CONSTRAINT `hoa_don_ibfk_2` FOREIGN KEY (`MaNhanVien`) REFERENCES `nhan_vien` (`MaNhanVien`) ON DELETE SET NULL,
  ADD CONSTRAINT `hoa_don_ibfk_3` FOREIGN KEY (`MaBan`) REFERENCES `ban_an` (`MaBan`) ON DELETE SET NULL;

--
-- Constraints for table `mon_an`
--
ALTER TABLE `mon_an`
  ADD CONSTRAINT `mon_an_ibfk_1` FOREIGN KEY (`MaDanhMuc`) REFERENCES `danh_muc_mon_an` (`MaDanhMuc`) ON DELETE SET NULL;

--
-- Constraints for table `nhan_vien`
--
ALTER TABLE `nhan_vien`
  ADD CONSTRAINT `nhan_vien_ibfk_1` FOREIGN KEY (`MaChucVu`) REFERENCES `chuc_vu` (`MaChucVu`) ON DELETE SET NULL;

--
-- Constraints for table `tai_khoan`
--
ALTER TABLE `tai_khoan`
  ADD CONSTRAINT `tai_khoan_ibfk_1` FOREIGN KEY (`MaNhanVien`) REFERENCES `nhan_vien` (`MaNhanVien`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
