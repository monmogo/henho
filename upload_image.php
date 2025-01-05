<?php
header("Content-Type: application/json");

if ($_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = "uploads/";
    $fileName = time() . "_" . basename($_FILES["cover_image"]["name"]);
    $targetFile = $uploadDir . $fileName;

    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    if (move_uploaded_file($_FILES["cover_image"]["tmp_name"], $targetFile)) {
        echo json_encode(["status" => "success", "image_path" => $targetFile]);
    } else {
        echo json_encode(["status" => "error", "message" => "Không thể tải ảnh lên!"]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Lỗi khi tải ảnh lên!"]);
}
?>
