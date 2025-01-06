<?php
session_start();
require_once 'config.db.php';

header("Content-Type: application/json");

$query = isset($_GET['query']) ? trim($_GET['query']) : '';

if ($query == '') {
    echo json_encode([]);
    exit();
}

// Lấy danh sách user khớp với tìm kiếm
$sql = "SELECT id, username, points FROM users WHERE username LIKE ? LIMIT 10";
$stmt = $conn->prepare($sql);
$search = "%" . $query . "%";
$stmt->bind_param("s", $search);
$stmt->execute();
$result = $stmt->get_result();

$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

echo json_encode($users);
?>
