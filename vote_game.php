<?php
session_start();
require_once 'config.db.php';

// Kiểm tra game_id hợp lệ
if (!isset($_GET['game_id'])) {
    die("Game không hợp lệ!");
}

$game_id = intval($_GET['game_id']);

// Lấy thông tin game từ CSDL
$sql = "SELECT name, cover_image FROM vote_games WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $game_id);
$stmt->execute();
$result = $stmt->get_result();
$game = $result->fetch_assoc();

if (!$game) {
    die("Game không tồn tại!");
}

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Lấy thông tin user
$sql = "SELECT username, points FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Lấy số kỳ quay hiện tại
$current_round = isset($_SESSION['round']) ? $_SESSION['round'] : 1;

// Reset game mỗi 15 giây
if (!isset($_SESSION['last_update']) || time() - $_SESSION['last_update'] >= 15) {
    $options = ["A", "B", "C", "D"];
    shuffle($options);
    $_SESSION['correct_choices'] = array_slice($options, 0, 2);
    $_SESSION['last_update'] = time();
    $_SESSION['round']++;
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($game['name']); ?> - Game Bình Chọn</title>
    <link rel="stylesheet" href="css/vote_style.css">
    <link rel="stylesheet" href="css/tabbar.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">


</head>
<body>

<div class="game-container">
    <!-- Header -->
    <div class="header">
        <button class="back-btn" onclick="history.back()">←</button>
        <h2><?= htmlspecialchars($game['name']); ?></h2>
    </div>

    <!-- Game Cover -->
    <div class="game-cover-container">
        <img src="<?= htmlspecialchars($game['cover_image']); ?>" class="game-cover">
    </div>

    <!-- Timer -->
    <div class="timer">
        ⏳ Thời gian còn lại: <span id="countdown">15</span> giây
    </div>

    <!-- Vote Grid -->
    <div class="vote-grid">
        <button class="vote-btn" data-choice="A">A</button>
        <button class="vote-btn" data-choice="B">B</button>
        <button class="vote-btn" data-choice="C">C</button>
        <button class="vote-btn" data-choice="D">D</button>
    </div>

    <!-- Betting Panel -->
    <div class="betting-panel">
        <p>Lựa chọn: <span id="selectedChoices">-</span></p>
        <label>Số điểm cược mỗi ô:</label>
        <input type="number" id="betAmount" placeholder="Nhập điểm" min="1">
        <p>Tổng cược: <span id="totalBet">0</span></p>
    </div>

    <!-- Vote Button -->
    <button id="submitVote" class="vote-btn-submit">✅ Bình Chọn</button>
<!-- History Button -->
<button id="viewHistory" class="history-btn" onclick="window.location.href='history.php?game_id=<?= $game_id ?>'">
    📜 Xem Lịch Sử
</button>
    <!-- Points Info -->
    <div class="points-info">
        <p>Số điểm hiện tại: <span id="userPoints"><?= $user['points']; ?></span></p>
    </div>
    <?php include 'tabbar.php'; ?>

</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    let countdown = 15;

    setInterval(() => {
        countdown--;
        document.getElementById('countdown').innerText = countdown;

        if (countdown <= 0) {
            location.reload();
        }
    }, 1000);

    document.querySelectorAll(".vote-btn").forEach(button => {
        button.addEventListener("click", () => {
            button.classList.toggle("selected");
            updateSelection();
        });
    });

    document.getElementById("submitVote").addEventListener("click", () => {
        let selected = Array.from(document.querySelectorAll(".vote-btn.selected")).map(btn => btn.dataset.choice);
        let betAmount = parseInt(document.getElementById("betAmount").value);

        if (betAmount <= 0 || isNaN(betAmount)) {
            alert("Vui lòng nhập số điểm cược hợp lệ!");
            return;
        }

        if (selected.length === 0) {
            alert("Bạn chưa chọn ô nào!");
            return;
        }

        processVote(selected, betAmount);
    });
});

function updateSelection() {
    let selected = Array.from(document.querySelectorAll(".vote-btn.selected")).map(btn => btn.dataset.choice);
    document.getElementById("selectedChoices").textContent = selected.join(", ") || "-";
    let betAmount = document.getElementById("betAmount").value;
    document.getElementById("totalBet").textContent = selected.length * betAmount;
}

function processVote(choices, betAmount) {
    fetch("process_vote.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ game_id: <?= $game_id ?>, choices, betAmount })
    })
    .then(response => response.text())
    .then(data => {
        alert(data);
        location.reload();
    })
    .catch(error => console.error("Lỗi:", error));
}
</script>

</body>
</html>
