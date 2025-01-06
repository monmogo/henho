<?php
// leaderboard.php - Hiển thị bảng xếp hạng người chơi
include 'config.db.php'; // Kết nối database
session_start();

// Truy vấn bảng xếp hạng người chơi có lợi nhuận cao nhất
$query = $conn->prepare("SELECT u.id, u.username, SUM(vr.profit) AS total_profit
                          FROM users u
                          LEFT JOIN vote_results vr ON u.id = vr.user_id
                          GROUP BY u.id
                          ORDER BY total_profit DESC
                          LIMIT 10");
$query->execute();
$result = $query->get_result();

$leaderboard = [];
while ($row = $result->fetch_assoc()) {
    $leaderboard[] = $row;
}

echo json_encode(["status" => "success", "leaderboard" => $leaderboard]);
?>
