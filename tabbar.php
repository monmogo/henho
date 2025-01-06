<!-- Tab Bar -->
<div class="navigation-bar">
    <div class="nav-item" onclick="navigateTo('index.php')">
      <i class="fas fa-home"></i>
      <div>Trang chủ</div>
    </div>
    <div class="nav-item" onclick="navigateTo('vote_list.php')">
      <i class="fas fa-cog"></i>
      <div>Sảnh Bình Chọn</div>
    </div>
    <div class="nav-item" onclick="navigateTo('cinema.php')">
      <i class="fas fa-film"></i>
      <div>Rạp Chiếu Phim</div>
    </div>
    <div class="nav-item active" onclick="navigateTo('profile.php')">
      <i class="fas fa-user-circle"></i>
      <div>Hồ sơ</div>
    </div>
  </div>

<script>
    function navigateTo(url) {
        window.location.href = url;
    }

    // Đánh dấu tab hiện tại là active
    document.addEventListener("DOMContentLoaded", function() {
        let navItems = document.querySelectorAll(".nav-item");
        let currentPage = window.location.pathname.split("/").pop();

        navItems.forEach(item => {
            let link = item.getAttribute("onclick").match(/'(.*?)'/)[1];
            if (currentPage === link) {
                item.classList.add("active");
            }
        });
    });
</script>
