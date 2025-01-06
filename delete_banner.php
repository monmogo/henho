<?php
session_start();
require_once 'config.db.php';

// Kiểm tra quyền Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("Bạn không có quyền thực hiện thao tác này!");
}

// Kiểm tra ID banner hợp lệ
if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    die("Lỗi: ID banner không hợp lệ!");
}

$banner_id = intval($_POST['id']);

// Lấy đường dẫn ảnh để xóa
$sql = "SELECT image_url FROM banners WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $banner_id);
$stmt->execute();
$stmt->bind_result($image_url);
$stmt->fetch();
$stmt->close();

// Xóa banner từ database
$sql = "DELETE FROM banners WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $banner_id);

if ($stmt->execute()) {
    // Xóa file ảnh trên server nếu tồn tại
    if (!empty($image_url) && file_exists($image_url)) {
        unlink($image_url);
    }
    echo "success";
} else {
    echo "Lỗi khi xóa banner: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
