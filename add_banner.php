<?php
session_start();
require_once 'config.db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("error: Bạn không có quyền!");
}

// Kiểm tra file có tồn tại không
if (!isset($_FILES['banner']) || $_FILES['banner']['error'] !== UPLOAD_ERR_OK) {
    die("error: Vui lòng chọn một file hợp lệ!");
}

$uploadDir = "uploads/banners/";
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$fileName = time() . "_" . basename($_FILES["banner"]["name"]);
$targetFile = $uploadDir . $fileName;
$imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

$validFormats = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
if (!in_array($imageFileType, $validFormats)) {
    die("error: Chỉ chấp nhận JPG, PNG, GIF, WEBP.");
}

if ($_FILES["banner"]["size"] > 5 * 1024 * 1024) {
    die("error: Ảnh quá lớn! Tối đa 5MB.");
}

if (!move_uploaded_file($_FILES["banner"]["tmp_name"], $targetFile)) {
    die("error: Không thể tải ảnh lên!");
}

// Thêm vào database
$sql = "INSERT INTO banners (image_url) VALUES (?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $targetFile);

if ($stmt->execute()) {
    echo "success";
} else {
    echo "error: Lỗi database - " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
