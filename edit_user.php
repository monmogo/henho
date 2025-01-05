<?php
require_once 'config.db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)$_POST['id'];
    $username = htmlspecialchars(trim($_POST['username']));
    $email = htmlspecialchars(trim($_POST['email']));
    $points = (int)$_POST['points'];
    $trust_points = (int)$_POST['trust_points'];
    $role = trim($_POST['role']);
    $fullname = htmlspecialchars(trim($_POST['fullname']));
    $gender = trim($_POST['gender']);
    $bank_account = htmlspecialchars(trim($_POST['bank_account']));
    $card_holder_name = htmlspecialchars(trim($_POST['card_holder_name']));
    $bank_name = htmlspecialchars(trim($_POST['bank_name']));

    // Xác minh giá trị hợp lệ cho trường role
    $allowed_roles = ['user', 'admin'];
    if (!in_array($role, $allowed_roles)) {
        echo 'error_invalid_role';
        exit();
    }

    // Xác minh giá trị hợp lệ cho trường gender
    $allowed_genders = ['male', 'female', 'other'];
    if (!in_array($gender, $allowed_genders)) {
        echo 'error_invalid_gender';
        exit();
    }

    // Xử lý avatar nếu có
    $avatar = null;
    if (!empty($_FILES['avatar']['name'])) {
        $upload_dir = 'uploads/avatars/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $avatar = $upload_dir . uniqid() . '_' . basename($_FILES['avatar']['name']);
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        $file_extension = strtolower(pathinfo($avatar, PATHINFO_EXTENSION));

        if (!in_array($file_extension, $allowed_extensions)) {
            echo 'error_invalid_avatar_format';
            exit();
        }

        if (!move_uploaded_file($_FILES['avatar']['tmp_name'], $avatar)) {
            echo 'error_upload_avatar';
            exit();
        }
    }

    // Câu lệnh SQL
    $sql = "UPDATE users 
            SET username = ?, 
                email = ?, 
                points = ?, 
                trust_points = ?, 
                role = ?, 
                fullname = ?, 
                gender = ?, 
                bank_account = ?, 
                card_holder_name = ?, 
                bank_name = ?, 
                avatar = COALESCE(?, avatar) 
            WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssiiissssssi', 
        $username, 
        $email, 
        $points, 
        $trust_points, 
        $role, 
        $fullname, 
        $gender, 
        $bank_account, 
        $card_holder_name, 
        $bank_name, 
        $avatar, 
        $id
    );

    // Kiểm tra trạng thái thực thi
    if ($stmt->execute()) {
        echo 'success';
    } else {
        // Hiển thị lỗi SQL
        echo 'error_sql: ' . $stmt->error;
    }
} else {
    echo 'error_invalid_request';
}
?>
