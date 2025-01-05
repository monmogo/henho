<?php
session_start();

// Kiểm tra quyền admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Đọc dữ liệu hiện tại từ JSON
$movies_json = file_get_contents("movies_data.json");
$movies = json_decode($movies_json, true) ?? [];

$action = $_POST['action'];
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$title = trim($_POST['title']);
$category = trim($_POST['category']);
$views = intval($_POST['views']);
$embed_url = trim($_POST['embed_url']);
$description = trim($_POST['description']);
$thumbnail = "";

// Xử lý upload ảnh thumbnail
if (!empty($_FILES['thumbnail']['name'])) {
    $upload_dir = "uploads/thumbnails/";
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    $image_file = $upload_dir . uniqid() . "_" . basename($_FILES['thumbnail']['name']);
    $imageFileType = strtolower(pathinfo($image_file, PATHINFO_EXTENSION));
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];

    if (in_array($imageFileType, $allowed_types)) {
        if ($_FILES['thumbnail']['size'] <= 5 * 1024 * 1024) { // Giới hạn 5MB
            if (move_uploaded_file($_FILES['thumbnail']['tmp_name'], $image_file)) {
                $thumbnail = $image_file;
            } else {
                $_SESSION['error'] = "Không thể tải lên ảnh.";
            }
        } else {
            $_SESSION['error'] = "File ảnh vượt quá 5MB!";
        }
    } else {
        $_SESSION['error'] = "Chỉ chấp nhận JPG, PNG, GIF.";
    }
}

// Nếu không upload ảnh mới, giữ ảnh cũ
if (!$thumbnail && isset($_POST['current_thumbnail'])) {
    $thumbnail = $_POST['current_thumbnail'];
}

if ($action === 'add') {
    if ($id > 0) {
        // Chỉnh sửa phim
        foreach ($movies as &$movie) {
            if ($movie['id'] == $id) {
                $movie['title'] = $title;
                $movie['category'] = $category;
                $movie['views'] = $views;
                $movie['thumbnail'] = $thumbnail;
                $movie['embed_url'] = $embed_url;
                $movie['description'] = $description;
                break;
            }
        }
    } else {
        // Thêm phim mới
        $new_movie = [
            "id" => count($movies) + 1,
            "title" => $title,
            "category" => $category,
            "views" => $views,
            "thumbnail" => $thumbnail,
            "embed_url" => $embed_url,
            "description" => $description
        ];
        array_push($movies, $new_movie);
    }
} elseif ($action === 'delete') {
    // Xóa phim
    $movies = array_values(array_filter($movies, fn($movie) => $movie['id'] !== $id));
}

// Ghi lại dữ liệu vào JSON
if (file_put_contents("movies_data.json", json_encode($movies, JSON_PRETTY_PRINT))) {
    $_SESSION['success'] = "Cập nhật thành công!";
} else {
    $_SESSION['error'] = "❌ Lỗi: Không thể lưu dữ liệu vào file JSON!";
}

// Quay lại trang admin
header("Location: admin_movies.php");
exit();
