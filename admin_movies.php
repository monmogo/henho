<?php
session_start();

// Kiểm tra quyền admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Đọc dữ liệu phim từ JSON
$movies_json = file_get_contents("movies_data.json");
$movies = json_decode($movies_json, true) ?? [];
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🎬 Quản Lý Phim</title>
    <link rel="stylesheet" href="css/admin_style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body>

<!-- Sidebar -->
<aside class="navi">
    <?php include 'navigation.php'; ?>
</aside>

<!-- Nội dung chính -->
<div class="admin-container">
    <h1>🎬 Quản Lý Phim</h1>

    <!-- Form Thêm/Sửa Phim -->
    <div class="form-container">
        <h2>➕ Thêm/Sửa Phim</h2>
        <form id="movieForm" action="process_movies.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="id" id="movieId">

            <div class="form-group">
                <label><i class="fas fa-film"></i> Tiêu đề phim:</label>
                <input type="text" name="title" id="title" required>
            </div>

            <div class="form-group">
                <label><i class="fas fa-list"></i> Thể loại:</label>
                <select name="category" id="category">
                    <option value="hot">🔥 Phim Hot</option>
                    <option value="vietnam">🇻🇳 Việt Nam</option>
                    <option value="japan">🇯🇵 Nhật Bản</option>
                    <option value="gay">🌈 Phim GAY</option>
                </select>
            </div>

            <div class="form-group">
                <label><i class="fas fa-eye"></i> Lượt xem:</label>
                <input type="number" name="views" id="views" required>
            </div>

            <!-- Upload Ảnh Thumbnail -->
            <div class="form-group">
                <label><i class="fas fa-image"></i> Ảnh Thumbnail:</label>
                <input type="file" name="thumbnail" id="thumbnail" accept="image/*">
                <img id="thumbnailPreview" src="default-thumbnail.jpg" class="thumbnail-preview">
            </div>

            <div class="form-group">
                <label><i class="fas fa-link"></i> Đường link embed phim:</label>
                <input type="text" name="embed_url" id="embed_url" required>
            </div>

            <div class="form-group">
                <label><i class="fas fa-align-left"></i> Mô tả:</label>
                <textarea name="description" id="description" rows="3"></textarea>
            </div>

            <button type="submit" name="action" value="add" class="btn add-movie">
                <i class="fas fa-plus"></i> Thêm/Sửa Phim
            </button>
        </form>
    </div>

    <hr>

    <!-- Danh Sách Phim -->
    <h2>📜 Danh Sách Phim</h2>
    <table class="movie-table">
        <thead>
            <tr>
                <th>Tiêu đề</th>
                <th>Thể loại</th>
                <th>Lượt xem</th>
                <th>Thumbnail</th>
                <th>Link Embed</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($movies as $movie): ?>
                <tr>
                    <td><?= htmlspecialchars($movie['title']) ?></td>
                    <td><?= htmlspecialchars($movie['category']) ?></td>
                    <td><?= number_format($movie['views']) ?></td>
                    <td><img src="<?= $movie['thumbnail'] ?>" class="thumbnail"></td>
                    <td><a href="<?= $movie['embed_url'] ?>" target="_blank" class="watch-link">🔗 Xem</a></td>
                    <td class="actions">
                        <button class="edit-btn" onclick="editMovie(<?= htmlspecialchars(json_encode($movie)) ?>)">
                            ✏️ Sửa
                        </button>
                        <form action="process_movies.php" method="POST" class="inline-form">
                            <input type="hidden" name="id" value="<?= $movie['id'] ?>">
                            <button type="submit" name="action" value="delete" class="delete-btn">🗑️ Xóa</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
document.getElementById('thumbnail').addEventListener('change', function(event) {
    const reader = new FileReader();
    reader.onload = function() {
        document.getElementById('thumbnailPreview').src = reader.result;
    };
    reader.readAsDataURL(event.target.files[0]);
});

function editMovie(movie) {
    document.getElementById('movieId').value = movie.id;
    document.getElementById('title').value = movie.title;
    document.getElementById('category').value = movie.category;
    document.getElementById('views').value = movie.views;
    document.getElementById('embed_url').value = movie.embed_url;
    document.getElementById('description').value = movie.description;
}
</script>

</body>
</html>
