<?php
// process_vote.php - Xử lý kết quả sau 120 giây
include 'config.db.php'; // Kết nối database
session_start();

$game_id = intval($_POST['game_id']);

// Đợi 120 giây trước khi xử lý kết quả
sleep(120);

// Kiểm tra nếu Admin đã đặt kết quả
$check = $conn->prepare("SELECT correct_choice FROM admin_controls WHERE game_id = ? ORDER BY created_at DESC LIMIT 1");
$check->bind_param("i", $_POST['game_id']);
$check->execute();
$res = $check->get_result();
$row = $res->fetch_assoc();

// Nếu Admin chưa đặt kết quả, chọn ngẫu nhiên
if (!$row) {
    $choices = ['A', 'B', 'C', 'D'];
    $correct_choice = $choices[array_rand($choices)];
} else {
    $correct_choice = $row['correct_choice'];
}

// Cập nhật kết quả vào `vote_results`
$update = $conn->prepare("UPDATE vote_results SET correct_choice = ?, result = IF(choice = ?, 'Thắng', 'Thua') WHERE game_id = ?");
$update->bind_param("ssi", $correct_choice, $correct_choice, $_POST['game_id']);
$update->execute();


// Cập nhật điểm số người chơi
$update_users = $conn->prepare("UPDATE users u JOIN vote_results vr ON u.id = vr.user_id SET u.points = u.points + vr.profit WHERE vr.game_id = ?");
$update_users->bind_param("i", $game_id);
$update_users->execute();

echo json_encode(["status" => "success", "message" => "Kết quả đã được công bố."]);
?>