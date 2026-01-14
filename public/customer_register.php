<?php
session_start();
require_once __DIR__ . '/../config/db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $HoTen = trim($_POST['HoTen']);
    $SoDienThoai = trim($_POST['SoDienThoai']);
    $Email = trim($_POST['Email'] ?? '');
    $agreeTerms = isset($_POST['agree_terms']);
    
    if (empty($HoTen) || empty($SoDienThoai)) {
        $error = 'Vui lòng điền đầy đủ thông tin bắt buộc!';
    } elseif (!$agreeTerms) {
        $error = 'Bạn cần đồng ý với điều khoản sử dụng để đăng ký!';
    } else {
        try {
            // Kiểm tra số điện thoại đã tồn tại
            $stmt = $conn->prepare("SELECT MaKhachHang FROM khach_hang WHERE SoDienThoai = ?");
            $stmt->execute([$SoDienThoai]);
            if ($stmt->fetch()) {
                $error = 'Số điện thoại đã được đăng ký!';
            } else {
                // Thêm khách hàng mới (MaKhachHang tự động tăng)
                $stmt = $conn->prepare("INSERT INTO khach_hang (HoTen, SoDienThoai, Email, DiemTichLuy) VALUES (?, ?, ?, 0)");
                $stmt->execute([$HoTen, $SoDienThoai, $Email]);
                
                $success = 'Đăng ký thành công! Bạn có thể đăng nhập bằng số điện thoại.';
            }
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
    <title>Nhà hàng 3CE - Đăng ký</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #F5F5DC 0%, #EDE8D0 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .register-container {
            background: white;
            border-radius: 15px;
            padding: 40px;
            max-width: 500px;
            width: 100%;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        .register-container h2 {
            text-align: center;
            color: #001f3f;
            margin-bottom: 30px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #001f3f;
            font-weight: 500;
        }
        .form-group input, .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
        }
        .form-group input:focus, .form-group textarea:focus {
            outline: none;
            border-color: #001f3f;
        }
        .btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #001f3f 0%, #003366 100%);
            color: #F5F5DC;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        .btn:hover {
            background: linear-gradient(135deg, #003366 0%, #004080 100%);
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .success {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .links {
            text-align: center;
            margin-top: 20px;
        }
        .links a {
            color: #001f3f;
            text-decoration: none;
        }
        .links a:hover {
            text-decoration: underline;
        }
        body { padding-top: 80px; }
        
        /* Checkbox điều khoản */
        .terms-group {
            margin-bottom: 20px;
        }
        .terms-checkbox {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            cursor: pointer;
        }
        .terms-checkbox input[type="checkbox"] {
            width: 20px;
            height: 20px;
            margin-top: 2px;
            cursor: pointer;
            accent-color: #001f3f;
        }
        .terms-checkbox label {
            font-size: 14px;
            color: #333;
            line-height: 1.5;
            cursor: pointer;
        }
        .terms-checkbox a {
            color: #001f3f;
            font-weight: 600;
        }
        .terms-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.6);
            z-index: 2000;
            align-items: center;
            justify-content: center;
        }
        .terms-modal.active { display: flex; }
        .terms-content {
            background: white;
            border-radius: 15px;
            max-width: 600px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
        }
        .terms-header {
            padding: 20px;
            border-bottom: 2px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            background: white;
        }
        .terms-header h3 { color: #001f3f; }
        .terms-close {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #666;
        }
        .terms-body {
            padding: 25px;
            line-height: 1.8;
            color: #333;
        }
        .terms-body h4 {
            color: #001f3f;
            margin: 20px 0 10px;
        }
        .terms-body h4:first-child { margin-top: 0; }
        .terms-body ul {
            margin-left: 20px;
            margin-bottom: 15px;
        }
        .terms-body li { margin-bottom: 8px; }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="register-container">
        <h2><i class="fas fa-user-plus"></i> Đăng ký khách hàng thân thiết</h2>
        
        <?php if ($error): ?>
        <div class="error">
            <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
        <div class="success">
            <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
        </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label><i class="fas fa-user"></i> Tên khách hàng *</label>
                <input type="text" name="HoTen" required placeholder="Nhập họ tên đầy đủ">
            </div>
            
            <div class="form-group">
                <label><i class="fas fa-phone"></i> Số điện thoại *</label>
                <input type="text" name="SoDienThoai" required placeholder="Ví dụ: 0123456789">
            </div>
            
            <div class="form-group">
                <label><i class="fas fa-envelope"></i> Email (tùy chọn)</label>
                <input type="email" name="Email" placeholder="example@email.com">
            </div>
            
            <div class="terms-group">
                <div class="terms-checkbox">
                    <input type="checkbox" name="agree_terms" id="agree_terms" required>
                    <label for="agree_terms">
                        Tôi đã đọc và đồng ý với <a href="#" onclick="openTermsModal(); return false;">Điều khoản sử dụng</a> và <a href="#" onclick="openPrivacyModal(); return false;">Chính sách bảo mật</a> của Nhà hàng 3CE
                    </label>
                </div>
            </div>
            
            <button type="submit" class="btn">
                <i class="fas fa-user-plus"></i> Đăng ký
            </button>
        </form>
        
        <div class="links">
            Đã có tài khoản? <a href="customer_login.php">Đăng nhập ngay</a> |
            <a href="index.php">Về trang chủ</a>
        </div>
    </div>
    
    <!-- Modal Điều khoản sử dụng -->
    <div class="terms-modal" id="termsModal">
        <div class="terms-content">
            <div class="terms-header">
                <h3><i class="fas fa-file-contract"></i> Điều khoản sử dụng</h3>
                <button class="terms-close" onclick="closeTermsModal()">&times;</button>
            </div>
            <div class="terms-body">
                <h4>1. Giới thiệu</h4>
                <p>Chào mừng bạn đến với Nhà hàng 3CE. Khi đăng ký tài khoản và sử dụng dịch vụ của chúng tôi, bạn đồng ý tuân thủ các điều khoản sau đây.</p>
                
                <h4>2. Tài khoản người dùng</h4>
                <ul>
                    <li>Bạn phải cung cấp thông tin chính xác và đầy đủ khi đăng ký.</li>
                    <li>Bạn chịu trách nhiệm bảo mật thông tin tài khoản của mình.</li>
                    <li>Mỗi số điện thoại chỉ được đăng ký một tài khoản.</li>
                </ul>
                
                <h4>3. Đặt bàn và đặt món</h4>
                <ul>
                    <li>Thông tin đặt bàn cần được xác nhận bởi nhà hàng.</li>
                    <li>Vui lòng đến đúng giờ đã đặt hoặc thông báo trước nếu có thay đổi.</li>
                    <li>Nhà hàng có quyền hủy đặt bàn nếu khách đến trễ quá 30 phút mà không thông báo.</li>
                </ul>
                
                <h4>4. Thanh toán</h4>
                <ul>
                    <li>Giá món ăn có thể thay đổi mà không cần thông báo trước.</li>
                    <li>Điểm tích lũy chỉ có giá trị khi thanh toán tại nhà hàng.</li>
                    <li>Điểm tích lũy không được quy đổi thành tiền mặt.</li>
                </ul>
                
                <h4>5. Quyền và nghĩa vụ</h4>
                <ul>
                    <li>Nhà hàng có quyền từ chối phục vụ trong trường hợp cần thiết.</li>
                    <li>Khách hàng có trách nhiệm giữ gìn vệ sinh và trật tự tại nhà hàng.</li>
                </ul>
                
                <h4>6. Thay đổi điều khoản</h4>
                <p>Nhà hàng 3CE có quyền thay đổi điều khoản sử dụng bất cứ lúc nào. Các thay đổi sẽ có hiệu lực ngay khi được đăng tải.</p>
            </div>
        </div>
    </div>
    
    <!-- Modal Chính sách bảo mật -->
    <div class="terms-modal" id="privacyModal">
        <div class="terms-content">
            <div class="terms-header">
                <h3><i class="fas fa-shield-alt"></i> Chính sách bảo mật</h3>
                <button class="terms-close" onclick="closePrivacyModal()">&times;</button>
            </div>
            <div class="terms-body">
                <h4>1. Thu thập thông tin</h4>
                <p>Chúng tôi thu thập các thông tin sau khi bạn đăng ký:</p>
                <ul>
                    <li>Họ tên</li>
                    <li>Số điện thoại</li>
                    <li>Địa chỉ email (nếu cung cấp)</li>
                </ul>
                
                <h4>2. Mục đích sử dụng</h4>
                <p>Thông tin của bạn được sử dụng để:</p>
                <ul>
                    <li>Xác nhận đặt bàn và đơn hàng</li>
                    <li>Liên hệ khi cần thiết</li>
                    <li>Gửi thông báo về ưu đãi và khuyến mãi (nếu bạn đồng ý)</li>
                    <li>Cải thiện chất lượng dịch vụ</li>
                </ul>
                
                <h4>3. Bảo mật thông tin</h4>
                <ul>
                    <li>Chúng tôi cam kết bảo mật thông tin cá nhân của bạn.</li>
                    <li>Thông tin không được chia sẻ cho bên thứ ba mà không có sự đồng ý của bạn.</li>
                    <li>Dữ liệu được lưu trữ an toàn trên hệ thống bảo mật.</li>
                </ul>
                
                <h4>4. Quyền của bạn</h4>
                <ul>
                    <li>Bạn có quyền yêu cầu xem, chỉnh sửa hoặc xóa thông tin cá nhân.</li>
                    <li>Bạn có thể hủy đăng ký nhận thông báo bất cứ lúc nào.</li>
                </ul>
                
                <h4>5. Liên hệ</h4>
                <p>Nếu có thắc mắc về chính sách bảo mật, vui lòng liên hệ:</p>
                <ul>
                    <li>Email: support@nhahang3ce.com</li>
                    <li>Hotline: 0123 456 789</li>
                </ul>
            </div>
        </div>
    </div>
    
    <script>
        function openTermsModal() {
            document.getElementById('termsModal').classList.add('active');
        }
        function closeTermsModal() {
            document.getElementById('termsModal').classList.remove('active');
        }
        function openPrivacyModal() {
            document.getElementById('privacyModal').classList.add('active');
        }
        function closePrivacyModal() {
            document.getElementById('privacyModal').classList.remove('active');
        }
        
        // Đóng modal khi click bên ngoài
        document.getElementById('termsModal').addEventListener('click', function(e) {
            if (e.target === this) closeTermsModal();
        });
        document.getElementById('privacyModal').addEventListener('click', function(e) {
            if (e.target === this) closePrivacyModal();
        });
    </script>
</body>
</html>
