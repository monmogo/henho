<?php
// admin_set_result.php - Admin đặt kết quả thủ công
include 'config.db.php'; // Kết nối database

session_start();

if (!isset($_SESSION['admin_id']) || $_SESSION['role'] !== 'admin') {
    die(json_encode(["status" => "error", "message" => "Bạn không có quyền truy cập."]));
}

$admin_id = $_SESSION['admin_id'];

// Lấy danh sách game
$query_games = $conn->query("SELECT id, name FROM vote_games ORDER BY created_at DESC");
$games = $query_games ? $query_games->fetch_all(MYSQLI_ASSOC) : [];


// Xử lý POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die(json_encode([
        "status" => "error",
        "message" => "Phương thức không hợp lệ.",
        "debug" => $_SERVER['REQUEST_METHOD']
    ]));
}

// Kiểm tra dữ liệu đầu vào
if (empty($_POST)) {
    die(json_encode([
        "status" => "error",
        "message" => "Dữ liệu không được gửi hoặc bị chặn.",
        "debug" => $_POST
    ]));
}

// Kiểm tra nếu Admin đã đặt kết quả
$check = $conn->prepare("SELECT id FROM admin_controls WHERE game_id = ? AND round_number = ?");
$check->bind_param("ii", $_POST['game_id'], $_POST['round_number']);
$check->execute();
$res = $check->get_result();

if ($res->num_rows > 0) {
    die(json_encode(["status" => "error", "message" => "Kết quả đã tồn tại."]));
}

// Lưu kết quả vào bảng `admin_controls`
$query = $conn->prepare("INSERT INTO admin_controls (game_id, round_number, correct_choice, admin_id, created_at) VALUES (?, ?, ?, ?, NOW())");
$query->bind_param("iisi", $_POST['game_id'], $_POST['round_number'], $_POST['correct_choice'], $_SESSION['admin_id']);

if ($query->execute()) {
    echo json_encode(["status" => "success", "message" => "Kết quả đã được cập nhật."]);
} else {
    echo json_encode(["status" => "error", "message" => "Lỗi khi cập nhật kết quả: " . $query->error]);
}

// Lấy lịch sử kết quả để hiển thị
$query_results = $conn->query("SELECT ac.id, vg.name AS game_name, ac.round_number, ac.correct_choice, ac.created_at FROM admin_controls ac JOIN vote_games vg ON ac.game_id = vg.id ORDER BY ac.created_at DESC");
$results = $query_results ? $query_results->fetch_all(MYSQLI_ASSOC) : [];
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Đặt Kết Quả</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>

<h2>Đặt Kết Quả Trước</h2>

<form id="setResultForm">
    <label>Chọn Game:</label>
    <select name="game_id" id="game_id" required>
        <?php if (!empty($games)): ?>
            <?php foreach ($games as $game): ?>
                <option value="<?= htmlspecialchars($game['id']) ?>"><?= htmlspecialchars($game['name']) ?></option>
            <?php endforeach; ?>
        <?php else: ?>
            <option value="">Không có game nào</option>
        <?php endif; ?>
    </select>

    <label>Kỳ Quay:</label>
    <input type="number" name="round_number" id="round_number" required readonly>

    <label>Kết Quả Đúng:</label>
    <select name="correct_choice" id="correct_choice" required>
        <option value="A">A</option>
        <option value="B">B</option>
        <option value="C">C</option>
        <option value="D">D</option>
    </select>

    <button type="submit">Lưu Kết Quả</button>
</form>

<h2>Lịch Sử Kết Quả</h2>
<table border="1">
    <tr>
        <th>ID</th>
        <th>Game</th>
        <th>Kỳ Quay</th>
        <th>Kết Quả</th>
        <th>Ngày Tạo</th>
    </tr>
    <?php if (!empty($results)): ?>
        <?php foreach ($results as $result): ?>
            <tr>
                <td><?= $result['id'] ?></td>
                <td><?= htmlspecialchars($result['game_name']) ?></td>
                <td><?= $result['round_number'] ?></td>
                <td><?= htmlspecialchars($result['correct_choice']) ?></td>
                <td><?= $result['created_at'] ?></td>
            </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr><td colspan="5">Không có kết quả nào.</td></tr>
    <?php endif; ?>
</table>

<script>
$(document).ready(function() {
    $("#game_id").change(function() {
        let gameId = $(this).val();
        if (gameId) {
            $.get("get_latest_round.php", { game_id: gameId }, function(response) {
                if (response.latest_round) {
                    $("#round_number").val(response.latest_round);
                } else {
                    console.error("Lỗi khi lấy kỳ quay:", response.error);
                }
            }, "json").fail(function() {
                console.error("Lỗi kết nối đến server!");
            });
        }
    });

    $("#game_id").trigger("change");

    $("#setResultForm").submit(function(e) {
        e.preventDefault();

        $.ajax({
            type: "POST",
            url: "admin_set_result.php",
            data: $(this).serialize(),
            dataType: "json",
            success: function(response) {
                alert(response.message);
                if (response.status === "success") location.reload();
            },
            error: function(xhr) {
                console.error("Lỗi AJAX:", xhr.responseText);
                alert("Lỗi kết nối đến server!");
            }
        });
    });
});
</script>

</body>
</html>
