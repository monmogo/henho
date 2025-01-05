// Điều hướng tới trang khác với hiệu ứng trượt ngang (slide-out)
function navigateTo(page) {
    document.body.classList.add("slide-out"); // Thêm hiệu ứng trượt ra ngoài
    setTimeout(() => {
      window.location.href = page; // Điều hướng tới trang mới sau hiệu ứng
    }, 500); // Thời gian hiệu ứng (500ms)
  }
  
  // Hiệu ứng slide-in khi tải trang
  document.addEventListener("DOMContentLoaded", () => {
    document.body.classList.add("loaded"); // Thêm class để hiển thị hiệu ứng trượt vào
    updateActiveTab(); // Cập nhật trạng thái active của tab
  });
  
  // Cập nhật trạng thái "active" cho tab hiện tại
  function updateActiveTab() {
    const currentPath = window.location.pathname.split("/").pop(); // Lấy tên file hiện tại
    const navItems = document.querySelectorAll(".nav-item");
  
    navItems.forEach((item) => {
      const link = item.getAttribute("onclick").match(/'([^']+)'/)[1]; // Lấy đường dẫn từ onclick
      if (currentPath === link) {
        item.classList.add("active"); // Thêm trạng thái active
      } else {
        item.classList.remove("active"); // Xóa trạng thái active
      }
    });
  }
  ///
  document.addEventListener("DOMContentLoaded", () => {
    const slider = document.querySelector(".interaction-slider .interaction-list");
    const prevBtn = document.querySelector(".prev-btn");
    const nextBtn = document.querySelector(".next-btn");

    if (slider && prevBtn && nextBtn) {
        nextBtn.addEventListener("click", () => {
            slider.scrollBy({ left: 150, behavior: "smooth" });
        });

        prevBtn.addEventListener("click", () => {
            slider.scrollBy({ left: -150, behavior: "smooth" });
        });
    }
});
$(document).ready(function() {
  // Tự động chuyển đổi banner mỗi 5 giây
  $('#bannerCarousel').carousel({
      interval: 5000, // 5000ms = 5 giây
      pause: "hover" // Dừng khi di chuột vào
  });
});
