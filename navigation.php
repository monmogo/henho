<style>/* Reset và cấu hình cơ bản */
body {
    font-family: 'Arial', sans-serif;
}

.navi {
    background-color: #343a40; /* Màu nền tối */
    padding: 20px 0;
    height: 100vh;
    position: fixed;
    width: 240px;
    overflow: hidden;
}

.navi ul {
    list-style-type: none;
    padding: 0;
    margin: 0;
}

.navi ul li {
    margin: 15px 0;
}

.navi ul li a {
    text-decoration: none;
    color: #ffffff; /* Màu chữ */
    font-size: 16px;
    padding: 10px 20px;
    display: flex;
    align-items: center;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.navi ul li a i {
    margin-right: 15px; /* Khoảng cách giữa icon và chữ */
    font-size: 18px;
}

.navi ul li a span {
    display: inline-block;
    white-space: nowrap;
    transition: color 0.3s ease;
}

.navi ul li a:hover {
    background-color: #495057; /* Màu nền khi hover */
    color: #ffc107; /* Màu chữ khi hover */
}

.navi ul li a:hover span {
    color: #ffc107; /* Màu chữ khi hover */
}

.navi ul li.active a {
    background-color: #007bff; /* Màu nền cho menu đang được active */
    color: #fff; /* Màu chữ */
    font-weight: bold;
}

.navi ul li.active a i {
    color: #ffffff;
}

.navi ul li.active a span {
    color: #ffffff;
}

/* Responsive */
@media (max-width: 768px) {
    .navi {
        position: relative;
        height: auto;
        width: 100%;
    }

    .navi ul li a {
        justify-content: center;
    }

    .navi ul li a span {
        display: none; /* Ẩn chữ trên màn hình nhỏ */
    }
    .navi ul li.active a {
    background-color: #007bff; /* Màu nền khi active */
    color: #fff; /* Màu chữ */
    font-weight: bold; /* Chữ đậm */
}

.navi ul li.active a i {
    color: #ffffff;
}

.navi ul li.active a span {
    color: #ffffff;
}

}
</style>




    <ul>
        <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'admin_dashboard.php' ? 'active' : ''; ?>">
            <a href="admin_dashboard.php"><i class="fa fa-home" aria-hidden="true"></i><span>Quản Lý Người Dùng</span></a>
        </li>
        <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'manage_transactions.php' ? 'active' : ''; ?>">
            <a href="manage_transactions.php"><i class="fa fa-tasks" aria-hidden="true"></i><span>Lịch sử giao dịch</span></a>
        </li>
        <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'admin_games.php' ? 'active' : ''; ?>">
            <a href="admin_games.php"><i class="fa fa-bar-chart" aria-hidden="true"></i><span>Quản lý Games</span></a>
        </li>
        <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'admin_movies.php' ? 'active' : ''; ?>">
            <a href="admin_movies.php"><i class="fa fa-film" aria-hidden="true"></i><span>Quản lý phim</span></a>
        </li>
        <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'admin_set_result.php' ? 'active' : ''; ?>">
            <a href="admin_set_result.php"><i class="fa fa-users" aria-hidden="true"></i><span>Users</span></a>
        </li>
        <li class="<?php echo basename($_SERVER['PHP_SELF']) == 'admin_settings.php' ? 'active' : ''; ?>">
            <a href="admin_settings.php"><i class="fa fa-cogs" aria-hidden="true"></i><span>Cài đặt</span></a>
        </li>
    </ul>

