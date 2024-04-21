<?php
session_start();

if ($_POST) {
  var_dump($_POST);
  exit;
}
// レッスンをした講師と生徒のIDを取得する
// uriを取得して講師か生徒かを判断する
// 評価を登録する
// 講師と生徒のそれぞれのindexの画面にリダイレクトする

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

  .rating-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    font-size: 2rem;
  }

  .star {
    color: #ccc;
    cursor: pointer;
    font-size: 5rem;
    /* 星の大きさを大きく */
  }

  .star.checked {
    color: #ffc107;
  }

  .star:hover {
    color: #ff9800;
  }

  .rating-description {
    margin-top: 10px;
  }

  .submit-button {
    margin-top: 20px;
    padding: 10px 20px;
    font-size: 1.2rem;
    background-color: #4caf50;
    color: white;
    border: none;
    cursor: pointer;
  }

  .submit-button:hover {
    background-color: #45a049;
  }
</style>

<body>
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
      alert(`評価が ${rating} / 5 で送信されました。`);
      // PHPのinput要素のvalueに評価の値を設定する
      document.getElementById('ratingInput').value = rating;

      // ページをリロードせずにフォームを送信する
      document.getElementById('ratingForm').submit();

      // OKボタンが押された後にindex.htmlに遷移する
      // window.location.href = "index.html";
    }
  </script>
</body>

</html>