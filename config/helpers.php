<?php
/**
 * Helper functions cho ứng dụng
 */

/**
 * Lấy đường dẫn ảnh đúng dựa trên vị trí file gọi
 * @param string $imagePath Đường dẫn ảnh từ database
 * @param string $callerDir Thư mục của file gọi ('public' hoặc 'admin')
 * @return string Đường dẫn ảnh đã xử lý
 */
function getImagePath($imagePath, $callerDir = 'public') {
    if (empty($imagePath)) {
        return '';
    }
    
    // Nếu là URL đầy đủ (http/https)
    if (strpos($imagePath, 'http://') === 0 || strpos($imagePath, 'https://') === 0) {
        return $imagePath;
    }
    
    // Nếu là đường dẫn upload từ admin (uploads/...)
    if (strpos($imagePath, 'uploads/') === 0) {
        if ($callerDir === 'public') {
            return '../admin/' . $imagePath;
        } else {
            return $imagePath; // Đã ở trong admin
        }
    }
    
    // Nếu là đường dẫn images cũ
    if (strpos($imagePath, 'images/') === 0) {
        if ($callerDir === 'public') {
            return '../' . $imagePath;
        } else {
            return '../' . $imagePath;
        }
    }
    
    // Trả về nguyên gốc
    return $imagePath;
}
