<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';

if (!isset($_SESSION['customer_id'])) {
    header("Location: customer_login.php");
    exit();
}

$maKhachHang = $_SESSION['customer_id'];
$message = '';
$error = '';

// Xử lý hủy hóa đơn
if (isset($_GET['cancel'])) {
    $maHoaDonHuy = intval($_GET['cancel']);
    try {
        // Kiểm tra hóa đơn thuộc về khách hàng và đang chờ thanh toán
        $stmt = $conn->prepare("SELECT * FROM hoa_don WHERE MaHoaDon = ? AND MaKhachHang = ? AND TrangThai = 'Chưa thanh toán'");
        $stmt->execute([$maHoaDonHuy, $maKhachHang]);
        $hoaDonHuy = $stmt->fetch();
        
        if ($hoaDonHuy) {
            // Cập nhật trạng thái hóa đơn thành Đã hủy
            $stmt = $conn->prepare("UPDATE hoa_don SET TrangThai = 'Đã hủy', GhiChu = CONCAT(IFNULL(GhiChu, ''), ' | Khách hàng hủy: ', NOW()) WHERE MaHoaDon = ?");
            $stmt->execute([$maHoaDonHuy]);
            $message = 'Đã hủy hóa đơn #' . $maHoaDonHuy . ' thành công!';
        } else {
            $error = 'Không thể hủy hóa đơn này!';
        }
    } catch(PDOException $e) {
        $error = 'Lỗi: ' . $e->getMessage();
    }
    
    // Redirect để tránh refresh lại trang gây hủy lần nữa
    if ($message) {
        $_SESSION['thanh_toan_message'] = $message;
    }
    if ($error) {
        $_SESSION['thanh_toan_error'] = $error;
    }
    header("Location: thanh_toan.php");
    exit();
}

// Lấy thông báo từ session
if (isset($_SESSION['thanh_toan_message'])) {
    $message = $_SESSION['thanh_toan_message'];
    unset($_SESSION['thanh_toan_message']);
}
if (isset($_SESSION['thanh_toan_error'])) {
    $error = $_SESSION['thanh_toan_error'];
    unset($_SESSION['thanh_toan_error']);
}

// Xử lý thanh toán
$hoaDonVuaThanhToan = null;
$chiTietMonVuaThanhToan = [];
$phuongThucThanhToan = '';
$diemCongDuoc = 0;
$danhSachHoaDonThanhToan = []; // Cho thanh toán gộp

// Xử lý thanh toán gộp nhiều hóa đơn
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['thanh_toan_gop'])) {
    $selectedIds = $_POST['selected_invoices'] ?? [];
    $phuongThuc = $_POST['PhuongThuc'] ?? 'Tiền mặt';
    $diemSuDung = intval($_POST['DiemSuDung'] ?? 0);
    
    if (empty($selectedIds)) {
        $error = 'Vui lòng chọn ít nhất một hóa đơn để thanh toán!';
    } else {
        try {
            $conn->beginTransaction();
            
            // Lấy điểm hiện tại của khách hàng
            $stmt = $conn->prepare("SELECT DiemTichLuy FROM khach_hang WHERE MaKhachHang = ?");
            $stmt->execute([$maKhachHang]);
            $khachHangInfo = $stmt->fetch();
            $diemCoSan = $khachHangInfo['DiemTichLuy'] ?? 0;
            
            // Kiểm tra điểm sử dụng hợp lệ
            if ($diemSuDung > $diemCoSan) {
                $diemSuDung = $diemCoSan;
            }
            
            $tongTienGop = 0;
            $tongDiem = 0;
            
            foreach ($selectedIds as $maHoaDon) {
                $maHoaDon = intval($maHoaDon);
                
                // Kiểm tra hóa đơn thuộc về khách hàng này
                $stmt = $conn->prepare("SELECT * FROM hoa_don WHERE MaHoaDon = ? AND MaKhachHang = ? AND TrangThai = 'Chưa thanh toán'");
                $stmt->execute([$maHoaDon, $maKhachHang]);
                $hoaDon = $stmt->fetch();
                
                if ($hoaDon) {
                    // Cập nhật trạng thái hóa đơn
                    $stmt = $conn->prepare("UPDATE hoa_don SET TrangThai = 'Đã thanh toán', ThoiGianRa = NOW(), GhiChu = CONCAT(IFNULL(GhiChu, ''), ' | Thanh toán gộp: ', ?) WHERE MaHoaDon = ?");
                    $stmt->execute([$phuongThuc, $maHoaDon]);
                    
                    $tongTienGop += $hoaDon['TongTien'];
                    
                    // Lấy chi tiết món ăn
                    $stmt = $conn->prepare("SELECT ct.*, ma.TenMonAn FROM chi_tiet_hoa_don ct JOIN mon_an ma ON ct.MaMonAn = ma.MaMonAn WHERE ct.MaHoaDon = ?");
                    $stmt->execute([$maHoaDon]);
                    $chiTiet = $stmt->fetchAll();
                    
                    $danhSachHoaDonThanhToan[] = [
                        'hoaDon' => $hoaDon,
                        'chiTiet' => $chiTiet
                    ];
                }
            }
            
            // Kiểm tra điểm sử dụng hợp lệ (tối thiểu 1000 điểm)
            if ($diemSuDung > 0 && $diemSuDung < 1000) {
                $diemSuDung = 0;
            }
            
            // Tính giảm giá từ điểm (1000 điểm = 10,000đ)
            $giamGia = floor($diemSuDung / 1000) * 10000;
            if ($giamGia > $tongTienGop) {
                $giamGia = $tongTienGop;
                $diemSuDung = ceil($giamGia / 10000) * 1000;
            }
            
            $thanhTien = $tongTienGop - $giamGia;
            
            // Trừ điểm đã sử dụng
            if ($diemSuDung > 0) {
                $stmt = $conn->prepare("UPDATE khach_hang SET DiemTichLuy = DiemTichLuy - ? WHERE MaKhachHang = ?");
                $stmt->execute([$diemSuDung, $maKhachHang]);
            }
            
            // Cộng điểm tích lũy từ số tiền thanh toán (10,000đ = 1 điểm)
            $tongDiem = floor($thanhTien / 10000);
            if ($tongDiem > 0) {
                $stmt = $conn->prepare("UPDATE khach_hang SET DiemTichLuy = DiemTichLuy + ? WHERE MaKhachHang = ?");
                $stmt->execute([$tongDiem, $maKhachHang]);
            }
            
            $conn->commit();
            
            // Lấy thông tin khách hàng
            $stmt = $conn->prepare("SELECT * FROM khach_hang WHERE MaKhachHang = ?");
            $stmt->execute([$maKhachHang]);
            $khachHang = $stmt->fetch();
            
            $hoaDonVuaThanhToan = [
                'TongTien' => $tongTienGop,
                'ThanhTien' => $thanhTien,
                'GiamGia' => $giamGia,
                'DiemDaSuDung' => $diemSuDung,
                'HoTen' => $khachHang['HoTen'] ?? '',
                'SoDienThoai' => $khachHang['SoDienThoai'] ?? '',
                'SoLuongHoaDon' => count($danhSachHoaDonThanhToan)
            ];
            
            $phuongThucThanhToan = $phuongThuc;
            $diemCongDuoc = $tongDiem;
            
            $message = 'Thanh toán gộp ' . count($danhSachHoaDonThanhToan) . ' hóa đơn thành công! Bạn được cộng ' . $tongDiem . ' điểm tích lũy.';
            
        } catch(PDOException $e) {
            $conn->rollBack();
            $error = 'Lỗi: ' . $e->getMessage();
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['thanh_toan'])) {
    $maHoaDon = intval($_POST['MaHoaDon']);
    $phuongThuc = $_POST['PhuongThuc'] ?? 'Tiền mặt';
    $diemSuDung = intval($_POST['DiemSuDung'] ?? 0);
    
    try {
        // Kiểm tra hóa đơn thuộc về khách hàng này
        $stmt = $conn->prepare("SELECT * FROM hoa_don WHERE MaHoaDon = ? AND MaKhachHang = ? AND TrangThai = 'Chưa thanh toán'");
        $stmt->execute([$maHoaDon, $maKhachHang]);
        $hoaDon = $stmt->fetch();
        
        if ($hoaDon) {
            // Lấy điểm hiện tại của khách hàng
            $stmt = $conn->prepare("SELECT DiemTichLuy FROM khach_hang WHERE MaKhachHang = ?");
            $stmt->execute([$maKhachHang]);
            $khachHangInfo = $stmt->fetch();
            $diemCoSan = $khachHangInfo['DiemTichLuy'] ?? 0;
            
            // Kiểm tra điểm sử dụng hợp lệ (tối thiểu 1000 điểm)
            if ($diemSuDung > 0 && $diemSuDung < 1000) {
                $diemSuDung = 0;
            }
            if ($diemSuDung > $diemCoSan) {
                $diemSuDung = $diemCoSan;
            }
            
            // Tính giảm giá từ điểm (1000 điểm = 10,000đ)
            $giamGia = floor($diemSuDung / 1000) * 10000;
            if ($giamGia > $hoaDon['TongTien']) {
                $giamGia = $hoaDon['TongTien'];
                $diemSuDung = ceil($giamGia / 1000);
            }
            
            $thanhTien = $hoaDon['TongTien'] - $giamGia;
            
            // Cập nhật trạng thái hóa đơn
            $ghiChuThem = ' | Thanh toán: ' . $phuongThuc;
            if ($diemSuDung > 0) {
                $ghiChuThem .= ' | Đổi ' . $diemSuDung . ' điểm (-' . number_format($giamGia) . 'đ)';
            }
            
            $stmt = $conn->prepare("UPDATE hoa_don SET TrangThai = 'Đã thanh toán', ThanhTien = ?, ThoiGianRa = NOW(), GhiChu = CONCAT(IFNULL(GhiChu, ''), ?) WHERE MaHoaDon = ?");
            $stmt->execute([$thanhTien, $ghiChuThem, $maHoaDon]);
            
            // Trừ điểm đã sử dụng
            if ($diemSuDung > 0) {
                $stmt = $conn->prepare("UPDATE khach_hang SET DiemTichLuy = DiemTichLuy - ? WHERE MaKhachHang = ?");
                $stmt->execute([$diemSuDung, $maKhachHang]);
            }
            
            // Cộng điểm tích lũy cho khách hàng (10,000đ = 1 điểm)
            $diemCong = floor($thanhTien / 10000);
            if ($diemCong > 0) {
                $stmt = $conn->prepare("UPDATE khach_hang SET DiemTichLuy = DiemTichLuy + ? WHERE MaKhachHang = ?");
                $stmt->execute([$diemCong, $maKhachHang]);
            }
            
            // Lấy thông tin hóa đơn vừa thanh toán để hiển thị
            $stmt = $conn->prepare("SELECT hd.*, kh.HoTen, kh.SoDienThoai, kh.Email FROM hoa_don hd LEFT JOIN khach_hang kh ON hd.MaKhachHang = kh.MaKhachHang WHERE hd.MaHoaDon = ?");
            $stmt->execute([$maHoaDon]);
            $hoaDonVuaThanhToan = $stmt->fetch();
            $hoaDonVuaThanhToan['GiamGia'] = $giamGia;
            $hoaDonVuaThanhToan['DiemDaSuDung'] = $diemSuDung;
            
            // Lấy chi tiết món ăn
            $stmt = $conn->prepare("SELECT ct.*, ma.TenMonAn FROM chi_tiet_hoa_don ct JOIN mon_an ma ON ct.MaMonAn = ma.MaMonAn WHERE ct.MaHoaDon = ?");
            $stmt->execute([$maHoaDon]);
            $chiTietMonVuaThanhToan = $stmt->fetchAll();
            
            $phuongThucThanhToan = $phuongThuc;
            $diemCongDuoc = $diemCong;
            
            $message = 'Thanh toán thành công! Bạn được cộng ' . $diemCong . ' điểm tích lũy.';
        } else {
            $error = 'Hóa đơn không hợp lệ hoặc đã được thanh toán!';
        }
    } catch(PDOException $e) {
        $error = 'Lỗi: ' . $e->getMessage();
    }
}

// Lấy danh sách hóa đơn chưa thanh toán
$hoaDonChuaThanhToan = [];
try {
    $stmt = $conn->prepare("SELECT * FROM hoa_don WHERE MaKhachHang = ? AND TrangThai = 'Chưa thanh toán' ORDER BY ThoiGianVao DESC");
    $stmt->execute([$maKhachHang]);
    $hoaDonChuaThanhToan = $stmt->fetchAll();
} catch(PDOException $e) {}

// Lấy lịch sử thanh toán
$lichSuThanhToan = [];
try {
    $stmt = $conn->prepare("SELECT * FROM hoa_don WHERE MaKhachHang = ? AND TrangThai IN ('Đã thanh toán', 'Đã hủy') ORDER BY ThoiGianVao DESC LIMIT 10");
    $stmt->execute([$maKhachHang]);
    $lichSuThanhToan = $stmt->fetchAll();
} catch(PDOException $e) {}

// Xem chi tiết hóa đơn
$chiTietHoaDon = null;
$chiTietMon = [];
$isNewOrder = isset($_GET['new']) && $_GET['new'] == '1';
if (isset($_GET['view'])) {
    $maHoaDon = intval($_GET['view']);
    try {
        $stmt = $conn->prepare("SELECT * FROM hoa_don WHERE MaHoaDon = ? AND MaKhachHang = ?");
        $stmt->execute([$maHoaDon, $maKhachHang]);
        $chiTietHoaDon = $stmt->fetch();
        
        if ($chiTietHoaDon) {
            $stmt = $conn->prepare("SELECT ct.*, ma.TenMonAn, ma.HinhAnh FROM chi_tiet_hoa_don ct JOIN mon_an ma ON ct.MaMonAn = ma.MaMonAn WHERE ct.MaHoaDon = ?");
            $stmt->execute([$maHoaDon]);
            $chiTietMon = $stmt->fetchAll();
        }
    } catch(PDOException $e) {}
    
    // Hiển thị thông báo đặt món thành công
    if ($isNewOrder && isset($_SESSION['order_success'])) {
        $message = $_SESSION['order_success'];
        unset($_SESSION['order_success']);
    }
}

// Tính tổng tiền chưa thanh toán
$tongChuaThanhToan = 0;
foreach ($hoaDonChuaThanhToan as $hd) {
    $tongChuaThanhToan += $hd['TongTien'];
}

// Lấy thông tin điểm tích lũy của khách hàng
$diemHienTai = 0;
try {
    $stmt = $conn->prepare("SELECT DiemTichLuy FROM khach_hang WHERE MaKhachHang = ?");
    $stmt->execute([$maKhachHang]);
    $kh = $stmt->fetch();
    $diemHienTai = $kh['DiemTichLuy'] ?? 0;
} catch(PDOException $e) {}

// Quy đổi: 1 điểm = 1,000đ giảm giá
$giaTriDiem = 1000; // VND per point
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nhà hàng 3CE - Thanh toán</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #F5F5DC 0%, #EDE8D0 100%);
            min-height: 100vh;
            padding-top: 70px;
        }
        .container {
            max-width: 1000px;
            margin: 30px auto;
            padding: 0 20px;
        }
        .page-header {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .page-header h1 { color: #001f3f; }
        .summary-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .summary-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        .summary-card i {
            font-size: 40px;
            color: #001f3f;
            margin-bottom: 10px;
        }
        .summary-card h3 {
            font-size: 28px;
            color: #003366;
            margin-bottom: 5px;
        }
        .summary-card p { color: #666; }
        .summary-card.warning { border-left: 4px solid #ffc107; }
        .card {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }
        .card h2 {
            color: #001f3f;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #eee;
        }
        .invoice-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px;
            border: 2px solid #eee;
            border-radius: 10px;
            margin-bottom: 15px;
            flex-wrap: wrap;
            gap: 15px;
        }
        .invoice-item:hover { border-color: #001f3f; }
        .invoice-info h4 { color: #001f3f; margin-bottom: 5px; }
        .invoice-info p { color: #666; font-size: 14px; }
        .invoice-amount {
            font-size: 24px;
            font-weight: bold;
            color: #003366;
        }
        .status-badge {
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 600;
        }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-paid { background: #d4edda; color: #155724; }
        .status-cancelled { background: #f8d7da; color: #721c24; }
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .btn-view { background: #001f3f; color: #F5F5DC; }
        /* Checkbox styles */
        .invoice-checkbox {
            width: 22px;
            height: 22px;
            cursor: pointer;
            accent-color: #001f3f;
        }
        .select-all-container {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 10px;
        }
        .select-all-container label {
            cursor: pointer;
            font-weight: 600;
            color: #001f3f;
        }
        .bulk-payment-bar {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(135deg, #001f3f 0%, #003366 100%);
            padding: 15px 20px;
            display: none;
            align-items: center;
            justify-content: center;
            gap: 20px;
            z-index: 100;
            box-shadow: 0 -5px 20px rgba(0,0,0,0.2);
        }
        .bulk-payment-bar.show {
            display: flex;
        }
        .bulk-payment-info {
            color: #F5F5DC;
            font-size: 16px;
        }
        .bulk-payment-info strong {
            font-size: 20px;
            color: #D4AF37;
        }
        .btn-bulk-pay {
            padding: 12px 30px;
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .btn-bulk-pay:hover {
            background: linear-gradient(135deg, #218838 0%, #1aa179 100%);
        }
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        .empty-state i {
            font-size: 50px;
            color: #ddd;
            margin-bottom: 15px;
            display: block;
        }
        .detail-section {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .detail-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 10px 0;
            border-bottom: 1px solid #eee;
        }
        .detail-item:last-child { border-bottom: none; }
        .detail-item-image {
            width: 50px;
            height: 50px;
            border-radius: 8px;
            object-fit: cover;
        }
        .detail-item-placeholder {
            width: 50px;
            height: 50px;
            border-radius: 8px;
            background: linear-gradient(135deg, #001f3f 0%, #003366 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #F5F5DC;
        }
        .detail-item-info { flex: 1; }
        .detail-item-name { font-weight: 600; color: #001f3f; }
        .detail-item-price { color: #666; font-size: 13px; }
        .detail-item-total { font-weight: bold; color: #003366; }
        .payment-note {
            background: #e7f3ff;
            border-left: 4px solid #007bff;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
        }
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .alert-success { background: #d4edda; color: #155724; }
        .alert-error { background: #f8d7da; color: #721c24; }
        .payment-methods {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin: 20px 0;
        }
        .payment-method {
            padding: 20px;
            border: 2px solid #ddd;
            border-radius: 10px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        .payment-method:hover, .payment-method.selected {
            border-color: #001f3f;
            background: #f0f8ff;
        }
        .payment-method i {
            font-size: 30px;
            color: #001f3f;
            margin-bottom: 10px;
            display: block;
        }
        .payment-method span {
            font-weight: 600;
            color: #001f3f;
        }
        .btn-pay {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 20px;
        }
        .btn-pay:hover {
            background: linear-gradient(135deg, #218838 0%, #1aa179 100%);
        }
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        .modal.active { display: flex; }
        .modal-content {
            background: white;
            border-radius: 15px;
            padding: 30px;
            max-width: 500px;
            width: 90%;
        }
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #eee;
        }
        .modal-header h3 { color: #001f3f; }
        .close-btn {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
        }
        /* Invoice Print Styles */
        .invoice-receipt {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            border: 2px dashed #28a745;
        }
        .invoice-header {
            text-align: center;
            border-bottom: 2px dashed #ddd;
            padding-bottom: 20px;
            margin-bottom: 20px;
        }
        .invoice-header h2 {
            color: #001f3f;
            margin-bottom: 5px;
        }
        .invoice-header p {
            color: #666;
            font-size: 14px;
        }
        .invoice-info-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px dotted #ddd;
        }
        .invoice-info-row:last-child {
            border-bottom: none;
        }
        .invoice-info-label {
            color: #666;
        }
        .invoice-info-value {
            font-weight: 600;
            color: #001f3f;
        }
        .invoice-items {
            margin: 20px 0;
            border-top: 2px dashed #ddd;
            border-bottom: 2px dashed #ddd;
            padding: 15px 0;
        }
        .invoice-item-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
        }
        .invoice-item-name {
            flex: 1;
        }
        .invoice-item-qty {
            width: 60px;
            text-align: center;
            color: #666;
        }
        .invoice-item-price {
            width: 100px;
            text-align: right;
            font-weight: 500;
        }
        .invoice-total {
            text-align: right;
            padding: 15px 0;
        }
        .invoice-total-row {
            display: flex;
            justify-content: flex-end;
            gap: 30px;
            padding: 5px 0;
        }
        .invoice-grand-total {
            font-size: 24px;
            font-weight: bold;
            color: #003366;
            border-top: 2px solid #001f3f;
            padding-top: 10px;
            margin-top: 10px;
        }
        .invoice-footer {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px dashed #ddd;
        }
        .invoice-footer p {
            color: #666;
            font-size: 14px;
        }
        .invoice-success-badge {
            display: inline-block;
            background: #28a745;
            color: white;
            padding: 10px 25px;
            border-radius: 25px;
            font-weight: 600;
            margin-bottom: 15px;
        }
        .btn-print {
            padding: 12px 30px;
            background: #001f3f;
            color: #F5F5DC;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            margin: 5px;
        }
        .btn-print:hover {
            background: #003366;
        }
        .btn-continue {
            padding: 12px 30px;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin: 5px;
        }
        @media print {
            body * {
                visibility: hidden;
            }
            .invoice-receipt, .invoice-receipt * {
                visibility: visible;
            }
            .invoice-receipt {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                border: none;
                box-shadow: none;
            }
            .no-print {
                display: none !important;
            }
        }
        
        /* Success Animation */
        .success-animation {
            display: inline-block;
        }
        
        .checkmark {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            display: block;
            stroke-width: 2;
            stroke: #28a745;
            stroke-miterlimit: 10;
            box-shadow: inset 0px 0px 0px #28a745;
            animation: fill .4s ease-in-out .4s forwards, scale .3s ease-in-out .9s both;
        }
        
        .checkmark__circle {
            stroke-dasharray: 166;
            stroke-dashoffset: 166;
            stroke-width: 2;
            stroke-miterlimit: 10;
            stroke: #28a745;
            fill: none;
            animation: stroke 0.6s cubic-bezier(0.65, 0, 0.45, 1) forwards;
        }
        
        .checkmark__check {
            transform-origin: 50% 50%;
            stroke-dasharray: 48;
            stroke-dashoffset: 48;
            animation: stroke 0.3s cubic-bezier(0.65, 0, 0.45, 1) 0.8s forwards;
        }
        
        @keyframes stroke {
            100% { stroke-dashoffset: 0; }
        }
        
        @keyframes scale {
            0%, 100% { transform: none; }
            50% { transform: scale3d(1.1, 1.1, 1); }
        }
        
        @keyframes fill {
            100% { box-shadow: inset 0px 0px 0px 30px transparent; }
        }
        
        /* Success notification popup */
        .success-popup {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 2000;
            animation: fadeIn 0.3s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .success-popup-content {
            background: white;
            border-radius: 20px;
            padding: 40px;
            text-align: center;
            max-width: 400px;
            width: 90%;
            animation: slideIn 0.3s ease;
        }
        
        @keyframes slideIn {
            from { transform: translateY(-50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        
        .success-popup-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #28a745, #20c997);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            animation: bounce 0.5s ease;
        }
        
        @keyframes bounce {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }
        
        .success-popup-icon i {
            font-size: 40px;
            color: white;
        }
        
        .success-popup h3 {
            color: #28a745;
            font-size: 24px;
            margin-bottom: 10px;
        }
        
        .success-popup p {
            color: #666;
            margin-bottom: 20px;
        }
        
        .success-popup .points-earned {
            background: linear-gradient(135deg, #D4AF37, #f7d774);
            color: #001f3f;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        
        .success-popup .points-earned i {
            font-size: 24px;
            margin-bottom: 5px;
        }
        
        .success-popup .points-earned span {
            font-size: 28px;
            font-weight: bold;
            display: block;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container">
        <div class="page-header">
            <h1><i class="fas fa-credit-card"></i> Thanh toán hóa đơn</h1>
            <p>Xem và thanh toán các hóa đơn của bạn</p>
        </div>

        <?php if ($message): ?>
        <div class="alert alert-success" style="<?php echo $isNewOrder ? 'animation: pulse 0.5s ease;' : ''; ?>">
            <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($message); ?>
            <?php if ($isNewOrder): ?>
            <br><small style="opacity: 0.8;">Vui lòng thanh toán để hoàn tất đơn hàng</small>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        <?php if ($error): ?>
        <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if ($hoaDonVuaThanhToan): ?>
        <!-- Hóa đơn thanh toán thành công -->
        <div class="invoice-receipt" id="invoicePrint">
            <!-- Success Animation -->
            <div style="text-align: center; margin-bottom: 20px;">
                <div class="success-animation">
                    <svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
                        <circle class="checkmark__circle" cx="26" cy="26" r="25" fill="none"/>
                        <path class="checkmark__check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"/>
                    </svg>
                </div>
            </div>
            
            <div class="invoice-header">
                <span class="invoice-success-badge"><i class="fas fa-check-circle"></i> THANH TOÁN THÀNH CÔNG</span>
                <h2><i class="fas fa-utensils"></i> NHÀ HÀNG 3CE</h2>
                <p>Địa chỉ: Đường Nguyễn Thiện Thành, Phường Trà Vinh, Tỉnh Vĩnh Long</p>
                <p>Hotline: 1900 xxxx | Email: contact@3ce.vn</p>
            </div>
            
            <div style="text-align: center; margin-bottom: 20px;">
                <h3 style="color: #001f3f;">HÓA ĐƠN THANH TOÁN<?php echo !empty($danhSachHoaDonThanhToan) ? ' GỘP' : ''; ?></h3>
                <?php if (!empty($danhSachHoaDonThanhToan)): ?>
                <p style="color: #666;"><?php echo count($danhSachHoaDonThanhToan); ?> hóa đơn</p>
                <?php else: ?>
                <p style="color: #666;">Số: #<?php echo str_pad($hoaDonVuaThanhToan['MaHoaDon'] ?? 0, 6, '0', STR_PAD_LEFT); ?></p>
                <?php endif; ?>
            </div>
            
            <div style="margin-bottom: 20px;">
                <div class="invoice-info-row">
                    <span class="invoice-info-label">Khách hàng:</span>
                    <span class="invoice-info-value"><?php echo htmlspecialchars($hoaDonVuaThanhToan['HoTen'] ?? 'Khách vãng lai'); ?></span>
                </div>
                <div class="invoice-info-row">
                    <span class="invoice-info-label">Số điện thoại:</span>
                    <span class="invoice-info-value"><?php echo htmlspecialchars($hoaDonVuaThanhToan['SoDienThoai'] ?? '-'); ?></span>
                </div>
                <div class="invoice-info-row">
                    <span class="invoice-info-label">Thời gian thanh toán:</span>
                    <span class="invoice-info-value"><?php echo date('d/m/Y H:i'); ?></span>
                </div>
                <div class="invoice-info-row">
                    <span class="invoice-info-label">Phương thức:</span>
                    <span class="invoice-info-value"><?php echo htmlspecialchars($phuongThucThanhToan); ?></span>
                </div>
            </div>
            
            <div class="invoice-items">
                <div class="invoice-item-row" style="font-weight: bold; border-bottom: 1px solid #ddd; padding-bottom: 10px; margin-bottom: 10px;">
                    <span class="invoice-item-name">Món ăn</span>
                    <span class="invoice-item-qty">SL</span>
                    <span class="invoice-item-price">Thành tiền</span>
                </div>
                <?php if (!empty($danhSachHoaDonThanhToan)): ?>
                    <?php foreach ($danhSachHoaDonThanhToan as $hdItem): ?>
                        <div style="background: #f8f9fa; padding: 8px; margin: 10px 0; border-radius: 5px;">
                            <strong style="color: #001f3f;">Hóa đơn #<?php echo $hdItem['hoaDon']['MaHoaDon']; ?></strong>
                            <small style="color: #666;"> - <?php echo date('d/m H:i', strtotime($hdItem['hoaDon']['ThoiGianVao'])); ?></small>
                        </div>
                        <?php foreach ($hdItem['chiTiet'] as $mon): ?>
                        <div class="invoice-item-row">
                            <span class="invoice-item-name"><?php echo htmlspecialchars($mon['TenMonAn']); ?></span>
                            <span class="invoice-item-qty">x<?php echo $mon['SoLuong']; ?></span>
                            <span class="invoice-item-price"><?php echo number_format($mon['ThanhTien'], 0, ',', '.'); ?>đ</span>
                        </div>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                <?php else: ?>
                    <?php foreach ($chiTietMonVuaThanhToan as $mon): ?>
                    <div class="invoice-item-row">
                        <span class="invoice-item-name"><?php echo htmlspecialchars($mon['TenMonAn']); ?></span>
                        <span class="invoice-item-qty">x<?php echo $mon['SoLuong']; ?></span>
                        <span class="invoice-item-price"><?php echo number_format($mon['ThanhTien'], 0, ',', '.'); ?>đ</span>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <div class="invoice-total">
                <div class="invoice-total-row">
                    <span>Tạm tính:</span>
                    <span><?php echo number_format($hoaDonVuaThanhToan['TongTien'], 0, ',', '.'); ?>đ</span>
                </div>
                <?php 
                $giamGiaHienThi = $hoaDonVuaThanhToan['GiamGia'] ?? 0;
                $thanhTienHienThi = ($hoaDonVuaThanhToan['ThanhTien'] ?? $hoaDonVuaThanhToan['TongTien']) - 0;
                if ($thanhTienHienThi <= 0) $thanhTienHienThi = $hoaDonVuaThanhToan['TongTien'] - $giamGiaHienThi;
                ?>
                <div class="invoice-total-row" <?php echo $giamGiaHienThi > 0 ? 'style="color: #28a745;"' : ''; ?>>
                    <span>Giảm giá (<?php echo $hoaDonVuaThanhToan['DiemDaSuDung'] ?? 0; ?> điểm):</span>
                    <span>-<?php echo number_format($giamGiaHienThi, 0, ',', '.'); ?>đ</span>
                </div>
                <div class="invoice-total-row invoice-grand-total">
                    <span>TỔNG CỘNG:</span>
                    <span><?php echo number_format($hoaDonVuaThanhToan['TongTien'] - $giamGiaHienThi, 0, ',', '.'); ?>đ</span>
                </div>
            </div>
            
            <div style="background: #e8f5e9; padding: 15px; border-radius: 10px; text-align: center; margin: 20px 0;">
                <i class="fas fa-gift" style="color: #28a745; font-size: 24px;"></i>
                <p style="margin: 10px 0 0; color: #155724;">
                    Bạn được cộng <strong><?php echo $diemCongDuoc; ?> điểm</strong> tích lũy!
                </p>
            </div>
            
            <div class="invoice-footer">
                <p><i class="fas fa-heart" style="color: #dc3545;"></i> Cảm ơn quý khách đã sử dụng dịch vụ!</p>
                <p>Hẹn gặp lại quý khách!</p>
            </div>
            
            <div style="text-align: center; margin-top: 25px;" class="no-print">
                <button class="btn-print" onclick="window.print()">
                    <i class="fas fa-print"></i> In hóa đơn
                </button>
                <a href="thanh_toan.php" class="btn-continue">
                    <i class="fas fa-arrow-right"></i> Tiếp tục
                </a>
                <a href="index.php" class="btn-continue" style="background: #001f3f;">
                    <i class="fas fa-home"></i> Về trang chủ
                </a>
            </div>
        </div>
        <?php else: ?>

        <div class="summary-cards">
            <div class="summary-card <?php echo $tongChuaThanhToan > 0 ? 'warning' : ''; ?>">
                <i class="fas fa-file-invoice-dollar"></i>
                <h3><?php echo count($hoaDonChuaThanhToan); ?></h3>
                <p>Hóa đơn chờ thanh toán</p>
            </div>
            <div class="summary-card">
                <i class="fas fa-money-bill-wave"></i>
                <h3><?php echo number_format($tongChuaThanhToan, 0, ',', '.'); ?>đ</h3>
                <p>Tổng tiền cần thanh toán</p>
            </div>
            <div class="summary-card" style="border-left: 4px solid #D4AF37;">
                <i class="fas fa-star" style="color: #D4AF37;"></i>
                <h3 style="color: #D4AF37;"><?php echo number_format($diemHienTai); ?></h3>
                <p>Điểm tích lũy</p>
                <small style="color: #666;">= <?php echo number_format($diemHienTai * $giaTriDiem, 0, ',', '.'); ?>đ giảm giá</small>
            </div>
        </div>

        <?php if ($chiTietHoaDon): ?>
        <div class="card">
            <h2><i class="fas fa-file-invoice"></i> Chi tiết hóa đơn #<?php echo $chiTietHoaDon['MaHoaDon']; ?></h2>
            
            <div class="detail-section">
                <p><strong>Thời gian:</strong> <?php echo date('d/m/Y H:i', strtotime($chiTietHoaDon['ThoiGianVao'])); ?></p>
                <p><strong>Trạng thái:</strong> 
                    <span class="status-badge <?php 
                        echo $chiTietHoaDon['TrangThai'] === 'Đã thanh toán' ? 'status-paid' : 
                            ($chiTietHoaDon['TrangThai'] === 'Đã hủy' ? 'status-cancelled' : 'status-pending'); 
                    ?>">
                        <?php echo htmlspecialchars($chiTietHoaDon['TrangThai']); ?>
                    </span>
                </p>
            </div>
            
            <h4 style="margin: 20px 0 15px; color: #001f3f;">Chi tiết món ăn:</h4>
            <?php foreach ($chiTietMon as $mon): ?>
            <div class="detail-item">
                <?php if (!empty($mon['HinhAnh'])): ?>
                    <?php $imgSrc = getImagePath($mon['HinhAnh']); ?>
                    <img src="<?php echo htmlspecialchars($imgSrc); ?>" class="detail-item-image">
                <?php else: ?>
                    <div class="detail-item-placeholder"><i class="fas fa-utensils"></i></div>
                <?php endif; ?>
                <div class="detail-item-info">
                    <div class="detail-item-name"><?php echo htmlspecialchars($mon['TenMonAn']); ?></div>
                    <div class="detail-item-price"><?php echo number_format($mon['DonGia'], 0, ',', '.'); ?>đ x <?php echo $mon['SoLuong']; ?></div>
                </div>
                <div class="detail-item-total"><?php echo number_format($mon['ThanhTien'], 0, ',', '.'); ?>đ</div>
            </div>
            <?php endforeach; ?>
            
            <div style="margin-top: 20px; padding-top: 15px; border-top: 2px solid #001f3f; text-align: right;">
                <span style="font-size: 20px;">Tổng cộng: </span>
                <span style="font-size: 28px; font-weight: bold; color: #003366;">
                    <?php echo number_format($chiTietHoaDon['TongTien'], 0, ',', '.'); ?>đ
                </span>
            </div>
            
            <?php if ($chiTietHoaDon['TrangThai'] === 'Chưa thanh toán'): ?>
            <div class="payment-note">
                <i class="fas fa-info-circle"></i> 
                <strong>Thanh toán online:</strong> Chọn phương thức thanh toán bên dưới để thanh toán ngay.
            </div>
            
            <button type="button" class="btn-pay" onclick="openPaymentModal(<?php echo $chiTietHoaDon['MaHoaDon']; ?>, <?php echo $chiTietHoaDon['TongTien']; ?>)">
                <i class="fas fa-credit-card"></i> Thanh toán ngay
            </button>
            <?php endif; ?>
            
            <div style="margin-top: 20px;">
                <a href="thanh_toan.php" class="btn btn-view">
                    <i class="fas fa-arrow-left"></i> Quay lại danh sách
                </a>
            </div>
        </div>
        <?php endif; ?>

        <div class="card">
            <h2><i class="fas fa-clock"></i> Hóa đơn chờ thanh toán</h2>
            <?php if (empty($hoaDonChuaThanhToan)): ?>
            <div class="empty-state">
                <i class="fas fa-check-circle"></i>
                <h3>Không có hóa đơn nào</h3>
                <p>Bạn không có hóa đơn nào cần thanh toán</p>
            </div>
            <?php else: ?>
            <form method="POST" id="bulkPaymentForm">
                <div class="select-all-container">
                    <input type="checkbox" id="selectAll" class="invoice-checkbox" onchange="toggleSelectAll()">
                    <label for="selectAll">Chọn tất cả (<?php echo count($hoaDonChuaThanhToan); ?> hóa đơn)</label>
                </div>
                
                <?php foreach ($hoaDonChuaThanhToan as $hd): ?>
                <div class="invoice-item">
                    <input type="checkbox" name="selected_invoices[]" value="<?php echo $hd['MaHoaDon']; ?>" 
                           class="invoice-checkbox invoice-select" data-amount="<?php echo $hd['TongTien']; ?>" 
                           onchange="updateBulkPayment()">
                    <div class="invoice-info">
                        <h4>Hóa đơn #<?php echo $hd['MaHoaDon']; ?></h4>
                        <p><i class="fas fa-calendar"></i> <?php echo date('d/m/Y H:i', strtotime($hd['ThoiGianVao'])); ?></p>
                    </div>
                    <div class="invoice-amount"><?php echo number_format($hd['TongTien'], 0, ',', '.'); ?>đ</div>
                    <span class="status-badge status-pending">Chờ thanh toán</span>
                    <a href="?view=<?php echo $hd['MaHoaDon']; ?>" class="btn btn-view">
                        <i class="fas fa-eye"></i> Chi tiết
                    </a>
                    <button type="button" class="btn btn-view" style="background: #28a745;" onclick="openPaymentModal(<?php echo $hd['MaHoaDon']; ?>, <?php echo $hd['TongTien']; ?>)">
                        <i class="fas fa-credit-card"></i> Thanh toán
                    </button>
                    <a href="?cancel=<?php echo $hd['MaHoaDon']; ?>" class="btn btn-view btn-cancel" style="background: #dc3545;" onclick="return confirm('Bạn có chắc muốn hủy hóa đơn #<?php echo $hd['MaHoaDon']; ?>?')">
                        <i class="fas fa-times"></i> Hủy
                    </a>
                </div>
                <?php endforeach; ?>
                
                <input type="hidden" name="PhuongThuc" id="bulkPhuongThuc" value="Tiền mặt">
            </form>
            <?php endif; ?>
        </div>

        <div class="card">
            <h2><i class="fas fa-history"></i> Lịch sử thanh toán</h2>
            <?php if (empty($lichSuThanhToan)): ?>
            <div class="empty-state">
                <i class="fas fa-receipt"></i>
                <h3>Chưa có lịch sử</h3>
                <p>Lịch sử thanh toán sẽ hiển thị ở đây</p>
            </div>
            <?php else: ?>
                <?php foreach ($lichSuThanhToan as $hd): ?>
                <div class="invoice-item">
                    <div class="invoice-info">
                        <h4>Hóa đơn #<?php echo $hd['MaHoaDon']; ?></h4>
                        <p><i class="fas fa-calendar"></i> <?php echo date('d/m/Y H:i', strtotime($hd['ThoiGianVao'])); ?></p>
                    </div>
                    <div class="invoice-amount"><?php echo number_format($hd['TongTien'], 0, ',', '.'); ?>đ</div>
                    <span class="status-badge <?php echo $hd['TrangThai'] === 'Đã thanh toán' ? 'status-paid' : 'status-cancelled'; ?>">
                        <?php echo htmlspecialchars($hd['TrangThai']); ?>
                    </span>
                    <a href="?view=<?php echo $hd['MaHoaDon']; ?>" class="btn btn-view">
                        <i class="fas fa-eye"></i> Chi tiết
                    </a>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- Bulk Payment Bar -->
    <?php if (!empty($hoaDonChuaThanhToan) && !$hoaDonVuaThanhToan): ?>
    <div class="bulk-payment-bar" id="bulkPaymentBar">
        <div class="bulk-payment-info">
            Đã chọn <strong id="selectedCount">0</strong> hóa đơn - Tổng: <strong id="selectedTotal">0đ</strong>
        </div>
        <button type="button" class="btn-bulk-pay" onclick="openBulkPaymentModal()">
            <i class="fas fa-credit-card"></i> Thanh toán gộp
        </button>
    </div>
    <?php endif; ?>

    <!-- Modal Thanh toán -->
    <div id="paymentModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-credit-card"></i> Thanh toán hóa đơn</h3>
                <button class="close-btn" onclick="closePaymentModal()">&times;</button>
            </div>
            
            <form method="POST" id="paymentForm">
                <input type="hidden" name="MaHoaDon" id="pay_MaHoaDon">
                <input type="hidden" name="PhuongThuc" id="pay_PhuongThuc" value="Tiền mặt">
                <input type="hidden" name="DiemSuDung" id="pay_DiemSuDung" value="0">
                
                <div style="text-align: center; margin-bottom: 20px;">
                    <p style="color: #666;">Tổng tiền hóa đơn:</p>
                    <p style="font-size: 32px; font-weight: bold; color: #003366;" id="pay_Amount">0đ</p>
                </div>
                
                <!-- Phần đổi điểm -->
                <div class="points-exchange-section" id="pay_PointsSection" style="background: linear-gradient(135deg, #fff9e6, #fff3cd); padding: 20px; border-radius: 12px; margin-bottom: 20px; border: 2px solid #D4AF37;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 15px;">
                        <div>
                            <p style="color: #856404; font-weight: 600; margin: 0;">
                                <i class="fas fa-star" style="color: #D4AF37;"></i> Đổi điểm lấy giảm giá
                            </p>
                            <p style="color: #666; font-size: 12px; margin: 5px 0 0;">
                                1000 điểm = 10,000đ giảm giá
                            </p>
                        </div>
                        <div style="text-align: right;">
                            <p style="font-size: 20px; font-weight: bold; color: #D4AF37; margin: 0;">
                                <?php echo number_format($diemHienTai); ?> điểm
                            </p>
                            <small style="color: #666;">= <?php echo number_format(floor($diemHienTai / 1000) * 10000, 0, ',', '.'); ?>đ</small>
                        </div>
                    </div>
                    
                    <?php if ($diemHienTai >= 1000): ?>
                    <!-- Có đủ điểm để đổi -->
                    <div style="margin-bottom: 15px;">
                        <label style="color: #666; font-size: 13px;">Nhập số điểm muốn đổi (tối thiểu 1000 điểm):</label>
                        <div style="display: flex; gap: 10px; margin-top: 8px;">
                            <input type="number" id="pay_DiemInput" min="0" max="<?php echo $diemHienTai; ?>" value="0" step="1000"
                                   style="flex: 1; padding: 12px; border: 2px solid #D4AF37; border-radius: 8px; font-size: 16px; font-weight: bold; text-align: center;"
                                   oninput="updatePaymentDiscount()">
                            <button type="button" onclick="setMaxPoints('pay')" style="padding: 12px 20px; background: #D4AF37; color: #001f3f; border: none; border-radius: 8px; font-weight: bold; cursor: pointer;">
                                Dùng hết
                            </button>
                        </div>
                    </div>
                    
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px; background: rgba(255,255,255,0.5); border-radius: 8px;">
                        <div>
                            <span style="color: #666;">Giảm giá: </span>
                            <span id="pay_Discount" style="font-weight: bold; color: #28a745; font-size: 18px;">0đ</span>
                        </div>
                    </div>
                    <?php else: ?>
                    <!-- Không đủ điểm -->
                    <div style="background: #fff3cd; padding: 15px; border-radius: 8px; border: 1px solid #ffc107;">
                        <p style="color: #856404; margin: 0; display: flex; align-items: center; gap: 10px;">
                            <i class="fas fa-exclamation-triangle" style="font-size: 20px;"></i>
                            <span>
                                <strong>Chưa đủ điểm để đổi!</strong><br>
                                <small>Cần tối thiểu 1,000 điểm. Bạn còn thiếu <?php echo number_format(1000 - $diemHienTai); ?> điểm.</small>
                            </span>
                        </p>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Số tiền cần thanh toán -->
                <div style="text-align: center; margin-bottom: 20px; padding: 15px; background: #e8f5e9; border-radius: 10px; border: 2px solid #28a745;">
                    <p style="color: #666; margin: 0 0 5px;">Số tiền cần thanh toán:</p>
                    <p style="font-size: 32px; font-weight: bold; color: #28a745; margin: 0;" id="pay_FinalAmount">0đ</p>
                </div>
                
                <p style="margin-bottom: 15px; color: #001f3f; font-weight: 600;">
                    <i class="fas fa-wallet"></i> Chọn phương thức thanh toán:
                </p>
                
                <div class="payment-methods">
                    <div class="payment-method selected" onclick="selectPayment(this, 'Tiền mặt')">
                        <i class="fas fa-money-bill-wave"></i>
                        <span>Tiền mặt</span>
                    </div>
                    <div class="payment-method" onclick="selectPayment(this, 'Thẻ ngân hàng')">
                        <i class="fas fa-credit-card"></i>
                        <span>Thẻ ngân hàng</span>
                    </div>
                    <div class="payment-method" onclick="selectPayment(this, 'Ví MoMo')">
                        <i class="fas fa-mobile-alt"></i>
                        <span>Ví MoMo</span>
                    </div>
                    <div class="payment-method" onclick="selectPayment(this, 'ZaloPay')">
                        <i class="fas fa-qrcode"></i>
                        <span>ZaloPay</span>
                    </div>
                </div>
                
                <div class="payment-note" style="margin: 20px 0;">
                    <i class="fas fa-gift"></i> 
                    Bạn sẽ được cộng <strong id="pay_Points">0</strong> điểm tích lũy sau khi thanh toán!
                </div>
                
                <button type="submit" name="thanh_toan" class="btn-pay">
                    <i class="fas fa-check-circle"></i> Xác nhận thanh toán
                </button>
            </form>
        </div>
    </div>

    <!-- Modal Thanh toán gộp -->
    <div id="bulkPaymentModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-layer-group"></i> Thanh toán gộp</h3>
                <button class="close-btn" onclick="closeBulkPaymentModal()">&times;</button>
            </div>
            
            <div style="text-align: center; margin-bottom: 20px;">
                <p style="color: #666;">Thanh toán <strong id="bulk_Count">0</strong> hóa đơn</p>
                <p style="font-size: 32px; font-weight: bold; color: #003366;" id="bulk_Amount">0đ</p>
            </div>
            
            <!-- Phần đổi điểm -->
            <div class="points-exchange-section" style="background: linear-gradient(135deg, #fff9e6, #fff3cd); padding: 20px; border-radius: 12px; margin-bottom: 20px; border: 2px solid #D4AF37;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 15px;">
                    <div>
                        <p style="color: #856404; font-weight: 600; margin: 0;">
                            <i class="fas fa-star" style="color: #D4AF37;"></i> Đổi điểm lấy giảm giá
                        </p>
                        <p style="color: #666; font-size: 12px; margin: 5px 0 0;">
                            1000 điểm = 10,000đ giảm giá
                        </p>
                    </div>
                    <div style="text-align: right;">
                        <p style="font-size: 20px; font-weight: bold; color: #D4AF37; margin: 0;">
                            <?php echo number_format($diemHienTai); ?> điểm
                        </p>
                        <small style="color: #666;">= <?php echo number_format(floor($diemHienTai / 1000) * 10000, 0, ',', '.'); ?>đ</small>
                    </div>
                </div>
                
                <?php if ($diemHienTai >= 1000): ?>
                <!-- Có đủ điểm để đổi -->
                <div style="margin-bottom: 15px;">
                    <label style="color: #666; font-size: 13px;">Nhập số điểm muốn đổi (tối thiểu 1000 điểm):</label>
                    <div style="display: flex; gap: 10px; margin-top: 8px;">
                        <input type="number" id="bulk_DiemInput" min="0" max="<?php echo $diemHienTai; ?>" value="0" step="1000"
                               style="flex: 1; padding: 12px; border: 2px solid #D4AF37; border-radius: 8px; font-size: 16px; font-weight: bold; text-align: center;"
                               oninput="updateBulkDiscount()">
                        <button type="button" onclick="setMaxPoints('bulk')" style="padding: 12px 20px; background: #D4AF37; color: #001f3f; border: none; border-radius: 8px; font-weight: bold; cursor: pointer;">
                            Dùng hết
                        </button>
                    </div>
                </div>
                
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px; background: rgba(255,255,255,0.5); border-radius: 8px;">
                    <div>
                        <span style="color: #666;">Giảm giá: </span>
                        <span id="bulk_Discount" style="font-weight: bold; color: #28a745; font-size: 18px;">0đ</span>
                    </div>
                </div>
                <?php else: ?>
                <!-- Không đủ điểm -->
                <div style="background: #fff3cd; padding: 15px; border-radius: 8px; border: 1px solid #ffc107;">
                    <p style="color: #856404; margin: 0; display: flex; align-items: center; gap: 10px;">
                        <i class="fas fa-exclamation-triangle" style="font-size: 20px;"></i>
                        <span>
                            <strong>Chưa đủ điểm để đổi!</strong><br>
                            <small>Cần tối thiểu 1,000 điểm. Bạn còn thiếu <?php echo number_format(1000 - $diemHienTai); ?> điểm.</small>
                        </span>
                    </p>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Số tiền cần thanh toán -->
            <div style="text-align: center; margin-bottom: 20px; padding: 15px; background: #e8f5e9; border-radius: 10px; border: 2px solid #28a745;">
                <p style="color: #666; margin: 0 0 5px;">Số tiền cần thanh toán:</p>
                <p style="font-size: 32px; font-weight: bold; color: #28a745; margin: 0;" id="bulk_FinalAmount">0đ</p>
            </div>
            
            <p style="margin-bottom: 15px; color: #001f3f; font-weight: 600;">
                <i class="fas fa-wallet"></i> Chọn phương thức thanh toán:
            </p>
            
            <div class="payment-methods" id="bulkPaymentMethods">
                <div class="payment-method selected" onclick="selectBulkPayment(this, 'Tiền mặt')">
                    <i class="fas fa-money-bill-wave"></i>
                    <span>Tiền mặt</span>
                </div>
                <div class="payment-method" onclick="selectBulkPayment(this, 'Thẻ ngân hàng')">
                    <i class="fas fa-credit-card"></i>
                    <span>Thẻ ngân hàng</span>
                </div>
                <div class="payment-method" onclick="selectBulkPayment(this, 'Ví MoMo')">
                    <i class="fas fa-mobile-alt"></i>
                    <span>Ví MoMo</span>
                </div>
                <div class="payment-method" onclick="selectBulkPayment(this, 'ZaloPay')">
                    <i class="fas fa-qrcode"></i>
                    <span>ZaloPay</span>
                </div>
            </div>
            
            <div class="payment-note" style="margin: 20px 0;">
                <i class="fas fa-gift"></i> 
                Bạn sẽ được cộng <strong id="bulk_Points">0</strong> điểm tích lũy sau khi thanh toán!
            </div>
            
            <button type="button" class="btn-pay" onclick="submitBulkPayment()">
                <i class="fas fa-check-circle"></i> Xác nhận thanh toán gộp
            </button>
        </div>
    </div>

    <script>
        let selectedTotal = 0;
        let selectedCount = 0;
        let currentPayAmount = 0;
        const giaTriDiem = 1000; // 1 điểm = 1,000đ
        
        function toggleSelectAll() {
            const selectAll = document.getElementById('selectAll');
            const checkboxes = document.querySelectorAll('.invoice-select');
            checkboxes.forEach(cb => {
                cb.checked = selectAll.checked;
            });
            updateBulkPayment();
        }
        
        function updateBulkPayment() {
            const checkboxes = document.querySelectorAll('.invoice-select:checked');
            selectedCount = checkboxes.length;
            selectedTotal = 0;
            
            checkboxes.forEach(cb => {
                selectedTotal += parseInt(cb.dataset.amount);
            });
            
            const bar = document.getElementById('bulkPaymentBar');
            if (bar) {
                if (selectedCount > 0) {
                    bar.classList.add('show');
                    document.getElementById('selectedCount').textContent = selectedCount;
                    document.getElementById('selectedTotal').textContent = formatPrice(selectedTotal) + 'đ';
                } else {
                    bar.classList.remove('show');
                }
            }
            
            // Update select all checkbox
            const allCheckboxes = document.querySelectorAll('.invoice-select');
            const selectAll = document.getElementById('selectAll');
            if (selectAll) {
                selectAll.checked = checkboxes.length === allCheckboxes.length && allCheckboxes.length > 0;
            }
        }
        
        function openBulkPaymentModal() {
            document.getElementById('bulk_Count').textContent = selectedCount;
            document.getElementById('bulk_Amount').textContent = formatPrice(selectedTotal) + 'đ';
            
            // Reset input đổi điểm
            const diemInput = document.getElementById('bulk_DiemInput');
            if (diemInput) {
                diemInput.value = 0;
                updateBulkDiscount();
            }
            
            // Cập nhật số tiền cần thanh toán ban đầu
            const finalEl = document.getElementById('bulk_FinalAmount');
            if (finalEl) {
                finalEl.textContent = formatPrice(selectedTotal) + 'đ';
            }
            
            // Tính điểm sẽ được cộng (10,000đ = 1 điểm)
            document.getElementById('bulk_Points').textContent = formatPrice(Math.floor(selectedTotal / 10000));
            
            document.getElementById('bulkPaymentModal').classList.add('active');
        }
        
        function updateBulkDiscount() {
            const diemInput = document.getElementById('bulk_DiemInput');
            if (!diemInput) return;
            
            let diemSuDung = parseInt(diemInput.value) || 0;
            
            // Kiểm tra tối thiểu 1000 điểm hoặc 0
            if (diemSuDung > 0 && diemSuDung < 1000) {
                diemSuDung = 0;
                diemInput.value = 0;
                alert('Tối thiểu phải đổi 1,000 điểm!');
                return;
            }
            
            // Kiểm tra không vượt quá điểm hiện có
            const maxDiem = parseInt(diemInput.max);
            if (diemSuDung > maxDiem) {
                diemSuDung = maxDiem;
                diemInput.value = maxDiem;
            }
            
            // Tính giảm giá (1000 điểm = 10,000đ)
            let giamGia = Math.floor(diemSuDung / 1000) * 10000;
            
            // Không cho giảm quá tổng tiền
            if (giamGia > selectedTotal) {
                giamGia = selectedTotal;
                diemSuDung = Math.ceil(giamGia / 10) * 1000;
                diemInput.value = diemSuDung;
            }
            
            const thanhTien = selectedTotal - giamGia;
            
            document.getElementById('bulk_Discount').textContent = '-' + formatPrice(giamGia) + 'đ';
            document.getElementById('bulk_FinalAmount').textContent = formatPrice(thanhTien) + 'đ';
            document.getElementById('bulk_Points').textContent = formatPrice(Math.floor(thanhTien / 10000));
        }
        
        function closeBulkPaymentModal() {
            document.getElementById('bulkPaymentModal').classList.remove('active');
        }
        
        function selectBulkPayment(el, method) {
            document.querySelectorAll('#bulkPaymentMethods .payment-method').forEach(function(item) {
                item.classList.remove('selected');
            });
            el.classList.add('selected');
            document.getElementById('bulkPhuongThuc').value = method;
        }
        
        function submitBulkPayment() {
            const form = document.getElementById('bulkPaymentForm');
            
            // Thêm input thanh_toan_gop
            let input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'thanh_toan_gop';
            input.value = '1';
            form.appendChild(input);
            
            // Thêm điểm sử dụng
            const diemInput = document.getElementById('bulk_DiemInput');
            if (diemInput) {
                let diemHidden = document.createElement('input');
                diemHidden.type = 'hidden';
                diemHidden.name = 'DiemSuDung';
                diemHidden.value = diemInput.value || 0;
                form.appendChild(diemHidden);
            }
            
            form.submit();
        }
        
        function openPaymentModal(maHoaDon, tongTien) {
            currentPayAmount = tongTien;
            document.getElementById('pay_MaHoaDon').value = maHoaDon;
            document.getElementById('pay_Amount').textContent = formatPrice(tongTien) + 'đ';
            
            // Reset input đổi điểm
            const diemInput = document.getElementById('pay_DiemInput');
            if (diemInput) {
                diemInput.value = 0;
                updatePaymentDiscount();
            }
            
            // Cập nhật số tiền cần thanh toán ban đầu
            const finalEl = document.getElementById('pay_FinalAmount');
            if (finalEl) {
                finalEl.textContent = formatPrice(tongTien) + 'đ';
            }
            
            // Tính điểm sẽ được cộng (10,000đ = 1 điểm)
            document.getElementById('pay_Points').textContent = formatPrice(Math.floor(tongTien / 10000));
            
            document.getElementById('paymentModal').classList.add('active');
        }
        
        function updatePaymentDiscount() {
            const diemInput = document.getElementById('pay_DiemInput');
            if (!diemInput) return;
            
            let diemSuDung = parseInt(diemInput.value) || 0;
            
            // Kiểm tra tối thiểu 1000 điểm hoặc 0
            if (diemSuDung > 0 && diemSuDung < 1000) {
                diemSuDung = 0;
                diemInput.value = 0;
                alert('Tối thiểu phải đổi 1,000 điểm!');
                return;
            }
            
            // Kiểm tra không vượt quá điểm hiện có
            const maxDiem = parseInt(diemInput.max);
            if (diemSuDung > maxDiem) {
                diemSuDung = maxDiem;
                diemInput.value = maxDiem;
            }
            
            // Tính giảm giá (1000 điểm = 10,000đ)
            let giamGia = Math.floor(diemSuDung / 1000) * 10000;
            
            // Không cho giảm quá tổng tiền
            if (giamGia > currentPayAmount) {
                giamGia = currentPayAmount;
                diemSuDung = Math.ceil(giamGia / 10) * 1000;
                diemInput.value = diemSuDung;
            }
            
            const thanhTien = currentPayAmount - giamGia;
            
            document.getElementById('pay_DiemSuDung').value = diemSuDung;
            document.getElementById('pay_Discount').textContent = '-' + formatPrice(giamGia) + 'đ';
            document.getElementById('pay_FinalAmount').textContent = formatPrice(thanhTien) + 'đ';
            document.getElementById('pay_Points').textContent = formatPrice(Math.floor(thanhTien / 10000));
        }
        
        function setMaxPoints(type) {
            if (type === 'pay') {
                const input = document.getElementById('pay_DiemInput');
                if (input) {
                    // Làm tròn xuống bội số của 1000
                    input.value = Math.floor(parseInt(input.max) / 1000) * 1000;
                    updatePaymentDiscount();
                }
            } else {
                const input = document.getElementById('bulk_DiemInput');
                if (input) {
                    input.value = Math.floor(parseInt(input.max) / 1000) * 1000;
                    updateBulkDiscount();
                }
            }
        }
        
        function closePaymentModal() {
            document.getElementById('paymentModal').classList.remove('active');
        }
        
        function selectPayment(el, method) {
            document.querySelectorAll('#paymentForm .payment-method').forEach(function(item) {
                item.classList.remove('selected');
            });
            el.classList.add('selected');
            document.getElementById('pay_PhuongThuc').value = method;
        }
        
        function formatPrice(price) {
            return new Intl.NumberFormat('vi-VN').format(price);
        }
        
        document.getElementById('paymentModal').addEventListener('click', function(e) {
            if (e.target === this) closePaymentModal();
        });
        
        document.getElementById('bulkPaymentModal').addEventListener('click', function(e) {
            if (e.target === this) closeBulkPaymentModal();
        });
        
        <?php if ($hoaDonVuaThanhToan): ?>
        // Show success popup on page load
        document.addEventListener('DOMContentLoaded', function() {
            showSuccessPopup();
        });
        
        function showSuccessPopup() {
            const popup = document.createElement('div');
            popup.className = 'success-popup';
            popup.innerHTML = `
                <div class="success-popup-content">
                    <div class="success-popup-icon">
                        <i class="fas fa-check"></i>
                    </div>
                    <h3>Thanh toán thành công!</h3>
                    <p>Cảm ơn bạn đã sử dụng dịch vụ của Nhà hàng 3CE</p>
                    <div class="points-earned">
                        <i class="fas fa-star"></i>
                        <span>+<?php echo $diemCongDuoc; ?> điểm</span>
                        <small>Điểm tích lũy đã được cộng vào tài khoản</small>
                    </div>
                    <button onclick="this.parentElement.parentElement.remove()" class="btn-continue" style="width: 100%;">
                        <i class="fas fa-receipt"></i> Xem hóa đơn
                    </button>
                </div>
            `;
            document.body.appendChild(popup);
            
            // Auto close after 5 seconds
            setTimeout(() => {
                if (popup.parentElement) {
                    popup.remove();
                }
            }, 5000);
            
            // Close on click outside
            popup.addEventListener('click', function(e) {
                if (e.target === this) {
                    this.remove();
                }
            });
        }
        <?php endif; ?>
    </script>
</body>
</html>