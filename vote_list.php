<?php
session_start();
require_once 'config.db.php';

// Truy vấn danh sách games từ cơ sở dữ liệu
$sql = "SELECT id, name, cover_image FROM vote_games";
$result = $conn->query($sql);
$games = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Sảnh Bình Chọn</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/vote_list.css">
    <link rel="stylesheet" href="css/tabbar.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>

<!-- Thanh Điều Hướng Mobile -->
<nav class="mobile-nav d-md-none">
    <button id="toggleSidebar"><i class="fas fa-bars"></i></button>
    
</nav>

<div class="container-fluid">
    <div class="row justify-content-center">
        <!-- Sidebar danh sách vote -->
        <!-- <nav class="col-md-2 sidebar" id="sidebar">
            <button id="closeSidebar" class="close-sidebar">&times;</button>
            <h3 class="sidebar-title">Sảnh Bình Chọn</h3>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a href="#" class="nav-link active">Tất cả</a>
                </li>
                <?php foreach ($games as $game): ?>
                    <li class="nav-item">
                        <a href="vote_game.php?game_id=<?= $game['id']; ?>" class="nav-link">
                            <?= htmlspecialchars($game['name']); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </nav> -->

        <!-- Danh sách bình chọn -->
        <main class="col-md-10 col-12 main-content">
            <h2 class="text-center">Sảnh Bình Chọn</h2>
            <div class="game-grid">
                <?php foreach ($games as $game): ?>
                    <div class="game-card">
                        <a href="vote_game.php?game_id=<?= $game['id']; ?>">
                            <img src="<?= htmlspecialchars($game['cover_image']); ?>" alt="<?= htmlspecialchars($game['name']); ?>" class="game-image">
                            <p class="game-title"><?= htmlspecialchars($game['name']); ?></p>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </main>
    </div>
</div>

<!-- TABBAR -->
<?php include 'tabbar.php'; ?>


<!-- Bootstrap & jQuery -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<script>
$(document).ready(function () {
    $("#toggleSidebar").click(function () {
        $("#sidebar").addClass("active");
    });

    $("#closeSidebar").click(function () {
        $("#sidebar").removeClass("active");
    });

    // Đóng sidebar khi bấm ra ngoài
    $(document).click(function (e) {
        if (!$(e.target).closest("#sidebar, #toggleSidebar").length) {
            $("#sidebar").removeClass("active");
        }
    });
});
</script>

</body>
</html>
