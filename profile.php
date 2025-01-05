<?php
session_start();
require_once 'config.db.php';

// Kiểm tra xem người dùng đã đăng nhập hay chưa
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); // Nếu chưa đăng nhập, chuyển hướng đến trang đăng nhập
    exit();
}

// Lấy thông tin người dùng từ cơ sở dữ liệu
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
} else {
    echo "Không tìm thấy thông tin người dùng.";
    exit();
}
$hasBankInfo = !empty($user['bank_account']) && !empty($user['card_holder_name']) && !empty($user['bank_name']);

?>

<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Hồ Sơ Người Dùng</title>
  <link rel="stylesheet" href="css/profile.css">
  <link rel="stylesheet" href="css/tabbar.css">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body>
  <!-- Header -->
  <div class="profile-header">
    <div class="profile-info">
      <img src="<?php echo htmlspecialchars($user['avatar']); ?>" alt="Avatar" class="profile-avatar">
      <div>
        <h2 class="username"><?php echo htmlspecialchars($user['username']); ?></h2>
        <p class="vip-status">
          <img src="image/v1.png" alt="VIP"> 
          <?php echo htmlspecialchars($user['role'] == 'admin' ? 'Admin' : 'VIP 1'); ?>
        </p>
      </div>
    </div>
    <div class="profile-icons">
    <i class="fas fa-headset header-icon" title="Hỗ trợ"></i>
    <i class="fas fa-globe header-icon" title="Ngôn ngữ"></i>
    <i class="fas fa-cog header-icon" title="Cài đặt" onclick="navigateToSettings()"></i>
</div>

  </div>

 
  <!-- Actions -->
  <div class="profile-actions">
    <button class="action-btn" onclick="navigateTo('deposit.php')">
      <i class="fas fa-coins"></i> Nạp điểm
    </button>
    <button class="action-btn" onclick="openWithdrawModal()">
      <i class="fas fa-wallet"></i> Rút điểm
    </button>
  </div>
   <!-- Points Section -->
   <div class="profile-points">
    <div class="point-item">
    <div class="point-item">
      <h3 id="userPoints"><?php echo htmlspecialchars($user['points']); ?></h3>
      <p>Số điểm</p>
    </div>
    </div>
    <div class="point-item">
      <h3 id="trustPoints"><?php echo htmlspecialchars($user['trust_points']); ?></h3>
      <p>Điểm tín nhiệm</p>
      <!-- <button class="refresh-btn" onclick="refreshUserPoints()">🔄</button> -->
    </div>
  </div>

  <!-- Links Section -->
  <div class="profile-links">
    <div class="link-item">
      <i class="fas fa-file-alt"></i>
      <p>Chi tiết tài khoản</p>
    </div>
    <div class="link-item">
      <i class="fas fa-user"></i>
      <p>Thông tin cá nhân</p>
    </div>
    <div class="link-item">
      <i class="fas fa-credit-card"></i>
      <p>Lịch sử rút</p>
    </div>
    <div class="link-item">
      <i class="fas fa-money-bill-wave"></i>
      <p>Lịch sử nạp</p>
    </div>
    <div class="link-item">
      <i class="fas fa-gamepad"></i>
      <p>Lịch sử thành viên</p>
    </div>
    <div class="link-item">
      <i class="fas fa-university"></i>
      <p>Liên kết ngân hàng</p>
    </div>
  </div>
 <!-- Popup Rút Tiền -->
 <div id="withdrawModal" class="modal">
    <div class="modal-content">
      <span class="close" onclick="closeWithdrawModal()">&times;</span>
      <h3>Yêu Cầu Rút Điểm</h3>

      <?php if (!$hasBankInfo): ?>
        <p class="error-text">❌ Bạn chưa nhập thông tin ngân hàng! Hãy cập nhật tại <a href="settings.php">Cài Đặt</a></p>
      <?php else: ?>
        <p>Ngân hàng: <strong><?php echo htmlspecialchars($user['bank_name']); ?></strong></p>
        <p>Chủ tài khoản: <strong><?php echo htmlspecialchars($user['card_holder_name']); ?></strong></p>
        <p>Số tài khoản: <strong><?php echo htmlspecialchars($user['bank_account']); ?></strong></p>

        <label for="withdrawAmount">Số điểm muốn rút:</label>
        <input type="number" id="withdrawAmount" min="1" max="<?php echo $user['points']; ?>" required>
        <button class="btn-confirm" onclick="submitWithdraw()">Xác Nhận Rút</button>
      <?php endif; ?>
    </div>
  </div>
  <!-- Tab Bar -->
  <?php include 'tabbar.php'; ?>


  <script>
   function navigateTo(page) {
      window.location.href = page;
    }

    function openWithdrawModal() {
      document.getElementById("withdrawModal").style.display = "flex";
    }

    function closeWithdrawModal() {
      document.getElementById("withdrawModal").style.display = "none";
    }

    function submitWithdraw() {
      let amount = document.getElementById("withdrawAmount").value;
      if (amount < 1) {
        alert("Số điểm rút phải lớn hơn 0!");
        return;
      }

      fetch("api_withdraw.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ amount: amount })
      })
      .then(response => response.json())
      .then(data => {
        alert(data.message);
        if (data.status === "success") {
          closeWithdrawModal();
          location.reload();
        }
      })
      .catch(error => console.error("Lỗi:", error));
    }
  </script>
</body>
</html>
