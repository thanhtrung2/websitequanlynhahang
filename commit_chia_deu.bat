@echo off
chcp 65001 >nul
echo ========================================
echo CHIA COMMIT DEU CHO 3 THANH VIEN
echo ========================================

REM Thanh vien 1: thanhtrung2 - tn67923@gmail.com
REM Thanh vien 2: minhthuttc - nguyenthutv.220403@gmail.com  
REM Thanh vien 3: vinhphamquang - vphamquang539@gmail.com

echo.
echo [1/3] Commit cua thanhtrung2...
git add README.md
git add admin/dashboard.php
git add admin/get_hoa_don_detail.php
git add admin/quan_ly_ban.php
git add admin/quan_ly_dat_ban.php
git add admin/quan_ly_doanh_thu.php
git add admin/quan_ly_hoa_don.php
git add admin/quan_ly_mon_an.php
git add admin/quan_ly_nhan_vien.php
git add admin/register.php
git add admin/includes/
git add admin/quan_ly_lien_he.php
git add admin/upload_image.php
git add admin/uploads/
git commit --author="thanhtrung2 <tn67923@gmail.com>" -m "feat: Quan ly admin - dashboard, ban, dat ban, doanh thu, hoa don, mon an, nhan vien, lien he"

echo.
echo [2/3] Commit cua minhthuttc...
git add public/add_to_order.php
git add public/customer_dashboard.php
git add public/customer_login.php
git add public/customer_profile.php
git add public/customer_register.php
git add public/dat_ban.php
git add public/gio_hang.php
git add public/index.php
git add public/lich_su_don_hang.php
git add public/thanh_toan.php
git add public/get_cart.php
git add public/includes/
git add public/lien_he.php
git add public/remove_cart.php
git add public/thong_bao.php
git commit --author="minhthuttc <nguyenthutv.220403@gmail.com>" -m "feat: Giao dien khach hang - dang nhap, dang ky, gio hang, thanh toan, dat ban, lien he, thong bao"

echo.
echo [3/3] Commit cua vinhphamquang...
git add public/thuc_don.php
git add public/update_cart.php
git add assets/
git add config/helpers.php
git add database/create_lien_he.sql
git add database/create_thong_bao.sql
git add database/them_mon_an.sql
git add database/them_mon_an_moi.sql
git add database/update_chuc_vu.sql
git add database/update_mo_ta_mon_an.sql
git add setup_lien_he.php
git add setup_mo_ta.php
git add setup_mon_an.php
git add setup_them_mon_an.php
git commit --author="vinhphamquang <vphamquang539@gmail.com>" -m "feat: Database, setup scripts, thuc don, assets va config"

echo.
echo ========================================
echo HOAN THANH! Kiem tra bang: git log --oneline -3
echo ========================================
git log --oneline -3

echo.
echo De push len GitHub: git push origin main
pause
