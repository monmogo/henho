<?php
session_start();
require_once 'config.db.php';

// Lấy danh sách game
$sql = "SELECT * FROM vote_games ORDER BY created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản lý Game</title>
    <link rel="stylesheet" href="css/admin_style.css">
</head>
<body>

<div class="admin-container">
    <h1>🎲 Quản lý Game</h1>
    
    <button onclick="document.getElementById('addGameModal').style.display='block'">➕ Thêm Game</button>
    
    <table>
        <tr>
            <th>ID</th>
            <th>Tên Game</th>
            <th>Ảnh</th>
            <th>Ngày Tạo</th>
            <th>Hành động</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()) { ?>
        <tr>
            <td><?= $row['id']; ?></td>
            <td><?= $row['game_name']; ?></td>
            <td><img src="<?= $row['avatar']; ?>" width="50"></td>
            <td><?= $row['created_at']; ?></td>
            <td>
                <a href="manage_sessions.php?game_id=<?= $row['id']; ?>">📅 Kỳ Quay</a> |
                <a href="process_games.php?delete=<?= $row['id']; ?>" onclick="return confirm('Xóa game này?')">🗑️ Xóa</a>
            </td>
        </tr>
        <?php } ?>
    </table>
</div>

<!-- Modal Thêm Game -->
<div id="addGameModal">
    <form action="process_games.php" method="POST">
        <label>Tên Game:</label>
        <input type="text" name="game_name" required>
        <label>Ảnh Đại Diện:</label>
        <input type="text" name="avatar" required>
        <button type="submit" name="action" value="add">Thêm Game</button>
    </form>
</div>

</body>
</html>
