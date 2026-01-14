# 🍽️ HỆ THỐNG QUẢN LÝ NHÀ HÀNG 3CE

## 📋 TỔNG QUAN DỰ ÁN

Hệ thống quản lý nhà hàng được xây dựng bằng PHP và MySQL, cung cấp giải pháp toàn diện cho việc quản lý hoạt động kinh doanh nhà hàng. Hệ thống hỗ trợ 2 vai trò chính: **Quản lý (Admin)** và **Khách hàng**.

---

## 🛠️ MÔ TẢ CÁC CÔNG CỤ CHÍNH

### 🔧 Công cụ thiết lập & cấu hình

**Thiết lập hệ thống** là công cụ tổng hợp cho phép người dùng thực hiện các tác vụ quan trọng như kiểm tra kết nối database, tạo tài khoản Admin mới và xem dashboard tổng quan của dự án. Đây là công cụ đầu tiên cần sử dụng khi cài đặt hệ thống lần đầu.

**Khởi động ứng dụng** đóng vai trò như trang khởi động, tự động kiểm tra trạng thái hệ thống (database, tài khoản admin) và cung cấp các liên kết điều hướng nhanh đến các trang chính như đăng nhập admin, đăng nhập khách hàng và trang chủ.

**Cấu hình kết nối database** chứa thông tin kết nối MySQL sử dụng PDO (PHP Data Objects), bao gồm host, tên database, username và password. Cấu hình này được sử dụng bởi tất cả các chức năng cần truy cập database.

**Hàm tiện ích** cung cấp các hàm dùng chung trong toàn bộ ứng dụng, đặc biệt là xử lý đường dẫn hình ảnh để đảm bảo ảnh hiển thị đúng dù được gọi từ module admin hay public.

---

### 👨‍💼 Công cụ quản lý (Admin)

**Dashboard quản lý** là trang tổng quan dành cho quản lý, hiển thị thông tin về hoạt động kinh doanh bao gồm doanh thu hôm nay, số hóa đơn, số đơn chờ xử lý, số bàn trống và danh sách các đơn hàng mới cần xử lý. Giao diện được thiết kế với sidebar điều hướng và các card thống kê trực quan.

**Quản lý món ăn** là công cụ quản lý thực đơn với đầy đủ chức năng thêm, sửa, xóa món. Quản lý có thể thêm món mới, sửa thông tin món, xóa món và đặc biệt là upload hình ảnh món ăn bằng cách kéo thả (drag & drop) hoặc nhập URL. Công cụ cũng cho phép cập nhật nhanh trạng thái còn hàng/hết hàng của từng món.

**Quản lý bàn ăn** hiển thị sơ đồ bàn ăn trực quan của nhà hàng dưới dạng lưới các card. Mỗi bàn hiển thị tên, sức chứa, vị trí và trạng thái hiện tại. Quản lý có thể thay đổi trạng thái bàn giữa Trống, Đang phục vụ và Đã đặt thông qua dropdown menu.

**Quản lý hóa đơn** quản lý toàn bộ hóa đơn trong hệ thống với khả năng lọc theo trạng thái (Tất cả, Chờ thanh toán, Đã thanh toán, Đã hủy). Quản lý có thể xem chi tiết hóa đơn, xác nhận thanh toán hoặc hủy hóa đơn. Giao diện hiển thị số lượng đơn theo từng trạng thái bằng badge.

**Quản lý đặt bàn** xử lý các yêu cầu đặt bàn từ khách hàng. Quản lý có thể xem danh sách đặt bàn, xác nhận và xếp bàn cho khách, đánh dấu khách đã đến hoặc hủy đặt bàn. Hệ thống tự động cập nhật trạng thái bàn khi xác nhận đặt bàn.

**Báo cáo doanh thu** cung cấp báo cáo doanh thu chi tiết với các thống kê theo ngày, tháng và năm. Công cụ hiển thị doanh thu đã thanh toán, doanh thu chờ xử lý và bảng doanh thu 7 ngày gần nhất kèm số đơn hàng mỗi ngày.

**Quản lý nhân viên** hiển thị danh sách nhân viên trong hệ thống với thông tin mã nhân viên, họ tên, số điện thoại, chức vụ và ngày vào làm. Quản lý có thể thêm nhân viên mới thông qua chức năng đăng ký.

---

### 👤 Công cụ khách hàng (Public)

**Trang chủ & Thực đơn** là trang chính của nhà hàng, hiển thị thực đơn với giao diện card đẹp mắt. Mỗi món ăn hiển thị hình ảnh, tên, mô tả, giá và nút thêm vào giỏ hàng. Trang hỗ trợ tìm kiếm món ăn theo tên, có giỏ hàng nổi (floating cart) ở góc màn hình và modal xem chi tiết món. Khách hàng có thể đặt món trực tiếp từ trang này.

**Dashboard khách hàng** là trang tổng quan dành cho khách hàng sau khi đăng nhập, hiển thị thông tin cá nhân, điểm tích lũy, tổng số đơn hàng, số đơn chờ xử lý và tổng chi tiêu. Trang cung cấp các liên kết nhanh đến các chức năng như xem thực đơn, giỏ hàng, đặt bàn, thanh toán, lịch sử đơn hàng và thông tin cá nhân.

**Đặt bàn trực tuyến** cho phép khách hàng đặt bàn trước khi đến nhà hàng. Khách hàng chọn thời gian đến, số lượng khách, có thể chọn bàn cụ thể (nếu muốn) và thêm ghi chú. Trang cũng hiển thị lịch sử đặt bàn của khách hàng với khả năng hủy đặt bàn đang chờ xác nhận.

**Giỏ hàng** hiển thị danh sách các món ăn khách hàng đã chọn với hình ảnh, tên món, đơn giá, số lượng và thành tiền. Khách hàng có thể cập nhật số lượng, xóa món và xác nhận đặt món. Khi xác nhận, hệ thống tạo hóa đơn mới và chuyển đến trang thanh toán.

**Thanh toán** là công cụ thanh toán đa năng, cho phép khách hàng thanh toán từng hóa đơn hoặc thanh toán gộp nhiều hóa đơn cùng lúc. Khách hàng có thể sử dụng điểm tích lũy để giảm giá (1 điểm = 1,000đ), chọn phương thức thanh toán (Tiền mặt, Chuyển khoản, Thẻ). Sau khi thanh toán, hệ thống hiển thị hóa đơn chi tiết có thể in và tự động cộng điểm tích lũy cho khách hàng.

**Lịch sử đơn hàng** hiển thị danh sách tất cả đơn hàng của khách hàng với thông tin mã đơn, thời gian, tổng tiền và trạng thái. Khách hàng có thể xem chi tiết từng đơn hàng bao gồm danh sách món ăn, số lượng và giá.

**Đăng nhập & Đăng ký khách hàng** cho phép khách hàng đăng nhập bằng số điện thoại và đăng ký tài khoản mới với thông tin họ tên, số điện thoại và email (tùy chọn).

---

### 🗄️ Công cụ Database & Xác thực

**Database SQL** chứa toàn bộ cấu trúc database bao gồm lệnh tạo database, tạo các bảng (nhân viên, khách hàng, món ăn, bàn ăn, hóa đơn, chi tiết hóa đơn, đặt bàn, chức vụ, danh mục món ăn, tài khoản) và dữ liệu mẫu ban đầu.

**Đăng xuất** xử lý đăng xuất cho cả admin và khách hàng, hủy session và chuyển hướng về trang đăng nhập tương ứng

---

## 🗄️ DATABASE

### File SQL
| File | Mô tả |
|------|-------|
| `database/quanlynhahang_db.sql` | File import database chính (tạo DB + tables + dữ liệu mẫu) |
| `database/migrate_add_matkhau.php` | Migration thêm cột mật khẩu cho nhân viên |
| `database/update_mo_ta_mon_an.sql` | Cập nhật mô tả món ăn |

### Các bảng chính
| Bảng | Mô tả |
|------|-------|
| `nhan_vien` | Thông tin nhân viên |
| `khach_hang` | Thông tin khách hàng + điểm tích lũy |
| `mon_an` | Danh sách món ăn (tên, giá, hình ảnh, mô tả, trạng thái) |
| `ban_an` | Danh sách bàn ăn (tên, số ghế, vị trí, trạng thái) |
| `hoa_don` | Hóa đơn bán hàng |
| `chi_tiet_hoa_don` | Chi tiết món trong hóa đơn |
| `dat_ban` | Thông tin đặt bàn |
| `chuc_vu` | Danh mục chức vụ |
| `danh_muc_mon_an` | Danh mục món ăn |
| `tai_khoan` | Tài khoản đăng nhập |

---

## 🎨 GIAO DIỆN & ASSETS

| Thư mục/File | Mô tả |
|--------------|-------|
| `assets/css/style.css` | CSS chung cho toàn bộ ứng dụng |
| `admin/uploads/` | Thư mục lưu hình ảnh upload |

---

## 📁 CẤU TRÚC THƯ MỤC

```
📦 quanlynhahang/
├── 📂 admin/                    # Module quản lý
│   ├── admin_login.php         # Đăng nhập admin
│   ├── dashboard.php           # Dashboard chính
│   ├── register.php            # Đăng ký nhân viên
│   ├── quan_ly_mon_an.php      # Quản lý món ăn
│   ├── quan_ly_ban.php         # Quản lý bàn
│   ├── quan_ly_nhan_vien.php   # Quản lý nhân viên
│   ├── quan_ly_hoa_don.php     # Quản lý hóa đơn
│   ├── quan_ly_dat_ban.php     # Quản lý đặt bàn
│   ├── quan_ly_doanh_thu.php   # Báo cáo doanh thu
│   ├── get_hoa_don_detail.php  # API chi tiết hóa đơn
│   ├── upload_image.php        # Upload hình ảnh
│   └── 📂 uploads/             # Thư mục lưu ảnh
│
├── 📂 public/                   # Module khách hàng
│   ├── index.php               # Trang chủ + thực đơn
│   ├── customer_login.php      # Đăng nhập khách hàng
│   ├── customer_register.php   # Đăng ký khách hàng
│   ├── customer_dashboard.php  # Dashboard khách hàng
│   ├── customer_profile.php    # Thông tin cá nhân
│   ├── dat_ban.php             # Đặt bàn
│   ├── gio_hang.php            # Giỏ hàng
│   ├── thanh_toan.php          # Thanh toán
│   ├── lich_su_don_hang.php    # Lịch sử đơn hàng
│   ├── add_to_order.php        # API thêm giỏ hàng
│   ├── get_cart.php            # API lấy giỏ hàng
│   ├── update_cart.php         # API cập nhật giỏ hàng
│   └── remove_cart.php         # API xóa giỏ hàng
│
├── 📂 auth/                     # Xác thực
│   └── logout.php              # Đăng xuất
│
├── 📂 config/                   # Cấu hình
│   ├── db.php                  # Kết nối database
│   └── helpers.php             # Hàm tiện ích
│
├── 📂 database/                 # Database
│   ├── quanlynhahang_db.sql    # File SQL chính
│   ├── migrate_add_matkhau.php # Migration mật khẩu
│   └── update_mo_ta_mon_an.sql # Update mô tả món
│
├── 📂 assets/                   # Tài nguyên
│   └── 📂 css/
│       └── style.css           # CSS chung
│
├── setup.php                    # Thiết lập hệ thống
├── start_app.php               # Khởi động ứng dụng
├── setup_admin.php             # Tạo admin
├── setup_mo_ta.php             # Cập nhật mô tả món
├── .env                        # Biến môi trường
├── .env.example                # Mẫu biến môi trường
├── CAI_DAT.md                  # Hướng dẫn cài đặt nhanh
└── README.md                   # Tài liệu này
```

---

## 🚀 CÀI ĐẶT NHANH

### 1. Yêu cầu hệ thống
- PHP >= 7.4
- MySQL >= 5.7
- Web Server: XAMPP, Laragon, WAMP

### 2. Các bước cài đặt

```bash
# Bước 1: Copy dự án vào thư mục web
# XAMPP: C:\xampp\htdocs\
# Laragon: C:\laragon\www\

# Bước 2: Import database
# Mở phpMyAdmin → Import → Chọn file database/quanlynhahang_db.sql

# Bước 3: Cấu hình kết nối (nếu cần)
# Sửa file config/db.php
```

### 3. Truy cập hệ thống
- **Thiết lập**: `http://localhost/[tên_thư_mục]/setup.php`
- **Trang chủ**: `http://localhost/[tên_thư_mục]/public/index.php`
- **Admin**: `http://localhost/[tên_thư_mục]/admin/admin_login.php`

---

## 🔐 TÀI KHOẢN MẶC ĐỊNH

### Admin
- **Đăng nhập**: Mã nhân viên (tạo qua `setup.php?action=create_admin`)
- **Mật khẩu**: `admin123`

### Khách hàng (có sẵn)
- **SĐT**: `0988776655` hoặc `0911223344`

---

## ✨ TÍNH NĂNG NỔI BẬT

### Dành cho Admin
- ✅ Dashboard thống kê trực quan
- ✅ Quản lý món ăn với upload hình ảnh (drag & drop)
- ✅ Sơ đồ bàn ăn trực quan
- ✅ Xử lý đặt bàn từ khách hàng
- ✅ Quản lý hóa đơn với bộ lọc
- ✅ Báo cáo doanh thu chi tiết

### Dành cho Khách hàng
- ✅ Giao diện thực đơn đẹp mắt
- ✅ Giỏ hàng nổi tiện lợi
- ✅ Đặt bàn trực tuyến
- ✅ Thanh toán với nhiều phương thức
- ✅ Tích điểm và đổi điểm giảm giá
- ✅ Lịch sử đơn hàng

### Bảo mật
- ✅ Mật khẩu mã hóa bcrypt
- ✅ PDO Prepared Statements (chống SQL Injection)
- ✅ Session management

---

## 📝 GHI CHÚ PHÁT TRIỂN

- Sử dụng PDO cho kết nối database
- Session-based authentication
- Responsive design với CSS thuần
- Font Awesome cho icons
- AJAX cho các thao tác giỏ hàng

---

## 📄 LICENSE

Dự án được phát triển cho mục đích học tập và nghiên cứu.

---

---

## �  HƯỚNG DẪN XÂY DỰNG CÁC CHỨC NĂNG

### 1. 🔐 Chức năng Đăng ký tài khoản khách hàng

**File:** `public/customer_register.php`

**Cấu trúc Database:**
```sql
CREATE TABLE khach_hang (
    MaKhachHang INT AUTO_INCREMENT PRIMARY KEY,
    HoTen VARCHAR(100) NOT NULL,
    SoDienThoai VARCHAR(15) UNIQUE NOT NULL,
    Email VARCHAR(100),
    DiemTichLuy INT DEFAULT 0
);
```

**Luồng xử lý Backend (PHP):**
```
1. Khởi tạo session: session_start()
2. Include file kết nối DB: require_once 'config/db.php'
3. Kiểm tra method POST: if ($_SERVER['REQUEST_METHOD'] === 'POST')
4. Lấy dữ liệu từ form: $_POST['HoTen'], $_POST['SoDienThoai'], $_POST['Email']
5. Validate dữ liệu: kiểm tra rỗng, định dạng
6. Kiểm tra SĐT trùng: SELECT MaKhachHang FROM khach_hang WHERE SoDienThoai = ?
7. Insert vào DB: INSERT INTO khach_hang (HoTen, SoDienThoai, Email, DiemTichLuy) VALUES (?, ?, ?, 0)
8. Trả về thông báo thành công/lỗi
```

**Giao diện Frontend (HTML/CSS):**
- Form với các input: HoTen (required), SoDienThoai (required), Email (optional)
- Button submit
- Hiển thị thông báo lỗi/thành công
- Link đến trang đăng nhập

---

### 2. 🍽️ Chức năng Đặt món (Thêm vào giỏ hàng)

**Files liên quan:**
- `public/index.php` - Hiển thị thực đơn
- `public/add_to_order.php` - API thêm giỏ hàng

**Cấu trúc Database:**
```sql
CREATE TABLE mon_an (
    MaMonAn INT AUTO_INCREMENT PRIMARY KEY,
    TenMonAn VARCHAR(100) NOT NULL,
    DonGia DECIMAL(10,2) NOT NULL,
    HinhAnh VARCHAR(255),
    MoTa TEXT,
    TrangThai ENUM('Còn hàng', 'Hết hàng') DEFAULT 'Còn hàng'
);
```

**Cấu trúc giỏ hàng (Session):**
```php
$_SESSION['cart'] = [
    [
        'MaMonAn' => 1,
        'TenMonAn' => 'Phở bò',
        'DonGia' => 50000,
        'SoLuong' => 2,
        'HinhAnh' => 'pho.jpg'
    ],
    // ... các món khác
];
```

**Luồng xử lý API thêm giỏ hàng (`add_to_order.php`):**
```
1. Kiểm tra đăng nhập: isset($_SESSION['customer_id'])
2. Nhận JSON data: json_decode(file_get_contents('php://input'))
3. Validate: MaMonAn, SoLuong (1-99)
4. Kiểm tra món tồn tại và còn hàng: SELECT * FROM mon_an WHERE MaMonAn = ?
5. Khởi tạo giỏ hàng nếu chưa có: $_SESSION['cart'] = []
6. Kiểm tra món đã có trong giỏ:
   - Có: Cộng thêm số lượng
   - Chưa: Thêm mới vào mảng
7. Tính tổng số món trong giỏ
8. Trả về JSON response: success, message, cartCount
```

**Giao diện Frontend:**
- Grid hiển thị món ăn dạng card
- Mỗi card: hình ảnh, tên, giá, nút thêm giỏ hàng
- Modal xem chi tiết món với selector số lượng
- Floating cart button (góc phải dưới)
- Toast notification khi thêm thành công
- AJAX call để thêm món không reload trang

---

### 3. 🛒 Chức năng Giỏ hàng

**Files liên quan:**
- `public/gio_hang.php` - Trang giỏ hàng
- `public/get_cart.php` - API lấy giỏ hàng
- `public/update_cart.php` - API cập nhật số lượng
- `public/remove_cart.php` - API xóa món

**Luồng xử lý trang giỏ hàng (`gio_hang.php`):**
```
1. Kiểm tra đăng nhập
2. Xử lý xóa món (GET ?remove=index): array_splice($_SESSION['cart'], $index, 1)
3. Xử lý cập nhật số lượng (POST update_cart): cập nhật $_SESSION['cart'][$index]['SoLuong']
4. Xử lý xác nhận đặt món (POST confirm_order):
   a. Bắt đầu transaction: $conn->beginTransaction()
   b. Tính tổng tiền từ giỏ hàng
   c. Tạo hóa đơn: INSERT INTO hoa_don (MaKhachHang, ThoiGianVao, TongTien, ThanhTien, TrangThai)
   d. Lấy MaHoaDon vừa tạo: $conn->lastInsertId()
   e. Thêm chi tiết hóa đơn: INSERT INTO chi_tiet_hoa_don (MaHoaDon, MaMonAn, SoLuong, DonGia, ThanhTien)
   f. Commit transaction: $conn->commit()
   g. Xóa giỏ hàng: $_SESSION['cart'] = []
   h. Redirect đến trang thanh toán
5. Hiển thị danh sách món trong giỏ
```

**Cấu trúc Database hóa đơn:**
```sql
CREATE TABLE hoa_don (
    MaHoaDon INT AUTO_INCREMENT PRIMARY KEY,
    MaKhachHang INT,
    ThoiGianVao DATETIME,
    ThoiGianRa DATETIME,
    TongTien DECIMAL(10,2),
    ThanhTien DECIMAL(10,2),
    TrangThai ENUM('Chưa thanh toán', 'Đã thanh toán', 'Đã hủy'),
    GhiChu TEXT
);

CREATE TABLE chi_tiet_hoa_don (
    MaChiTiet INT AUTO_INCREMENT PRIMARY KEY,
    MaHoaDon INT,
    MaMonAn INT,
    SoLuong INT,
    DonGia DECIMAL(10,2),
    ThanhTien DECIMAL(10,2)
);
```

**API Endpoints:**

| API | Method | Input | Output |
|-----|--------|-------|--------|
| `get_cart.php` | GET | - | {items, total, count, isLoggedIn} |
| `update_cart.php` | POST | {index, quantity} | {success, message, total, count} |
| `remove_cart.php` | POST | {index} | {success, message, total, count} |

---

### 4. 💳 Chức năng Thanh toán

**File:** `public/thanh_toan.php`

**Luồng xử lý thanh toán đơn lẻ:**
```
1. Nhận MaHoaDon, PhuongThuc, DiemSuDung từ POST
2. Kiểm tra hóa đơn thuộc khách hàng và chưa thanh toán
3. Lấy điểm hiện tại của khách hàng
4. Validate điểm sử dụng (tối thiểu 1000, không vượt quá điểm có)
5. Tính giảm giá: giamGia = floor(diemSuDung / 1000) * 10000
6. Tính thành tiền: thanhTien = tongTien - giamGia
7. Cập nhật hóa đơn: UPDATE hoa_don SET TrangThai = 'Đã thanh toán', ThanhTien = ?, ThoiGianRa = NOW()
8. Trừ điểm đã sử dụng: UPDATE khach_hang SET DiemTichLuy = DiemTichLuy - ?
9. Cộng điểm mới (10,000đ = 1 điểm): UPDATE khach_hang SET DiemTichLuy = DiemTichLuy + ?
10. Hiển thị hóa đơn chi tiết
```

**Luồng xử lý thanh toán gộp:**
```
1. Nhận mảng selected_invoices[], PhuongThuc, DiemSuDung
2. Bắt đầu transaction
3. Lặp qua từng hóa đơn được chọn:
   a. Kiểm tra hóa đơn hợp lệ
   b. Cập nhật trạng thái "Đã thanh toán"
   c. Cộng dồn tổng tiền
4. Tính giảm giá từ điểm cho tổng tiền gộp
5. Trừ điểm, cộng điểm mới
6. Commit transaction
7. Hiển thị tổng hợp các hóa đơn đã thanh toán
```

**Công thức tích điểm:**
- Quy đổi điểm: 1000 điểm = 10,000đ giảm giá
- Tích điểm: 10,000đ chi tiêu = 1 điểm

**Giao diện:**
- Danh sách hóa đơn chưa thanh toán với checkbox
- Card hiển thị điểm tích lũy hiện có
- Form chọn phương thức thanh toán (radio buttons)
- Input nhập số điểm muốn đổi
- Bulk payment bar (fixed bottom) khi chọn nhiều hóa đơn
- Modal/popup hiển thị hóa đơn sau thanh toán (có thể in)

---

### 5. 🪑 Chức năng Đặt bàn

**File:** `public/dat_ban.php`

**Cấu trúc Database:**
```sql
CREATE TABLE ban_an (
    MaBan INT AUTO_INCREMENT PRIMARY KEY,
    TenBan VARCHAR(50),
    SoGhe INT,
    ViTri VARCHAR(100),
    TrangThai ENUM('Trống', 'Đang phục vụ', 'Đã đặt')
);

CREATE TABLE dat_ban (
    MaDatBan INT AUTO_INCREMENT PRIMARY KEY,
    MaKhachHang INT,
    MaBan INT NULL,
    ThoiGianDat DATETIME,
    SoLuongKhach INT,
    GhiChu TEXT,
    TrangThai ENUM('Chờ xác nhận', 'Đã xác nhận', 'Đã đến', 'Đã hủy')
);
```

**Luồng xử lý đặt bàn:**
```
1. Kiểm tra đăng nhập
2. Xử lý POST đặt bàn:
   a. Lấy dữ liệu: MaBan (optional), ThoiGianDat, SoLuongKhach, GhiChu
   b. Validate: ThoiGianDat không rỗng, SoLuongKhach >= 1
   c. Insert: INSERT INTO dat_ban (MaKhachHang, MaBan, ThoiGianDat, SoLuongKhach, GhiChu, TrangThai) VALUES (?, ?, ?, ?, ?, 'Chờ xác nhận')
3. Xử lý GET hủy đặt bàn (?cancel=MaDatBan):
   a. UPDATE dat_ban SET TrangThai = 'Đã hủy' WHERE MaDatBan = ? AND MaKhachHang = ? AND TrangThai = 'Chờ xác nhận'
4. Lấy danh sách bàn trống: SELECT * FROM ban_an WHERE TrangThai = 'Trống'
5. Lấy lịch sử đặt bàn: SELECT db.*, ba.TenBan FROM dat_ban db LEFT JOIN ban_an ba ON db.MaBan = ba.MaBan WHERE db.MaKhachHang = ?
```

**Giao diện:**
- Form đặt bàn: datetime-local input, number input, textarea ghi chú
- Grid hiển thị bàn trống (clickable cards)
- Danh sách lịch sử đặt bàn với status badges
- Nút hủy cho đơn đang "Chờ xác nhận"

---

### 6. 📊 Sơ đồ luồng dữ liệu tổng quan

```
┌─────────────┐     ┌─────────────┐     ┌─────────────┐
│  FRONTEND   │────▶│   BACKEND   │────▶│  DATABASE   │
│  (HTML/JS)  │◀────│    (PHP)    │◀────│   (MySQL)   │
└─────────────┘     └─────────────┘     └─────────────┘

Đăng ký:
[Form] ──POST──▶ [customer_register.php] ──INSERT──▶ [khach_hang]

Đặt món:
[Card click] ──AJAX──▶ [add_to_order.php] ──▶ [$_SESSION['cart']]

Giỏ hàng → Hóa đơn:
[Xác nhận] ──POST──▶ [gio_hang.php] ──INSERT──▶ [hoa_don] + [chi_tiet_hoa_don]

Thanh toán:
[Form] ──POST──▶ [thanh_toan.php] ──UPDATE──▶ [hoa_don] + [khach_hang.DiemTichLuy]

Đặt bàn:
[Form] ──POST──▶ [dat_ban.php] ──INSERT──▶ [dat_ban]
```

---

### 7. 🔧 API Endpoints

| Endpoint | Method | Input | Output |
|----------|--------|-------|--------|
| `add_to_order.php` | POST | `{MaMonAn, SoLuong}` | `{success, message, cartCount}` |
| `get_cart.php` | GET | - | `{items[], total, count, isLoggedIn}` |
| `update_cart.php` | POST | `{index, quantity}` | `{success, message, total, count}` |
| `remove_cart.php` | POST | `{index}` | `{success, message, total, count}` |

**Ví dụ AJAX call:**
```javascript
// Thêm món vào giỏ hàng
fetch('add_to_order.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ MaMonAn: 1, SoLuong: 2 })
})
.then(res => res.json())
.then(data => {
    if (data.success) {
        showToast(data.message);
        updateCartBadge(data.cartCount);
    }
});
```

---

### 8. 🔍 Chức năng Tìm kiếm món ăn

**File:** `public/index.php`

**Luồng xử lý:**
```
1. Nhận từ khóa: $search = $_GET['search'] ?? ''
2. Nếu có từ khóa → Query: SELECT * FROM mon_an WHERE TenMonAn LIKE ? OR MaMonAn LIKE ?
3. Nếu không có → Hiển thị tất cả món ăn
4. Render kết quả ra grid card
```

**Code PHP:**
```php
$search = $_GET['search'] ?? '';
if (!empty($search)) {
    $stmt = $conn->prepare("SELECT * FROM mon_an WHERE TenMonAn LIKE ? OR MaMonAn LIKE ? ORDER BY MaMonAn");
    $stmt->execute(["%$search%", "%$search%"]);
} else {
    $stmt = $conn->query("SELECT * FROM mon_an ORDER BY MaMonAn");
}
$monAns = $stmt->fetchAll();
```

**Giao diện:**
```html
<form class="search-box" method="GET">
    <input type="text" name="search" placeholder="Tìm kiếm món ăn..." value="<?= htmlspecialchars($search) ?>">
    <button type="submit"><i class="fas fa-search"></i> Tìm kiếm</button>
</form>
```

**Đặc điểm:**
| Thành phần | Mô tả |
|------------|-------|
| Phương thức | GET |
| Tìm theo | TenMonAn hoặc MaMonAn |
| Kiểu tìm | Fuzzy search (LIKE '%keyword%') |
| Bảo mật | PDO Prepared Statements, htmlspecialchars() |

---

**Cập nhật lần cuối:** 07/01/2026
**Phiên bản:** 1.2
