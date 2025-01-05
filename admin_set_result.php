<?php
session_start();
require_once 'config.db.php';

// Kiểm tra quyền admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die(json_encode(["status" => "error", "message" => "Bạn không có quyền truy cập!"]));
}

// CSRF Token để bảo vệ form
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Lấy danh sách games
$sql = "SELECT id, name FROM vote_games";
$games = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);

// Xử lý khi admin gửi dữ liệu
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json'); // Fix lỗi AJAX
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        echo json_encode(["status" => "error", "message" => "CSRF Token không hợp lệ!"]);
        exit();
    }

    $game_id = intval($_POST['game_id']);
    $round_number = intval($_POST['round_number']);
    $correct_choice = $_POST['correct_choice'];
    $admin_id = $_SESSION['user_id'];

    if (!in_array($correct_choice, ['A', 'B', 'C', 'D'])) {
        echo json_encode(["status" => "error", "message" => "Lựa chọn không hợp lệ!"]);
        exit();
    }

    // Kiểm tra xem kỳ quay này đã có kết quả chưa
    $stmt = $conn->prepare("SELECT id FROM admin_controls WHERE game_id = ? AND round_number = ?");
    $stmt->bind_param("ii", $game_id, $round_number);
    $stmt->execute();
    $existing = $stmt->get_result()->fetch_assoc();

    if ($existing) {
        echo json_encode(["status" => "error", "message" => "Kết quả đã tồn tại cho kỳ quay này!"]);
        exit();
    }

    // Lưu kết quả vào database
    $stmt = $conn->prepare("INSERT INTO admin_controls (game_id, round_number, correct_choice, admin_id) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iisi", $game_id, $round_number, $correct_choice, $admin_id);
    
    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Đã lưu kết quả thành công!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Lỗi khi lưu kết quả!"]);
    }
    exit();
}

// Lấy danh sách lịch sử đặt kết quả
$sql = "SELECT ac.id, ac.game_id, vg.name AS game_name, ac.round_number, ac.correct_choice, ac.created_at 
        FROM admin_controls ac 
        JOIN vote_games vg ON ac.game_id = vg.id 
        ORDER BY ac.created_at DESC";
$results = $conn->query($sql)->fetch_all(MYSQLI_ASSOC);
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
    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token']; ?>">

    <label>Chọn Game:</label>
    <select name="game_id" id="game_id" required>
        <?php foreach ($games as $game): ?>
            <option value="<?= $game['id'] ?>"><?= htmlspecialchars($game['name']) ?></option>
        <?php endforeach; ?>
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
    <?php foreach ($results as $result): ?>
        <tr>
            <td><?= $result['id'] ?></td>
            <td><?= htmlspecialchars($result['game_name']) ?></td>
            <td><?= $result['round_number'] ?></td>
            <td><?= htmlspecialchars($result['correct_choice']) ?></td>
            <td><?= $result['created_at'] ?></td>
        </tr>
    <?php endforeach; ?>
</table>

<script>
$(document).ready(function() {
    // Tự động lấy kỳ quay mới nhất khi chọn game
    $("#game_id").change(function() {
        let gameId = $(this).val();
        
        $.get("get_latest_round.php", { game_id: gameId }, function(response) {
            if (response.latest_round) {
                $("#round_number").val(response.latest_round);
            } else {
                console.error("Lỗi khi lấy kỳ quay:", response.error);
            }
        }, "json").fail(function() {
            console.error("Lỗi kết nối đến server!");
        });
    });

    // Khi trang load, cập nhật kỳ quay cho game đầu tiên
    $("#game_id").trigger("change");

    // Gửi form qua AJAX
    $("#setResultForm").submit(function(e) {
        e.preventDefault();

        $.ajax({
            type: "POST",
            url: "admin_set_results.php",
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
