<?php
session_start();
require_once 'config.db.php';

header("Content-Type: application/json");

// Kiểm tra quyền admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Bạn không có quyền thực hiện hành động này!']);
    exit();
}

// Nhận tham số trang hiện tại và số lượng dòng trên mỗi trang
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Đếm tổng số giao dịch
$countQuery = "SELECT COUNT(*) as total FROM transaction_history WHERE transaction_type = 'deposit'";
$countResult = $conn->query($countQuery);
$totalRows = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $limit);

// Lấy danh sách giao dịch theo phân trang
$sql = "SELECT u.username, th.amount, th.transaction_date 
        FROM transaction_history th
        JOIN users u ON th.user_id = u.id
        WHERE th.transaction_type = 'deposit'
        ORDER BY th.transaction_date DESC
        LIMIT ? OFFSET ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();

$history = [];
while ($row = $result->fetch_assoc()) {
    $history[] = $row;
}

// Trả về dữ liệu JSON bao gồm tổng số trang
echo json_encode([
    'history' => $history,
    'total_pages' => $totalPages,
    'current_page' => $page
]);
?>
