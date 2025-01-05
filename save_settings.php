<?php
session_start();
require_once 'config.db.php';

// Kiểm tra quyền Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    exit("Bạn không có quyền thực hiện thao tác này!");
}

$admin_id = $_SESSION['user_id']; // ID Admin đang thao tác
$notification = trim($_POST['notification']);
$bannerPath = "";

// Xử lý upload banner nếu có
if (!empty($_FILES['banner']['name'])) {
    $uploadDir = "uploads/";
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $fileName = time() . "_" . basename($_FILES["banner"]["name"]);
    $targetFile = $uploadDir . $fileName;

    if (move_uploaded_file($_FILES["banner"]["tmp_name"], $targetFile)) {
        $bannerPath = $targetFile;
    }
}

// Kiểm tra xem có dữ liệu trong bảng `settings` chưa
$sql = "SELECT COUNT(*) AS count FROM settings";
$result = $conn->query($sql);
$row = $result->fetch_assoc();

if ($row['count'] > 0) {
    // Cập nhật banner & thông báo
    if (!empty($bannerPath)) {
        $sql = "UPDATE settings SET banner_url = ?, notification = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $bannerPath, $notification);
    } else {
        $sql = "UPDATE settings SET notification = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $notification);
    }
} else {
    // Thêm mới nếu chưa có dữ liệu
    $sql = "INSERT INTO settings (banner_url, notification) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $bannerPath, $notification);
}

if ($stmt->execute()) {
    // Ghi lại lịch sử thay đổi
    $sql = "INSERT INTO settings_history (admin_id, banner_url, notification) VALUES (?, ?, ?)";
    $stmt_history = $conn->prepare($sql);
    $stmt_history->bind_param("iss", $admin_id, $bannerPath, $notification);
    $stmt_history->execute();

    echo "Cập nhật thành công!";
} else {
    echo "Lỗi khi cập nhật!";
}

$stmt->close();
$conn->close();
?>
