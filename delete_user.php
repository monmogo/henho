<?php
session_start();
require_once 'config.db.php';

// Kiểm tra quyền admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

if (isset($_GET['id'])) {
    $user_id = (int)$_GET['id'];

    $sql = "DELETE FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $user_id);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Xóa người dùng thành công!";
    } else {
        $_SESSION['error'] = "Đã xảy ra lỗi khi xóa người dùng.";
    }
}
header('Location: admin_dashboard.php');
exit();
?>
