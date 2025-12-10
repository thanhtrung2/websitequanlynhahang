<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: admin_login.php");
    exit();
}

try {
    // Doanh thu hôm nay (chỉ tính đơn đã thanh toán)
    $stmt = $conn->query("SELECT SUM(TongTien) as total FROM hoa_don WHERE DATE(ThoiGianVao) = CURDATE() AND TrangThai = 'Đã thanh toán'");
    $doanhThuHomNay = $stmt->fetch()['total'] ?? 0;
    
    // Doanh thu tháng này
    $stmt = $conn->query("SELECT SUM(TongTien) as total FROM hoa_don WHERE MONTH(ThoiGianVao) = MONTH(CURDATE()) AND YEAR(ThoiGianVao) = YEAR(CURDATE()) AND TrangThai = 'Đã thanh toán'");
    $doanhThuThang = $stmt->fetch()['total'] ?? 0;
    
    // Doanh thu năm nay
    $stmt = $conn->query("SELECT SUM(TongTien) as total FROM hoa_don WHERE YEAR(ThoiGianVao) = YEAR(CURDATE()) AND TrangThai = 'Đã thanh toán'");
    $doanhThuNam = $stmt->fetch()['total'] ?? 0;
    
    // Số hóa đơn hôm nay
    $stmt = $conn->query("SELECT COUNT(*) as total FROM hoa_don WHERE DATE(ThoiGianVao) = CURDATE()");
    $soHoaDonHomNay = $stmt->fetch()['total'];
    
    // Số đơn đã thanh toán hôm nay
    $stmt = $conn->query("SELECT COUNT(*) as total FROM hoa_don WHERE DATE(ThoiGianVao) = CURDATE() AND TrangThai = 'Đã thanh toán'");
    $soDonThanhToan = $stmt->fetch()['total'];
    
    // Số đơn chờ xử lý
    $stmt = $conn->query("SELECT COUNT(*) as total FROM hoa_don WHERE TrangThai = 'Chưa thanh toán'");
    $donChoXuLy = $stmt->fetch()['total'];
    
    // Doanh thu chờ xử lý
    $stmt = $conn->query("SELECT SUM(TongTien) as total FROM hoa_don WHERE TrangThai = 'Chưa thanh toán'");
    $doanhThuCho = $stmt->fetch()['total'] ?? 0;
    
    // Doanh thu 7 ngày gần nhất
    $stmt = $conn->query("SELECT DATE(ThoiGianVao) as ngay, SUM(TongTien) as total, COUNT(*) as soDon 
                          FROM hoa_don 
                          WHERE ThoiGianVao >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) AND TrangThai = 'Đã thanh toán'
                          GROUP BY DATE(ThoiGianVao) 
                          ORDER BY ngay DESC");
    $doanhThu7Ngay = $stmt->fetchAll();
    
} catch(PDOException $e) {
    die("Lỗi: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Nhà hàng 3CE - Báo cáo doanh thu</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #F5F5DC 0%, #EDE8D0 100%);
            min-height: 100vh;
        }
        .header {
            background: linear-gradient(135deg, #001f3f 0%, #003366 100%);
            padding: 15px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 24px;
            font-weight: bold;
            color: #F5F5DC;
        }
        .back-btn {
            padding: 8px 20px;
            background: #F5F5DC;
            color: #001f3f;
            text-decoration: none;
            border-radius: 5px;
        }
        .back-btn:hover {
            background: #E8E4C9;
        }
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        .page-header {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            margin-bottom: 30px;
        }
        .page-header h1 {
            color: #001f3f;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }
        .stat-card {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        .stat-card i {
            font-size: 40px;
            color: #001f3f;
            margin-bottom: 15px;
        }
        .stat-card h3 {
            font-size: 32px;
            color: #003366;
            margin-bottom: 10px;
        }
        .stat-card p {
            color: #666;
            font-size: 16px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <div class="logo">
                <i class="fas fa-chart-bar"></i>
                <span>Báo cáo doanh thu</span>
            </div>
            <a href="dashboard.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Quay lại
            </a>
        </div>
    </div>

    <div class="container">
        <div class="page-header">
            <h1><i class="fas fa-chart-bar"></i> Thống kê doanh thu</h1>
            <p>Báo cáo chi tiết về doanh thu nhà hàng</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <i class="fas fa-calendar-day"></i>
                <h3><?php echo number_format($doanhThuHomNay, 0, ',', '.'); ?>đ</h3>
                <p>Doanh thu hôm nay</p>
                <p style="margin-top: 10px; color: #001f3f;">
                    <i class="fas fa-check-circle"></i> <?php echo $soDonThanhToan; ?> đơn đã thanh toán
                </p>
            </div>

            <div class="stat-card" style="border-left: 4px solid #ffc107;">
                <i class="fas fa-clock" style="color: #ffc107;"></i>
                <h3><?php echo number_format($doanhThuCho, 0, ',', '.'); ?>đ</h3>
                <p>Doanh thu chờ xử lý</p>
                <p style="margin-top: 10px; color: #856404;">
                    <i class="fas fa-hourglass-half"></i> <?php echo $donChoXuLy; ?> đơn chờ thanh toán
                </p>
            </div>

            <div class="stat-card">
                <i class="fas fa-calendar-alt"></i>
                <h3><?php echo number_format($doanhThuThang, 0, ',', '.'); ?>đ</h3>
                <p>Doanh thu tháng này</p>
            </div>

            <div class="stat-card">
                <i class="fas fa-calendar"></i>
                <h3><?php echo number_format($doanhThuNam, 0, ',', '.'); ?>đ</h3>
                <p>Doanh thu năm nay</p>
            </div>
        </div>

        <?php if (!empty($doanhThu7Ngay)): ?>
        <div class="stat-card" style="margin-top: 30px;">
            <h3 style="font-size: 20px; margin-bottom: 20px; color: #001f3f;">
                <i class="fas fa-chart-line"></i> Doanh thu 7 ngày gần nhất
            </h3>
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: linear-gradient(135deg, #001f3f 0%, #003366 100%); color: #F5F5DC;">
                        <th style="padding: 12px; text-align: left;">Ngày</th>
                        <th style="padding: 12px; text-align: center;">Số đơn</th>
                        <th style="padding: 12px; text-align: right;">Doanh thu</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($doanhThu7Ngay as $row): ?>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding: 12px;">
                            <?php 
                            $date = new DateTime($row['ngay']);
                            echo $date->format('d/m/Y');
                            if ($row['ngay'] === date('Y-m-d')) echo ' <span style="color: #28a745; font-size: 12px;">(Hôm nay)</span>';
                            ?>
                        </td>
                        <td style="padding: 12px; text-align: center;">
                            <span style="background: #e9ecef; padding: 3px 10px; border-radius: 10px;">
                                <?php echo $row['soDon']; ?> đơn
                            </span>
                        </td>
                        <td style="padding: 12px; text-align: right; font-weight: bold; color: #003366;">
                            <?php echo number_format($row['total'], 0, ',', '.'); ?>đ
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
