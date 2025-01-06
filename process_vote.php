<?php
session_start();
require_once 'config.db.php';

if (!isset($_SESSION['user_id'])) {
    die("Vui lòng đăng nhập!");
}

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents("php://input"), true);

$game_id = intval($data['game_id']);
$choices = $data['choices'];
$bet_amount = intval($data['betAmount']);

if (empty($choices) || $bet_amount <= 0 || $game_id <= 0) {
    die("Lựa chọn không hợp lệ!");
}

// Lấy điểm user
$sql = "SELECT points FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user || $user['points'] < $bet_amount) {
    die("Không đủ điểm cược!");
}

// Lấy đáp án đúng từ session
$correct_choices = $_SESSION['correct_choices'];
$correct_str = implode(",", $correct_choices);
$selected_str = implode(",", $choices);

// Kiểm tra số lượng đáp án đúng user chọn
$correct_count = count(array_intersect($choices, $correct_choices));
$total_choices = count($choices);

$result = "Thua";
$profit = -$bet_amount; // Mặc định mất toàn bộ điểm nếu sai hết

if ($total_choices == 4) {
    // Nếu chọn cả 4 đáp án -> Hòa (mất 10% phí cược)
    $result = "Hoà";
    $profit = -0.1 * $bet_amount;
} elseif ($correct_count > 0) {
    // Nếu có ít nhất 1 đáp án đúng -> Thắng (nhận 20% lợi nhuận trên tổng cược)
    $result = "Thắng";
    $profit = $bet_amount * 0.2;
}

// Cập nhật điểm user
$new_points = $user['points'] + $profit;
$sql = "UPDATE users SET points = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $new_points, $user_id);
$stmt->execute();

// Lưu kết quả vào bảng vote_results
$sql = "INSERT INTO vote_results (user_id, game_id, choice, bet_amount, correct_choice, result, profit) 
        VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iissssi", $user_id, $game_id, $selected_str, $bet_amount, $correct_str, $result, $profit);
$stmt->execute();

// Lưu lịch sử lượt quay của game
$round_number = isset($_SESSION['round']) ? $_SESSION['round'] : 1;
$sql = "INSERT INTO game_rounds_history (game_id, round_number, correct_choices) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iis", $game_id, $round_number, $correct_str);
$stmt->execute();

$response = [
    "status" => "success",
    "message" => "Bạn đã chọn: $selected_str | Kết quả: $result | Điểm mới: " . number_format($new_points, 0, ',', '.')
];

echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>
