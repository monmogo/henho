<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once 'config.db.php';

// Lấy danh sách các vote cần xử lý (sau 120 giây)
$sql = "SELECT * FROM vote_submissions WHERE TIMESTAMPDIFF(SECOND, created_at, NOW()) >= 120";
$result = $conn->query($sql);

while ($row = $result->fetch_assoc()) {
    $user_id = $row['user_id'];
    $game_id = $row['game_id'];
    $choices = explode(",", $row['choice']);
    $bet_amount = $row['bet_amount'];

    // Lấy đáp án đúng từ session hoặc admin đặt trước
    $sql = "SELECT correct_choice FROM admin_controls WHERE game_id = ? ORDER BY created_at DESC LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $game_id);
    $stmt->execute();
    $correct_data = $stmt->get_result()->fetch_assoc();
    $correct_choice = $correct_data['correct_choice'] ?? null;

    // Xử lý kết quả
    $result = in_array($correct_choice, $choices) ? "Thắng" : "Thua";
    $profit = ($result === "Thắng") ? $bet_amount * 2 : -$bet_amount;

    // Cập nhật điểm user
    $sql = "UPDATE users SET points = points + ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $profit, $user_id);
    $stmt->execute();

    // Lưu vào bảng vote_results
    $sql = "INSERT INTO vote_results (user_id, game_id, choice, bet_amount, correct_choice, result, profit) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iissssi", $user_id, $game_id, implode(",", $choices), $bet_amount, $correct_choice, $result, $profit);
    $stmt->execute();
}

echo "Xử lý xong!";
?>
