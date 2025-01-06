<?php
require_once 'config.db.php';

// Truy vấn danh sách games từ CSDL
$sql = "SELECT id, name, cover_image FROM vote_games";
$result = $conn->query($sql);
$games = $result->fetch_all(MYSQLI_ASSOC);
// Lấy danh sách banner từ bảng `banners`
$sql = "SELECT image_url FROM banners ORDER BY id DESC";
$result = $conn->query($sql);
$banners = $result->fetch_all(MYSQLI_ASSOC);
//mov.js
$movies_json = file_get_contents("movies_data.json");
$movies = json_decode($movies_json, true);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang Chủ</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/tabbar.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <!-- Bootstrap CSS -->
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
<!-- Bootstrap JS & jQuery -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</head>
<style>
.header-banner {
    width: 100%;
    max-height: 300px;
    overflow: hidden;
}

.carousel-item img {
    width: 100%;
    height: auto;
    max-height: 300px;
    object-fit: cover;
}

.carousel-control-prev, .carousel-control-next {
    width: 5%;
}

.carousel-control-prev-icon,
.carousel-control-next-icon {
    background-color: rgba(0, 0, 0, 0.5);
    border-radius: 50%;
    padding: 10px;
}


</style>
<body>

<!-- banner -->
<header class="header-banner">
    <div id="bannerCarousel" class="carousel slide" data-ride="carousel">
        <div class="carousel-inner">
            <?php if (!empty($banners)): ?>
                <?php foreach ($banners as $index => $banner): ?>
                    <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                        <img src="<?= htmlspecialchars($banner['image_url']); ?>" class="d-block w-100" alt="Banner <?= $index + 1; ?>">
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="carousel-item active">
                    <img src="image/default-banner.jpg" class="d-block w-100" alt="Default Banner">
                </div>
            <?php endif; ?>
        </div>

        <!-- Nút điều hướng -->
        <a class="carousel-control-prev" href="#bannerCarousel" role="button" data-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        </a>
        <a class="carousel-control-next" href="#bannerCarousel" role="button" data-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
        </a>
    </div>
</header>


<!-- THÔNG BÁO -->
<div class="notification">
    <i class="fas fa-bell"></i> <?= htmlspecialchars($settings['notification'] ?? 'Chào mừng bạn đến với hệ thống bình chọn!'); ?>
</div>

<!-- DANH SÁCH BÌNH CHỌN -->
<section class="interaction-section">
    <h2 class="section-title">🔥 TƯƠNG TÁC CÙNG CÁC BÉ</h2>
    <div class="interaction-slider">
        <div class="interaction-list">
        <?php foreach (array_slice($games, 0, 6) as $game): ?> <!-- Lấy 4 game đầu -->
                <div class="interaction-item">
                    <a href="vote_game.php?game_id=<?= $game['id']; ?>">
                        <img src="<?= htmlspecialchars($game['cover_image']); ?>" alt="<?= htmlspecialchars($game['name']); ?>">
                        <span><?= htmlspecialchars($game['name']); ?></span>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="interaction-section">
    <h2 class="section-title">💪 TƯƠNG TÁC CÙNG CÁC ANH</h2>
    <div class="interaction-slider">
        <div class="interaction-list">
        <?php foreach (array_reverse($games) as $game): ?> <!-- Đảo ngược danh sách, lấy tất cả game -->
                <div class="interaction-item">
                    <a href="vote_game.php?game_id=<?= $game['id']; ?>">
                        <img src="<?= htmlspecialchars($game['cover_image']); ?>" alt="<?= htmlspecialchars($game['name']); ?>">
                        <span><?= htmlspecialchars($game['name']); ?></span>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<section class="movie-section">
    <h2 class="section-title">📺 Danh sách phim nổi bật</h2>
    <div class="movie-grid" id="movieList">
        <?php foreach ($movies as $movie): ?>
            <div class="movie-card" data-category="<?= $movie['category'] ?>" onclick="watchMovie(<?= $movie['id'] ?>)">
                <img src="<?= $movie['thumbnail'] ?>" alt="<?= htmlspecialchars($movie['title']) ?>">
                <div class="movie-info">
                    <p class="movie-title"><?= htmlspecialchars($movie['title']) ?></p>
                    <span class="movie-views">Lượt xem: <?= $movie['views'] ?></span>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- TABBAR -->
<?php include 'tabbar.php'; ?>

<!-- SCRIPT -->
<script>
     function watchMovie(movieId) {
        window.location.href = "watch.php?id=" + movieId;
    }
</script>

<script src="script.js"></script>

</body>
</html>
