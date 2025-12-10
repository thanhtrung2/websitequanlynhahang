<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if (isset($_SESSION['customer_id'])) {
    header("Location: customer_dashboard.php");
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = $_POST['phone'] ?? '';
    
    if (!empty($phone)) {
        try {
            $stmt = $conn->prepare("SELECT * FROM khach_hang WHERE SoDienThoai = ?");
            $stmt->execute([$phone]);
            $customer = $stmt->fetch();
            
            if ($customer) {
                $_SESSION['customer_id'] = $customer['MaKhachHang'];
                $_SESSION['customer_name'] = $customer['HoTen'];
                $_SESSION['message'] = 'Đăng nhập thành công!';
                header("Location: customer_dashboard.php");
                exit();
            } else {
                $error = 'Số điện thoại không tồn tại!';
            }
        } catch(PDOException $e) {
            $error = 'Lỗi: ' . $e->getMessage();
        }
    } else {
        $error = 'Vui lòng nhập số điện thoại!';
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nhà hàng 3CE - Đăng nhập</title>
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
        .login-container {
            background: white;
            border-radius: 15px;
            padding: 40px;
            max-width: 450px;
            width: 100%;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        .login-container h2 {
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
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
        }
        .form-group input:focus {
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
        .links {
            text-align: center;
            margin-top: 20px;
        }
        .links a {
            color: #001f3f;
            text-decoration: none;
            margin: 0 10px;
        }
        .links a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2><i class="fas fa-user"></i> Đăng nhập khách hàng</h2>
        
        <?php if ($error): ?>
        <div class="error">
            <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label><i class="fas fa-phone"></i> Số điện thoại</label>
                <input type="text" name="phone" required placeholder="Nhập số điện thoại">
            </div>
            
            <button type="submit" class="btn">
                <i class="fas fa-sign-in-alt"></i> Đăng nhập
            </button>
        </form>
        
        <div class="links">
            <a href="customer_register.php">Đăng ký khách hàng thân thiết</a> |
            <a href="index.php">Về trang chủ</a>
        </div>
    </div>
</body>
</html>
