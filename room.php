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
  // $student_nickname = $student["nickname"];
  // $teacher_nickname = $teacher["nickname"];

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

// レディースドリンクの購入
if (isset($_POST[DRINK["A"]["name"]]) || isset($_POST[DRINK["B"]["name"]]) || isset($_POST[DRINK["C"]["name"]])) {
  if ($_POST[DRINK["A"]["name"]]) $drink = "A";
  elseif ($_POST[DRINK["B"]["name"]]) $drink = "B";
  elseif ($_POST[DRINK["C"]["name"]]) $drink = "C";
  require 'vendor/autoload.php';
  $secret_key = $dbConnect->getStripeSecretKey();
  \Stripe\Stripe::setApiKey($secret_key);
  $checkout_session = \Stripe\Checkout\Session::create([
    "mode" => "payment",
    "success_url" => $url . "room?lesson=" . $_GET["lesson"] . "&result=success&session_id={CHECKOUT_SESSION_ID}&d=$drink",
    "cancel_url"  => $url . "room?lesson=" . $_GET["lesson"] . "&result=cancel", // 必要に応じてキャンセルURLも設定
    "line_items" => [
      [
        "quantity" => 1,
        "price_data" => [
          "currency" => "jpy",
          "unit_amount" => 1000,
          "product_data" => [
            "name" => "レディースドリンク_A"
          ]
        ]
      ]
    ]
  ]);
  http_response_code(303);
  header("Location: " . $checkout_session->url);
}

if (isset($_GET["session_id"])) {
  require 'vendor/autoload.php';
  \Stripe\Stripe::setApiKey($dbConnect->getStripeSecretKey());
  $checkout_session = \Stripe\Checkout\Session::retrieve($_GET["session_id"]);
  if ($checkout_session["payment_status"] == "paid") {
    $dbConnect = new dbConnect();
    $dbConnect->initPDO();

    $lesson_id = 0;
    if ($_GET["lesson"]) {
      $lesson = $dbConnect->findLessonByHash($_GET["lesson"]);
      $lesson_id = $lesson[0][0];
    }

    if ($dbConnect->findDrinkBySessionID($_GET["session_id"])) {
      // ドリンクの金額も保存する？金額は要らないか。定数を定義していたら計算するときにそこから金額を引っ張ってくるだけでいい

      $dbConnect->insertDrink($lesson_id, DRINK[$_GET["d"]]["name"], $_GET["session_id"]);
    }
  }
}

// レッスンが完了した場合
if ($_POST["lesson"]) {
  $dbConnect = new dbConnect();
  $dbConnect->initPDO();
  $url = $dbConnect->getURL();

  // レッスンを受けているユーザーとログインしているユーザーが一致した場合、レッスンのユーザー退出フラグを1にする
  if ($student["id"] ==  $_SESSION["userData"]["id"]) {
    $dbConnect->updateLesson($_GET["lesson"],  "leave_flg_student", LESSON["leave_flg_student"]);
  } elseif ($teacher["id"] ==  $_SESSION["userData"]["id"]) {
    $dbConnect->updateLesson($_GET["lesson"],  "leave_flg_teacher", LESSON["leave_flg_teacher"]);
  }

  header('Location:' . $url . "rating?lesson=" . $_GET["lesson"]);
  exit;
}
?>

<html>

<head>
  <meta http-equiv="content-type" charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Bootstrapを利用する -->
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

  .buy-link {
    width: 20% !important;
    /* padding: 10px;
    background-color: #007bff;
    color: #fff;
    border: none;
    border-radius: 5px;
    cursor: pointer; */
  }

  form div {
    margin: 10px;
  }

  /* .buy-link a {
    color: white;
  } */

  /* トーストの基本スタイル */
  .toast {
    visibility: hidden;
    /* 初期状態は非表示 */
    min-width: 250px;
    margin: 0 auto;
    background-color: #333;
    color: #fff;
    text-align: center;
    border-radius: 5px;
    padding: 16px;
    position: fixed;
    z-index: 1;
    top: 10px;
    /* 画面上部に配置 */
    left: 50%;
    transform: translateX(-50%);
    box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
    font-size: 17px;
    opacity: 0;
    transition: opacity 0.5s, visibility 0.5s;
  }

  /* トースト表示時のスタイル */
  .toast.show {
    visibility: visible;
    opacity: 1;
    /* フェードイン */
  }
</style>
<?php require_once('modal_message.php'); ?>

<body class="bg-dark">
  <div id="header" class="bg-dark w-100 d-block d-sm-none" style="position: absolute; top: 0; z-index: 10;" hidden>
    <!-- 退室ボタン(スマホ用) -->
    <div class="p-2 d-flex flex-column rounded-pill py-3 m-3" id="leave-button" style="width: 100px; position: absolute; right: 0; background-color: #d82919; cursor: pointer;" onmouseover="this.style.background='#e83929'" onmouseout="this.style.background='#d82919'" onclick="submitForm()">
      <div class="row text-white justify-content-center">
        退出
      </div>
    </div>
  </div>

  <!-- トースト通知 -->
  <div id="toast" class="toast"></div>

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
    <div class="p-2 d-flex flex-column" onclick="showModal()" style="position: absolute; left: 40%; margin-left: -100px; cursor: pointer;" onmouseover="this.style.background='#313539'" onmouseout="this.style.background='#212529'">
      <div class="row justify-content-center" style="color: white;">
        レディースドリンク
      </div>
    </div>
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
    function showToast(msg) {
      var toast = document.getElementById("toast");
      toast.innerHTML = msg;
      toast.classList.add("show");

      // 3秒後にトーストを自動で非表示にする
      setTimeout(function() {
        toast.classList.remove("show");
      }, 3000);
    }

    window.onload = function() {
      // 現在のURLのクエリ文字列を取得
      const queryString = window.location.search;

      // URLSearchParamsオブジェクトを使ってクエリパラメータを解析
      const urlParams = new URLSearchParams(queryString);

      // resultパラメータの値を取得
      const result = urlParams.get('result');

      // resultが存在するかチェックし、値を表示
      if (result) {
        if (result === 'success') {
          showToast('成功しました！');
        } else if (result === 'cancel') {
          showToast('失敗しました。');
        }
      }
    };

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
        // remainingMinutes + " : " + remainingSeconds;
        remainingHours + "時間 " + remainingMinutes + " : " + remainingSeconds;

      // カウントダウンが終了した場合
      if (remainingMillis <= 0) {
        // document.getElementById('countdown').innerText = "カウントダウン終了";
        clearInterval(intervalId);
        submitForm();
      }
    }
    // 1秒ごとにカウントダウンを更新
    var intervalId = setInterval(updateCountdown, 1000);

    // 初回表示を即座に更新
    updateCountdown();

    function submitForm() {
      // フォームを取得
      var form = document.getElementById('hidden-form');

      // フォームを送信
      form.submit();
    }

    // レディースドリンクのデータ
    const drinksData = <?php echo json_encode(DRINK); ?>;

    // PHPから渡されたDRINKデータを使用してladiesDrinks配列を生成
    const ladiesDrinks = Object.keys(drinksData).map(key => ({
      name: drinksData[key].name,
      price: drinksData[key].price
    }));

    // モーダル表示関数
    function showModal() {
      // フォームを生成
      const form = $('<form method="POST"></form>');

      // ドリンク情報をフォームに追加
      ladiesDrinks.forEach(drink => {
        const p = `<div class="flex"><span>${drink.name} ${drink.price}円</span> <input type="submit" class="buy-link" name="${drink.name}" value="奢る"></div>`;
        form.append(p);
        // const p = `<p>${drink.name} ${drink.price}円 <input type="submit" class="buy-link" name="${drink.name}" value="奢る"></p>`;
        // form.append(p);
      });
      if ($('#flashMessageDisplay form').length === 0) {
        // フォームをモーダル内に追加
        $('#flashMessageDisplay').append(form);
      }
      $('#modalArea').fadeIn();
    }
    // モーダルを閉じる処理
    $('#closeModal , #modalBg').click(function() {
      $('#modalArea').fadeOut();
    });
    // function showModal() {
    //   // レディースドリンクの情報を作成
    //   let drinksHtml = ladiesDrinks.map(drink =>
    //     `<form method="POST"><p>${drink.name} ${drink.price}円 <input type="submit" class="buy-link" name="${drink.name}" value="奢る"></p></form>`);
    //   // `<p>${drink.name} ${drink.price}円 <span class="buy-link"><a href="${drink.link}" target="_blank">奢る</a></span></p>`);
    //   $('#modalArea').fadeIn();

    //   // モーダルに表示
    //   $('#flashMessageDisplay').html(drinksHtml);
    // }
    // // モーダルを閉じる処理
    // $('#closeModal , #modalBg').click(function() {
    //   $('#modalArea').fadeOut();
    // });
  </script>
  <script src="https://cdn.jsdelivr.net/npm/@skyway-sdk/room/dist/skyway_room-latest.js"></script>
  <script src="config.js"></script>
  <script src="main.js"></script>
  <script src="Bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
<?php require_once('footer.php'); ?>

</html>