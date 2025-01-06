<?php
session_start();
require_once 'config.db.php';

header('Content-Type: application/json');

if (!isset($_GET['game_id'])) {
    echo json_encode(["error" => "Thiếu game_id!"]);
    exit();
}

$game_id = intval($_GET['game_id']);

// Lấy số kỳ quay mới nhất từ `vote_submissions`
$stmt = $conn->prepare("SELECT MAX(round_number) AS latest_round FROM vote_submissions WHERE game_id = ?");
$stmt->bind_param("i", $game_id);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$latest_round = isset($result['latest_round']) ? ($result['latest_round'] + 1) : 1; // Nếu chưa có, mặc định là 1

echo json_encode(["latest_round" => $latest_round]);
?>
