<?php
session_start();
require_once 'config.db.php';

// Kiểm tra quyền admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("Bạn không có quyền thực hiện thao tác này!");
}

$round_id = intval($_GET['id']);

// Xóa kỳ quay
$sql = "DELETE FROM admin_controls WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $round_id);

if ($stmt->execute()) {
    echo "<script>alert('Đã xóa kỳ quay thành công!'); window.location.href='admin_set_result.php';</script>";
} else {
    echo "<script>alert('Lỗi khi xóa!');</script>";
}
?>
