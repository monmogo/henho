<?php
// history.php - Hiển thị lịch sử cược của người chơi
include 'config.db.php'; // Kết nối database
session_start();

if (!isset($_SESSION['user_id'])) {
    die(json_encode(["status" => "error", "message" => "Bạn cần đăng nhập để xem lịch sử."]));
}

$user_id = $_SESSION['user_id'];

// Truy vấn lịch sử cược
$query = $conn->prepare("SELECT vr.id, vg.name AS game_name, vr.choice, vr.correct_choice, vr.bet_amount, vr.result, vr.profit, vr.created_at
                          FROM vote_results vr
                          JOIN vote_games vg ON vr.game_id = vg.id
                          WHERE vr.user_id = ?
                          ORDER BY vr.created_at DESC");
$query->bind_param("i", $user_id);
$query->execute();
$result = $query->get_result();

$history = [];
while ($row = $result->fetch_assoc()) {
    $history[] = $row;
}

echo json_encode(["status" => "success", "history" => $history]);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Lịch sử đặt cược</title>
</head>
<body>
    <h2>Lịch Sử Cược</h2>
    <table border="1">
        <tr>
            <th>Game</th>
            <th>Kỳ quay</th>
            <th>Lựa chọn</th>
            <th>Số tiền cược</th>
            <th>Kết quả</th>
            <th>Lợi nhuận</th>
        </tr>
        <?php foreach ($history as $row): ?>
            <tr>
                <td><?= htmlspecialchars($row['game_name']) ?></td>
                <td><?= $row['round_number'] ?></td>
                <td><?= $row['choice'] ?></td>
                <td><?= $row['bet_amount'] ?></td>
                <td><?= $row['result'] ?></td>
                <td><?= $row['profit'] ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
