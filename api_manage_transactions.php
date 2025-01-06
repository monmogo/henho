<?php
session_start();
require_once 'config.db.php';

header("Content-Type: application/json");

// Kiểm tra quyền admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Bạn không có quyền thực hiện hành động này.']);
    exit();
}

// Lấy dữ liệu từ request
$data = json_decode(file_get_contents("php://input"), true);
$action = $data['action'];
$user_id = intval($data['user_id']);
$admin_id = $_SESSION['user_id'];

if ($action === 'deposit') {
    $amount = floatval($data['amount']);
    if ($amount <= 0) {
        echo json_encode(['status' => 'error', 'message' => 'Số tiền phải lớn hơn 0.']);
        exit();
    }

    // Cập nhật điểm user
    $stmt = $conn->prepare("UPDATE users SET points = points + ? WHERE id = ?");
    $stmt->bind_param("di", $amount, $user_id);
    
    if ($stmt->execute()) {
        // Ghi lịch sử giao dịch
        $stmt = $conn->prepare("INSERT INTO transaction_history (user_id, admin_id, transaction_type, amount, status) VALUES (?, ?, 'deposit', ?, 'approved')");
        $stmt->bind_param("iid", $user_id, $admin_id, $amount);
        $stmt->execute();

        echo json_encode(['status' => 'success', 'message' => 'Nạp điểm thành công!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Lỗi khi nạp điểm.']);
    }

} elseif ($action === 'approve_withdraw' || $action === 'reject_withdraw') {
    $transaction_id = intval($data['transaction_id']);
    $status = ($action === 'approve_withdraw') ? 'approved' : 'rejected';

    // Cập nhật trạng thái giao dịch rút tiền
    $stmt = $conn->prepare("UPDATE transaction_history SET status = ? WHERE id = ? AND transaction_type = 'withdraw'");
    $stmt->bind_param("si", $status, $transaction_id);
    
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Cập nhật trạng thái giao dịch thành công!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Lỗi khi cập nhật trạng thái giao dịch.']);
    }
}

$stmt->close();
$conn->close();
?>
