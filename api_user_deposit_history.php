<?php
session_start();
require_once 'config.db.php';

header("Content-Type: application/json");

if (!isset($_GET['username'])) {
    echo json_encode([]);
    exit();
}

$username = trim($_GET['username']);

// Tìm ID user theo username
$userQuery = $conn->prepare("SELECT id FROM users WHERE username = ?");
$userQuery->bind_param("s", $username);
$userQuery->execute();
$userResult = $userQuery->get_result();

if ($userResult->num_rows === 0) {
    echo json_encode([]);
    exit();
}

$user = $userResult->fetch_assoc();
$user_id = $user['id'];

// Lấy lịch sử nạp điểm
$sql = "SELECT u.username, th.amount, th.transaction_date 
        FROM transaction_history th
        JOIN users u ON th.user_id = u.id
        WHERE th.user_id = ? AND th.transaction_type = 'deposit'
        ORDER BY th.transaction_date DESC
        LIMIT 10";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$history = [];
while ($row = $result->fetch_assoc()) {
    $history[] = $row;
}

echo json_encode($history);
?>
