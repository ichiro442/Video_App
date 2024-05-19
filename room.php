<?php
session_start();
require_once('db_class.php');
require_once('definition.php');


// 講師と生徒の情報を取得して名前を表示する
if ($_GET["lesson"]) {
  $dbConnect = new dbConnect();
  $dbConnect->initPDO();
  $url = $dbConnect->getURL();

  // レッスンのハッシュから該当レッスンを検索し、講師と生徒の名前とIDを取得する
  $lesson = $dbConnect->findLessonByHash($_GET["lesson"]);
  $lesson = $lesson[0];

  $finished_flg_int = intval($lesson["finished_flg"]);
  // レッスンが完了していたら前のページにリダイレクトする
  if ($finished_flg_int !== 0) {
    $_SESSION['flash_message'] = FLASH_MESSAGE["LESSON"][5];
    header("Location: {$_SERVER['HTTP_REFERER']}");
    exit;
  }
  // 生徒はレッスン時刻よりも前には入室できない
  // if ($_SESSION['userType'] == "student" && $current_time <= $start_time) {
  //   $_SESSION['flash_message'] = FLASH_MESSAGE["LESSON"][6];
  //   header("Location: {$_SERVER['HTTP_REFERER']}");
  //   exit;
  // }

  // ユーザーと予約しているユーザーが違った場合、前のページにリダイレクト
  if (($lesson["student_id"] !== $_SESSION['userData']["id"]) &&
    ($lesson["teacher_id"] !== $_SESSION['userData']["id"])
  ) {
    $_SESSION['flash_message'] = FLASH_MESSAGE["LESSON"][4];
    header("Location: {$_SERVER['HTTP_REFERER']}");
    exit;
  }

  $student = $dbConnect->findByOneColumn("id", $lesson["student_id"], "Student");
  $teacher = $dbConnect->findByOneColumn("id", $lesson["teacher_id"], "Teacher");

  // 生徒と講師の名前
  $student = $student["nickname"];
  $teacher = $teacher["nickname"];

  // 25分を加算してend_timeを計算
  date_default_timezone_set('Asia/Tokyo');
  $start_time = new DateTime($lesson["start_time"]);
  $start_time->add(new DateInterval('PT25M'));
  $end_time = $start_time->format('Y-m-d H:i:s');
} else {
  // パラメーターのlessonが空の場合、前のページへリダイレクト
  $_SESSION['flash_message'] = FLASH_MESSAGE["LESSON"][4];
  header("Location: {$_SERVER['HTTP_REFERER']}");
  exit;
}

// レッスンが完了した場合
if ($_POST) {
  // $dbConnect = new dbConnect();
  // $dbConnect->initPDO();
  // $url = $dbConnect->getURL();
  // $finished_flg = 1;
  // $dbConnect->updateLesson($_GET["lesson"],  "finished_flg", $finished_flg);

  header('Location:' . $url . "rating?lesson=" . $_GET["lesson"]);
  exit;
}


?>

<html>

<head>
  <meta http-equiv="content-type" charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Bootstrapを利用する -->
  <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script> -->
  <link rel="stylesheet" href="Bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="style.css">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
</head>
<style>
  #countdown {
    font-size: 2em;
    text-align: center;
    margin: 20px;
    color: white;
  }

  span {
    margin: 0 10px;
  }
</style>

<body class="bg-dark">
  <div id="header" class="bg-dark w-100 d-block d-sm-none" style="position: absolute; top: 0; z-index: 10;" hidden>
    <!-- 退室ボタン(スマホ用) -->
    <div class="p-2 d-flex flex-column rounded-pill py-3 m-3" id="leave-button" style="width: 100px; position: absolute; right: 0; background-color: #d82919; cursor: pointer;" onmouseover="this.style.background='#e83929'" onmouseout="this.style.background='#d82919'" onclick="submitForm()">
      <div class="row text-white justify-content-center">
        退出
      </div>
    </div>
  </div>

  <!-- カウントダウン -->
  <div id="countdown" class="countdown"></div>

  <!-- 自分のカメラ映像とマイク -->
  <img id="local-video" src="Img/0.png"></img>
  <audio id="local-audio" autoplay muted></audio>

  <!-- 相手のカメラ映像とマイク -->
  <img id="remote-video" src="Img/1.png"></img>
  <audio id="remote-audio" autoplay></audio>

  <!-- 画面下部の操作バー -->
  <div id="footer" class="bg-dark w-100 d-flex align-items-center" style="position: absolute; bottom: 0; z-index: 10;">
    <!-- マイクミュートボタン -->
    <div class="p-2 d-flex flex-column" id="mute-audio-button" style="width: 100px; position: absolute; left: 50%; margin-left: -100px; cursor: pointer;" onmouseover="this.style.background='#313539'" onmouseout="this.style.background='#212529'">
      <div class="row justify-content-center">
        <img class="w-50" id="mute-audio-img" src="Img/mic_on.png"></img>
      </div>
      <small id="mute-audio-text" class="row text-white justify-content-center">
        ミュート
      </small>
    </div>
    <!-- ビデオ停止/開始ボタン -->
    <div class="p-2 d-flex flex-column" id="mute-video-button" style="width: 100px; position: absolute; right: 50%; margin-right: -100px; cursor: pointer;" onmouseover="this.style.background='#313539'" onmouseout="this.style.background='#212529'">
      <div class="row justify-content-center">
        <img class="w-50" id="mute-video-img" src="Img/video_on.png"></img>
      </div>
      <small id="mute-video-text" class="row text-white justify-content-center">
        ビデオの停止
      </small>
    </div>
    <!-- 退室ボタン -->
    <div class="p-2 d-flex flex-column rounded-pill py-3 d-none d-sm-block" id="leave-button" style="width: 100px; position: absolute; right: 60px; background-color: #d82919; cursor: pointer;" onmouseover="this.style.background='#e83929'" onmouseout="this.style.background='#d82919'" onclick="submitForm()">
      <div class="row text-white justify-content-center">
        退出
      </div>
    </div>
  </div>
  <form method="POST" id="hidden-form">
    <input type="hidden" name="lesson" value="lesson_value">
  </form>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/moment-timezone/0.5.34/moment-timezone-with-data.min.js"></script>

  <script>
    // PHPから取得した文字列をJavaScriptで日本時間に変換
    const end_time_str = "<?php echo $end_time; ?>";

    // moment-timezoneを使用して日時をパース
    var end_time = moment.tz(end_time_str, 'Asia/Tokyo');

    // 'Y-m-d H:i:s'形式でフォーマット
    console.log("終了時刻: " + end_time.format("YYYY-MM-DD HH:mm:ss"));

    // タイムゾーンを日本時間に設定する
    moment.tz.setDefault('Asia/Tokyo');

    // カウントダウンを更新する関数
    function updateCountdown() {
      var now_jpn = moment();
      console.log("日本時間 " + now_jpn.format("YYYY-MM-DD HH:mm:ss"));
      var remainingMillis = end_time.diff(now_jpn); // ミリ秒単位の差を計算

      // ミリ秒を時間、分、秒に変換
      var remainingSeconds = Math.floor(remainingMillis / 1000);
      var remainingMinutes = Math.floor(remainingSeconds / 60);
      var remainingHours = Math.floor(remainingMinutes / 60);

      remainingMinutes %= 60;
      remainingSeconds %= 60;

      // HTMLに表示
      document.getElementById('countdown').innerText =
        "残り時間: " + remainingHours + "時間 " + remainingMinutes + "分 " + remainingSeconds + "秒";

      // カウントダウンが終了した場合
      if (remainingMillis <= 0) {
        document.getElementById('countdown').innerText = "カウントダウン終了";
        clearInterval(intervalId);
      }
    }
    // 1秒ごとにカウントダウンを更新
    var intervalId = setInterval(updateCountdown, 1000);

    // 初回表示を即座に更新
    updateCountdown();

    function submitForm() {
      // ページ遷移時にカウントダウンの開始時間を削除する処理
      window.addEventListener('unload', function() {
        localStorage.removeItem('startTime');
      });

      // フォームを取得
      var form = document.getElementById('hidden-form');

      // フォームを送信
      form.submit();
    }
  </script>
  <script src="https://cdn.jsdelivr.net/npm/@skyway-sdk/room/dist/skyway_room-latest.js"></script>
  <script src="config.js"></script>
  <script src="main.js"></script>
  <!-- <script src="countdown.js"></script> -->
  <script src="Bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
<!-- <?php require_once('countdown2.php'); ?> -->
<?php require_once('footer.php'); ?>

</html>