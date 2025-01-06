<?php
require_once 'config.db.php';

$game_id = $conn->query("SELECT id FROM vote_games ORDER BY id DESC LIMIT 1")->fetch_assoc()['id'];

$results = ['A', 'B', 'C', 'D'];
$random_result = $results[array_rand($results)];
$conn->query("UPDATE vote_games SET result = '$random_result' WHERE id = $game_id");

$users = $conn->query("SELECT * FROM vote_history WHERE game_id = $game_id");

while ($user = $users->fetch_assoc()) {
    $user_id = $user['user_id'];
    $choices = explode(",", $user['choice']);
    $bet_points = $user['bet_points'];

    if (in_array($random_result, $choices)) {
        $win_points = $bet_points * 1.2;
        $conn->query("UPDATE users SET points = points + $win_points WHERE id = $user_id");
        $conn->query("UPDATE vote_history SET win = 1 WHERE id = {$user['id']}");
    }
}

$new_points = $conn->query("SELECT points FROM users WHERE id = $user_id")->fetch_assoc()['points'];
echo json_encode(["game_id" => $game_id, "result" => $random_result, "points" => $new_points]);
?>
