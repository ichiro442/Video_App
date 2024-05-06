<?php
session_start();
require_once('../db_class.php');
require_once('validation.php');
$userData = $_SESSION['userData'];

/**
 * 現在日以降の講師のスケジュールを取得する
 */
function searchCalendar() {
    $dbConnect = new dbConnect();
    $dbConnect->initPDO();
    $pdo = $dbConnect->getPDO();
    $sql = "SELECT CASE available WHEN 1 THEN '○' ELSE '×' END as title";
    $sql .= " ,(start_time - INTERVAL 60 MINUTE) as start,(start_time - INTERVAL 30 MINUTE) as end "; //フィリピンの時間に修正
    $sql .= "from teacher_schedules where teacher_id = :id and (start_time - INTERVAL 60 MINUTE) >= DATE_FORMAT(CURRENT_DATE,'%Y-%m-%d')";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(":id", $_SESSION["userData"]["id"], PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
} 

try {
    // 初期表示の場合
    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
        $dbConnect = new dbConnect();
        $dbConnect->initPDO();
        $pdo = $dbConnect->getPDO();
        $sql = "SELECT CASE available WHEN 1 THEN '○' ELSE '×' END as title";
        $sql .= " ,(start_time - INTERVAL 60 MINUTE) as start,(start_time - INTERVAL 30 MINUTE) as end "; //フィリピンの時間に修正
        $sql .= "from teacher_schedules where teacher_id = :id and (start_time - INTERVAL 60 MINUTE) >= DATE_FORMAT(CURRENT_DATE,'%Y-%m-%d')";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(":id", $_SESSION["userData"]["id"], PDO::PARAM_INT);
        $stmt->execute();
        $calendar = searchCalendar();
    }
    // 保存ボタンを押した場合
    if (!empty($_POST["submit"])) { //保存ボタンが押されたかどうかを確認
        $dbConnect = new dbConnect();
        $dbConnect->initPDO();
        $pdo = $dbConnect->getPDO();
        $arrEvents = json_decode($_POST['addEvents'], true);
        $sql = "INSERT INTO teacher_schedules (teacher_id, start_time, available, created_at, updated_at) VALUES";
        foreach ($arrEvents as $index => $event) {
            $sql .= "(" . $_SESSION["userData"]["id"] . "," . "STR_TO_DATE('" . $event["start"] ."','%Y-%m-%d %H:%i:%s')," . $event["available"] . ", now(), now())";
            if ($index !== array_key_last($arrEvents)) {
                $sql .= ',';
            }
        }
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $calendar = searchCalendar();
    }
} catch (PDOException $e) {
    echo "エラー";
    echo $e->getMessage();
    exit;
}

$title = "カレンダー変更";
require_once('header.php');
?>
<link rel="stylesheet" href="change.css">

<?php require_once('../modal_message.php'); ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js'></script>
<script>
  const now = new Date();
  const events = JSON.parse('<?php echo json_encode($calendar)?>');
  let addEvents = []; // 追加登録するデータの一覧　ここにデータを一時保管してhiddenへと移す

  // カレンダーの描画
  document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var calendar = new FullCalendar.Calendar(calendarEl, {
      initialView: 'timeGridWeek',
      firstDay: now.getDay(),
      displayEventTime: false,
      events: events,
      height: "auto",
      allDaySlot: false,
      selectable: true,
      selectMirror: true,
      selectOverlap: false,
      // 選択したときの動作
      select: function (arg) {
          // 選択時の確認画面
          Swal.fire({
              html: '<div class="mb-7">可否を入力ください</div>',
              icon: "info",
//              showDenyButton: true,
              showCancelButton: true,
              buttonsStyling: false,
              confirmButtonText: "○",
//              denyButtonText: "×",
              cancelButtonText: "Cancel",
              customClass: {
                  confirmButton: "btn btn-primary",
 //                 denyButton: "btn btn-active-light",
                  cancelButton: "btn btn-active-light"
              },
          }).then(function (result) {
              let start = arg.start;
              const end = arg.end;
              if (result.value) {
                  calendar.getEvents().filter(s => {                 
                      return start <= s.start && s.end <= end
                  }).forEach(s => {
                      s.remove();
                  });
                  while(start < end){
                      let tmpEnd = new Date(start.getTime());
                      tmpEnd.setMinutes(tmpEnd.getMinutes() + 30);
                      calendar.addEvent({
                          title: "○",
                          start: start,
                          end: tmpEnd,
                          allDay: arg.allDay
                      }) 
                      // フィリピンとの時差を考慮して60分進めて日本時間として登録
                      let registStart = new Date(start.getTime());
                      registStart.setMinutes(registStart.getMinutes() + 60);
                      // DBに登録したときに書式がわかるように書式を変更
                      const dateText = `${registStart.getFullYear()}-${registStart.getMonth() + 1}-${registStart.getDate()} ${registStart.getHours()}:${registStart.getMinutes()}:00`;
                      addEvents.push({"start": dateText, "available": true});
                      // hiddenとして追加したスケジュールを保持
                      document.querySelector('#addEvents').value = JSON.stringify(addEvents);
                      start = tmpEnd;
                  }
                  addEvents = Array.from(
                      new Map(addEvents.map((event) => [event.start, event])).values()
                  );
                  // 選択状態を解除
                  calendar.unselect()
              // ×を押したときの動作　現在は動作しない
              } else if (result.isDenied) {
                  calendar.getEvents().filter(s => {                 
                      return start <= s.start && s.end <= end
                  }).forEach(s => {
                      s.remove();
                  });
                  while(start < end){
                      let tmpEnd = new Date(start.getTime());
                      tmpEnd.setMinutes(tmpEnd.getMinutes() + 30);
                      calendar.addEvent({
                          title: "×",
                          start: start,
                          end: tmpEnd,
                          allDay: arg.allDay
                      }) 
                      start = tmpEnd;
                  }
                  calendar.unselect()
              } else if (result.dismiss === 'cancel') {
                  calendar.unselect()
              }
          });
        },
    });
    calendar.render();
  });

</script>
<style>
    .fc-event-title  {
        text-align: center;
        }
        
    .fc-timegrid-event-harness-inset {
        pointer-events: none;
    }
    th.fc-day-sat {
        color: blue;
    }

    th.fc-day-sun {
        color: red;
    }
</style>

<h2><?php echo h($title) ?></h2>
<div id='calendar'></div>
<form method="post">
    <div class="flex">
        <input type="hidden" name="addEvents" id="addEvents" />
        <input type="button" name="back" onclick="history.back(-1);" value="戻る" />
        <input type="submit" name="submit" value="保存" />
    </div>
</form>
</body>
<?php require_once('../footer.php'); ?>