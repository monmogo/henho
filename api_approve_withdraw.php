<?php
session_start();
require_once 'config.db.php';

header("Content-Type: application/json");

// Kiểm tra quyền admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Bạn không có quyền thực hiện hành động này!']);
    exit();
}

// Nhận dữ liệu từ request
$data = json_decode(file_get_contents("php://input"), true);
$transaction_id = intval($data['transaction_id']);

// Lấy thông tin giao dịch rút
$sql = "SELECT user_id, amount FROM transaction_history WHERE id = ? AND status = 'pending'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $transaction_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Giao dịch không hợp lệ hoặc đã được xử lý!']);
    exit();
}

$transaction = $result->fetch_assoc();
$user_id = $transaction['user_id'];
$amount = $transaction['amount'];

// Trừ điểm user trong bảng `users`
$updateUserSql = "UPDATE users SET points = points - ? WHERE id = ? AND points >= ?";
$stmt = $conn->prepare($updateUserSql);
$stmt->bind_param("iii", $amount, $user_id, $amount);
$stmt->execute();

// Kiểm tra xem điểm có bị trừ thành công không
if ($stmt->affected_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Không thể trừ điểm, có thể do số dư không đủ!']);
    exit();
}

// Cập nhật trạng thái giao dịch rút tiền
$updateTransactionSql = "UPDATE transaction_history SET status = 'approved' WHERE id = ?";
$stmt = $conn->prepare($updateTransactionSql);
$stmt->bind_param("i", $transaction_id);
$stmt->execute();

// Lấy số điểm mới của user
$getNewPointsSql = "SELECT points FROM users WHERE id = ?";
$stmt = $conn->prepare($getNewPointsSql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$newPoints = $stmt->get_result()->fetch_assoc()['points'];

echo json_encode(['status' => 'success', 'message' => 'Duyệt rút tiền thành công!', 'new_points' => $newPoints, 'user_id' => $user_id]);
?>
