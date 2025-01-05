<?php
session_start();
require_once 'config.db.php';

header("Content-Type: application/json");

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Bạn chưa đăng nhập!']);
    exit();
}

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents("php://input"), true);
$amount = intval($data['amount']);

// Kiểm tra số điểm hợp lệ
$sql = "SELECT points FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if ($amount <= 0 || $amount > $user['points']) {
    echo json_encode(['status' => 'error', 'message' => 'Số điểm rút không hợp lệ!']);
    exit();
}

// Ghi vào lịch sử giao dịch để admin duyệt
$insert = "INSERT INTO transaction_history (user_id, transaction_type, amount, status) VALUES (?, 'withdraw', ?, 'pending')";
$stmt = $conn->prepare($insert);
$stmt->bind_param("ii", $user_id, $amount);
$stmt->execute();

echo json_encode(['status' => 'success', 'message' => 'Yêu cầu rút điểm đã gửi, vui lòng chờ duyệt!']);
?>
