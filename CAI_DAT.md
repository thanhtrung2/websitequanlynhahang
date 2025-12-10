# 🚀 HƯỚNG DẪN CÀI ĐẶT - HỆ THỐNG QUẢN LÝ NHÀ HÀNG

## ⚡ CÀI ĐẶT NHANH (3 BƯỚC)

### Bước 1: Copy dự án vào Laragon
```
Copy thư mục BTHB3 vào: C:\laragon\www\
```

### Bước 2: Import Database
1. Mở phpMyAdmin: `http://localhost/phpmyadmin`
2. Click **Import**
3. Chọn file: `BTHB3/database/quanlynhahang_db.sql`
4. Click **Go**

### Bước 3: Tạo tài khoản Admin
1. Truy cập: `http://localhost/BTHB3/create_admin_simple.php`
2. Lưu lại **MÃ NHÂN VIÊN** hiển thị (ví dụ: 5)
3. Đăng nhập tại: `http://localhost/BTHB3/admin/admin_login.php`
   - **Mã nhân viên:** (Số vừa lưu)
   - **Mật khẩu:** `admin123`

---

## 🔐 THÔNG TIN ĐĂNG NHẬP

### Tài khoản Admin (Sau khi chạy create_admin_simple.php)
- **URL đăng nhập:** `http://localhost/BTHB3/admin/admin_login.php`
- **Mã nhân viên:** (Xem trong create_admin_simple.php)
- **Mật khẩu:** `admin123`

### Tài khoản Khách hàng (Có sẵn)
- **URL đăng nhập:** `http://localhost/BTHB3/public/customer_login.php`
- **SĐT:** `0988776655` hoặc `0911223344`

---

## 📁 CẤU TRÚC DỰ ÁN

```
BTHB3/
├── admin/                      # Module quản lý
│   ├── admin_login.php        # Đăng nhập admin
│   ├── dashboard.php          # Trang chính
│   ├── register.php           # Đăng ký nhân viên
│   ├── quan_ly_*.php          # Các trang quản lý
│
├── public/                     # Module khách hàng
│   ├── index.php              # Trang chủ
│   ├── customer_login.php     # Đăng nhập KH
│   ├── customer_register.php  # Đăng ký KH
│
├── auth/                       # Xác thực
│   └── logout.php             # Đăng xuất
│
├── config/                     # Cấu hình
│   └── db.php                 # Kết nối database
│
├── database/                   # Database
│   ├── quanlynhahang_db.sql   # File import chính
│   └── migrate_add_matkhau.php # Thêm cột mật khẩu
│
├── create_admin_simple.php     # 🔑 Tạo admin (QUAN TRỌNG)
├── test_connection.php         # Kiểm tra kết nối
├── dashboard_project.php       # Dashboard tổng quan
└── README.md                   # File này
```

---

## 🔧 XỬ LÝ LỖI

### Lỗi kết nối database
**Chạy:** `http://localhost/BTHB3/test_connection.php`

### Lỗi "Access denied"
**Sửa mật khẩu MySQL trong:** `config/db.php` (hoặc dùng `auto_setup.php`)

### Lỗi "Table doesn't exist"
**Import lại database:** `database/quanlynhahang_db.sql`

---

## 🎯 LINKS NHANH

| Trang | URL |
|-------|-----|
| 🏠 Trang chủ | `http://localhost/BTHB3/public/index.php` |
| 🔐 Admin Login | `http://localhost/BTHB3/admin/admin_login.php` |
| 👤 Customer Login | `http://localhost/BTHB3/public/customer_login.php` |
| 🔑 Tạo Admin | `http://localhost/BTHB3/create_admin_simple.php` |
| 🔍 Test DB | `http://localhost/BTHB3/test_connection.php` |
| 📊 Dashboard | `http://localhost/BTHB3/dashboard_project.php` |

---

**Phát triển bởi:**  
**Cập nhật:**
