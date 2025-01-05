<?php
session_start();
require_once 'config.db.php';

// Kiểm tra quyền Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("Bạn không có quyền thực hiện thao tác này!");
}

// Kiểm tra ID thông báo hợp lệ
if (!isset($_POST['id']) || !is_numeric($_POST['id'])) {
    die("Lỗi: ID thông báo không hợp lệ!");
}

$notification_id = intval($_POST['id']);

// Xóa thông báo từ database
$sql = "DELETE FROM notifications WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $notification_id);

if ($stmt->execute()) {
    echo "success";
} else {
    echo "Lỗi khi xóa thông báo: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
