<?php
session_start();
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/helpers.php';

// Lấy món nổi bật (6 món ngẫu nhiên)
$monNoiBat = [];
try {
    $stmt = $conn->query("SELECT * FROM mon_an WHERE TrangThai = 'Còn hàng' ORDER BY RAND() LIMIT 6");
    $monNoiBat = $stmt->fetchAll();
} catch(PDOException $e) {}

// Lấy danh mục
$danhMucs = [];
try {
    $stmt = $conn->query("SELECT dm.*, COUNT(ma.MaMonAn) as SoMon FROM danh_muc_mon_an dm LEFT JOIN mon_an ma ON dm.MaDanhMuc = ma.MaDanhMuc GROUP BY dm.MaDanhMuc");
    $danhMucs = $stmt->fetchAll();
} catch(PDOException $e) {}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nhà hàng 3CE - Ẩm thực đỉnh cao</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        
        /* Header */
        .header { background: linear-gradient(135deg, #001f3f 0%, #003366 100%); padding: 15px 0; position: fixed; width: 100%; top: 0; z-index: 1000; }
        .header-content { max-width: 1200px; margin: 0 auto; padding: 0 20px; display: flex; justify-content: space-between; align-items: center; }
        .logo { display: flex; align-items: center; gap: 10px; font-size: 24px; font-weight: bold; color: #F5F5DC; text-decoration: none; }
        .nav-menu { display: flex; gap: 30px; list-style: none; }
        .nav-menu a { text-decoration: none; color: #F5F5DC; font-weight: 500; transition: color 0.3s; }
        .nav-menu a:hover { color: #D4AF37; }
        
        /* Hero Slider */
        .hero-slider { height: 100vh; position: relative; overflow: hidden; }
        .slide { position: absolute; inset: 0; opacity: 0; transition: opacity 1s ease-in-out; pointer-events: none; }
        .slide.active { opacity: 1; pointer-events: auto; }
        .slide-bg { width: 100%; height: 100%; object-fit: cover; }
        .slide-overlay { position: absolute; inset: 0; background: linear-gradient(rgba(0,31,63,0.6), rgba(0,31,63,0.8)); display: flex; align-items: center; justify-content: center; }
        .hero-content { max-width: 800px; padding: 20px; text-align: center; color: white; position: relative; z-index: 5; }
        .hero-content h1 { font-size: 56px; margin-bottom: 20px; text-shadow: 2px 2px 10px rgba(0,0,0,0.5); animation: fadeInUp 1s ease; }
        .hero-content h1 span { color: #D4AF37; }
        .hero-content p { font-size: 20px; margin-bottom: 40px; opacity: 0.95; animation: fadeInUp 1s ease 0.3s both; }
        .hero-btns { display: flex; gap: 20px; justify-content: center; flex-wrap: wrap; animation: fadeInUp 1s ease 0.5s both; position: relative; z-index: 10; }
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
        
        /* Slider Controls */
        .slider-dots { position: absolute; bottom: 30px; left: 50%; transform: translateX(-50%); display: flex; gap: 12px; z-index: 20; }
        .dot { width: 12px; height: 12px; border-radius: 50%; background: rgba(255,255,255,0.5); cursor: pointer; transition: all 0.3s; }
        .dot.active { background: #D4AF37; transform: scale(1.3); }
        .slider-arrow { position: absolute; top: 50%; transform: translateY(-50%); width: 50px; height: 50px; background: rgba(255,255,255,0.2); border: none; border-radius: 50%; color: white; font-size: 20px; cursor: pointer; z-index: 20; transition: all 0.3s; }
        .slider-arrow:hover { background: #D4AF37; color: #001f3f; }
        .slider-arrow.prev { left: 30px; }
        .slider-arrow.next { right: 30px; }
        
        .btn { padding: 15px 40px; border-radius: 50px; font-size: 18px; font-weight: 600; text-decoration: none; transition: all 0.3s; display: inline-flex; align-items: center; gap: 10px; cursor: pointer; position: relative; z-index: 10; }
        .btn-primary { background: linear-gradient(135deg, #D4AF37, #f7d774); color: #001f3f; }
        .btn-primary:hover { transform: translateY(-3px); box-shadow: 0 10px 30px rgba(212,175,55,0.4); }
        .btn-outline { border: 2px solid white; color: white; background: transparent; }
        .btn-outline:hover { background: white; color: #001f3f; }
        
        /* Features */
        .features { padding: 80px 20px; background: #F5F5DC; }
        .features-grid { max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 30px; }
        .feature-card { background: white; padding: 40px 30px; border-radius: 20px; text-align: center; box-shadow: 0 10px 40px rgba(0,0,0,0.1); transition: transform 0.3s; }
        .feature-card:hover { transform: translateY(-10px); }
        .feature-icon { width: 80px; height: 80px; background: linear-gradient(135deg, #001f3f, #003366); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; }
        .feature-icon i { font-size: 35px; color: #D4AF37; }
        .feature-card h3 { color: #001f3f; margin-bottom: 15px; font-size: 22px; }
        .feature-card p { color: #666; line-height: 1.6; }
        
        /* Categories */
        .categories { padding: 80px 20px; background: white; }
        .section-title { text-align: center; margin-bottom: 50px; }
        .section-title h2 { font-size: 42px; color: #001f3f; margin-bottom: 15px; }
        .section-title p { color: #666; font-size: 18px; }
        .categories-grid { max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 25px; }
        .category-card { position: relative; height: 200px; border-radius: 20px; overflow: hidden; cursor: pointer; }
        .category-card img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s; }
        .category-card:hover img { transform: scale(1.1); }
        .category-overlay { position: absolute; inset: 0; background: linear-gradient(transparent, rgba(0,31,63,0.9)); display: flex; flex-direction: column; justify-content: flex-end; padding: 25px; color: white; }
        .category-overlay h3 { font-size: 24px; margin-bottom: 5px; }
        .category-overlay span { opacity: 0.8; }
        
        /* Featured Dishes */
        .featured { padding: 80px 20px; background: linear-gradient(135deg, #F5F5DC 0%, #EDE8D0 100%); }
        .dishes-grid { max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 30px; }
        .dish-card { background: white; border-radius: 20px; overflow: hidden; box-shadow: 0 10px 40px rgba(0,0,0,0.1); transition: all 0.3s; }
        .dish-card:hover { transform: translateY(-15px); box-shadow: 0 20px 50px rgba(0,0,0,0.2); }
        .dish-image { height: 220px; background: linear-gradient(135deg, #001f3f, #003366); display: flex; align-items: center; justify-content: center; color: #D4AF37; font-size: 60px; position: relative; overflow: hidden; }
        .dish-image img { width: 100%; height: 100%; object-fit: cover; }
        .dish-badge { position: absolute; top: 15px; left: 15px; background: linear-gradient(135deg, #D4AF37, #f7d774); color: #001f3f; padding: 5px 15px; border-radius: 20px; font-size: 12px; font-weight: 700; }
        .dish-content { padding: 25px; }
        .dish-content h3 { color: #001f3f; margin-bottom: 10px; font-size: 20px; }
        .dish-content p { color: #666; font-size: 14px; margin-bottom: 15px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .dish-footer { display: flex; justify-content: space-between; align-items: center; }
        .dish-price { font-size: 24px; font-weight: bold; color: #003366; }
        .btn-order { padding: 10px 25px; background: linear-gradient(135deg, #001f3f, #003366); color: #F5F5DC; border: none; border-radius: 25px; cursor: pointer; font-weight: 600; transition: all 0.3s; }
        .btn-order:hover { background: linear-gradient(135deg, #D4AF37, #f7d774); color: #001f3f; }
        
        /* CTA Section */
        .cta { padding: 100px 20px; background: linear-gradient(135deg, #001f3f 0%, #003366 100%); text-align: center; color: white; }
        .cta h2 { font-size: 42px; margin-bottom: 20px; }
        .cta p { font-size: 20px; margin-bottom: 40px; opacity: 0.9; max-width: 600px; margin-left: auto; margin-right: auto; }
        
        /* Footer */
        .footer { background: #001f3f; color: #F5F5DC; padding: 60px 20px 30px; }
        .footer-content { max-width: 1200px; margin: 0 auto; display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 40px; }
        .footer-section h4 { color: #D4AF37; margin-bottom: 20px; font-size: 20px; }
        .footer-section p, .footer-section a { color: #ccc; line-height: 2; text-decoration: none; display: block; }
        .footer-section a:hover { color: #D4AF37; }
        .footer-bottom { text-align: center; padding-top: 30px; margin-top: 30px; border-top: 1px solid rgba(255,255,255,0.1); }
        .social-links { display: flex; gap: 15px; margin-top: 20px; }
        .social-links a { width: 40px; height: 40px; background: rgba(255,255,255,0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #F5F5DC; transition: all 0.3s; }
        .social-links a:hover { background: #D4AF37; color: #001f3f; }
        
        @media (max-width: 768px) {
            .hero h1 { font-size: 36px; }
            .hero p { font-size: 16px; }
            .nav-menu { display: none; }
            .section-title h2 { font-size: 28px; }
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <!-- Hero Slider -->
    <section class="hero-slider">
        <div class="slide active">
            <img src="https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=1920" class="slide-bg" alt="Nhà hàng">
            <div class="slide-overlay">
                <div class="hero-content">
                    <h1>Chào mừng đến <span>Nhà hàng 3CE</span></h1>
                    <p>Trải nghiệm ẩm thực đỉnh cao với những món ăn được chế biến từ nguyên liệu tươi ngon nhất</p>
                    <div class="hero-btns">
                        <a href="thuc_don.php" class="btn btn-primary"><i class="fas fa-book-open"></i> Xem Thực Đơn</a>
                        <a href="dat_ban.php" class="btn btn-outline"><i class="fas fa-calendar-check"></i> Đặt Bàn Ngay</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="slide">
            <img src="https://images.unsplash.com/photo-1414235077428-338989a2e8c0?w=1920" class="slide-bg" alt="Món ăn">
            <div class="slide-overlay">
                <div class="hero-content">
                    <h1>Món Ăn <span>Đặc Sắc</span></h1>
                    <p>Thưởng thức hương vị truyền thống Việt Nam qua từng món ăn được chế biến tỉ mỉ</p>
                    <div class="hero-btns">
                        <a href="thuc_don.php?category=2" class="btn btn-primary"><i class="fas fa-utensils"></i> Món Chính</a>
                        <a href="thuc_don.php" class="btn btn-outline"><i class="fas fa-list"></i> Xem Tất Cả</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="slide">
            <img src="https://images.unsplash.com/photo-1555396273-367ea4eb4db5?w=1920" class="slide-bg" alt="Không gian">
            <div class="slide-overlay">
                <div class="hero-content">
                    <h1>Không Gian <span>Sang Trọng</span></h1>
                    <p>Tận hưởng bữa ăn trong không gian ấm cúng, lý tưởng cho mọi dịp đặc biệt</p>
                    <div class="hero-btns">
                        <a href="dat_ban.php" class="btn btn-primary"><i class="fas fa-calendar-check"></i> Đặt Bàn</a>
                        <?php if ($isLoggedIn): ?>
                        <a href="<?php echo $userType === 'admin' ? '../admin/dashboard.php' : 'customer_dashboard.php'; ?>" class="btn btn-outline"><i class="fas fa-user"></i> <?php echo htmlspecialchars($userName); ?></a>
                        <?php else: ?>
                        <a href="customer_login.php" class="btn btn-outline"><i class="fas fa-user"></i> Đăng Nhập</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="slide">
            <img src="https://images.unsplash.com/photo-1544145945-f90425340c7e?w=1920" class="slide-bg" alt="Đồ uống">
            <div class="slide-overlay">
                <div class="hero-content">
                    <h1>Thức Uống <span>Đa Dạng</span></h1>
                    <p>Từ cà phê truyền thống đến cocktail hiện đại, đáp ứng mọi sở thích</p>
                    <div class="hero-btns">
                        <a href="thuc_don.php?category=4" class="btn btn-primary"><i class="fas fa-glass-cheers"></i> Đồ Uống</a>
                        <a href="thuc_don.php?category=3" class="btn btn-outline"><i class="fas fa-ice-cream"></i> Tráng Miệng</a>
                    </div>
                </div>
            </div>
        </div>
        
        <button class="slider-arrow prev" onclick="changeSlide(-1)"><i class="fas fa-chevron-left"></i></button>
        <button class="slider-arrow next" onclick="changeSlide(1)"><i class="fas fa-chevron-right"></i></button>
        
        <div class="slider-dots">
            <span class="dot active" onclick="goToSlide(0)"></span>
            <span class="dot" onclick="goToSlide(1)"></span>
            <span class="dot" onclick="goToSlide(2)"></span>
            <span class="dot" onclick="goToSlide(3)"></span>
        </div>
    </section>

    <!-- Features -->
    <section class="features">
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-leaf"></i></div>
                <h3>Nguyên Liệu Tươi</h3>
                <p>Chúng tôi chỉ sử dụng nguyên liệu tươi ngon nhất, được chọn lọc kỹ càng mỗi ngày</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-award"></i></div>
                <h3>Đầu Bếp Chuyên Nghiệp</h3>
                <p>Đội ngũ đầu bếp giàu kinh nghiệm, được đào tạo bài bản từ các trường ẩm thực hàng đầu</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-clock"></i></div>
                <h3>Phục Vụ Nhanh Chóng</h3>
                <p>Cam kết phục vụ nhanh chóng, chu đáo để mang đến trải nghiệm tốt nhất cho khách hàng</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><i class="fas fa-heart"></i></div>
                <h3>Không Gian Ấm Cúng</h3>
                <p>Không gian nhà hàng được thiết kế sang trọng, ấm cúng phù hợp cho mọi dịp</p>
            </div>
        </div>
    </section>

    <!-- Categories -->
    <section class="categories">
        <div class="section-title">
            <h2><i class="fas fa-th-large"></i> Danh Mục Món Ăn</h2>
            <p>Khám phá các danh mục món ăn đa dạng của chúng tôi</p>
        </div>
        <div class="categories-grid">
            <?php 
            $catImages = [
                1 => 'https://images.unsplash.com/photo-1541014741259-de529411b96a?w=400',
                2 => 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=400',
                3 => 'https://images.unsplash.com/photo-1551024506-0bccd828d307?w=400',
                4 => 'https://images.unsplash.com/photo-1544145945-f90425340c7e?w=400'
            ];
            foreach ($danhMucs as $dm): 
            ?>
            <a href="thuc_don.php?category=<?php echo $dm['MaDanhMuc']; ?>" class="category-card">
                <img src="<?php echo $catImages[$dm['MaDanhMuc']] ?? $catImages[2]; ?>" alt="<?php echo htmlspecialchars($dm['TenDanhMuc']); ?>">
                <div class="category-overlay">
                    <h3><?php echo htmlspecialchars($dm['TenDanhMuc']); ?></h3>
                    <span><?php echo $dm['SoMon']; ?> món</span>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Featured Dishes -->
    <section class="featured">
        <div class="section-title">
            <h2><i class="fas fa-star"></i> Món Ăn Nổi Bật</h2>
            <p>Những món ăn được yêu thích nhất tại nhà hàng</p>
        </div>
        <div class="dishes-grid">
            <?php foreach ($monNoiBat as $index => $mon): 
                $badges = ['Bán chạy', 'Đặc biệt', 'Mới', 'Hot', 'Yêu thích', 'Đề xuất'];
            ?>
            <div class="dish-card">
                <div class="dish-image">
                    <span class="dish-badge"><i class="fas fa-fire"></i> <?php echo $badges[$index % 6]; ?></span>
                    <?php if (!empty($mon['HinhAnh'])): ?>
                    <img src="<?php echo htmlspecialchars(getImagePath($mon['HinhAnh'])); ?>" alt="<?php echo htmlspecialchars($mon['TenMonAn']); ?>" onerror="this.style.display='none'">
                    <?php endif; ?>
                    <i class="fas fa-utensils" style="position:absolute;"></i>
                </div>
                <div class="dish-content">
                    <h3><?php echo htmlspecialchars($mon['TenMonAn']); ?></h3>
                    <p><?php echo htmlspecialchars($mon['MoTa'] ?? 'Món ăn ngon từ Nhà hàng 3CE'); ?></p>
                    <div class="dish-footer">
                        <span class="dish-price"><?php echo number_format($mon['DonGia'], 0, ',', '.'); ?>đ</span>
                        <a href="thuc_don.php" class="btn-order"><i class="fas fa-eye"></i> Xem thêm</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <div style="text-align: center; margin-top: 40px;">
            <a href="thuc_don.php" class="btn btn-primary"><i class="fas fa-book-open"></i> Xem Toàn Bộ Thực Đơn</a>
        </div>
    </section>

    <!-- CTA -->
    <section class="cta">
        <h2><i class="fas fa-calendar-check"></i> Đặt Bàn Ngay Hôm Nay</h2>
        <p>Liên hệ với chúng tôi để đặt bàn và trải nghiệm những món ăn tuyệt vời nhất</p>
        <a href="dat_ban.php" class="btn btn-primary"><i class="fas fa-phone"></i> Đặt Bàn Ngay</a>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-section">
                <h4><i class="fas fa-utensils"></i> Nhà hàng 3CE</h4>
                <p>Mang đến trải nghiệm ẩm thực tuyệt vời với những món ăn được chế biến từ nguyên liệu tươi ngon nhất.</p>
                <div class="social-links">
                    <a href="#"><i class="fab fa-facebook-f"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-youtube"></i></a>
                    <a href="#"><i class="fab fa-tiktok"></i></a>
                </div>
            </div>
            <div class="footer-section">
                <h4>Liên Kết Nhanh</h4>
                <a href="index.php">Trang chủ</a>
                <a href="thuc_don.php">Thực đơn</a>
                <a href="dat_ban.php">Đặt bàn</a>
                <a href="customer_login.php">Đăng nhập</a>
            </div>
            <div class="footer-section">
                <h4>Giờ Mở Cửa</h4>
                <p>Thứ 2 - Thứ 6: 10:00 - 22:00</p>
                <p>Thứ 7 - Chủ nhật: 09:00 - 23:00</p>
                <p>Ngày lễ: 09:00 - 23:00</p>
            </div>
            <div class="footer-section">
                <h4>Liên Hệ</h4>
                <p><i class="fas fa-map-marker-alt"></i> 123 Đường ABC, Quận 1, TP.HCM</p>
                <p><i class="fas fa-phone"></i> 0123 456 789</p>
                <p><i class="fas fa-envelope"></i> info@nhahang3ce.com</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2024 Nhà hàng 3CE. All rights reserved.</p>
        </div>
    </footer>

    <script>
        // Slider functionality
        let currentSlide = 0;
        const slides = document.querySelectorAll('.slide');
        const dots = document.querySelectorAll('.dot');
        const totalSlides = slides.length;
        let autoSlideInterval;

        function showSlide(index) {
            if (index >= totalSlides) currentSlide = 0;
            else if (index < 0) currentSlide = totalSlides - 1;
            else currentSlide = index;

            slides.forEach((slide, i) => {
                slide.classList.remove('active');
                dots[i].classList.remove('active');
            });
            slides[currentSlide].classList.add('active');
            dots[currentSlide].classList.add('active');
        }

        function changeSlide(direction) {
            showSlide(currentSlide + direction);
            resetAutoSlide();
        }

        function goToSlide(index) {
            showSlide(index);
            resetAutoSlide();
        }

        function autoSlide() {
            autoSlideInterval = setInterval(() => {
                showSlide(currentSlide + 1);
            }, 5000); // Chuyển slide mỗi 5 giây
        }

        function resetAutoSlide() {
            clearInterval(autoSlideInterval);
            autoSlide();
        }

        // Bắt đầu auto slide
        autoSlide();

        // Pause khi hover
        document.querySelector('.hero-slider').addEventListener('mouseenter', () => {
            clearInterval(autoSlideInterval);
        });
        document.querySelector('.hero-slider').addEventListener('mouseleave', () => {
            autoSlide();
        });

        // Keyboard navigation
        document.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowLeft') changeSlide(-1);
            if (e.key === 'ArrowRight') changeSlide(1);
        });
    </script>
</body>
</html>
