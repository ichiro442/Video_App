<script>
    function generateWeeklyCalendar() {
        // 週間カレンダーを生成する関数
        var dayOfWeek = new Date().getDay(); // 曜日(数値)
        var dayOfWeeksStr = ["日", "月", "火", "水", "木", "金", "土"];

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
            // PHPで取得した$lessons_weekをJavaScriptに渡す
            var lessonsWeek = <?php echo json_encode($lessons_week); ?>;
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
                    let hasBookedLesson = false; // 予約があるかどうかを示すフラグ

                    // すでに予約されている時間帯は✕を表示する
                    for (let i = 0; i < lessonsWeek.length; i++) {
                        var lesson = lessonsWeek[i];

                        if (lesson.start_time === strDate) {
                            div.textContent = "✕";
                            const link = document.createElement('span');
                            link.appendChild(div);
                            cell.appendChild(link);
                            hasBookedLesson = true; // 予約があることを示すフラグを設定
                            cell.className = "white";
                        }
                    }

                    // 予約がない場合は○を表示する
                    if (!hasBookedLesson) {
                        div.textContent = "○";
                        const link = document.createElement('a');
                        // teacher_idとstart_timeと現在の時間を次のページに渡す
                        link.href = 'book_lesson?t_id=<?php echo h($teacher["id"]) ?>&strDate=' + strDate;
                        link.appendChild(div);
                        cell.appendChild(link);
                        cell.className = "white";
                    }
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