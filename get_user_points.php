<?php
session_start();
require_once 'config.db.php';

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Bạn chưa đăng nhập!"]);
    exit();
}

$user_id = $_SESSION['user_id'];

// Lấy điểm hiện tại
$stmt = $conn->prepare("SELECT points FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

echo json_encode(["status" => "success", "points" => $user['points']]);
exit();
