<?php
session_start();
require_once 'config.db.php';

// Kiểm tra quyền admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die("Bạn không có quyền thực hiện thao tác này!");
}

$round_id = intval($_GET['id']);

// Lấy dữ liệu kỳ quay
$sql = "SELECT game_id, round_number, correct_choices FROM admin_controls WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $round_id);
$stmt->execute();
$round = $stmt->get_result()->fetch_assoc();

// Cập nhật dữ liệu nếu admin sửa
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correct_choices = implode(",", $_POST['choices']);

    $sql = "UPDATE admin_controls SET correct_choices = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $correct_choices, $round_id);

    if ($stmt->execute()) {
        echo "<script>alert('Đã cập nhật kết quả thành công!'); window.location.href='admin_set_result.php';</script>";
    } else {
        echo "<script>alert('Lỗi khi cập nhật!');</script>";
    }
}
?>

<form method="POST">
    <label>Kết Quả Đúng:</label>
    <div>
        <input type="checkbox" name="choices[]" value="A" <?= strpos($round['correct_choices'], 'A') !== false ? 'checked' : '' ?>> A
        <input type="checkbox" name="choices[]" value="B" <?= strpos($round['correct_choices'], 'B') !== false ? 'checked' : '' ?>> B
        <input type="checkbox" name="choices[]" value="C" <?= strpos($round['correct_choices'], 'C') !== false ? 'checked' : '' ?>> C
        <input type="checkbox" name="choices[]" value="D" <?= strpos($round['correct_choices'], 'D') !== false ? 'checked' : '' ?>> D
    </div>
    <button type="submit">Cập Nhật</button>
</form>
