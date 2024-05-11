<?php
session_start();
require_once('db_class.php');
// 講師と生徒の情報を取得して名前を表示する
if ($_GET["lesson"]) {
  $dbConnect = new dbConnect();
  $dbConnect->initPDO();
  $url = $dbConnect->getURL();

  // レッスンのハッシュから該当レッスンを検索し、講師と生徒の名前とIDを取得する
  $lesson = $dbConnect->findLessonByHash($_GET["lesson"]);
  $lesson = $lesson[0];

  if (($lesson["student_id"] !== $_SESSION['userData']["id"]) &&
    ($lesson["teacher_id"] !== $_SESSION['userData']["id"])
  ) {
    $_SESSION['flash_message'] = FLASH_MESSAGE["LESSON"][4];
    header('Location:' . $url);
    exit;
  }
  $student = $dbConnect->findByOneColumn("id", $lesson["student_id"], "Student");
  $teacher = $dbConnect->findByOneColumn("id", $lesson["teacher_id"], "Teacher");
  $student = $student["nickname"];
  $teacher = $teacher["nickname"];
}

// パラメーターのlessonが空の場合、javascriptで判定されPOST送信されてここの処理に到達する
if ($_POST) {
  $dbConnect = new dbConnect();
  $url = $dbConnect->getURL();
  $_SESSION['flash_message'] = FLASH_MESSAGE["LESSON"][4];
  if ($_SESSION['userType'] == "student") {
    header('Location:' . $url . "Student/my_page");
    exit;
  } else if ($_SESSION['userType'] == "teacher") {
    header('Location:' . $url . "Teacher");
    exit;
  }
}

?>

<html>

<head>
  <meta http-equiv="content-type" charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Bootstrapを利用する -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
  <link rel="stylesheet" href="style.css">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
</head>

<body class="bg-dark">
  <div id="header" class="bg-dark w-100 d-block d-sm-none" style="position: absolute; top: 0; z-index: 10;" hidden>
    <!-- 退室ボタン(スマホ用) -->
    <div class="p-2 d-flex flex-column rounded-pill py-3 m-3" id="leave-button" style="width: 100px; position: absolute; right: 0; background-color: #d82919; cursor: pointer;" onmouseover="this.style.background='#e83929'" onmouseout="this.style.background='#d82919'" onclick="window.location.href='join.html'">
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
    <div class="p-2 d-flex flex-column rounded-pill py-3 d-none d-sm-block" id="leave-button" style="width: 100px; position: absolute; right: 60px; background-color: #d82919; cursor: pointer;" onmouseover="this.style.background='#e83929'" onmouseout="this.style.background='#d82919'" onclick="window.location.href='rating'">
      <div class="row text-white justify-content-center">
        退出
      </div>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/@skyway-sdk/room/dist/skyway_room-latest.js"></script>
  <script src="config.js"></script>
  <script src="main.js"></script>
  <script src="countdown.js"></script>
</body>

</html>