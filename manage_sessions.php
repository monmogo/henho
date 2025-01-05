<?php
session_start();
require_once 'config.db.php';

$game_id = $_GET['game_id'];
$sql = "SELECT * FROM vote_sessions WHERE game_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $game_id);
$stmt->execute();
$sessions = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Kỳ Quay</title>
    <link rel="stylesheet" href="css/admin_style.css">
</head>
<body>

<div class="admin-container">
    <h1>📅 Quản lý Kỳ Quay</h1>
    
    <button onclick="document.getElementById('addSessionModal').style.display='block'">➕ Thêm Kỳ Quay</button>

    <table>
        <tr>
            <th>ID</th>
            <th>Kỳ Quay</th>
            <th>Thời Gian</th>
            <th>Kết Quả</th>
            <th>Hành động</th>
        </tr>
        <?php while ($row = $sessions->fetch_assoc()) { ?>
        <tr>
            <td><?= $row['id']; ?></td>
            <td>Kỳ #<?= $row['session_round']; ?></td>
            <td><?= $row['start_time']; ?></td>
            <td><?= $row['result'] ?? "Chưa có"; ?></td>
            <td>
                <a href="vote_history.php?session_id=<?= $row['id']; ?>">📜 Lịch Sử</a> |
                <a href="process_sessions.php?delete=<?= $row['id']; ?>" onclick="return confirm('Xóa kỳ này?')">🗑️ Xóa</a>
            </td>
        </tr>
        <?php } ?>
    </table>
</div>

<!-- Modal Thêm Kỳ Quay -->
<div id="addSessionModal">
    <form action="process_sessions.php" method="POST">
        <input type="hidden" name="game_id" value="<?= $game_id; ?>">
        <button type="submit" name="action" value="add">Thêm Kỳ Quay</button>
    </form>
</div>

</body>
</html>
