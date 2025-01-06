<?php
function fetch_api_data($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Bỏ qua SSL nếu cần
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Cho phép redirect nếu có
    $output = curl_exec($ch);
    curl_close($ch);
    return $output;
}

$api_url = "https://api.sexnguon.com/api.php/provide/vod/?ac=list";
$json_data = fetch_api_data($api_url);

// Kiểm tra lỗi
if (!$json_data) {
    die("🚨 Không thể lấy dữ liệu từ API!");
}

// Giải mã JSON
$data = json_decode($json_data, true);

// Kiểm tra dữ liệu trả về
if (!isset($data['list'])) {
    die("🚨 API không trả về danh sách phim hoặc dữ liệu không hợp lệ!");
}

// Hiển thị dữ liệu API để kiểm tra
header('Content-Type: application/json');
echo json_encode($data, JSON_PRETTY_PRINT);
?>
