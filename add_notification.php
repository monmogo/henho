<?php
session_start();
require_once 'config.db.php';

// Kiểm tra quyền Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("Bạn không có quyền thực hiện thao tác này!");
}

// Kiểm tra dữ liệu đầu vào
if (!isset($_POST['message']) || empty(trim($_POST['message']))) {
    die("Lỗi: Nội dung thông báo không được để trống!");
}

$message = trim($_POST['message']);

// Thêm thông báo vào database
$sql = "INSERT INTO notifications (message) VALUES (?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $message);

if ($stmt->execute()) {
    echo "success";
} else {
    echo "Lỗi khi thêm thông báo: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
