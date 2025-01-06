<?php
include 'config.db.php'; // Kết nối database
session_start();

header('Content-Type: application/json; charset=UTF-8');

die(json_encode([
    "status" => "debug",
    "method" => $_SERVER['REQUEST_METHOD'],
    "post_data" => $_POST,
    "raw_input" => file_get_contents("php://input")
]));

// Kiểm tra nếu user đã đăng nhập
if (!isset($_SESSION['user_id'])) {
    die(json_encode(["status" => "error", "message" => "Bạn cần đăng nhập để chơi."]));
}

$user_id = $_SESSION['user_id'];
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die(json_encode([
        "status" => "error",
        "message" => "Phương thức không hợp lệ.",
        "debug" => $_SERVER['REQUEST_METHOD']
    ]));
}

// Kiểm tra nếu $_POST rỗng
$raw_data = file_get_contents("php://input");

if (empty($_POST) && !empty($raw_data)) {
    die(json_encode([
        "status" => "error",
        "message" => "Dữ liệu bị chặn trong `$_POST`, thử đọc từ `php://input`.",
        "raw_input" => $raw_data
    ]));
}


if (!isset($_POST['game_id'], $_POST['choice'], $_POST['bet_amount'])) {
    die(json_encode(["status" => "error", "message" => "Dữ liệu gửi không hợp lệ.", "debug" => $_POST]));
}

$game_id = intval($_POST['game_id']);
$choice = $_POST['choice'];
$bet_amount = intval($_POST['bet_amount']);

// Kiểm tra giá trị hợp lệ
if (!in_array($choice, ['A', 'B', 'C', 'D']) || $bet_amount <= 0) {
    die(json_encode(["status" => "error", "message" => "Lựa chọn hoặc số tiền cược không hợp lệ.", "debug" => $_POST]));
}

// Kiểm tra xem game có tồn tại không
$check_game = $conn->prepare("SELECT id FROM vote_games WHERE id = ?");
$check_game->bind_param("i", $game_id);
$check_game->execute();
$result = $check_game->get_result();

if ($result->num_rows === 0) {
    die(json_encode(["status" => "error", "message" => "Game không tồn tại."]));
}

// Lưu cược vào bảng vote_results
$query = $conn->prepare("INSERT INTO vote_results (user_id, game_id, choice, bet_amount, created_at) VALUES (?, ?, ?, ?, NOW())");
$query->bind_param("iisi", $user_id, $game_id, $choice, $bet_amount);
if ($query->execute()) {
    echo json_encode(["status" => "success", "message" => "Cược thành công! Chờ kết quả."]);
} else {
    echo json_encode(["status" => "error", "message" => "Lỗi khi đặt cược: " . $query->error]);
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt Cược Game</title>
    <link rel="stylesheet" href="styles.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <h1>Chào, <?php echo htmlspecialchars($_SESSION['username'] ?? 'Người chơi'); ?>!</h1>
    <p>Số điểm hiện tại: <span id="points">...</span></p>
    
    <h2>Chọn Cược</h2>
    <p id="countdown">Thời gian còn lại: 120 giây</p>

    <form id="betForm">
        <div id="bet-options">
            <label><input type="radio" name="choice" value="A"> A</label>
            <label><input type="radio" name="choice" value="B"> B</label>
            <label><input type="radio" name="choice" value="C"> C</label>
            <label><input type="radio" name="choice" value="D"> D</label>
        </div>
        
        <input type="number" id="bet_amount" name="bet_amount" placeholder="Nhập số tiền cược" min="1">
        <button type="submit">Đặt Cược</button>
    </form>
    
    <p id="status"></p>

    <script>
        let countdown = 120;
        let selectedChoice = null;

        function startCountdown() {
            let timer = setInterval(() => {
                countdown--;
                document.getElementById('countdown').innerText = `Thời gian còn lại: ${countdown} giây`;
                if (countdown <= 0) {
                    clearInterval(timer);
                    document.getElementById('status').innerText = "Kết quả đang được xử lý...";
                    setTimeout(fetchResult, 3000);
                }
            }, 1000);
        }

        $("#betForm").submit(function(e) {
    e.preventDefault();

    let gameId = 1; // Cập nhật ID game thực tế
    let choice = $("input[name='choice']:checked").val();
    let betAmount = $("#bet_amount").val();

    if (!choice || betAmount <= 0) {
        alert("Vui lòng chọn cược và nhập số tiền hợp lệ");
        return;
    }

    $.ajax({
        type: "POST", // 🔥 Đảm bảo phương thức là POST
        url: "vote_game.php",
        data: { game_id: gameId, choice: choice, bet_amount: betAmount },
        dataType: "json",
        success: function(response) {
            document.getElementById('status').innerText = response.message;
            if (response.status === "success") {
                startCountdown();
            }
        },
        error: function(xhr) {
            console.error("Lỗi AJAX:", xhr.responseText);
            alert("Lỗi kết nối đến server!");
        }
    });
});


        function fetchResult() {
            fetch('process_vote.php', {
                method: 'POST',
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: new URLSearchParams({ game_id: 1 })
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('status').innerText = data.message;
                if (data.status === 'success') {
                    document.getElementById('points').innerText = data.updated_points;
                }
            });
        }
    </script>
</body>
</html>
