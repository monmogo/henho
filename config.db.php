<?php
// File config.db.php
$host = 'localhost'; // Máy chủ
$db = 'henho'; // Tên cơ sở dữ liệu
$user = 'root'; // Tên đăng nhập MySQL
$pass = ''; // Mật khẩu MySQL

// Kết nối cơ sở dữ liệu
$conn = new mysqli($host, $user, $pass, $db);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die('Kết nối thất bại: ' . $conn->connect_error);
}

// Thiết lập mã hóa UTF-8
$conn->set_charset('utf8');
?>
