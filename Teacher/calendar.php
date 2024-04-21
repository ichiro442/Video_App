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
    </style>
  </head>
  <body>
    <div class="container">
      <h2>週間カレンダー</h2>
      <table>
        <thead>
          <tr>
            <th id="today" colspan="8"></th>
          </tr>
          <tr>
            <th>時間</th>
            <th>日</th>
            <th>月</th>
            <th>火</th>
            <th>水</th>
            <th>木</th>
            <th>金</th>
            <th>土</th>
          </tr>
        </thead>
        <tbody id="calendar-body">
          <!-- ここにJavaScriptでカレンダーが追加されます -->
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

        // 今日の日付を表示
        const todayElement = document.getElementById("today");
        todayElement.textContent =
          today.getMonth() +
          1 +
          "/" +
          today.getDate() +
          " (" +
          days[currentDayIndex] +
          ")";

        // 曜日ごとの日付を表示
        for (let hour = 0; hour < 24; hour++) {
          const row = document.createElement("tr");

          // 時間を表示
          const timeCell = document.createElement("td");
          timeCell.textContent = hour.toString().padStart(2, "0") + ":00";
          row.appendChild(timeCell);

          // 曜日ごとのセルを作成
          for (let dayIndex = 0; dayIndex < 7; dayIndex++) {
            const date = new Date(
              today.getFullYear(),
              today.getMonth(),
              today.getDate() + dayIndex - currentDayIndex
            );
            const cell = document.createElement("td");

            // 曜日が一致するかどうかを判定し、予定入力欄を生成
            if (date.getDay() === dayIndex) {
              const input = document.createElement("input");
              input.type = "text";
              input.placeholder = "予定を入力";
              cell.appendChild(input);
            }

            row.appendChild(cell);
          }

          calendarBody.appendChild(row);
        }
      }

      // 初期化
      window.onload = function () {
        generateWeeklyCalendar();
      };
    </script>
  </body>
</html>
