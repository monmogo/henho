<?php
// process_vote.php - Xử lý kết quả sau 120 giây
include 'config.db.php';
session_start();

header('Content-Type: application/json; charset=UTF-8');

// Kiểm tra nếu request là POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die(json_encode([
        "status" => "error",
        "message" => "Phương thức không hợp lệ.",
        "debug" => $_SERVER['REQUEST_METHOD']
    ]));
}

// Kiểm tra dữ liệu gửi từ AJAX
if (!isset($_POST['game_id'])) {
    die(json_encode(["status" => "error", "message" => "Dữ liệu không hợp lệ."]));
}

$game_id = intval($_POST['game_id']);

// Kiểm tra nếu game tồn tại
$check_game = $conn->prepare("SELECT id FROM vote_games WHERE id = ?");
$check_game->bind_param("i", $game_id);
$check_game->execute();
$result = $check_game->get_result();

if ($result->num_rows === 0) {
    die(json_encode(["status" => "error", "message" => "Game không tồn tại."]));
}

// Kiểm tra nếu Admin đã đặt kết quả
$check_admin = $conn->prepare("SELECT correct_choice FROM admin_controls WHERE game_id = ? ORDER BY created_at DESC LIMIT 1");
$check_admin->bind_param("i", $game_id);
$check_admin->execute();
$res_admin = $check_admin->get_result();
$admin_result = $res_admin->fetch_assoc();

// Nếu Admin chưa đặt kết quả, random một kết quả
if (!$admin_result) {
    $choices = ['A', 'B', 'C', 'D'];
    $correct_choice = $choices[array_rand($choices)];
} else {
    $correct_choice = $admin_result['correct_choice'];
}

// Cập nhật kết quả vào `vote_results`
$update_results = $conn->prepare("
    UPDATE vote_results 
    SET correct_choice = ?, 
        result = IF(choice = ?, 'Thắng', 'Thua'), 
        profit = IF(choice = ?, bet_amount * 1.2, -bet_amount) 
    WHERE game_id = ?
");
$update_results->bind_param("sssi", $correct_choice, $correct_choice, $correct_choice, $game_id);
$update_results->execute();

// Lấy danh sách người chơi và cập nhật điểm
$get_results = $conn->prepare("SELECT user_id, result, profit FROM vote_results WHERE game_id = ?");
$get_results->bind_param("i", $game_id);
$get_results->execute();
$res_results = $get_results->get_result();

while ($row = $res_results->fetch_assoc()) {
    $update_points = $conn->prepare("UPDATE users SET points = points + ? WHERE id = ?");
    $update_points->bind_param("ii", $row['profit'], $row['user_id']);
    $update_points->execute();
}

echo json_encode([
    "status" => "success",
    "message" => "Kết quả đã được cập nhật.",
    "correct_choice" => $correct_choice
]);
?>
