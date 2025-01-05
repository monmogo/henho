<?php
// Đọc dữ liệu từ movies_data.json
$movies_json = file_get_contents("movies_data.json");
$movies = json_decode($movies_json, true);

// Lấy ID phim từ URL
$movie_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$movie = null;

// Tìm phim theo ID
foreach ($movies as $m) {
    if ($m['id'] == $movie_id) {
        $movie = $m;
        break;
    }
}

// Kiểm tra nếu phim không tồn tại
if (!$movie) {
    echo "<h1>Không tìm thấy phim</h1>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($movie['title']) ?> - Xem Phim</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/tabbar.css">
    <link rel="stylesheet" href="css/watch.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>

<div class="watch-container">
    <!-- Tiêu đề phim -->
    <h1 class="watch-title"><?= htmlspecialchars($movie['title']) ?></h1>

    <!-- Khu vực Video -->
    <div class="video-container">
        <iframe src="<?= htmlspecialchars($movie['embed_url']) ?>" frameborder="0" allowfullscreen></iframe>
    </div>

    <!-- Mô tả phim
    <p class="movie-description"><?= nl2br(htmlspecialchars($movie['description'])) ?></p> -->

    <!-- Nút quay lại -->
    <div class="back-button">
        <a href="cinema.php" class="btn-back"><i class="fas fa-arrow-left"></i> Quay lại</a>
    </div>

    <!-- Danh sách phim khác -->
    <div class="movie-list">
        <h2>📺 Danh sách phim khác</h2>
        <div class="movie-grid">
            <?php foreach ($movies as $m): ?>
                <?php if ($m['id'] !== $movie_id): ?>
                    <div class="movie-item" onclick="window.location.href='watch.php?id=<?= $m['id']; ?>'">
                        <img src="<?= htmlspecialchars($m['thumbnail']); ?>" alt="<?= htmlspecialchars($m['title']); ?>">
                        <span><?= htmlspecialchars($m['title']); ?></span>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Tabbar -->
<?php include 'tabbar.php'; ?>

</body>
</html>
