<?php
session_start();
require_once 'config.db.php';

if (!isset($_SESSION['user_id'])) {
    die("Bạn chưa đăng nhập!");
}

$user_id = $_SESSION['user_id'];

$sql = "SELECT vg.name AS game_name, vr.round_number, vr.choice, vr.bet_amount, vr.correct_choice, vr.result, vr.profit 
        FROM vote_results vr
        JOIN vote_games vg ON vr.game_id = vg.id
        WHERE vr.user_id = ?
        ORDER BY vr.round_number DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$history = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
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
