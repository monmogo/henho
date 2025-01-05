<?php
session_start();
require_once 'config.db.php';

// Kiểm tra quyền admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Lấy danh sách user
$users = $conn->query("SELECT id, username, points FROM users ORDER BY username ASC");

// Lấy danh sách tất cả yêu cầu rút điểm (bao gồm cả đã duyệt và từ chối)
$withdraw_requests = $conn->query("
    SELECT th.id, u.username, th.amount, th.transaction_date, th.status
    FROM transaction_history th
    JOIN users u ON th.user_id = u.id
    WHERE th.transaction_type = 'withdraw'
    ORDER BY th.transaction_date DESC
");
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Quản Lý Nạp/Rút Điểm</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.6.3/css/font-awesome.min.css">
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="css/transactions.css">
    <style>
        .table-container {
            display: flex;
            justify-content: space-between;
            gap: 20px;
        }
        .table-wrapper {
            flex: 1;
            background: white;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .table th, .table td {
            padding: 12px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }
        .table thead {
            background-color: #007bff;
            color: white;
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<aside class="navi">
    <?php include 'navigation.php'; ?>
</aside>

<!-- Nội dung chính -->
<div class="container">
    <h2 class="text-center">📊 Quản Lý Nạp/Rút Điểm</h2>

    <div class="table-container">
        <!-- Bảng Nạp Điểm -->
        <div class="table-wrapper">
            <h3>📥 Nạp Điểm</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>Tìm Kiếm User</th>
                        <th>Số Điểm</th>
                        <th>Hành Động</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <input type="text" id="searchUser" class="form-control" placeholder="🔍 Nhập tên user...">
                            <div id="searchResults" class="autocomplete-dropdown"></div>
                        </td>
                        <td>
                            <input type="number" id="depositAmount" class="form-control" required placeholder="Nhập số điểm">
                        </td>
                        <td>
                            <button type="submit" id="depositButton" class="btn btn-primary">💰 Nạp Điểm</button>
                        </td>
                    </tr>
                </tbody>
            </table>

            <hr>

            <h3>📜 Lịch Sử Nạp Điểm</h3>
            <table class="table" id="historyTable">
                <thead>
                    <tr>
                        <th>Người Dùng</th>
                        <th>Số Điểm</th>
                        <th>Ngày Nạp</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Dữ liệu lịch sử sẽ được cập nhật động -->
                </tbody>
            </table>
            <!-- Điều khiển phân trang -->
<div id="paginationControls" class="pagination-container"></div>
        </div>

        <!-- Bảng Yêu Cầu Rút Điểm -->
        <div class="table-wrapper">
            <h3>📤 Yêu Cầu Rút Điểm</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>Người Dùng</th>
                        <th>Số Điểm</th>
                        <th>Ngày Yêu Cầu</th>
                        <th>Trạng Thái</th>
                        <th>Hành Động</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($request = $withdraw_requests->fetch_assoc()): ?>
                        <tr id="transaction-<?php echo $request['id']; ?>">
                            <td><?php echo htmlspecialchars($request['username']); ?></td>
                            <td><?php echo number_format($request['amount'], 2); ?></td>
                            <td><?php echo $request['transaction_date']; ?></td>
                            <td class="<?php echo 'status-' . $request['status']; ?>">
                                <?php echo ucfirst($request['status']); ?>
                            </td>
                            <td>
                                <?php if ($request['status'] === 'pending'): ?>
                                    <button class="btn btn-success btn-sm" onclick="approveWithdraw(<?php echo $request['id']; ?>)">✔ Duyệt</button>
                                    <button class="btn btn-danger btn-sm" onclick="rejectWithdraw(<?php echo $request['id']; ?>)">✖ Từ Chối</button>
                                <?php else: ?>
                                    <span>Đã xử lý</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="transactions.js"></script>
</body>
</html>
