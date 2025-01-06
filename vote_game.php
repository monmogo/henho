<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đặt Cược Game</title>
    <link rel="stylesheet" href="styles.css">
    <script>
        function listenForResult(gameId) {
            let eventSource = new EventSource("sse.php?game_id=" + gameId);

            eventSource.onmessage = function(event) {
                let data = JSON.parse(event.data);
                document.getElementById("status").innerText = "Kết quả: " + data.message;

                if (data.status === "success") {
                    eventSource.close(); // Dừng lắng nghe khi có kết quả
                }
            };

            eventSource.onerror = function() {
                console.error("Lỗi kết nối SSE.");
                eventSource.close();
            };
        }

        document.getElementById("betForm").addEventListener("submit", function (e) {
            e.preventDefault();

            let gameId = document.getElementById("game_id").value;
            let choice = document.querySelector("input[name='choice']:checked");
            let betAmount = document.getElementById("bet_amount").value;

            if (!choice || betAmount <= 0) {
                alert("Vui lòng chọn cược và nhập số tiền hợp lệ.");
                return;
            }

            let formData = new FormData();
            formData.append("game_id", gameId);
            formData.append("choice", choice.value);
            formData.append("bet_amount", betAmount);

            fetch("vote_game.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById("status").innerText = data.message;
                if (data.status === "success") {
                    listenForResult(gameId); // Bắt đầu lắng nghe kết quả
                }
            });
        });
    </script>
</head>
<body>
    <h1>Chào, <?php echo htmlspecialchars($_SESSION['username'] ?? 'Người chơi'); ?>!</h1>
    <p>Số điểm hiện tại: <span id="points">...</span></p>

    <h2>Chọn Cược</h2>
    <form id="betForm">
        <label>Chọn Game:</label>
        <select name="game_id" id="game_id">
            <option value="1">Game 1</option>
            <option value="2">Game 2</option>
        </select>

        <label>Chọn Kết Quả:</label>
        <input type="radio" name="choice" value="A"> A
        <input type="radio" name="choice" value="B"> B
        <input type="radio" name="choice" value="C"> C
        <input type="radio" name="choice" value="D"> D

        <label>Nhập số tiền cược:</label>
        <input type="number" id="bet_amount" name="bet_amount" placeholder="Nhập số tiền cược" min="1">
        
        <button type="submit">Đặt Cược</button>
    </form>
    <p id="status"></p>
</body>
</html>
