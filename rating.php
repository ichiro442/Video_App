<?php
session_start();
require_once('db_class.php');
require_once('definition.php');

if ($_GET["lesson"]) {
  $dbConnect = new dbConnect();
  $dbConnect->initPDO();
  $url = $dbConnect->getURL();

  // レッスンのハッシュから該当レッスンを検索し、講師と生徒の名前とIDを取得する
  $lesson = $dbConnect->findLessonByHash($_GET["lesson"]);
  $lesson = $lesson[0];
  $finished_flg_int = intval($lesson["finished_flg"]);

  // レッスンが完了していない場合、前のページにリダイレクトする
  if ($finished_flg_int == 0) {
    $_SESSION['flash_message'] = FLASH_MESSAGE["LESSON"][7];
    header("Location: {$_SERVER['HTTP_REFERER']}");
    exit;
  }
  $student = $dbConnect->findByOneColumn("id", $lesson["student_id"], "Student");
  $teacher = $dbConnect->findByOneColumn("id", $lesson["teacher_id"], "Teacher");
}
if ($_POST) {
  if ($student["id"] == $_SESSION["userData"]["id"]) {
    // 生徒→講師の評価を登録する
    $rating_target = "teacher";
    $dbConnect->insertRating($lesson["id"], $student["id"], $teacher["id"], $rating_target, $_POST["rating"], $_POST["comment"]);
    // 生徒のそれぞれのindexの画面にリダイレクトする
    $_SESSION['flash_message'] = FLASH_MESSAGE["LESSON"][8];
    header('Location:' . $url . "Student");
    exit;
  } elseif ($teacher["id"] == $_SESSION["userData"]["id"]) {
    // 講師→生徒の評価を登録する
    $rating_target = "student";
    $dbConnect->insertRating($lesson["id"], $student["id"], $teacher["id"], $rating_target, $_POST["rating"], $_POST["comment"]);
    // 生徒のそれぞれのindexの画面にリダイレクトする
    $_SESSION['flash_message'] = FLASH_MESSAGE["LESSON"][8];
    header('Location:' . $url . "Teacher");
    exit;
  }

  $_SESSION["flash_message"] = "評価が " . $_POST["rating"] . " / 5 で送信されました。";
  header("Location: " . $url . "Student");
  exit;
}

$title = "レビュー";
require_once('header.php');
?>
<style>
  body {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 100vh;
    margin: 0;
    font-size: 1.5rem;
  }
</style>

<body>
  <?php require_once('modal_message.php'); ?>

  <div class="rating-container">
    <div>
      <span class="star" data-value="1">&#9733;</span>
      <span class="star" data-value="2">&#9733;</span>
      <span class="star" data-value="3">&#9733;</span>
      <span class="star" data-value="4">&#9733;</span>
      <span class="star" data-value="5">&#9733;</span>
    </div>
    <input type="text" name="test" value="" hidden>
    <div class="rating-description">
      評価: <span id="ratingValue">0</span> / 5
    </div>
    <div class="rating-description">
      <label for="">コメント</label>
    </div>
    <div class="rating-description">
      <textarea name="comment" id="ratingComment" cols="60" rows="7"></textarea>
    </div>
    <button class="submit-button" onclick="submitRating()">送信する</button>
    <form id="ratingForm" method="POST">
      <input type="hidden" name="rating" id="ratingInput">
      <input type="hidden" name="comment" id="commentInput">
    </form>
  </div>

  <script>
    // 星の要素を取得
    const stars = document.querySelectorAll(".star");
    // 評価値を表示する要素を取得
    const ratingValue = document.getElementById("ratingValue");

    // 各星にクリックイベントを追加
    stars.forEach((star) => {
      star.addEventListener("click", () => {
        // クリックされた星の評価値を取得してセットする
        const value = parseInt(star.getAttribute("data-value"));
        setRating(value);
      });
    });

    // 評価値を設定する関数
    function setRating(value) {
      stars.forEach((star) => {
        // 各星の評価値を取得
        const starValue = parseInt(star.getAttribute("data-value"));
        // クリックされた星よりも小さい評価値の星はチェック済みにする
        if (starValue <= value) {
          star.classList.add("checked");
        } else {
          star.classList.remove("checked");
        }
      });
      // ページ上に評価値を表示する
      ratingValue.textContent = value;
    }

    // 評価を送信する関数
    function submitRating() {
      // 現在の評価値を取得
      const rating = parseInt(ratingValue.textContent);
      const comment = document.getElementById('ratingComment').value;

      // 評価値をPHPのinput要素のvalueに設定する
      document.getElementById('ratingInput').value = rating;
      document.getElementById('commentInput').value = comment;

      // ページをリロードせずにフォームを送信する
      document.getElementById('ratingForm').submit();
    }
  </script>
  <?php require_once('footer.php'); ?>
</body>

</html>