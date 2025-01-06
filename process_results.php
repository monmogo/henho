<?php
session_start();
require_once 'config.db.php';

header('Content-Type: application/json');

if (!isset($_GET['game_id'])) {
    echo json_encode(["status" => "error", "message" => "Thiếu game_id!"]);
    exit();
}

$game_id = intval($_GET['game_id']);
$round_number = $_SESSION['round'] ?? 1;

// Kiểm tra kết quả từ Admin trước
$sql = "SELECT correct_choice FROM admin_controls WHERE game_id = ? AND round_number = ? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $game_id, $round_number);
$stmt->execute();
$admin_result = $stmt->get_result()->fetch_assoc();

if ($admin_result) {
    $correct_choice = $admin_result['correct_choice'];
} else {
    // Nếu không có Admin đặt trước -> Chọn ngẫu nhiên
    $options = ["A", "B", "C", "D"];
    $correct_choice = $options[array_rand($options)];
}

// Lấy danh sách cược của người chơi
$sql = "SELECT vs.user_id, vs.choice, vs.bet_amount, u.points 
        FROM vote_submissions vs 
        JOIN users u ON vs.user_id = u.id 
        WHERE vs.game_id = ? AND vs.round_number = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $game_id, $round_number);
$stmt->execute();
$votes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$results = [];

foreach ($votes as $vote) {
    $user_id = $vote['user_id'];
    $choice = $vote['choice'];
    $bet_amount = $vote['bet_amount'];
    $is_correct = ($choice === $correct_choice);
    $profit = $is_correct ? $bet_amount * 2 : -$bet_amount;
    $new_points = $vote['points'] + $profit;

    // Cập nhật điểm của người chơi
    $stmt = $conn->prepare("UPDATE users SET points = ? WHERE id = ?");
    $stmt->bind_param("ii", $new_points, $user_id);
    $stmt->execute();

    // Lưu kết quả vào vote_results
    $stmt = $conn->prepare("INSERT INTO vote_results (user_id, game_id, round_number, choice, bet_amount, correct_choice, result, profit) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $result_text = $is_correct ? "Thắng" : "Thua";
    $stmt->bind_param("iiisiisi", $user_id, $game_id, $round_number, $choice, $bet_amount, $correct_choice, $result_text, $profit);
    $stmt->execute();

    $results[] = ["user_id" => $user_id, "choice" => $choice, "bet_amount" => $bet_amount, "result" => $result_text, "profit" => $profit];
}

// Xóa dữ liệu tạm thời sau khi tổng hợp
$sql = "DELETE FROM vote_submissions WHERE game_id = ? AND round_number = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $game_id, $round_number);
$stmt->execute();

echo json_encode(["status" => "success", "correct_choice" => $correct_choice, "results" => $results]);
?>
