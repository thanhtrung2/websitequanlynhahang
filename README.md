# HỆ THỐNG QUẢN LÝ NHÀ HÀNG

## 📋 TỔNG QUAN DỰ ÁN

Hệ thống quản lý nhà hàng được xây dựng bằng PHP, MySQL với các chức năng quản lý nhân viên, món ăn, bàn ăn, hóa đơn và doanh thu. Hệ thống hỗ trợ 2 vai trò: **Quản lý** (Admin/Nhân viên) và **Khách hàng**.

---

## 📁 CẤU TRÚC THỦ MUC

```
BTHB3/
├── admin/                          # Module quản lý (Admin)
│   ├── admin_login.php            # Đăng nhập quản lý
│   ├── dashboard.php              # Dashboard quản lý
│   ├── quan_ly_mon_an.php         # Quản lý món ăn
│   ├── quan_ly_ban.php            # Quản lý bàn ăn
│   ├── quan_ly_nhan_vien.php      # Quản lý nhân viên
│   ├── quan_ly_hoa_don.php        # Quản lý hóa đơn
│   └── quan_ly_doanh_thu.php      # Báo cáo doanh thu
│
├── public/                         # Module công khai (Khách hàng)
│   ├── index.php                  # Trang chủ
│   ├── customer_login.php         # Đăng nhập khách hàng
│   ├── customer_register.php      # Đăng ký khách hàng
│   └── customer_dashboard.php     # Dashboard khách hàng
│
├── auth/                          # Xác thực và phân quyền
│   ├── handle_register.php        # Xử lý đăng ký
│   └── logout.php                 # Đăng xuất
│
├── config/                        # Cấu hình hệ thống
│   └── db.php                     # Kết nối database
│
├── database/                      # Database SQL
│   └── quanlynhahang_db.sql      # File import database
│
├── docs/                          # Tài liệu hướng dẫn
│   ├── HUONG_DAN.md
│   ├── HUONG_DAN_SU_DUNG.md
│   ├── HUONG_DAN_DANG_KY_DANG_NHAP.md
│   ├── HUONG_DAN_SUA_LOI_DANG_NHAP.md
│   └── README_PHAN_QUYEN.md
│
├── dashboard_project.php          # 🎯 Dashboard điều hướng dự án
├── test_connection.php            # 🔍 Kiểm tra kết nối database
├── HUONG_DAN_CHAY.md             # 📖 Hướng dẫn chạy chi tiết
└── README.md                      # 📄 File này
```

---

## 🚀 CÀI ĐẶT VÀ CHẠY DỰ ÁN

### 1. YÊU CẦU HỆ THỐNG

- **Web Server:** XAMPP, Laragon, WAMP (Apache)
- **PHP:** >= 7.4
- **MySQL:** >= 5.7
- **Trình duyệt:** Chrome, Edge (phiên bản mới)

### 2. CÀI ĐẶT DATABASE

#### Bước 1: Tạo Database
1. Mở **phpMyAdmin**: `http://localhost/phpmyadmin`
2. Click **New** để tạo database mới
3. Tên database: `quanlynhahang_db`
4. Collation: `utf8mb4_vietnamese_ci`

#### Bước 2: Import Database
1. Chọn database `quanlynhahang_db` vừa tạo
2. Click tab **Import**
3. Chọn file: `database/quanlynhahang_db.sql`
4. Click **Go** để import

> File SQL đã được cấu hình sẵn lệnh `CREATE DATABASE` và `USE`, có thể import trực tiếp mà không cần chọn database trước.

### 3. CẤU HÌNH KẾT NỐI DATABASE

Mở file `config/db.php` và cập nhật thông tin:

```php
$host = 'localhost';        // Host MySQL
$dbname = 'quanlynhahang_db'; // Tên database
$username = 'root';          // Username MySQL
$password = '';              // Mật khẩu MySQL (mặc định rỗng)
```

### 4. CHẠY ỨNG DỤNG

1. Copy thư mục `BTHB3` vào thư mục:
   - **XAMPP**: `C:\xampp\htdocs\`
   - **Laragon**: `C:\laragon\www\`
   
2. Khởi động **Apache** và **MySQL**

3. **Kiểm tra kết nối (BẮT BUỘC):**
   - Truy cập: `http://localhost/BTHB3/test_connection.php`
   - Kiểm tra tất cả bảng đã được tạo thành công

4. **Mở Dashboard Dự Án:**
   - Truy cập: `http://localhost/BTHB3/dashboard_project.php`
   - Dashboard này cho phép điều hướng nhanh đến tất cả trang

5. **Hoặc truy cập trực tiếp:**
   - **Trang chủ**: `http://localhost/BTHB3/public/index.php`
   - **Đăng nhập Admin**: `http://localhost/BTHB3/admin/admin_login.php`
   - **Đăng nhập Khách hàng**: `http://localhost/BTHB3/public/customer_login.php`

---

## 👤 TÀI KHOẢN MẶC ĐỊNH

### 🔐 Tài khoản ADMIN (Cao nhất - Ưu tiên dùng)

#### Cách lấy tài khoản Admin:

**Bước 1: Tạo tài khoản Admin**
- Truy cập: `http://localhost/BTHB3/create_admin_simple.php`
- Hoặc: `http://localhost/BTHB3/create_admin.php`

**Bước 2: Lưu thông tin đăng nhập**

Sau khi trang load xong, bạn sẽ thấy bảng thông tin:

| Thông tin | Giá trị |
|-----------|---------|
| **Mã nhân viên** | `5` (hoặc số khác) |
| **Mật khẩu** | `admin123` |
| **Họ tên** | ADMIN - Quản Trị Viên |
| **SĐT** | 0999999999 |

> ⚠️ **QUAN TRỌNG:** Hãy lưu lại **MÃ NHÂN VIÊN** (số màu hồng) để đăng nhập!

**Bước 3: Đăng nhập**
- Truy cập: `http://localhost/BTHB3/admin/admin_login.php`
- Nhập:
  - **Mã nhân viên:** `5` (số vừa lưu ở Bước 2)
  - **Mật khẩu:** `admin123`
- Click **Đăng nhập**

#### Thông tin tài khoản Admin:
- **Mã nhân viên:** (Xem trong `create_admin_simple.php`)
- **Mật khẩu:** `admin123`
- **Họ tên:** ADMIN - Quản Trị Viên
- **Số điện thoại:** 0999999999
- **Email:** admin@nhahang.com
- **Chức vụ:** Quản lý (Quyền cao nhất)

### Tài khoản Quản lý (Admin)
Hệ thống có sẵn 4 nhân viên trong database (Mật khẩu mặc định: `admin123`):

| Mã NV | Tên | Chức vụ | Đăng nhập | Mật khẩu |
|-------|-----|---------|-----------|----------|
| 1 | Nguyễn Văn An | Quản lý | Mã NV: **1** | admin123 |
| 2 | Trần Thị Bình | Nhân viên PV | Mã NV: **2** | admin123 |
| 3 | Lê Văn Cường | Đầu bếp | Mã NV: **3** | admin123 |
| 4 | Phạm Thị Dung | Thu ngân | Mã NV: **4** | admin123 |

> ⚠️ **Lưu ý:** Cần chạy `create_admin.php` hoặc `database/migrate_add_matkhau.php` để thêm cột MatKhau và cập nhật mật khẩu cho các tài khoản này.

### Tài khoản Khách hàng
Database có sẵn 2 khách hàng:

| Mã KH | Tên | SĐT | Đăng nhập |
|-------|-----|-----|-----------|
| 1 | Trần Văn Khách | 0988776655 | Dùng SĐT: **0988776655** |
| 2 | Nguyễn Thị Quý | 0911223344 | Dùng SĐT: **0911223344** |

---

## 💡 CHỨC NĂNG CHÍNH

### 🔐 HỆ THỐNG PHÂN QUYỀN

#### 1. VAI TRÒ QUẢN LÝ (Admin)
**Đăng nhập:** `admin/admin_login.php`
- Sử dụng Mã nhân viên (MaNV) để đăng nhập

**Chức năng:**
- ✅ Quản lý nhân viên (thêm, xem danh sách)
- ✅ Quản lý món ăn (thêm, sửa, xóa)
- ✅ Quản lý bàn ăn (xem trạng thái)
- ✅ Quản lý hóa đơn (xem tất cả hóa đơn)
- ✅ Báo cáo doanh thu (hôm nay, tháng, năm)

#### 2. VAI TRÒ KHÁCH HÀNG (Customer)
**Đăng nhập:** `public/customer_login.php`
- Sử dụng Số điện thoại (SDT) để đăng nhập

**Chức năng:**
- ✅ Xem thông tin cá nhân
- ✅ Xem thực đơn
- 🚧 Đặt bàn (đang phát triển)
- 🚧 Đặt món (đang phát triển)
- 🚧 Thanh toán (đang phát triển)

---

## 🗄️ CẤU TRÚC DATABASE

### Các bảng chính:

1. **nhan_vien** - Thông tin nhân viên
2. **khach_hang** - Thông tin khách hàng
3. **mon_an** - Danh sách món ăn
4. **ban_an** - Danh sách bàn ăn
5. **hoa_don** - Hóa đơn bán hàng
6. **chi_tiet_hoa_don** - Chi tiết món trong hóa đơn
7. **chuc_vu** - Danh mục chức vụ
8. **danh_muc_mon_an** - Danh mục món ăn
9. **dat_ban** - Đặt bàn trước
10. **tai_khoan** - Tài khoản đăng nhập

### Sơ đồ quan hệ:
```
nhan_vien (1) ----< (n) hoa_don (n) >---- (1) khach_hang
                           |
                           | (1)
                           |
                        (n) chi_tiet_hoa_don (n) >---- (1) mon_an
```

---

## 🔧 CẤU HÌNH NÂNG CAO

### Đường dẫn tuyệt đối
Hệ thống sử dụng `__DIR__` để tham chiếu file:

```php
// Trong admin/dashboard.php
require_once __DIR__ . '/../config/db.php';

// Trong public/index.php  
require_once __DIR__ . '/../config/db.php';
```

### Session Management
- Session được bắt đầu ở đầu mỗi file PHP
- Kiểm tra đăng nhập: `$_SESSION['user_id']` (Admin) hoặc `$_SESSION['customer_id']` (Customer)
- Đăng xuất: `auth/logout.php?type=customer` hoặc `auth/logout.php?type=admin`

---

## 🛡️ BẢO MẬT

- ✅ Mật khẩu được mã hóa bằng `password_hash()` (bcrypt)
- ✅ Sử dụng **PDO Prepared Statements** để chống SQL Injection
- ✅ Kiểm tra session trước khi truy cập trang quản lý
- ✅ Validate dữ liệu đầu vào
- ⚠️ **Chưa có**: CSRF protection, Rate limiting, XSS protection

---

## 📝 HƯỚNG DẪN SỬ DỤNG

### Lấy tài khoản Admin lần đầu:
1. Truy cập: `http://localhost/BTHB3/create_admin_simple.php`
2. Xem và lưu lại **Mã nhân viên** (số màu vàng trong bảng)
3. Truy cập: `http://localhost/BTHB3/admin/admin_login.php`
4. Đăng nhập với:
   - Mã nhân viên: (Số vừa lưu)
   - Mật khẩu: `admin123`

### Đăng nhập quản lý lần đầu:
1. Truy cập: `http://localhost/BTHB3/admin/admin_login.php`
2. Nhập Mã nhân viên: `5` (hoặc số từ create_admin_simple.php)
3. Nhập mật khẩu: `admin123`
4. Click **Đăng nhập**

### Đăng ký khách hàng mới:
1. Truy cập: `http://localhost/BTHB3/public/customer_register.php`
2. Điền đầy đủ thông tin
3. Click **Đăng ký**
4. Sử dụng SĐT để đăng nhập

### Thêm món ăn mới:
1. Đăng nhập quản lý
2. Vào **Quản lý món ăn**
3. Click **Thêm món mới**
4. Điền thông tin và lưu

---

## 🐛 XỬ LÝ LỖI THƯỜNG GẶP

### Lỗi: "No database selected"
**Nguyên nhân:** Chưa chọn database trong phpMyAdmin  
**Giải pháp:** Import lại file SQL, file đã có lệnh `USE quanlynhahang_db`

### Lỗi: "Access denied for user 'root'@'localhost'"
**Nguyên nhân:** Sai mật khẩu MySQL  
**Giải pháp:** Kiểm tra và sửa mật khẩu trong `config/db.php`

### Lỗi: "Call to undefined function password_hash()"
**Nguyên nhân:** PHP version < 5.5  
**Giải pháp:** Nâng cấp PHP lên >= 7.4

### Lỗi: "require_once: failed to open stream"
**Nguyên nhân:** Đường dẫn file sai  
**Giải pháp:** Kiểm tra lại cấu trúc thư mục

---

## 📚 TÀI LIỆU THAM KHẢO

- [Hướng dẫn cài đặt chi tiết](docs/HUONG_DAN.md)
- [Hướng dẫn sử dụng](docs/HUONG_DAN_SU_DUNG.md)
- [Phân quyền hệ thống](docs/README_PHAN_QUYEN.md)
- [Đăng ký/Đăng nhập](docs/HUONG_DAN_DANG_KY_DANG_NHAP.md)
- [Sửa lỗi đăng nhập](docs/HUONG_DAN_SUA_LOI_DANG_NHAP.md)

---

## 🔄 PHIÊN BẢN

**Version 1.0** (Nov 2025)
- ✅ Hoàn thành module quản lý
- ✅ Hoàn thành đăng nhập/đăng ký
- ✅ Cấu trúc database hoàn chỉnh
- ✅ Tổ chức lại cấu trúc thư mục
- 🚧 Đang phát triển: Module đặt bàn, đặt món cho khách hàng

---

## 👨‍💻 ĐÓNG GÓP

Nếu bạn muốn đóng góp vào dự án:
1. Fork repository
2. Tạo branch mới: `git checkout -b feature/amazing-feature`
3. Commit changes: `git commit -m 'Add some amazing feature'`
4. Push to branch: `git push origin feature/amazing-feature`
5. Mở Pull Request

---

## 📞 HỖ TRỢ

Nếu gặp vấn đề, vui lòng:
1. Kiểm tra file `docs/HUONG_DAN_SUA_LOI_DANG_NHAP.md`
2. Xem lại cấu hình trong `config/db.php`
3. Kiểm tra log lỗi PHP trong XAMPP/Laragon

---

## 📄 LICENSE

Dự án này được phát triển cho mục đích học tập và nghiên cứu.

---

**Cập nhật lần cuối:** 29/11/2025  
**Phát triển bởi:** BTHB3 Team
#   w e b s i t e q u a n l y n h a h a n g  
 