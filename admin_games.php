<?php
session_start();
require_once 'config.db.php';

// Kiểm tra quyền Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit();
}

// Lấy danh sách games từ CSDL
$sql = "SELECT * FROM vote_games";
$result = $conn->query($sql);
$games = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Games</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="css/admin_style.css">
    <link rel="stylesheet" href="css/games.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>

<div class="dashboard-container">
    <aside class="navi">
        <?php include 'navigation.php'; ?>
    </aside>

    <main class="main-content">
        <h1 class="text-center my-4">🎮 Quản Lý Games</h1>

        <div class="d-flex justify-content-between align-items-center mb-3">
            <input type="text" class="form-control w-50" id="searchGame" placeholder="🔍 Tìm kiếm games...">
            <button class="btn btn-success" data-toggle="modal" data-target="#addGameModal">➕ Thêm Game</button>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-hover text-center">
                <thead class="thead-dark">
                    <tr>
                        <th>ID</th>
                        <th>Tên Game</th>
                        <th>Ảnh Đại diện</th>
                        <th>Số Kỳ Quay</th>
                        <th>% Lợi Nhuận</th>
                        <th>Hành Động</th>
                    </tr>
                </thead>
                <tbody id="gameTable">
                    <?php foreach ($games as $game): ?>
                        <tr>
                            <td><?= $game['id']; ?></td>
                            <td><?= htmlspecialchars($game['name']); ?></td>
                            <td>
                                <img src="<?= !empty($game['cover_image']) ? htmlspecialchars($game['cover_image']) : 'uploads/default-game.jpg'; ?>" class="game-cover">
                            </td>
                            <td><?= $game['total_rounds']; ?></td>
                            <td><?= $game['profit_share']; ?>%</td>
                            <td>
                                <button class="btn btn-primary btn-sm" onclick="openEditGameModal(<?= htmlspecialchars(json_encode($game)); ?>)">✏️ Sửa</button>
                                <button class="btn btn-danger btn-sm" onclick="confirmDeleteGame(<?= $game['id']; ?>)">🗑️ Xóa</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<!-- Modal Thêm Game -->
<div class="modal fade" id="addGameModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="addGameForm" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title">➕ Thêm Game</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="text" class="form-control mt-2" name="name" placeholder="Tên Game" required>
                    <input type="file" class="form-control mt-2" name="cover_image" accept="image/*" required>
                    <img id="previewAddImage" class="img-fluid mt-2" style="max-height: 100px; display: none;">
                    <input type="number" class="form-control mt-2" name="total_rounds" placeholder="Số Kỳ Quay" min="1" required>
                    <input type="number" class="form-control mt-2" name="profit_share" placeholder="% Lợi Nhuận" min="0" max="100" required>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Thêm</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Chỉnh sửa Game -->
<!-- Modal Chỉnh sửa Game -->
<div class="modal fade" id="editGameModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editGameForm" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title">✏️ Chỉnh sửa Game</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id">
                    <input type="text" class="form-control mt-2" name="name" required>
                    <input type="number" class="form-control mt-2" name="total_rounds" min="1" required>
                    <input type="number" class="form-control mt-2" name="profit_share" min="0" max="100" required>
                    
                    <label class="mt-2">Ảnh Đại Diện</label>
                    <input type="file" class="form-control mt-2" name="cover_image" id="editCoverImage" accept="image/*">
                    <img id="editPreviewImage" src="" class="img-fluid mt-2" style="max-height: 200px; display: block;">
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Lưu</button>
                </div>
            </form>
        </div>
    </div>
</div>


<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<script>
$(document).ready(function () {
    $("#addGameForm").submit(function (e) {
        e.preventDefault();
        let formData = new FormData(this);

        $.ajax({
            url: "add_game.php",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            success: function () {
                location.reload();
            },
            error: function () {
                alert("Lỗi hệ thống! Vui lòng thử lại.");
            }
        });
    });

    $("#editGameForm").submit(function (e) {
        e.preventDefault();
        let formData = new FormData(this);

        $.ajax({
            url: "edit_game.php",
            type: "POST",
            data: formData,
            processData: false,
            contentType: false,
            success: function () {
                location.reload();
            },
            error: function () {
                alert("Lỗi hệ thống! Vui lòng thử lại.");
            }
        });
    });

    // Hiển thị ảnh xem trước khi chọn ảnh mới
    $("#editCoverImage").change(function () {
        let reader = new FileReader();
        reader.onload = function (e) {
            $("#editPreviewImage").attr("src", e.target.result);
        };
        reader.readAsDataURL(this.files[0]);
    });
});

// Mở modal sửa game và điền dữ liệu
function openEditGameModal(game) {
    $("#editGameModal input[name='id']").val(game.id);
    $("#editGameModal input[name='name']").val(game.name);
    $("#editGameModal input[name='total_rounds']").val(game.total_rounds);
    $("#editGameModal input[name='profit_share']").val(game.profit_share);
    $("#editPreviewImage").attr("src", game.cover_image);
    $("#editGameModal").modal("show");
}

// Xóa game
function confirmDeleteGame(id) {
    if (confirm("Bạn có chắc muốn xóa game này?")) {
        $.post("delete_game.php", { id: id }, function () {
            location.reload();
        });
    }
}

</script>

</body>
</html>
