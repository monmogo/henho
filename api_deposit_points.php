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
$username = trim($data['username']);
$amount = intval($data['amount']);

if (!$username || $amount <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'Dữ liệu không hợp lệ!']);
    exit();
}

// Tìm ID user theo username
$userQuery = $conn->prepare("SELECT id, points FROM users WHERE username = ?");
$userQuery->bind_param("s", $username);
$userQuery->execute();
$userResult = $userQuery->get_result();

if ($userResult->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Không tìm thấy người dùng!']);
    exit();
}

$user = $userResult->fetch_assoc();
$user_id = $user['id'];
$newPoints = $user['points'] + $amount;

// Cập nhật điểm cho user
$updateUserSql = "UPDATE users SET points = ? WHERE id = ?";
$updateStmt = $conn->prepare($updateUserSql);
$updateStmt->bind_param("ii", $newPoints, $user_id);
$updateStmt->execute();

// Lưu lịch sử giao dịch
$insertTransaction = "INSERT INTO transaction_history (user_id, transaction_type, amount, status) VALUES (?, 'deposit', ?, 'approved')";
$insertStmt = $conn->prepare($insertTransaction);
$insertStmt->bind_param("ii", $user_id, $amount);
$insertStmt->execute();

echo json_encode(['status' => 'success', 'message' => 'Nạp điểm thành công!', 'new_points' => $newPoints]);
?>
