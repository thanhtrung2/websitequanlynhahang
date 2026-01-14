<?php
session_start();
require_once __DIR__ . '/../config/db.php';

$message = '';
$error = '';

// Tự động tạo bảng nếu chưa tồn tại
try {
    $conn->exec("CREATE TABLE IF NOT EXISTS lien_he (
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
        MaKhachHang INT
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
} catch(PDOException $e) {}

// Lấy thông tin khách hàng nếu đã đăng nhập
$customerInfo = null;
if (isset($_SESSION['customer_id'])) {
    try {
        $stmt = $conn->prepare("SELECT * FROM khach_hang WHERE MaKhachHang = ?");
        $stmt->execute([$_SESSION['customer_id']]);
        $customerInfo = $stmt->fetch();
    } catch(PDOException $e) {}
}

// Xử lý gửi liên hệ
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hoTen = trim($_POST['HoTen'] ?? '');
    $email = trim($_POST['Email'] ?? '');
    $soDienThoai = trim($_POST['SoDienThoai'] ?? '');
    $chuDe = trim($_POST['ChuDe'] ?? '');
    $noiDung = trim($_POST['NoiDung'] ?? '');
    $maKhachHang = $_SESSION['customer_id'] ?? null;
    
    if (empty($hoTen) || empty($soDienThoai) || empty($chuDe) || empty($noiDung)) {
        $error = 'Vui lòng điền đầy đủ thông tin bắt buộc!';
    } else {
        try {
            $stmt = $conn->prepare("INSERT INTO lien_he (HoTen, Email, SoDienThoai, ChuDe, NoiDung, MaKhachHang) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$hoTen, $email, $soDienThoai, $chuDe, $noiDung, $maKhachHang]);
            $message = 'Gửi liên hệ thành công! Chúng tôi sẽ phản hồi sớm nhất có thể.';
        } catch(PDOException $e) {
            $error = 'Lỗi: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liên hệ - Nhà hàng 3CE</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #F5F5DC 0%, #EDE8D0 100%);
            min-height: 100vh;
            padding-top: 70px;
        }
        .container { max-width: 1200px; margin: 30px auto; padding: 0 20px; }
        .page-header {
            text-align: center;
            margin-bottom: 40px;
        }
        .page-header h1 {
            font-size: 42px;
            color: #001f3f;
            margin-bottom: 10px;
        }
        .page-header p { color: #666; font-size: 18px; }
        .contact-wrapper {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
        }
        .contact-info {
            background: linear-gradient(135deg, #001f3f 0%, #003366 100%);
            border-radius: 20px;
            padding: 40px;
            color: #F5F5DC;
        }
        .contact-info h2 {
            margin-bottom: 30px;
            font-size: 28px;
        }
        .info-item {
            display: flex;
            align-items: flex-start;
            gap: 20px;
            margin-bottom: 30px;
        }
        .info-icon {
            width: 50px;
            height: 50px;
            background: rgba(212,175,55,0.2);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            color: #D4AF37;
        }
        .info-content h4 { margin-bottom: 5px; color: #D4AF37; }
        .info-content p { opacity: 0.9; line-height: 1.6; }
        .social-links {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }
        .social-links a {
            width: 45px;
            height: 45px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #F5F5DC;
            font-size: 20px;
            transition: all 0.3s;
        }
        .social-links a:hover {
            background: #D4AF37;
            color: #001f3f;
            transform: translateY(-3px);
        }
        .contact-form {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        }
        .contact-form h2 {
            color: #001f3f;
            margin-bottom: 25px;
            font-size: 28px;
        }
        .form-group { margin-bottom: 20px; }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #001f3f;
            font-weight: 500;
        }
        .form-group input, .form-group textarea, .form-group select {
            width: 100%;
            padding: 14px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        .form-group input:focus, .form-group textarea:focus, .form-group select:focus {
            outline: none;
            border-color: #001f3f;
        }
        .form-group textarea { resize: vertical; min-height: 120px; }
        .btn-submit {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #001f3f 0%, #003366 100%);
            color: #F5F5DC;
            border: none;
            border-radius: 10px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        .btn-submit:hover {
            background: linear-gradient(135deg, #D4AF37 0%, #f7d774 100%);
            color: #001f3f;
        }
        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .alert-success { background: #d4edda; color: #155724; }
        .alert-error { background: #f8d7da; color: #721c24; }
        .map-section {
            margin-top: 40px;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        }
        .map-section iframe {
            width: 100%;
            height: 400px;
            border: none;
        }
        @media (max-width: 900px) {
            .contact-wrapper { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container">
        <div class="page-header">
            <h1><i class="fas fa-envelope"></i> Liên Hệ</h1>
            <p>Chúng tôi luôn sẵn sàng lắng nghe và hỗ trợ bạn</p>
        </div>

        <div class="contact-wrapper">
            <div class="contact-info">
                <h2><i class="fas fa-info-circle"></i> Thông Tin Liên Hệ</h2>
                
                <div class="info-item">
                    <div class="info-icon"><i class="fas fa-map-marker-alt"></i></div>
                    <div class="info-content">
                        <h4>Địa chỉ</h4>
                        <p>123 Đường ABC, Quận 1<br>TP. Hồ Chí Minh, Việt Nam</p>
                    </div>
                </div>
                
                <div class="info-item">
                    <div class="info-icon"><i class="fas fa-phone-alt"></i></div>
                    <div class="info-content">
                        <h4>Điện thoại</h4>
                        <p>Hotline: 0123 456 789<br>Đặt bàn: 0987 654 321</p>
                    </div>
                </div>
                
                <div class="info-item">
                    <div class="info-icon"><i class="fas fa-envelope"></i></div>
                    <div class="info-content">
                        <h4>Email</h4>
                        <p>info@nhahang3ce.com<br>support@nhahang3ce.com</p>
                    </div>
                </div>
                
                <div class="info-item">
                    <div class="info-icon"><i class="fas fa-clock"></i></div>
                    <div class="info-content">
                        <h4>Giờ mở cửa</h4>
                        <p>Thứ 2 - Thứ 6: 10:00 - 22:00<br>Thứ 7 - CN: 09:00 - 23:00</p>
                    </div>
                </div>
                
                <div class="social-links">
                    <a href="#" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" title="Instagram"><i class="fab fa-instagram"></i></a>
                    <a href="#" title="Youtube"><i class="fab fa-youtube"></i></a>
                    <a href="#" title="TikTok"><i class="fab fa-tiktok"></i></a>
                </div>
            </div>

            <div class="contact-form">
                <h2><i class="fas fa-paper-plane"></i> Gửi Tin Nhắn</h2>
                
                <?php if ($message): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($message); ?>
                </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
                <?php endif; ?>
                
                <form method="POST">
                    <div class="form-group">
                        <label><i class="fas fa-user"></i> Họ tên *</label>
                        <input type="text" name="HoTen" required 
                               value="<?php echo htmlspecialchars($customerInfo['HoTen'] ?? ''); ?>"
                               placeholder="Nhập họ tên của bạn">
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-phone"></i> Số điện thoại *</label>
                        <input type="text" name="SoDienThoai" required
                               value="<?php echo htmlspecialchars($customerInfo['SoDienThoai'] ?? ''); ?>"
                               placeholder="Nhập số điện thoại">
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-envelope"></i> Email</label>
                        <input type="email" name="Email"
                               value="<?php echo htmlspecialchars($customerInfo['Email'] ?? ''); ?>"
                               placeholder="Nhập email (tùy chọn)">
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-tag"></i> Chủ đề *</label>
                        <select name="ChuDe" required>
                            <option value="">-- Chọn chủ đề --</option>
                            <option value="Hỏi về thực đơn">Hỏi về thực đơn</option>
                            <option value="Đặt bàn / Đặt tiệc">Đặt bàn / Đặt tiệc</option>
                            <option value="Góp ý dịch vụ">Góp ý dịch vụ</option>
                            <option value="Khiếu nại">Khiếu nại</option>
                            <option value="Hợp tác kinh doanh">Hợp tác kinh doanh</option>
                            <option value="Khác">Khác</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label><i class="fas fa-comment-alt"></i> Nội dung *</label>
                        <textarea name="NoiDung" required placeholder="Nhập nội dung tin nhắn của bạn..."></textarea>
                    </div>
                    
                    <button type="submit" class="btn-submit">
                        <i class="fas fa-paper-plane"></i> Gửi Tin Nhắn
                    </button>
                </form>
            </div>
        </div>

        <div class="map-section">
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3919.4241674197956!2d106.69765841533417!3d10.778789792319695!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31752f38f9ed887b%3A0x14aded5703768989!2zUXXhuq1uIDEsIEjhu5MgQ2jDrSBNaW5oLCBWaeG7h3QgTmFt!5e0!3m2!1svi!2s!4v1635000000000!5m2!1svi!2s" allowfullscreen="" loading="lazy"></iframe>
        </div>
    </div>

    <footer style="background: linear-gradient(135deg, #001f3f, #003366); color: #F5F5DC; text-align: center; padding: 20px; margin-top: 50px;">
        <p>&copy; 2024 Nhà hàng 3CE. All rights reserved.</p>
    </footer>
</body>
</html>
