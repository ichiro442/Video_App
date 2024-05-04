<?php
session_start();
require_once('db_class.php');

if ($_POST["rating"]) {
  $dbConnect = new dbConnect();
  $url = $dbConnect->getURL();
  // レッスンをした講師と生徒のIDを取得する
  // uriを取得して講師か生徒かを判断する
  // 評価を登録する
  // 講師と生徒のそれぞれのindexの画面にリダイレクトする
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
      <textarea name="comment" id="" cols="60" rows="7"></textarea>
    </div>
    <button class="submit-button" onclick="submitRating()">送信する</button>
    <form id="ratingForm" method="POST">
      <input type="hidden" name="rating" id="ratingInput">
    </form>
  </div>

  <script>
    const stars = document.querySelectorAll(".star");
    const ratingValue = document.getElementById("ratingValue");

    stars.forEach((star) => {
      star.addEventListener("click", () => {
        const value = parseInt(star.getAttribute("data-value"));
        setRating(value);
      });
    });

    function setRating(value) {
      stars.forEach((star) => {
        const starValue = parseInt(star.getAttribute("data-value"));
        if (starValue <= value) {
          star.classList.add("checked");
        } else {
          star.classList.remove("checked");
        }
      });
      ratingValue.textContent = value;
    }

    function submitRating() {
      const rating = parseInt(ratingValue.textContent);

      // PHPのinput要素のvalueに評価の値を設定する
      document.getElementById('ratingInput').value = rating;

      // ページをリロードせずにフォームを送信する
      document.getElementById('ratingForm').submit();
    }
  </script>
  <?php require_once('footer.php'); ?>
</body>

</html>