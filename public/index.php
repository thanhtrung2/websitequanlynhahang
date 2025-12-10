<?php
session_start();
require_once __DIR__ . '/../config/db.php';

// Kiểm tra đăng nhập
$isLoggedIn = false;
$userName = '';
$userType = '';

if (isset($_SESSION['user_id'])) {
    $isLoggedIn = true;
    $userType = 'admin';
    $userName = $_SESSION['user_name'] ?? 'Admin';
} elseif (isset($_SESSION['customer_id'])) {
    $isLoggedIn = true;
    $userType = 'customer';
    $userName = $_SESSION['customer_name'] ?? 'Khách hàng';
}

// Xử lý tìm kiếm
$search = $_GET['search'] ?? '';
$monAns = [];

try {
    if (!empty($search)) {
        // Tìm kiếm theo tên món hoặc mã món
        $stmt = $conn->prepare("SELECT * FROM mon_an WHERE TenMonAn LIKE ? OR MaMonAn LIKE ? ORDER BY MaMonAn");
        $searchTerm = "%$search%";
        $stmt->execute([$searchTerm, $searchTerm]);
        $monAns = $stmt->fetchAll();
    } else {
        // Hiển thị tất cả món ăn
        $stmt = $conn->query("SELECT * FROM mon_an ORDER BY MaMonAn");
        $monAns = $stmt->fetchAll();
    }
} catch(PDOException $e) {
    $monAns = [];
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nhà hàng 3CE - Trang chủ</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #F5F5DC 0%, #EDE8D0 50%, #E8E4C9 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        /* Header */
        .header {
            background: linear-gradient(135deg, #001f3f 0%, #003366 100%);
            padding: 15px 0;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            border-bottom: 3px solid #D4AF37;
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
        
        .nav-menu {
            display: flex;
            gap: 30px;
            list-style: none;
        }
        
        .nav-menu a {
            text-decoration: none;
            color: #F5F5DC;
            font-weight: 500;
            transition: color 0.3s;
        }
        
        .nav-menu a:hover {
            color: #D4AF37;
        }
        
        /* Main Content */
        .container {
            flex: 1;
            max-width: 1200px;
            margin: 50px auto;
            padding: 0 20px;
        }
        
        .hero-section {
            text-align: center;
            color: #001f3f;
            margin-bottom: 50px;
        }
        
        .hero-section h1 {
            font-size: 56px;
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
            color: #001f3f;
        }
        
        .hero-section p {
            font-size: 22px;
            margin-bottom: 40px;
            color: #003366;
        }
        
        .cta-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
            margin-bottom: 50px;
        }
        
        .btn-primary, .btn-secondary {
            padding: 15px 40px;
            border-radius: 8px;
            font-size: 18px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #001f3f 0%, #003366 100%);
            color: #F5F5DC;
            box-shadow: 0 5px 20px rgba(0, 31, 63, 0.3);
            border: none;
        }
        
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 30px rgba(0, 31, 63, 0.4);
            background: linear-gradient(135deg, #003366 0%, #004080 100%);
        }
        
        .btn-secondary {
            background: #F5F5DC;
            color: #001f3f;
            border: 2px solid #001f3f;
        }
        
        .btn-secondary:hover {
            background: #001f3f;
            color: #F5F5DC;
        }
        

        /* Menu Section */
        .section-title {
            text-align: center;
            color: #001f3f;
            margin: 60px 0 30px;
        }
        
        .section-title h2 {
            font-size: 42px;
            margin-bottom: 10px;
            color: #001f3f;
        }
        
        .section-title p {
            font-size: 18px;
            color: #003366;
        }
        
        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-bottom: 50px;
        }
        
        .menu-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        
        .menu-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        .menu-card-image {
            width: 100%;
            height: 250px;
            object-fit: cover;
        }
        
        .menu-card-image:not(img) {
            background: linear-gradient(135deg, #001f3f 0%, #003366 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #F5F5DC;
            font-size: 60px;
        }
        
        .menu-card-content {
            padding: 20px;
        }
        
        .menu-card-content h3 {
            color: #001f3f;
            margin-bottom: 10px;
            font-size: 22px;
        }
        
        .menu-card-price {
            font-size: 24px;
            font-weight: bold;
            color: #003366;
            margin: 10px 0;
        }
        
        .menu-card-code {
            color: #666;
            font-size: 14px;
        }
        
        /* Search Box */
        .search-container {
            max-width: 600px;
            margin: 30px auto;
        }
        
        .search-box {
            display: flex;
            gap: 10px;
            background: white;
            padding: 10px;
            border-radius: 50px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .search-box input {
            flex: 1;
            border: none;
            padding: 12px 20px;
            font-size: 16px;
            outline: none;
        }
        
        .search-box button {
            padding: 12px 30px;
            background: linear-gradient(135deg, #001f3f 0%, #003366 100%);
            color: #F5F5DC;
            border: none;
            border-radius: 50px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .search-box button:hover {
            background: linear-gradient(135deg, #003366 0%, #004080 100%);
        }
        
        .search-result {
            text-align: center;
            color: #001f3f;
            margin: 20px 0;
            font-size: 18px;
        }
        
        /* Footer */
        .footer {
            background: linear-gradient(135deg, #001f3f 0%, #003366 100%);
            color: #F5F5DC;
            text-align: center;
            padding: 20px;
            margin-top: 50px;
            border-top: 3px solid #D4AF37;
        }
        
        /* Menu Card Clickable */
        .menu-card {
            cursor: pointer;
        }
        
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.6);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        .modal.active {
            display: flex;
        }
        .modal-content {
            background: white;
            border-radius: 20px;
            max-width: 600px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            animation: modalSlide 0.3s ease;
        }
        @keyframes modalSlide {
            from { transform: translateY(-50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        .modal-header {
            position: relative;
        }
        .modal-image {
            width: 100%;
            height: 300px;
            object-fit: cover;
            border-radius: 20px 20px 0 0;
        }
        .modal-image-placeholder {
            width: 100%;
            height: 300px;
            background: linear-gradient(135deg, #001f3f 0%, #003366 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #F5F5DC;
            font-size: 80px;
            border-radius: 20px 20px 0 0;
        }
        .close-btn {
            position: absolute;
            top: 15px;
            right: 15px;
            width: 40px;
            height: 40px;
            background: white;
            border: none;
            border-radius: 50%;
            font-size: 20px;
            cursor: pointer;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #001f3f;
        }
        .close-btn:hover {
            background: #f0f0f0;
        }
        .modal-body {
            padding: 30px;
        }
        .modal-title {
            font-size: 28px;
            color: #001f3f;
            margin-bottom: 10px;
        }
        .modal-price {
            font-size: 32px;
            font-weight: bold;
            color: #003366;
            margin-bottom: 15px;
        }
        .modal-info {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .modal-info-item {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #666;
            font-size: 14px;
        }
        .modal-info-item i {
            color: #001f3f;
        }
        .modal-description {
            color: #555;
            line-height: 1.8;
            margin-bottom: 25px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 10px;
        }
        .modal-status {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 20px;
        }
        .modal-status.available {
            background: #d4edda;
            color: #155724;
        }
        .modal-status.unavailable {
            background: #f8d7da;
            color: #721c24;
        }
        .order-section {
            border-top: 2px solid #f0f0f0;
            padding-top: 20px;
        }
        .quantity-selector {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
        }
        .quantity-selector label {
            font-weight: 600;
            color: #001f3f;
        }
        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .qty-btn {
            width: 40px;
            height: 40px;
            border: 2px solid #001f3f;
            background: white;
            border-radius: 8px;
            font-size: 20px;
            cursor: pointer;
            color: #001f3f;
            transition: all 0.3s;
        }
        .qty-btn:hover {
            background: #001f3f;
            color: white;
        }
        .qty-input {
            width: 60px;
            height: 40px;
            text-align: center;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 18px;
            font-weight: 600;
        }
        .order-total {
            font-size: 20px;
            color: #001f3f;
            margin-bottom: 20px;
        }
        .order-total span {
            font-weight: bold;
            color: #003366;
        }
        .btn-order {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #001f3f 0%, #003366 100%);
            color: #F5F5DC;
            border: none;
            border-radius: 10px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        .btn-order:hover {
            background: linear-gradient(135deg, #003366 0%, #004080 100%);
            transform: translateY(-2px);
        }
        .btn-order:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
        }
        .login-prompt {
            text-align: center;
            padding: 20px;
            background: #fff3cd;
            border-radius: 10px;
            color: #856404;
        }
        .login-prompt a {
            color: #001f3f;
            font-weight: 600;
        }
        .alert-modal {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
            display: none;
        }
        .alert-modal.success {
            background: #d4edda;
            color: #155724;
            display: block;
        }
        .alert-modal.error {
            background: #f8d7da;
            color: #721c24;
            display: block;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="header-content">
            <div class="logo">
                <i class="fas fa-utensils"></i>
                <span>Nhà hàng 3CE</span>
            </div>
            <ul class="nav-menu">
                <li><a href="index.php"><i class="fas fa-home"></i> Trang chủ</a></li>
                <li><a href="#menu"><i class="fas fa-utensils"></i> Thực đơn</a></li>
                <?php if ($isLoggedIn): ?>
                    <li><a href="<?php echo $userType === 'admin' ? '../admin/dashboard.php' : 'customer_dashboard.php'; ?>">
                        <i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($userName); ?>
                    </a></li>
                    <li><a href="../auth/logout.php?type=<?php echo $userType; ?>">
                        <i class="fas fa-sign-out-alt"></i> Đăng xuất
                    </a></li>
                <?php else: ?>
                    <li><a href="../admin/admin_login.php"><i class="fas fa-user-tie"></i> Quản lý</a></li>
                    <li><a href="customer_login.php"><i class="fas fa-user"></i> Khách hàng</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container">
        <div class="hero-section">
            <h1>Chào mừng đến với nhà hàng của chúng tôi</h1>
            <p>Trải nghiệm ẩm thực tuyệt vời với hệ thống đặt bàn và đặt món hiện đại</p>
            
            <div class="cta-buttons">
                <?php if ($isLoggedIn): ?>
                    <a href="<?php echo $userType === 'admin' ? '../admin/dashboard.php' : 'customer_dashboard.php'; ?>" class="btn-primary">
                        <i class="fas fa-tachometer-alt"></i> Vào Dashboard
                    </a>
                    <a href="../auth/logout.php?type=<?php echo $userType; ?>" class="btn-secondary">
                        <i class="fas fa-sign-out-alt"></i> Đăng xuất
                    </a>
                <?php else: ?>
                    <a href="customer_login.php" class="btn-primary">
                        <i class="fas fa-utensils"></i> Đặt bàn ngay
                    </a>
                    <a href="../admin/admin_login.php" class="btn-secondary">
                        <i class="fas fa-user-tie"></i> Đăng nhập quản lý
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Menu Section -->
        <div class="section-title" id="menu">
            <h2><i class="fas fa-utensils"></i> Thực đơn đặc biệt</h2>
            <p>Những món ăn ngon nhất của chúng tôi</p>
        </div>

        <!-- Search Box -->
        <div class="search-container">
            <form method="GET" action="index.php#menu" class="search-box">
                <input type="text" name="search" placeholder="Tìm kiếm món ăn..." 
                       value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit">
                    <i class="fas fa-search"></i> Tìm kiếm
                </button>
            </form>
        </div>

        <?php if (!empty($search)): ?>
        <div class="search-result">
            <i class="fas fa-info-circle"></i> 
            Tìm thấy <?php echo count($monAns); ?> món ăn cho "<?php echo htmlspecialchars($search); ?>"
            <a href="index.php#menu" style="color: #001f3f; text-decoration: underline; margin-left: 10px;">
                Xem tất cả
            </a>
        </div>
        <?php endif; ?>

        <div class="menu-grid">
            <?php if (count($monAns) > 0): ?>
                <?php foreach ($monAns as $mon): ?>
                <div class="menu-card" onclick='openFoodModal(<?php echo json_encode([
                    "MaMonAn" => $mon["MaMonAn"],
                    "TenMonAn" => $mon["TenMonAn"],
                    "DonGia" => $mon["DonGia"],
                    "HinhAnh" => $mon["HinhAnh"] ?? "",
                    "MoTa" => $mon["MoTa"] ?? "Món ăn ngon từ Nhà hàng 3CE",
                    "DonViTinh" => $mon["DonViTinh"] ?? "Phần",
                    "TrangThai" => $mon["TrangThai"] ?? "Còn hàng"
                ]); ?>)'>
                    <?php if (!empty($mon['HinhAnh'])): ?>
                        <img src="<?php echo htmlspecialchars($mon['HinhAnh']); ?>" 
                             alt="<?php echo htmlspecialchars($mon['TenMonAn']); ?>" 
                             class="menu-card-image"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="menu-card-image" style="display: none;">
                            <i class="fas fa-utensils"></i>
                        </div>
                    <?php else: ?>
                        <div class="menu-card-image">
                            <i class="fas fa-utensils"></i>
                        </div>
                    <?php endif; ?>
                    <div class="menu-card-content">
                        <h3><?php echo htmlspecialchars($mon['TenMonAn']); ?></h3>
                        <div class="menu-card-price">
                            <?php echo number_format($mon['DonGia'], 0, ',', '.'); ?>đ
                        </div>
                        <div class="menu-card-code">
                            <i class="fas fa-tag"></i> Mã: <?php echo htmlspecialchars($mon['MaMonAn']); ?>
                            <?php if (($mon['TrangThai'] ?? 'Còn hàng') === 'Hết hàng'): ?>
                                <span style="color: #dc3545; margin-left: 10px;"><i class="fas fa-times-circle"></i> Hết hàng</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="grid-column: 1/-1; text-align: center; color: #001f3f; padding: 40px;">
                    <i class="fas fa-info-circle" style="font-size: 48px; margin-bottom: 20px;"></i>
                    <p style="font-size: 18px;">Chưa có món ăn nào trong thực đơn</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal Chi tiết món ăn -->
    <div id="foodModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <div id="modalImageContainer"></div>
                <button class="close-btn" onclick="closeFoodModal()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <div id="modalAlert" class="alert-modal"></div>
                <h2 id="modalTitle" class="modal-title"></h2>
                <div id="modalPrice" class="modal-price"></div>
                <div id="modalStatus" class="modal-status"></div>
                <div class="modal-info">
                    <div class="modal-info-item">
                        <i class="fas fa-tag"></i>
                        <span>Mã: <strong id="modalCode"></strong></span>
                    </div>
                    <div class="modal-info-item">
                        <i class="fas fa-box"></i>
                        <span>Đơn vị: <strong id="modalUnit"></strong></span>
                    </div>
                </div>
                <div id="modalDescription" class="modal-description"></div>
                
                <div class="order-section">
                    <?php if ($isLoggedIn && $userType === 'customer'): ?>
                        <div id="orderForm">
                            <div class="quantity-selector">
                                <label>Số lượng:</label>
                                <div class="quantity-controls">
                                    <button type="button" class="qty-btn" onclick="changeQty(-1)">-</button>
                                    <input type="number" id="quantity" class="qty-input" value="1" min="1" max="99" onchange="updateTotal()">
                                    <button type="button" class="qty-btn" onclick="changeQty(1)">+</button>
                                </div>
                            </div>
                            <div class="order-total">
                                Tổng tiền: <span id="totalPrice">0đ</span>
                            </div>
                            <button type="button" id="btnOrder" class="btn-order" onclick="addToOrder()">
                                <i class="fas fa-cart-plus"></i> Thêm vào đơn hàng
                            </button>
                        </div>
                    <?php elseif ($isLoggedIn && $userType === 'admin'): ?>
                        <div class="login-prompt">
                            <i class="fas fa-info-circle"></i> Bạn đang đăng nhập với tư cách quản lý. 
                            <br>Vui lòng <a href="../auth/logout.php?type=admin">đăng xuất</a> và đăng nhập với tài khoản khách hàng để đặt món.
                        </div>
                    <?php else: ?>
                        <div class="login-prompt">
                            <i class="fas fa-lock"></i> Vui lòng <a href="customer_login.php">đăng nhập</a> để đặt món.
                            <br>Chưa có tài khoản? <a href="customer_register.php">Đăng ký ngay</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>&copy; 2024 Nhà hàng 3CE. Tất cả quyền được bảo lưu.</p>
    </div>

    <script>
        let currentFood = null;
        
        function openFoodModal(food) {
            currentFood = food;
            
            // Set image
            const imageContainer = document.getElementById('modalImageContainer');
            if (food.HinhAnh) {
                imageContainer.innerHTML = `<img src="${food.HinhAnh}" class="modal-image" onerror="this.outerHTML='<div class=\\'modal-image-placeholder\\'><i class=\\'fas fa-utensils\\'></i></div>'">`;
            } else {
                imageContainer.innerHTML = `<div class="modal-image-placeholder"><i class="fas fa-utensils"></i></div>`;
            }
            
            // Set info
            document.getElementById('modalTitle').textContent = food.TenMonAn;
            document.getElementById('modalPrice').textContent = formatPrice(food.DonGia) + 'đ';
            document.getElementById('modalCode').textContent = food.MaMonAn;
            document.getElementById('modalUnit').textContent = food.DonViTinh;
            document.getElementById('modalDescription').textContent = food.MoTa;
            
            // Set status
            const statusEl = document.getElementById('modalStatus');
            if (food.TrangThai === 'Còn hàng') {
                statusEl.className = 'modal-status available';
                statusEl.innerHTML = '<i class="fas fa-check-circle"></i> Còn hàng';
            } else {
                statusEl.className = 'modal-status unavailable';
                statusEl.innerHTML = '<i class="fas fa-times-circle"></i> Hết hàng';
            }
            
            // Reset quantity and update total
            const qtyInput = document.getElementById('quantity');
            if (qtyInput) {
                qtyInput.value = 1;
                updateTotal();
                
                // Disable order button if out of stock
                const btnOrder = document.getElementById('btnOrder');
                if (btnOrder) {
                    btnOrder.disabled = food.TrangThai !== 'Còn hàng';
                }
            }
            
            // Clear alert
            document.getElementById('modalAlert').className = 'alert-modal';
            document.getElementById('modalAlert').textContent = '';
            
            // Show modal
            document.getElementById('foodModal').classList.add('active');
            document.body.style.overflow = 'hidden';
        }
        
        function closeFoodModal() {
            document.getElementById('foodModal').classList.remove('active');
            document.body.style.overflow = 'auto';
            currentFood = null;
        }
        
        function changeQty(delta) {
            const input = document.getElementById('quantity');
            let value = parseInt(input.value) + delta;
            if (value < 1) value = 1;
            if (value > 99) value = 99;
            input.value = value;
            updateTotal();
        }
        
        function updateTotal() {
            if (!currentFood) return;
            const qty = parseInt(document.getElementById('quantity').value) || 1;
            const total = qty * parseFloat(currentFood.DonGia);
            document.getElementById('totalPrice').textContent = formatPrice(total) + 'đ';
        }
        
        function formatPrice(price) {
            return new Intl.NumberFormat('vi-VN').format(price);
        }
        
        function addToOrder() {
            if (!currentFood) return;
            
            const qty = parseInt(document.getElementById('quantity').value) || 1;
            const alertEl = document.getElementById('modalAlert');
            
            // Send AJAX request
            fetch('add_to_order.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    MaMonAn: currentFood.MaMonAn,
                    SoLuong: qty
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alertEl.className = 'alert-modal success';
                    alertEl.innerHTML = '<i class="fas fa-check-circle"></i> ' + data.message;
                    setTimeout(() => {
                        closeFoodModal();
                    }, 1500);
                } else {
                    alertEl.className = 'alert-modal error';
                    alertEl.innerHTML = '<i class="fas fa-exclamation-circle"></i> ' + data.message;
                }
            })
            .catch(error => {
                alertEl.className = 'alert-modal error';
                alertEl.innerHTML = '<i class="fas fa-exclamation-circle"></i> Có lỗi xảy ra, vui lòng thử lại!';
            });
        }
        
        // Close modal when clicking outside
        document.getElementById('foodModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeFoodModal();
            }
        });
        
        // Close modal with Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeFoodModal();
            }
        });
    </script>
</body>
</html>