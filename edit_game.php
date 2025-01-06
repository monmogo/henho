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
$id = intval($_POST['id']);
$name = trim($_POST['name']);
$total_rounds = intval($_POST['total_rounds']);
$profit_share = floatval($_POST['profit_share']);
$imagePath = null;

// Kiểm tra xem game có tồn tại không
$sqlCheck = "SELECT cover_image FROM vote_games WHERE id = ?";
$stmtCheck = $conn->prepare($sqlCheck);
$stmtCheck->bind_param("i", $id);
$stmtCheck->execute();
$result = $stmtCheck->get_result();
if ($result->num_rows === 0) {
    $_SESSION['error'] = "Game không tồn tại!";
    header("Location: admin_games.php");
    exit();
}
$game = $result->fetch_assoc();
$currentImage = $game['cover_image']; // Ảnh hiện tại

// Kiểm tra và xử lý ảnh mới nếu có
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

// Nếu không tải ảnh mới, giữ nguyên ảnh cũ
if (!$imagePath) {
    $imagePath = $currentImage;
}

// Cập nhật dữ liệu vào database
$sql = "UPDATE vote_games SET name=?, cover_image=?, total_rounds=?, profit_share=? WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssiii", $name, $imagePath, $total_rounds, $profit_share, $id);

if ($stmt->execute()) {
    $_SESSION['success'] = "Cập nhật game thành công!";
} else {
    $_SESSION['error'] = "Lỗi khi cập nhật game!";
}

// Chuyển hướng về trang quản lý games
header("Location: admin_games.php");
exit();
?>
