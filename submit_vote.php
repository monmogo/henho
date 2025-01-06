<?php
session_start();
require_once 'config.db.php';

$user_id = $_SESSION['user_id'];
$choices = $_POST['choice'];
$bet_points = intval($_POST['bet_points']);

if (empty($choices)) {
    echo json_encode(["message" => "Bạn phải chọn ít nhất một đáp án!", "points" => 0]);
    exit();
}

$choice_str = implode(",", $choices);
$total_bet = count($choices) == 4 ? $bet_points * 0.9 : $bet_points;

// Lưu bình chọn
$game_id = $conn->query("SELECT id FROM vote_games ORDER BY id DESC LIMIT 1")->fetch_assoc()['id'];
$conn->query("INSERT INTO vote_history (game_id, user_id, choice, bet_points) VALUES ($game_id, $user_id, '$choice_str', $bet_points)");

// Trừ điểm cược
$conn->query("UPDATE users SET points = points - $total_bet WHERE id = $user_id");

$new_points = $conn->query("SELECT points FROM users WHERE id = $user_id")->fetch_assoc()['points'];
echo json_encode(["message" => "Đặt cược thành công!", "points" => $new_points]);
?>
