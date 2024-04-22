<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>週間カレンダー</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      margin: 0;
      padding: 0;
      background-color: #f4f4f4;
    }

    .container {
      max-width: 800px;
      margin: 50px auto;
      background-color: #fff;
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    h2 {
      text-align: center;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
    }

    th,
    td {
      border: 1px solid #ccc;
      padding: 10px;
      text-align: center;
    }

    th {
      background-color: #f0f0f0;
    }

    input[type="text"] {
      width: calc(100% - 20px);
      padding: 5px;
      border: 1px solid #ccc;
      border-radius: 5px;
    }

    input[type="submit"] {
      padding: 5px 10px;
      background-color: #007bff;
      color: #fff;
      border: none;
      border-radius: 5px;
      cursor: pointer;
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
  </style>
</head>

<body>
  <div class="container">
    <h2>週間カレンダー</h2>
    <table>
      <thead>
        <!-- ここにJavaScriptで日付と曜日が追加されます -->

      </thead>
      <tbody id="calendar-body">
        <!-- ここにJavaScriptで出勤入力欄が追加されます -->
      </tbody>
    </table>
  </div>

  <script>
    // 週間カレンダーを生成する関数
    function generateWeeklyCalendar() {
      const days = ["日", "月", "火", "水", "木", "金", "土"];
      const calendarBody = document.getElementById("calendar-body");
      calendarBody.innerHTML = "";

      // 現在の日付を取得
      const today = new Date();
      const currentDayIndex = today.getDay(); // 今日の曜日のインデックス

      // 今日から日曜日までの日付を計算
      const sunday = new Date(today);
      sunday.setDate(today.getDate() - currentDayIndex);

      // 曜日ごとの日付を表示
      const headerRow = document.createElement("tr");
      const timeHeader = document.createElement("th");
      timeHeader.textContent = "時間";
      headerRow.appendChild(timeHeader);

      for (let dayIndex = 0; dayIndex < 7; dayIndex++) {
        const date = new Date(sunday);
        date.setDate(sunday.getDate() + dayIndex);
        const headerCell = document.createElement("th");
        const formattedDate = `${date.getMonth() + 1}/${date.getDate()}(${days[dayIndex]})`;
        headerCell.textContent = formattedDate;
        headerRow.appendChild(headerCell);
      }

      calendarBody.appendChild(headerRow);

      for (let hour = 0; hour < 24; hour++) {
        const row = document.createElement("tr");

        // 時間を表示
        const timeCell = document.createElement("td");
        timeCell.textContent = hour.toString().padStart(2, "0") + ":00";
        row.appendChild(timeCell);

        // 曜日ごとのセルを作成
        for (let dayIndex = 0; dayIndex < 7; dayIndex++) {
          const date = new Date(sunday);
          date.setDate(sunday.getDate() + dayIndex);
          const cell = document.createElement("td");

          // 予定入力欄を生成
          // ドロップダウンを生成
          const select = document.createElement("select");
          const option0 = document.createElement("option");
          option0.text = "";
          select.add(option0);
          const option1 = document.createElement("option");
          option1.text = "◯";
          select.add(option1);
          const option2 = document.createElement("option");
          option2.text = "✕";
          select.add(option2);

          // ドロップダウンの変更を監視
          select.addEventListener("change", function() {
            if (select.value === "◯") {
              select.classList.add("cell-selected");
            } else {
              select.classList.remove("cell-selected");
            }
          });
          cell.appendChild(select);

          row.appendChild(cell);
        }

        calendarBody.appendChild(row);
      }
    }

    // 初期化
    window.onload = function() {
      generateWeeklyCalendar();
    };
  </script>
</body>

</html>