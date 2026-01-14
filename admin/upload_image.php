<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập!']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Phương thức không hợp lệ!']);
    exit();
}

if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    $errorMessages = [
        UPLOAD_ERR_INI_SIZE => 'File quá lớn (vượt quá giới hạn server)',
        UPLOAD_ERR_FORM_SIZE => 'File quá lớn (vượt quá giới hạn form)',
        UPLOAD_ERR_PARTIAL => 'File chỉ được upload một phần',
        UPLOAD_ERR_NO_FILE => 'Không có file nào được chọn',
        UPLOAD_ERR_NO_TMP_DIR => 'Thiếu thư mục tạm',
        UPLOAD_ERR_CANT_WRITE => 'Không thể ghi file',
        UPLOAD_ERR_EXTENSION => 'Upload bị chặn bởi extension'
    ];
    $error = $_FILES['image']['error'] ?? UPLOAD_ERR_NO_FILE;
    $message = $errorMessages[$error] ?? 'Lỗi không xác định';
    echo json_encode(['success' => false, 'message' => $message]);
    exit();
}

$file = $_FILES['image'];
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
$maxSize = 5 * 1024 * 1024; // 5MB

// Kiểm tra loại file
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!in_array($mimeType, $allowedTypes)) {
    echo json_encode(['success' => false, 'message' => 'Chỉ chấp nhận file ảnh (JPG, PNG, GIF, WEBP)!']);
    exit();
}

// Kiểm tra kích thước
if ($file['size'] > $maxSize) {
    echo json_encode(['success' => false, 'message' => 'File quá lớn! Tối đa 5MB.']);
    exit();
}

// Tạo thư mục uploads nếu chưa có
$uploadDir = __DIR__ . '/uploads/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Tạo tên file unique
$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$newFileName = 'mon_an_' . time() . '_' . uniqid() . '.' . strtolower($extension);
$targetPath = $uploadDir . $newFileName;

// Di chuyển file
if (move_uploaded_file($file['tmp_name'], $targetPath)) {
    // Trả về đường dẫn tương đối để lưu vào database
    $relativePath = 'uploads/' . $newFileName;
    echo json_encode([
        'success' => true, 
        'message' => 'Upload thành công!',
        'path' => $relativePath,
        'fullPath' => '../admin/' . $relativePath
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Không thể lưu file!']);
}
