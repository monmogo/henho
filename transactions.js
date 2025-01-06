//timkiem
document.getElementById("searchUser").addEventListener("input", async function () {
    let query = this.value.trim().toLowerCase();
    let searchResults = document.getElementById("searchResults");

    if (query.length < 2) {
        searchResults.innerHTML = "";
        searchResults.style.display = "none";
        return;
    }

    const response = await fetch("api_search_users.php?query=" + encodeURIComponent(query));
    const users = await response.json();

    searchResults.innerHTML = "";
    
    if (users.length === 0) {
        searchResults.style.display = "none";
        return;
    }

    searchResults.style.display = "block";

    users.forEach(user => {
        let div = document.createElement("div");
        div.classList.add("autocomplete-item");
        div.innerHTML = `<strong>${user.username}</strong> (${user.points} điểm)`;
        div.onclick = function () {
            document.getElementById("searchUser").value = user.username;
            searchResults.innerHTML = "";
            searchResults.style.display = "none";
        };
        searchResults.appendChild(div);
    });
});

/* Ẩn dropdown khi click bên ngoài */
document.addEventListener("click", function (event) {
    let searchBox = document.getElementById("searchUser");
    let searchResults = document.getElementById("searchResults");

    if (!searchBox.contains(event.target) && !searchResults.contains(event.target)) {
        searchResults.style.display = "none";
    }
});
///Hien thi nap
document.addEventListener("DOMContentLoaded", function () {
    loadAllDepositHistory(1);
});

async function loadAllDepositHistory(page) {
    const response = await fetch(`api_get_deposit_history.php?page=${page}`);
    const data = await response.json();
    const history = data.history;
    const totalPages = data.total_pages;
    const currentPage = data.current_page;

    let historyTable = document.getElementById("historyTable").querySelector("tbody");
    historyTable.innerHTML = "";

    history.forEach(entry => {
        let row = `<tr>
            <td>${entry.username}</td>
            <td>${entry.amount}</td>
            <td>${entry.transaction_date}</td>
        </tr>`;
        historyTable.innerHTML += row;
    });

    updatePaginationButtons(currentPage, totalPages);
}

function updatePaginationButtons(currentPage, totalPages) {
    let pagination = document.getElementById("paginationControls");
    pagination.innerHTML = "";

    if (currentPage > 1) {
        pagination.innerHTML += `<button onclick="loadAllDepositHistory(${currentPage - 1})" class="btn btn-sm btn-secondary">⬅ Trang Trước</button>`;
    }

    pagination.innerHTML += `<span class="current-page">Trang ${currentPage} / ${totalPages}</span>`;

    if (currentPage < totalPages) {
        pagination.innerHTML += `<button onclick="loadAllDepositHistory(${currentPage + 1})" class="btn btn-sm btn-secondary">Trang Tiếp ➡</button>`;
    }
}


///nap diem

document.getElementById("depositButton").addEventListener("click", async function (e) {
    e.preventDefault();

    const username = document.getElementById("searchUser").value.trim();
    const amount = parseInt(document.getElementById("depositAmount").value, 10);

    if (!username || isNaN(amount) || amount <= 0) {
        alert("Vui lòng nhập tên user và số điểm hợp lệ!");
        return;
    }

    const response = await fetch("api_deposit_points.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ username: username, amount: amount })
    });

    const result = await response.json();

    if (result.status === "success") {
        alert("Nạp điểm thành công!");
        document.getElementById("depositAmount").value = "";
        updateUserPoints(username, result.new_points);
        loadUserDepositHistory(username);
    } else {
        alert("Lỗi: " + result.message);
    }
});

function updateUserPoints(username, newPoints) {
    let options = document.querySelectorAll("#depositUser option");
    options.forEach(option => {
        if (option.textContent.includes(username)) {
            option.textContent = `${username} (${newPoints} điểm)`;
        }
    });
}

async function loadUserDepositHistory(username) {
    const response = await fetch(`api_user_deposit_history.php?username=${username}`);
    const history = await response.json();

    let historyTable = document.getElementById("historyTable").querySelector("tbody");
    historyTable.innerHTML = "";

    history.forEach(entry => {
        let row = `<tr>
            <td>${entry.username}</td>
            <td>${entry.amount}</td>
            <td>${entry.transaction_date}</td>
        </tr>`;
        historyTable.innerHTML += row;
    });
}

////////////////

async function approveWithdraw(transactionId) {
    const response = await fetch("api_approve_withdraw.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ transaction_id: transactionId })
    });

    const result = await response.json();

    if (result.status === "success") {
        alert("Duyệt rút điểm thành công!");
        updateTransactionStatus(transactionId, "approved");
    } else {
        alert("Lỗi: " + result.message);
    }
}

async function rejectWithdraw(transactionId) {
    const response = await fetch("api_reject_withdraw.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ transaction_id: transactionId })
    });

    const result = await response.json();

    if (result.status === "success") {
        alert("Yêu cầu rút đã bị từ chối!");
        updateTransactionStatus(transactionId, "rejected");
    } else {
        alert("Lỗi: " + result.message);
    }
}

function updateTransactionStatus(transactionId, status) {
    const row = document.getElementById(`transaction-${transactionId}`);
    if (row) {
        row.cells[3].innerText = status.charAt(0).toUpperCase() + status.slice(1);
        row.cells[3].className = status === "approved" ? "status-approved" : "status-rejected";
        row.cells[4].innerHTML = "<span>Đã xử lý</span>";
    }
}

function updateUserPoints(userId, newPoints) {
    // Gửi cập nhật đến profile.php nếu user đang mở trang đó
    const userPointsElement = document.getElementById("userPoints");
    if (userPointsElement) {
        userPointsElement.innerText = newPoints;
    }
}
