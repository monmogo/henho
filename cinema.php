<?php
// Đọc dữ liệu từ movies_data.json
$movies_json = file_get_contents("movies_data.json");
$movies = json_decode($movies_json, true);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rạp Chiếu Phim</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="css/tabbar.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

</head>
<body>

<div class="cinema-container">
    <h1 class="header-title" style="    margin-top: 0;">Rạp Chiếu Phim</h1>

    <!-- Thanh Tab Chọn Thể Loại -->
    <div class="category-tabs">
        <button class="tab active" onclick="filterMovies('hot')">Phim Hot</button>
        <button class="tab" onclick="filterMovies('vietnam')">Việt Nam</button>
        <button class="tab" onclick="filterMovies('japan')">Nhật Bản</button>
        <button class="tab" onclick="filterMovies('gay')">Phim GAY</button>
    </div>

    <!-- Danh sách phim -->
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

<?php include 'tabbar.php'; ?>
</div>



<script>
    function watchMovie(movieId) {
        window.location.href = "watch.php?id=" + movieId;
    }

    function filterMovies(category) {
        let movies = document.querySelectorAll('.movie-card');
        movies.forEach(movie => {
            if (category === 'hot' || movie.dataset.category === category) {
                movie.style.display = "block";
            } else {
                movie.style.display = "none";
            }
        });

        document.querySelectorAll('.tab').forEach(tab => tab.classList.remove('active'));
        event.target.classList.add('active');
    }
</script>

</body>
</html>
