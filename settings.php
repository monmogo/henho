<?php
session_start();
require_once 'config.db.php';

// Kiểm tra xem người dùng đã đăng nhập hay chưa
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Lấy thông tin người dùng từ CSDL
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
} else {
    $_SESSION['error'] = "Không tìm thấy thông tin người dùng.";
    header('Location: login.php');
    exit();
}

// Xử lý cập nhật thông tin
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fullname = htmlspecialchars(trim($_POST['fullname']));
    $gender = htmlspecialchars(trim($_POST['gender']));
    $bank_account = htmlspecialchars(trim($_POST['bank_account']));
    $card_holder_name = htmlspecialchars(trim($_POST['card_holder_name']));
    $bank_name = htmlspecialchars(trim($_POST['bank_name']));
    $password = trim($_POST['password']);
    $avatar = $user['avatar']; // Giữ nguyên avatar mặc định nếu không thay đổi

    // Xử lý upload avatar mới nếu có
    if (!empty($_FILES['avatar']['name'])) {
        $target_dir = "uploads/avatars/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0755, true);
        }

        $target_file = $target_dir . uniqid() . "_" . basename($_FILES['avatar']['name']);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($imageFileType, $allowed_types)) {
            if ($_FILES['avatar']['size'] <= 5 * 1024 * 1024) {
                if (move_uploaded_file($_FILES['avatar']['tmp_name'], $target_file)) {
                    $avatar = $target_file;
                } else {
                    $_SESSION['error'] = "Không thể tải lên ảnh.";
                }
            } else {
                $_SESSION['error'] = "Kích thước file vượt quá 5MB.";
            }
        } else {
            $_SESSION['error'] = "Định dạng file không hợp lệ.";
        }
    }

    // Chỉ cập nhật mật khẩu nếu có nhập mới
    if (!empty($password)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $update_sql = "UPDATE users SET fullname = ?, gender = ?, bank_account = ?, card_holder_name = ?, bank_name = ?, avatar = ?, password = ? WHERE id = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param('sssssssi', $fullname, $gender, $bank_account, $card_holder_name, $bank_name, $avatar, $hashed_password, $user_id);
    } else {
        // Không cập nhật mật khẩu nếu không có nhập mới
        $update_sql = "UPDATE users SET fullname = ?, gender = ?, bank_account = ?, card_holder_name = ?, bank_name = ?, avatar = ? WHERE id = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param('ssssssi', $fullname, $gender, $bank_account, $card_holder_name, $bank_name, $avatar, $user_id);
    }

    // Thực thi truy vấn và kiểm tra lỗi
    if ($stmt->execute()) {
        $_SESSION['success'] = "✅ Cập nhật thông tin thành công!";
        header('Location: profile.php');
        exit();
    } else {
        $_SESSION['error'] = "❌ Lỗi khi cập nhật: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cài đặt </title>
    <link rel="stylesheet" href="css/settings.css">
</head>
<body>
    <div class="settings-container">
        <!-- Nút quay lại -->
        <div class="back-button">
            <a href="profile.php" class="btn-back">← Quay lại</a>
        </div>

        <h1>Cài đặt</h1>

        <!-- Thông báo thành công hoặc lỗi -->
        <?php if (isset($_SESSION['success'])): ?>
            <p class="success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></p>
        <?php elseif (isset($_SESSION['error'])): ?>
            <p class="error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></p>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <!-- Avatar -->
            <label for="avatar">Ảnh đại diện:</label>
    <div class="avatar-container">
        <div class="avatar-preview-wrapper">
            <img src="<?php echo htmlspecialchars(!empty($user['avatar']) ? $user['avatar'] : 'default-avatar.png'); ?>" 
                 alt="Avatar" 
                 class="avatar-preview" 
                 id="avatarPreview">
        </div>
        <div class="upload-btn-wrapper">
            <label class="upload-label" for="avatar">
                <i class="fas fa-upload"></i> Chọn ảnh mới
            </label>
            <input type="file" name="avatar" id="avatar" accept="image/*" onchange="previewAvatar(event)">
        </div>
    </div>

            <!-- Fullname -->
            <div class="form-group">
                <label for="fullname">Họ và tên:</label>
                <input type="text" name="fullname" id="fullname" value="<?php echo htmlspecialchars($user['fullname']); ?>" required>
            </div>

            <!-- Gender -->
            <div class="form-group">
                <label for="gender">Giới tính:</label>
                <select name="gender" id="gender" required>
                    <option value="male" <?php echo ($user['gender'] == 'male') ? 'selected' : ''; ?>>Nam</option>
                    <option value="female" <?php echo ($user['gender'] == 'female') ? 'selected' : ''; ?>>Nữ</option>
                    <option value="other" <?php echo ($user['gender'] == 'other') ? 'selected' : ''; ?>>Khác</option>
                </select>
            </div>

            <!-- Bank Account -->
            <div class="form-group">
                <label for="bank_account">Số tài khoản ngân hàng:</label>
                <input type="text" name="bank_account" id="bank_account" value="<?php echo htmlspecialchars($user['bank_account']); ?>">
            </div>

            <!-- Card Holder Name -->
            <div class="form-group">
                <label for="card_holder_name">Tên chủ thẻ:</label>
                <input type="text" name="card_holder_name" id="card_holder_name" value="<?php echo htmlspecialchars($user['card_holder_name']); ?>">
            </div>

            <!-- Bank Name -->
            <div class="form-group">
                <label for="bank_name">Tên ngân hàng:</label>
                <input type="text" name="bank_name" id="bank_name" value="<?php echo htmlspecialchars($user['bank_name']); ?>">
            </div>

            <!-- Password -->
            <div class="form-group">
                <label for="password">Mật khẩu mới (để trống nếu không thay đổi):</label>
                <input type="password" name="password" id="password">
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn">Cập nhật</button>
        </form>
    </div>
</body>
<script>
    function previewAvatar(event) {
        const reader = new FileReader();
        reader.onload = function () {
            const avatarPreview = document.getElementById('avatarPreview');
            avatarPreview.src = reader.result;
        };
        reader.readAsDataURL(event.target.files[0]);
    }
</script>

</html>
