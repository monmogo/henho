<?php
require_once 'config.db.php';

$username = $_POST['username'];
$email = $_POST['email'];
$password = password_hash($_POST['password'], PASSWORD_DEFAULT);
$role = $_POST['role'];

$sql = "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ssss', $username, $email, $password, $role);

if ($stmt->execute()) {
    echo json_encode(["message" => "Thêm người dùng thành công!"]);
} else {
    echo json_encode(["message" => "Lỗi khi thêm người dùng."]);
}
