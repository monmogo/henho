<?php
session_start();
require_once 'config.db.php';

// Kiểm tra quyền admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error'] = "Bạn không có quyền!";
    header("Location: admin_games.php");
    exit();
}

// Nhận dữ liệu từ form
$name = trim($_POST['name']);
$total_rounds = intval($_POST['total_rounds']);
$profit_share = floatval($_POST['profit_share']);
$imagePath = "";

// Kiểm tra và xử lý ảnh upload
if (!empty($_FILES['cover_image']['name'])) {
    $uploadDir = "uploads/";
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $fileName = time() . "_" . basename($_FILES["cover_image"]["name"]);
    $targetFile = $uploadDir . $fileName;

    if (move_uploaded_file($_FILES["cover_image"]["tmp_name"], $targetFile)) {
        $imagePath = $targetFile;
    } else {
        $_SESSION['error'] = "Không thể tải ảnh lên!";
        header("Location: admin_games.php");
        exit();
    }
}

// Kiểm tra đầu vào
if (empty($name) || empty($imagePath)) {
    $_SESSION['error'] = "Tên game và ảnh không được để trống!";
    header("Location: admin_games.php");
    exit();
}

// Chèn dữ liệu vào database
$sql = "INSERT INTO vote_games (name, cover_image, total_rounds, profit_share) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssii", $name, $imagePath, $total_rounds, $profit_share);

if ($stmt->execute()) {
    $_SESSION['success'] = "Thêm game thành công!";
} else {
    $_SESSION['error'] = "Lỗi khi thêm game!";
}

// Chuyển hướng về trang quản lý games
header("Location: admin_games.php");
exit();
?>
