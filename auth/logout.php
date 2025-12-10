<?php
session_start();

// Kiểm tra loại người dùng
$type = $_GET['type'] ?? 'admin';

// Hủy tất cả các biến session
$_SESSION = array();

// Nếu muốn hủy session hoàn toàn, hãy xóa cả cookie session.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Cuối cùng, hủy session
session_destroy();

// Kiểm tra redirect
$redirect = $_GET['redirect'] ?? '';

// Chuyển hướng
if ($redirect === 'home') {
    header("Location: ../public/index.php");
} else if ($type === 'customer') {
    header("Location: ../public/customer_login.php");
} else {
    header("Location: ../admin/admin_login.php");
}
exit();
?>