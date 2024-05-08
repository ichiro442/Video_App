<?php
session_start();
require_once('../db_class.php');
require_once('validation.php');

// 公開ページからの遷移でない場合
if ($_GET["u"] !== "un") {
    // 登録者でなければ講師検索画面にリダイレクト
    if ($_SESSION['userType'] !== "student") {
        $_SESSION['flash_message'] = FLASH_MESSAGE[13];
        $dbConnect = new dbConnect();
        $url = $dbConnect->getURL();
        header("Location: " . $url . "Student");
        exit;
    }
}

try {
    $dbConnect = new dbConnect();
    $dbConnect->initPDO();
    $pdo = $dbConnect->getPDO();

    $stmt = $pdo->prepare("SELECT * FROM teachers WHERE id=:id ");
    $stmt->bindValue(":id", $_GET["id"], PDO::PARAM_INT);
    $stmt->execute();
    $teacher = $stmt->fetch();

    // 講師のスケジュールを取得する
    $sql = "SELECT CASE available WHEN 1 THEN '○' ELSE '×' END as title";
    $sql .= " ,start_time as start,(start_time + INTERVAL 30 MINUTE) as end ";
    $sql .= "from teacher_schedules where teacher_id = :id and start_time >= DATE_FORMAT(CURRENT_DATE,'%Y-%m-%d')";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(":id", $_GET["id"], PDO::PARAM_INT);
    $stmt->execute();
    $calendar = $stmt->fetchAll();
} catch (PDOException $e) {
    echo $e->getMessage();
    exit;
}

$title = "講師詳細";
require_once('header.php');
?>
<html>

<head>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
        }

        h2 {
            text-align: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th {
            padding: 0;
            background-color: #fff;
            border: 1px solid #ccc;
            font-weight: normal;
        }

        .container {
            max-width: 800px;
            margin: 50px auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: center;
        }

        input[type="submit"]:hover {
            background-color: #0056b3;
        }

        .cell-selected {
            background-color: #a4e8a4;
        }

        td {
            position: relative;
        }

        select {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            padding: 0;
            margin: 0;
            text-align: center;
        }

        table td {
            background: #DCDCDC;
            padding: 5px;
        }

        table tr:nth-child(odd) td {
            background: #fff;
        }

        table td.grey {
            background: #eee !important;
        }

        table td.white {
            background: #fff;
        }

        th.saturday {
            color: blue;
        }

        th.sunday {
            color: red;
        }

        table td>div {
            background: #66cdaa;
            color: #fff;
            border-radius: 5px;
            font-size: 20px;
        }
    </style>
</head>

<body>
    <div class="teacher-detail-box flex">
        <div class="container flex">
            <div class="teacher-left">
                <div class="img-box">
                    <?php
                    if (isset($teacher["picture"]) || !is_null($teacher["picture"])) {
                        echo '<img src="../uploaded_pictures/' . h($teacher["picture"]) . '" alt="講師の画像"> ';
                    } else {
                        echo '<img src="../Img/person.jpg" alt="デフォルトの画像"> ';
                    }
                    ?>
                </div>
                <!-- <div class="">
                <p><?php echo h($teacher["nickname"]) ?></p>
            </div> -->
            </div>
            <div class="teacher-right">
                <div class="item flex">
                    <p>名前</p>
                    <div class="detail-item"><?php echo h($teacher["nickname"]) ?></div>
                </div>
                <div class="item flex">
                    <p>国籍</p>
                    <div class="detail-item">フィリピン</div>
                </div>
            </div>
        </div>
    </div>
    <div class="">
        <p>カレンダー</p>
        <div class=""></div>
        <table>
            <thead>
                <!-- ここにJavaScriptで日付と曜日が追加されます -->

            </thead>
            <tbody id="calendar-body">
                <!-- ここにJavaScriptで出勤入力欄が追加されます -->
            </tbody>
        </table>
    </div>
</body>
<script>
    // 週間カレンダーを生成する関数
    var dayOfWeek = new Date().getDay(); // 曜日(数値)
    var dayOfWeeksStr = ["日", "月", "火", "水", "木", "金", "土"];

    function generateWeeklyCalendar() {
        const days = [dayOfWeeksStr[dayOfWeek % 7], dayOfWeeksStr[(dayOfWeek + 1) % 7], dayOfWeeksStr[(dayOfWeek + 2) % 7], dayOfWeeksStr[(dayOfWeek + 3) % 7], dayOfWeeksStr[(dayOfWeek + 4) % 7], dayOfWeeksStr[(dayOfWeek + 5) % 7], dayOfWeeksStr[(dayOfWeek + 6) % 7], dayOfWeeksStr[(dayOfWeek + 7) % 7]];
        const calendarBody = document.getElementById("calendar-body");
        calendarBody.innerHTML = "";

        // 現在の日付を取得
        const today = new Date();
        const currentDayIndex = today.getDay(); // 今日の曜日のインデックス

        // 曜日ごとの日付を表示
        const headerRow = document.createElement("tr");
        const timeHeader = document.createElement("th");
        headerRow.appendChild(timeHeader);

        for (let dayIndex = 0; dayIndex < 7; dayIndex++) {
            const date = new Date();
            date.setDate(date.getDate() + dayIndex);
            const headerCell = document.createElement("th");
            if (days[dayIndex] === '日') {
                headerCell.className = 'sunday';
            } else if (days[dayIndex] === '土') {
                headerCell.className = 'saturday';
            }
            const divDate = document.createElement("div");
            const formattedDate = `${("0" + String(date.getMonth() + 1)).slice(-2)}/${("0" + String(date.getDate())).slice(-2)}`;
            divDate.textContent = formattedDate;
            headerCell.appendChild(divDate)

            const divWeek = document.createElement("div");
            const formattedWeek = `(${days[dayIndex]})`;
            divWeek.textContent = formattedWeek;
            headerCell.appendChild(divWeek)
            headerRow.appendChild(headerCell);
        }

        calendarBody.appendChild(headerRow);

        const events = JSON.parse('<?php echo json_encode($calendar) ?>');

        for (let hour = 0; hour < 48; hour++) {
            const row = document.createElement("tr");

            // 時間を表示
            const timeCell = document.createElement("td");
            timeCell.textContent = (Math.floor(hour / 2)).toString().padStart(2, "0") +
                (hour % 2 === 0 ? ":00" : ":30");
            row.appendChild(timeCell);

            // 曜日ごとのセルを作成
            const date = new Date();
            for (let dayIndex = 0; dayIndex < 7; dayIndex++) {
                const year = date.getFullYear();
                const month = ("0" + String(date.getMonth() + 1)).slice(-2);
                const day = ("0" + String(date.getDate())).slice(-2);
                const formatDate = year + "-" + month + "-" + day;
                const strDate = formatDate + " " + timeCell.textContent + ":00";
                const cell = document.createElement("td");

                // 予定を生成
                if (events.filter(event => strDate === event.start).length > 0) {
                    const div = document.createElement("div");
                    div.textContent = "○";
                    cell.appendChild(div);
                    cell.className = "white";
                } else {
                    cell.className = "grey";
                }
                row.appendChild(cell);
                date.setDate(date.getDate() + 1); //1日先に進める
            }

            calendarBody.appendChild(row);
        }
    }

    // 初期化
    window.onload = function() {
        generateWeeklyCalendar();
    };
</script>

</html>