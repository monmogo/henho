<?php
session_start();
require_once 'config.db.php';

// Kiểm tra quyền Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(["status" => "error", "message" => "Bạn không có quyền thực hiện thao tác này!"]);
    exit();
}

// Kiểm tra nếu có dữ liệu POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = intval($_POST['id']);

    // Kiểm tra ID hợp lệ
    if ($id <= 0) {
        echo json_encode(["status" => "error", "message" => "ID game không hợp lệ!"]);
        exit();
    }

    // Xóa game khỏi bảng vote_games
    $sql = "DELETE FROM vote_games WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Game đã được xóa thành công!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Lỗi khi xóa game!"]);
    }

    $stmt->close();
    $conn->close();
}
?>
