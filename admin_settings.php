<?php
session_start();
require_once 'config.db.php';

// Kiểm tra quyền Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Lấy danh sách banner
$sql = "SELECT * FROM banners ORDER BY id DESC";
$result = $conn->query($sql);
$banners = $result->fetch_all(MYSQLI_ASSOC);

// Lấy danh sách thông báo
$sql = "SELECT * FROM notifications ORDER BY id DESC";
$result = $conn->query($sql);
$notifications = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Cài Đặt</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>

<div class="dashboard-container">
    <aside class="navigation">
        <?php include 'navigation.php'; ?>
    </aside>

    <main class="main-content">
        <h1 class="text-center">🎨 Quản Lý Banner & Thông Báo</h1>

        <!-- QUẢN LÝ BANNER -->
        <section class="settings-section">
            <h2>🖼️ Quản Lý Banner</h2>
            <button class="btn btn-success mb-3" data-toggle="modal" data-target="#addBannerModal">➕ Thêm Banner</button>
            <div class="table-container">
                <table class="table table-bordered text-center">
                    <thead class="thead-dark">
                        <tr>
                            <th>ID</th>
                            <th>Hình Ảnh</th>
                            <th>Hành Động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($banners)): ?>
                            <?php foreach ($banners as $banner): ?>
                                <tr>
                                    <td><?= htmlspecialchars($banner['id']); ?></td>
                                    <td><img src="<?= htmlspecialchars($banner['image_url']); ?>" class="img-fluid" style="max-height: 100px;"></td>
                                    <td>
                                        <button class="btn btn-danger btn-sm" onclick="confirmDeleteBanner(<?= $banner['id']; ?>)">🗑️ Xóa</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="3" class="text-center">Chưa có banner nào.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- QUẢN LÝ THÔNG BÁO -->
        <section class="settings-section">
            <h2>🔔 Quản Lý Thông Báo</h2>
            <button class="btn btn-success mb-3" data-toggle="modal" data-target="#addNotificationModal">➕ Thêm Thông Báo</button>
            <div class="table-container">
                <table class="table table-bordered text-center">
                    <thead class="thead-dark">
                        <tr>
                            <th>ID</th>
                            <th>Nội Dung</th>
                            <th>Hành Động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($notifications)): ?>
                            <?php foreach ($notifications as $notification): ?>
                                <tr>
                                    <td><?= htmlspecialchars($notification['id']); ?></td>
                                    <td><?= htmlspecialchars($notification['message']); ?></td>
                                    <td>
                                        <button class="btn btn-primary btn-sm" onclick="openEditNotificationModal(<?= htmlspecialchars(json_encode($notification)); ?>)">✏️ Sửa</button>
                                        <button class="btn btn-danger btn-sm" onclick="confirmDeleteNotification(<?= $notification['id']; ?>)">🗑️ Xóa</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="3" class="text-center">Chưa có thông báo nào.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</div>

<!-- Modal Thêm Banner -->
<!-- Modal Thêm Banner -->
<div class="modal fade" id="addBannerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="addBannerForm" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title">➕ Thêm Banner</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <label>Hình Ảnh</label>
                    <input type="file" class="form-control mt-2" name="banner" accept="image/*" required>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Thêm</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Thêm Thông Báo -->
<div class="modal fade" id="addNotificationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="addNotificationForm">
                <div class="modal-header">
                    <h5 class="modal-title">➕ Thêm Thông Báo</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <label>Nội Dung</label>
                    <textarea class="form-control mt-2" name="message" required></textarea>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Thêm</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- SCRIPT -->
<!-- Thêm Bootstrap & jQuery -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
$(document).ready(function () {
    $("#addBannerForm").submit(function (e) {
        e.preventDefault(); // Ngăn form tải lại trang

        let formData = new FormData(this);

        $.ajax({
            url: "add_banner.php",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function () {
                $("#addBannerForm button[type=submit]").prop("disabled", true).text("Đang thêm...");
            },
            success: function (response) {
                console.log("Server response:", response);
                if (response.trim() === "success") {
                    alert("Thêm banner thành công!");
                    location.reload();
                } else {
                    alert("Lỗi: " + response);
                }
            },
            error: function (xhr, status, error) {
                console.error("Lỗi AJAX:", status, error);
                alert("Lỗi hệ thống! Vui lòng thử lại.");
            },
            complete: function () {
                $("#addBannerForm button[type=submit]").prop("disabled", false).text("Thêm");
            }
        });
    });
});



function confirmDeleteBanner(id) {
    if (confirm("Bạn có chắc muốn xóa banner này?")) {
        $.post("delete_banner.php", { id: id }, function () {
            alert("Xóa banner thành công!");
            location.reload();
        });
    }
}

function confirmDeleteNotification(id) {
    if (confirm("Bạn có chắc muốn xóa thông báo này?")) {
        $.post("delete_notification.php", { id: id }, function () {
            alert("Xóa thông báo thành công!");
            location.reload();
        });
    }
}
</script>

</body>
</html>
