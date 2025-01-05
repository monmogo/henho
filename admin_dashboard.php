<?php
session_start();
require_once 'config.db.php';

// Kiểm tra quyền truy cập
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Truy vấn danh sách người dùng với Prepared Statements
$sql = "SELECT id, username, email, role, points, trust_points, fullname, gender, 
               bank_account, card_holder_name, bank_name, avatar 
        FROM users";
$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
$users = $result->fetch_all(MYSQLI_ASSOC);
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $id = intval($_POST['id']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $points = intval($_POST['points']);
    $trust_points = intval($_POST['trust_points']);
    $fullname = trim($_POST['fullname']);
    $gender = trim($_POST['gender']);
    $bank_account = trim($_POST['bank_account']);
    $card_holder_name = trim($_POST['card_holder_name']);
    $bank_name = trim($_POST['bank_name']);
    $role = trim($_POST['role']); // Cần kiểm tra kỹ giá trị này!

    // Kiểm tra giá trị role hợp lệ
    $valid_roles = ['admin', 'user']; 
    if (!in_array($role, $valid_roles)) {
        echo "error_sql: Vai trò không hợp lệ!";
        exit();
    }

    // Chuẩn bị truy vấn
    $sql = "UPDATE users SET username=?, email=?, points=?, trust_points=?, fullname=?, gender=?, bank_account=?, card_holder_name=?, bank_name=?, role=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo "error_sql: Lỗi chuẩn bị truy vấn.";
        exit();
    }

    $stmt->bind_param("ssiiisssssi", $username, $email, $points, $trust_points, $fullname, $gender, $bank_account, $card_holder_name, $bank_name, $role, $id);
    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error_sql: " . $stmt->error;
    }
    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Người Dùng</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css">
</head>
<body>
    <div class="dashboard-container">
        <aside class="navigation">
            <?php include 'navigation.php'; ?>
        </aside>

        <main class="main-content">
            <h1 class="text-center">Quản Lý Người Dùng</h1>
            <div class="text-right mb-3">
                <button class="btn btn-success" data-toggle="modal" data-target="#addUserModal">Thêm Người Dùng</button>
            </div>
            <div class="row mb-3">
    <div class="col-md-6">
        <input type="text" class="form-control" id="searchUser" placeholder="Tìm kiếm người dùng...">
    </div>
    <div class="col-md-3">
        <select id="filterRole" class="form-control">
            <option value="">Tất cả vai trò</option>
            <option value="admin">Admin</option>
            <option value="user">Người dùng</option>
        </select>
    </div>
</div>

            <div class="table-container">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr class="table-primary">
                            <th>#</th>
                            <th>Tên Đăng Nhập</th>
                            <th>Email</th>
                            <th>Vai Trò</th>
                            <th>Điểm</th>
                            <th>Điểm Tin Cậy</th>
                            <th>Họ và Tên</th>
                            <th>Giới Tính</th>
                            <th>Tài Khoản Ngân Hàng</th>
                            <th>Chủ Thẻ</th>
                            <th>Ngân Hàng</th>
                            <th>Ảnh Đại Diện</th>
                            <th>Hành Động</th>
                        </tr>
                    </thead>
                    <tbody id="userTable">
                        <?php if (!empty($users)): ?>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?= htmlspecialchars($user['id']); ?></td>
                                    <td><?= htmlspecialchars($user['username']); ?></td>
                                    <td><?= htmlspecialchars($user['email']); ?></td>
                                    <td><?= htmlspecialchars($user['role']); ?></td>
                                    <td><?= htmlspecialchars($user['points']); ?></td>
                                    <td><?= htmlspecialchars($user['trust_points']); ?></td>
                                    <td><?= htmlspecialchars($user['fullname']); ?></td>
                                    <td><?= htmlspecialchars($user['gender']); ?></td>
                                    <td><?= htmlspecialchars($user['bank_account']); ?></td>
                                    <td><?= htmlspecialchars($user['card_holder_name']); ?></td>
                                    <td><?= htmlspecialchars($user['bank_name']); ?></td>
                                    <td>
                                        <img src="<?= !empty($user['avatar']) ? htmlspecialchars($user['avatar']) : 'default-avatar.png'; ?>" 
                                             alt="Avatar" class="avatar-preview">
                                    </td>
                                    <td>
    <?php if ($_SESSION['role'] === 'admin'): ?>
        <button class="btn btn-primary btn-sm" onclick="openEditUserModal(<?= htmlspecialchars(json_encode($user)); ?>)">Sửa</button>
        <button class="btn btn-danger btn-sm" onclick="loadDeleteForm(<?= $user['id']; ?>)">Xóa</button>
    <?php else: ?>
        <span class="text-muted">Không có quyền</span>
    <?php endif; ?>
</td>

                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="13" class="text-center">Không có người dùng nào.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

 <!-- Modal Thêm Người Dùng -->
<div class="modal fade" id="addUserModal" tabindex="-1" role="dialog" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="addUserForm">
                <div class="modal-header">
                    <h5 class="modal-title">Thêm Người Dùng</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="addUserError" class="alert alert-danger d-none"></div>
                    <div class="form-group">
                        <label for="addUsername">Tên Đăng Nhập</label>
                        <input type="text" class="form-control" id="addUsername" name="username" required>
                    </div>
                    <div class="form-group">
                        <label for="addEmail">Email</label>
                        <input type="email" class="form-control" id="addEmail" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="addPassword">Mật Khẩu</label>
                        <input type="password" class="form-control" id="addPassword" name="password" required>
                    </div>
                    <div class="form-group">
                        <label for="addRole">Vai Trò</label>
                        <select class="form-control" id="addRole" name="role" required>
                            <option value="user">Người dùng</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">
                        <span class="spinner-border spinner-border-sm d-none" id="addUserLoading"></span>
                        Thêm
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Sửa Người Dùng -->
<div class="modal fade" id="editUserModal" tabindex="-1" role="dialog" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="editUserForm" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title">Sửa Người Dùng</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <div id="editUserError" class="alert alert-danger d-none"></div>

                    <input type="hidden" id="editUserId" name="id">

                    <div class="form-group">
                        <label for="editUsername">Tên Đăng Nhập</label>
                        <input type="text" class="form-control" id="editUsername" name="username" required>
                    </div>

                    <div class="form-group">
                        <label for="editEmail">Email</label>
                        <input type="email" class="form-control" id="editEmail" name="email" required>
                    </div>

                    <div class="form-group">
                        <label for="editPoints">Điểm</label>
                        <input type="number" class="form-control" id="editPoints" name="points" required>
                    </div>

                    <div class="form-group">
                        <label for="editTrustPoints">Điểm Tin Cậy</label>
                        <input type="number" class="form-control" id="editTrustPoints" name="trust_points" required>
                    </div>

                    <div class="form-group">
                        <label for="editRole">Vai Trò</label>
                        <select class="form-control" id="editRole" name="role" required>
                            <option value="user">Người dùng</option>
                            <option value="admin">Admin</option>
                            <option value="moderator">Moderator</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="editFullname">Họ và Tên</label>
                        <input type="text" class="form-control" id="editFullname" name="fullname">
                    </div>

                    <div class="form-group">
                        <label for="editGender">Giới Tính</label>
                        <select class="form-control" id="editGender" name="gender">
                            <option value="male">Nam</option>
                            <option value="female">Nữ</option>
                            <option value="other">Khác</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="editAvatar">Ảnh Đại Diện</label>
                        <input type="file" class="form-control-file" id="editAvatar" name="avatar">
                        <img id="currentAvatarPreview" src="default-avatar.png" alt="Avatar" class="avatar-preview mt-2">
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary">
                        <span class="spinner-border spinner-border-sm d-none" id="editUserLoading"></span>
                        Cập Nhật
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- Modal Xóa Người Dùng -->
<div class="modal fade" id="deleteUserModal" tabindex="-1" role="dialog" aria-labelledby="deleteUserModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="deleteUserForm">
                <div class="modal-header">
                    <h5 class="modal-title">Xóa Người Dùng</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="deleteUserError" class="alert alert-danger d-none"></div>
                    <p>Bạn có chắc chắn muốn xóa người dùng này không?</p>
                    <input type="hidden" id="deleteUserId" name="id">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-danger">
                        <span class="spinner-border spinner-border-sm d-none" id="deleteUserLoading"></span>
                        Xóa
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>


    <script src="//code.jquery.com/jquery-1.11.1.min.js"></script>
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.0/js/bootstrap.min.js"></script>
    <script src="js/admin.js"></script>
    <script>
        $(document).ready(function () {
    function showLoading(buttonId, loadingId, show = true) {
        $(buttonId).prop("disabled", show);
        $(loadingId).toggleClass("d-none", !show);
    }

    function showError(errorId, message) {
        $(errorId).removeClass("d-none").text(message);
    }

    // Thêm người dùng
    $("#addUserForm").submit(async function (e) {
        e.preventDefault();
        showLoading("#addUserForm button[type=submit]", "#addUserLoading", true);
        $("#addUserError").addClass("d-none");

        try {
            const response = await $.post("add_user.php", $(this).serialize(), null, "json");
            if (response.status === "success") {
                alert("Thêm người dùng thành công!");
                location.reload();
            } else {
                showError("#addUserError", response.message || "Có lỗi xảy ra.");
            }
        } catch {
            showError("#addUserError", "Không thể kết nối tới máy chủ.");
        } finally {
            showLoading("#addUserForm button[type=submit]", "#addUserLoading", false);
        }
    });

    // Xóa người dùng
    $("#deleteUserForm").submit(async function (e) {
        e.preventDefault();
        showLoading("#deleteUserForm button[type=submit]", "#deleteUserLoading", true);
        $("#deleteUserError").addClass("d-none");

        try {
            const response = await $.post("delete_user.php", $(this).serialize(), null, "json");
            if (response.status === "success") {
                alert("Xóa người dùng thành công!");
                location.reload();
            } else {
                showError("#deleteUserError", response.message || "Có lỗi xảy ra.");
            }
        } catch {
            showError("#deleteUserError", "Không thể kết nối tới máy chủ.");
        } finally {
            showLoading("#deleteUserForm button[type=submit]", "#deleteUserLoading", false);
        }
    });
});

    </script>
<script src="js/edit_user.js"></script>



</body>
</html>
