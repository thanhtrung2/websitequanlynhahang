<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';

// Xử lý tìm kiếm và lọc danh mục
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';
$monAns = [];
$danhMucs = [];

try {
    $stmt = $conn->query("SELECT * FROM danh_muc_mon_an ORDER BY MaDanhMuc");
    $danhMucs = $stmt->fetchAll();
    
    if (!empty($search)) {
        $stmt = $conn->prepare("SELECT ma.*, dm.TenDanhMuc FROM mon_an ma LEFT JOIN danh_muc_mon_an dm ON ma.MaDanhMuc = dm.MaDanhMuc WHERE ma.TenMonAn LIKE ? ORDER BY ma.MaDanhMuc, ma.MaMonAn");
        $stmt->execute(["%$search%"]);
        $monAns = $stmt->fetchAll();
    } elseif (!empty($category)) {
        $stmt = $conn->prepare("SELECT ma.*, dm.TenDanhMuc FROM mon_an ma LEFT JOIN danh_muc_mon_an dm ON ma.MaDanhMuc = dm.MaDanhMuc WHERE ma.MaDanhMuc = ? ORDER BY ma.MaMonAn");
        $stmt->execute([$category]);
        $monAns = $stmt->fetchAll();
    } else {
        $stmt = $conn->query("SELECT ma.*, dm.TenDanhMuc FROM mon_an ma LEFT JOIN danh_muc_mon_an dm ON ma.MaDanhMuc = dm.MaDanhMuc ORDER BY ma.MaDanhMuc, ma.MaMonAn");
        $monAns = $stmt->fetchAll();
    }
} catch(PDOException $e) {
    $monAns = [];
}

// Nhóm món ăn theo danh mục
$monAnTheoDanhMuc = [];
foreach ($monAns as $mon) {
    $maDM = $mon['MaDanhMuc'] ?? 0;
    if (!isset($monAnTheoDanhMuc[$maDM])) {
        $monAnTheoDanhMuc[$maDM] = ['TenDanhMuc' => $mon['TenDanhMuc'] ?? 'Khác', 'items' => []];
    }
    $monAnTheoDanhMuc[$maDM]['items'][] = $mon;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thực đơn - Nhà hàng 3CE</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: linear-gradient(135deg, #F5F5DC 0%, #EDE8D0 100%); min-height: 100vh; padding-top: 70px; }
        .container { max-width: 1200px; margin: 30px auto; padding: 0 20px; }
        .page-title { text-align: center; color: #001f3f; margin-bottom: 30px; }
        .page-title h1 { font-size: 42px; margin-bottom: 10px; }
        .page-title p { color: #666; font-size: 18px; }
        .search-box { display: flex; gap: 10px; max-width: 500px; margin: 0 auto 30px; background: white; padding: 8px; border-radius: 50px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); }
        .search-box input { flex: 1; border: none; padding: 12px 20px; font-size: 16px; outline: none; border-radius: 50px; }
        .search-box button { padding: 12px 25px; background: linear-gradient(135deg, #001f3f, #003366); color: #F5F5DC; border: none; border-radius: 50px; cursor: pointer; font-weight: 600; }
        .category-filter { display: flex; flex-wrap: wrap; gap: 10px; justify-content: center; margin-bottom: 30px; }
        .category-btn { padding: 10px 20px; background: white; color: #001f3f; border: 2px solid #001f3f; border-radius: 25px; text-decoration: none; font-weight: 600; transition: all 0.3s; }
        .category-btn:hover, .category-btn.active { background: linear-gradient(135deg, #001f3f, #003366); color: #F5F5DC; }
        .category-section { margin-bottom: 50px; }
        .category-title { font-size: 28px; color: #001f3f; margin-bottom: 25px; padding-bottom: 15px; border-bottom: 3px solid #D4AF37; display: flex; align-items: center; gap: 15px; }
        .category-title i { color: #D4AF37; }
        .menu-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 25px; }
        .menu-card { background: white; border-radius: 15px; overflow: hidden; box-shadow: 0 5px 20px rgba(0,0,0,0.1); transition: transform 0.3s, box-shadow 0.3s; cursor: pointer; }
        .menu-card:hover { transform: translateY(-10px); box-shadow: 0 15px 40px rgba(0,0,0,0.2); }
        .menu-card-image { width: 100%; height: 180px; object-fit: cover; background: linear-gradient(135deg, #001f3f, #003366); display: flex; align-items: center; justify-content: center; color: #F5F5DC; font-size: 50px; }
        .menu-card-content { padding: 20px; }
        .menu-card-content h3 { color: #001f3f; margin-bottom: 8px; font-size: 18px; }
        .menu-card-desc { color: #666; font-size: 14px; margin-bottom: 15px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .menu-card-footer { display: flex; justify-content: space-between; align-items: center; }
        .menu-card-price { font-size: 20px; font-weight: bold; color: #003366; }
        .btn-add { width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, #001f3f, #003366); color: #F5F5DC; border: none; cursor: pointer; font-size: 18px; transition: all 0.3s; }
        .btn-add:hover { background: linear-gradient(135deg, #D4AF37, #f7d774); color: #001f3f; transform: rotate(90deg); }
        .out-of-stock { opacity: 0.6; }
        .out-of-stock .menu-card-image::after { content: 'HẾT HÀNG'; position: absolute; background: #dc3545; color: white; padding: 5px 15px; border-radius: 5px; }
        .footer { background: linear-gradient(135deg, #001f3f, #003366); color: #F5F5DC; text-align: center; padding: 20px; margin-top: 50px; }
        /* Modal */
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); z-index: 1000; align-items: center; justify-content: center; }
        .modal.active { display: flex; }
        .modal-content { background: white; border-radius: 20px; max-width: 500px; width: 90%; max-height: 90vh; overflow-y: auto; }
        .modal-header { position: relative; }
        .modal-image { width: 100%; height: 250px; object-fit: cover; border-radius: 20px 20px 0 0; }
        .modal-image-placeholder { width: 100%; height: 250px; background: linear-gradient(135deg, #001f3f, #003366); display: flex; align-items: center; justify-content: center; color: #F5F5DC; font-size: 60px; border-radius: 20px 20px 0 0; }
        .close-btn { position: absolute; top: 15px; right: 15px; width: 35px; height: 35px; background: white; border: none; border-radius: 50%; font-size: 18px; cursor: pointer; }
        .modal-body { padding: 25px; }
        .modal-title { font-size: 24px; color: #001f3f; margin-bottom: 10px; }
        .modal-price { font-size: 28px; font-weight: bold; color: #003366; margin-bottom: 15px; }
        .modal-desc { color: #666; line-height: 1.6; margin-bottom: 20px; }
        .modal-status { display: inline-block; padding: 6px 15px; border-radius: 20px; font-size: 14px; font-weight: 600; }
        .modal-status.available { background: #d4edda; color: #155724; }
        .modal-status.unavailable { background: #f8d7da; color: #721c24; }
        .order-section { border-top: 2px solid #eee; padding-top: 20px; margin-top: 20px; }
        .quantity-selector { display: flex; align-items: center; gap: 15px; margin-bottom: 15px; }
        .qty-btn { width: 35px; height: 35px; border: 2px solid #001f3f; background: white; border-radius: 8px; font-size: 18px; cursor: pointer; }
        .qty-btn:hover { background: #001f3f; color: white; }
        .qty-input { width: 60px; height: 35px; text-align: center; border: 2px solid #ddd; border-radius: 8px; font-size: 16px; }
        .btn-order { width: 100%; padding: 15px; background: linear-gradient(135deg, #001f3f, #003366); color: #F5F5DC; border: none; border-radius: 10px; font-size: 16px; font-weight: 600; cursor: pointer; }
        .btn-order:hover { background: linear-gradient(135deg, #003366, #004080); }
        .btn-order:disabled { background: #ccc; cursor: not-allowed; }
        .login-prompt { text-align: center; padding: 20px; background: #fff3cd; border-radius: 10px; color: #856404; }
        .login-prompt a { color: #001f3f; font-weight: 600; }
        .alert-modal { padding: 12px; border-radius: 8px; margin-bottom: 15px; display: none; }
        .alert-modal.success { background: #d4edda; color: #155724; display: block; }
        .alert-modal.error { background: #f8d7da; color: #721c24; display: block; }
        /* Floating Cart */
        .floating-cart { position: fixed; bottom: 30px; right: 30px; z-index: 999; }
        .cart-btn { width: 60px; height: 60px; border-radius: 50%; background: linear-gradient(135deg, #001f3f, #003366); color: #F5F5DC; border: none; cursor: pointer; font-size: 24px; box-shadow: 0 5px 25px rgba(0,0,0,0.3); position: relative; }
        .cart-btn:hover { transform: scale(1.1); background: linear-gradient(135deg, #D4AF37, #f7d774); color: #001f3f; }
        .cart-badge { position: absolute; top: -5px; right: -5px; background: #dc3545; color: white; width: 24px; height: 24px; border-radius: 50%; font-size: 12px; font-weight: bold; display: flex; align-items: center; justify-content: center; }
        .cart-badge.hidden { display: none; }
        .toast { position: fixed; bottom: 100px; right: 30px; padding: 15px 25px; border-radius: 10px; color: white; font-weight: 600; z-index: 9999; transform: translateX(150%); transition: transform 0.3s; }
        .toast.show { transform: translateX(0); }
        .toast-success { background: linear-gradient(135deg, #28a745, #20c997); }
        .toast-error { background: linear-gradient(135deg, #dc3545, #e74c3c); }
        @media (max-width: 768px) { .menu-grid { grid-template-columns: 1fr; } .nav-menu { gap: 15px; font-size: 14px; } }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container">
        <div class="page-title">
            <h1><i class="fas fa-book-open"></i> Thực Đơn</h1>
            <p>Khám phá các món ăn ngon tại nhà hàng chúng tôi</p>
        </div>

        <form method="GET" class="search-box">
            <input type="text" name="search" placeholder="Tìm kiếm món ăn..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit"><i class="fas fa-search"></i> Tìm</button>
        </form>

        <div class="category-filter">
            <a href="thuc_don.php" class="category-btn <?php echo empty($category) && empty($search) ? 'active' : ''; ?>"><i class="fas fa-th-large"></i> Tất cả</a>
            <?php foreach ($danhMucs as $dm): 
                $icon = $dm['MaDanhMuc'] == 1 ? 'seedling' : ($dm['MaDanhMuc'] == 2 ? 'drumstick-bite' : ($dm['MaDanhMuc'] == 3 ? 'ice-cream' : 'glass-water'));
            ?>
            <a href="?category=<?php echo $dm['MaDanhMuc']; ?>" class="category-btn <?php echo $category == $dm['MaDanhMuc'] ? 'active' : ''; ?>">
                <i class="fas fa-<?php echo $icon; ?>"></i> <?php echo htmlspecialchars($dm['TenDanhMuc']); ?>
            </a>
            <?php endforeach; ?>
        </div>

        <?php if (!empty($search)): ?>
        <p style="text-align: center; margin-bottom: 20px;">Tìm thấy <strong><?php echo count($monAns); ?></strong> món cho "<?php echo htmlspecialchars($search); ?>" - <a href="thuc_don.php">Xem tất cả</a></p>
        <?php endif; ?>

        <?php if (empty($search) && empty($category)): ?>
            <?php foreach ($monAnTheoDanhMuc as $maDM => $dmData): 
                $icon = $maDM == 1 ? 'seedling' : ($maDM == 2 ? 'drumstick-bite' : ($maDM == 3 ? 'ice-cream' : 'glass-water'));
            ?>
            <div class="category-section">
                <h2 class="category-title"><i class="fas fa-<?php echo $icon; ?>"></i> <?php echo htmlspecialchars($dmData['TenDanhMuc']); ?> <span style="font-size:16px;color:#666;font-weight:normal;">(<?php echo count($dmData['items']); ?> món)</span></h2>
                <div class="menu-grid">
                <?php foreach ($dmData['items'] as $mon): $isOutOfStock = ($mon['TrangThai'] ?? 'Còn hàng') === 'Hết hàng'; ?>
                <div class="menu-card <?php echo $isOutOfStock ? 'out-of-stock' : ''; ?>" onclick='openModal(<?php echo json_encode(["MaMonAn"=>$mon["MaMonAn"],"TenMonAn"=>$mon["TenMonAn"],"DonGia"=>$mon["DonGia"],"HinhAnh"=>getImagePath($mon["HinhAnh"]??""),"MoTa"=>$mon["MoTa"]??"","DonViTinh"=>$mon["DonViTinh"]??"Phần","TrangThai"=>$mon["TrangThai"]??"Còn hàng"]); ?>)'>
                    <?php if (!empty($mon['HinhAnh'])): ?>
                    <img src="<?php echo htmlspecialchars(getImagePath($mon['HinhAnh'])); ?>" class="menu-card-image" onerror="this.outerHTML='<div class=menu-card-image><i class=fas fa-utensils></i></div>'">
                    <?php else: ?>
                    <div class="menu-card-image"><i class="fas fa-utensils"></i></div>
                    <?php endif; ?>
                    <div class="menu-card-content">
                        <h3><?php echo htmlspecialchars($mon['TenMonAn']); ?></h3>
                        <p class="menu-card-desc"><?php echo htmlspecialchars($mon['MoTa'] ?? ''); ?></p>
                        <div class="menu-card-footer">
                            <span class="menu-card-price"><?php echo number_format($mon['DonGia'], 0, ',', '.'); ?>đ</span>
                            <button class="btn-add" onclick="event.stopPropagation(); <?php echo $isOutOfStock ? '' : 'quickAdd('.$mon['MaMonAn'].')'; ?>" <?php echo $isOutOfStock ? 'disabled' : ''; ?>><i class="fas fa-plus"></i></button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="menu-grid">
            <?php if (count($monAns) > 0): foreach ($monAns as $mon): $isOutOfStock = ($mon['TrangThai'] ?? 'Còn hàng') === 'Hết hàng'; ?>
            <div class="menu-card <?php echo $isOutOfStock ? 'out-of-stock' : ''; ?>" onclick='openModal(<?php echo json_encode(["MaMonAn"=>$mon["MaMonAn"],"TenMonAn"=>$mon["TenMonAn"],"DonGia"=>$mon["DonGia"],"HinhAnh"=>getImagePath($mon["HinhAnh"]??""),"MoTa"=>$mon["MoTa"]??"","DonViTinh"=>$mon["DonViTinh"]??"Phần","TrangThai"=>$mon["TrangThai"]??"Còn hàng"]); ?>)'>
                <?php if (!empty($mon['HinhAnh'])): ?>
                <img src="<?php echo htmlspecialchars(getImagePath($mon['HinhAnh'])); ?>" class="menu-card-image" onerror="this.outerHTML='<div class=menu-card-image><i class=fas fa-utensils></i></div>'">
                <?php else: ?>
                <div class="menu-card-image"><i class="fas fa-utensils"></i></div>
                <?php endif; ?>
                <div class="menu-card-content">
                    <h3><?php echo htmlspecialchars($mon['TenMonAn']); ?></h3>
                    <p class="menu-card-desc"><?php echo htmlspecialchars($mon['MoTa'] ?? ''); ?></p>
                    <div class="menu-card-footer">
                        <span class="menu-card-price"><?php echo number_format($mon['DonGia'], 0, ',', '.'); ?>đ</span>
                        <button class="btn-add" onclick="event.stopPropagation(); <?php echo $isOutOfStock ? '' : 'quickAdd('.$mon['MaMonAn'].')'; ?>"><i class="fas fa-plus"></i></button>
                    </div>
                </div>
            </div>
            <?php endforeach; else: ?>
            <p style="grid-column:1/-1;text-align:center;padding:50px;color:#666;">Không tìm thấy món ăn nào</p>
            <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Modal -->
    <div id="foodModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <div id="modalImage"></div>
                <button class="close-btn" onclick="closeModal()"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <div id="modalAlert" class="alert-modal"></div>
                <h2 id="modalTitle" class="modal-title"></h2>
                <div id="modalPrice" class="modal-price"></div>
                <div id="modalStatus" class="modal-status"></div>
                <p id="modalDesc" class="modal-desc"></p>
                <div class="order-section">
                    <?php if ($isLoggedIn && $userType === 'customer'): ?>
                    <div class="quantity-selector">
                        <span>Số lượng:</span>
                        <button type="button" class="qty-btn" onclick="changeQty(-1)">-</button>
                        <input type="number" id="qty" class="qty-input" value="1" min="1" max="99">
                        <button type="button" class="qty-btn" onclick="changeQty(1)">+</button>
                    </div>
                    <p style="margin-bottom:15px;">Tổng: <strong id="totalPrice">0đ</strong></p>
                    <button id="btnOrder" class="btn-order" onclick="addToCart()"><i class="fas fa-cart-plus"></i> Thêm vào giỏ</button>
                    <?php else: ?>
                    <div class="login-prompt"><i class="fas fa-lock"></i> <a href="customer_login.php">Đăng nhập</a> để đặt món</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <?php if ($isLoggedIn && $userType === 'customer'): ?>
    <div class="floating-cart">
        <button class="cart-btn" onclick="window.location.href='gio_hang.php'"><i class="fas fa-shopping-cart"></i><span class="cart-badge hidden" id="cartBadge">0</span></button>
    </div>
    <?php endif; ?>

    <div class="footer"><p>&copy; 2024 Nhà hàng 3CE. All rights reserved.</p></div>

    <script>
    let currentFood = null;
    function openModal(food) {
        currentFood = food;
        document.getElementById('modalImage').innerHTML = food.HinhAnh ? 
            `<img src="${food.HinhAnh}" class="modal-image" onerror="this.outerHTML='<div class=modal-image-placeholder><i class=fas fa-utensils></i></div>'">` : 
            '<div class="modal-image-placeholder"><i class="fas fa-utensils"></i></div>';
        document.getElementById('modalTitle').textContent = food.TenMonAn;
        document.getElementById('modalPrice').textContent = formatPrice(food.DonGia) + 'đ/' + food.DonViTinh;
        document.getElementById('modalDesc').textContent = food.MoTa;
        const status = document.getElementById('modalStatus');
        status.className = 'modal-status ' + (food.TrangThai === 'Còn hàng' ? 'available' : 'unavailable');
        status.innerHTML = food.TrangThai === 'Còn hàng' ? '<i class="fas fa-check"></i> Còn hàng' : '<i class="fas fa-times"></i> Hết hàng';
        const qty = document.getElementById('qty');
        if (qty) { qty.value = 1; updateTotal(); }
        const btn = document.getElementById('btnOrder');
        if (btn) btn.disabled = food.TrangThai !== 'Còn hàng';
        document.getElementById('modalAlert').className = 'alert-modal';
        document.getElementById('foodModal').classList.add('active');
    }
    function closeModal() { document.getElementById('foodModal').classList.remove('active'); currentFood = null; }
    function changeQty(d) { const i = document.getElementById('qty'); let v = parseInt(i.value) + d; i.value = Math.max(1, Math.min(99, v)); updateTotal(); }
    function updateTotal() { if (!currentFood) return; document.getElementById('totalPrice').textContent = formatPrice(currentFood.DonGia * parseInt(document.getElementById('qty').value)) + 'đ'; }
    function formatPrice(p) { return new Intl.NumberFormat('vi-VN').format(p); }
    function addToCart() {
        if (!currentFood) return;
        fetch('add_to_order.php', { method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify({MaMonAn: currentFood.MaMonAn, SoLuong: parseInt(document.getElementById('qty').value)}) })
        .then(r => r.json()).then(d => {
            const a = document.getElementById('modalAlert');
            a.className = 'alert-modal ' + (d.success ? 'success' : 'error');
            a.innerHTML = '<i class="fas fa-' + (d.success ? 'check' : 'exclamation') + '-circle"></i> ' + d.message;
            if (d.success) { updateBadge(d.cartCount); setTimeout(closeModal, 1500); }
        });
    }
    function quickAdd(id) {
        <?php if ($isLoggedIn && $userType === 'customer'): ?>
        fetch('add_to_order.php', { method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify({MaMonAn: id, SoLuong: 1}) })
        .then(r => r.json()).then(d => { showToast(d.success ? 'success' : 'error', d.message); if (d.success) updateBadge(d.cartCount); });
        <?php else: ?>
        showToast('error', 'Vui lòng đăng nhập để đặt món!');
        setTimeout(() => window.location.href = 'customer_login.php', 1500);
        <?php endif; ?>
    }
    function updateBadge(c) { const b = document.getElementById('cartBadge'); if (b) { b.textContent = c; b.classList.toggle('hidden', c < 1); } }
    function showToast(t, m) {
        document.querySelectorAll('.toast').forEach(e => e.remove());
        const toast = document.createElement('div');
        toast.className = 'toast toast-' + t;
        toast.innerHTML = '<i class="fas fa-' + (t === 'success' ? 'check' : 'exclamation') + '-circle"></i> ' + m;
        document.body.appendChild(toast);
        setTimeout(() => toast.classList.add('show'), 100);
        setTimeout(() => { toast.classList.remove('show'); setTimeout(() => toast.remove(), 300); }, 3000);
    }
    document.getElementById('foodModal').addEventListener('click', e => { if (e.target.id === 'foodModal') closeModal(); });
    // Load cart count
    <?php if ($isLoggedIn && $userType === 'customer'): ?>
    fetch('get_cart.php').then(r => r.json()).then(d => updateBadge(d.count));
    <?php endif; ?>
    </script>
</body>
</html>
