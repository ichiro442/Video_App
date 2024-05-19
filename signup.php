<?php
session_start();
require_once('db_class.php');
require_once('mail_class.php');
require_once('definition.php');

try {
  $dbConnect = new dbConnect();
  // 正規のアクセスでなければ一つ前の画面にリダイレクト
  if (!$_GET["u"]) {
    $url = $dbConnect->getURL();
    $_SESSION['flash_message'] = FLASH_MESSAGE[14];
    header("Location: " . $url);
  }

  // 初期値
  $first_name = "";
  $last_name = "";
  $nickname = "";
  $email = "";
  $country = "";

  // 登録ボタンが押された時の処理
  if (!empty($_POST["submit"])) {
    $dbConnect->initPDO();
    $userData = $_POST;
    $url = $dbConnect->getURL();
    $_SESSION['userData'] = $userData;
    $first_name = $_POST["fist_name"];
    $last_name = $_POST["last_name"];
    $nickname = $_POST["nickname"];
    $email = $_POST["email"];

    // パスワードの一致を確認する
    $password = 0;
    if ($userData['password'] !== $userData['confirm_password']) {
      $_SESSION['flash_message'] = FLASH_MESSAGE[15];
    } else {
      $password = 1;
    }

    // すでに登録済みのメールアドレスの利用者はリダイレクト
    if ($dbConnect->findAllUsersByMail($_POST["email"])) {
      $_SESSION['flash_message'] = FLASH_MESSAGE[16];
      header("Location: " . $url);
      exit;
    }
    if ($password) {
      if (
        !empty($_POST["last_name"]) && !empty($_POST["first_name"]) && !empty($_POST["nickname"]) &&
        !empty($_POST["email"]) && !empty($_POST["country"]) && !empty($_POST["password"])
      ) {
        $table = $_GET["u"] == "teacher" ? "teachers" : "students";
        $shash = $dbConnect->insertUser($_POST["first_name"], $_POST['last_name'], $_POST["nickname"], $_POST['email'], $_POST['country'], $_POST['password'], $table);

        $directry = $_GET["u"] == "teacher" ? "Teacher" : "Student";
        echo "<p class='flashMessage'>" . $_SESSION['flash_message'] . "</p>";
        $message = "仮登録が完了しました。以下のURLから本登録を完了させてください。\n" .
          $url . $directry . "/register_confirm?s=" . $shash . "&m=" . urlencode($_POST["email"]);
        $mailer = new mail();
        $mailer->setTo($_POST["email"], $_POST["last_name"]);
        $mailer->setSubject(MAIL[2]);
        $mailer->setBody($message);
        $mailer->send();

        $_SESSION['flash_message'] = FLASH_MESSAGE[17];
        header("Location: " . $url . $directry . "/login");
        exit;
      } else {
        throw new Exception("未入力項目があります。");
      }
      header("Location: " . $url);
      exit;
    }
  }
} catch (Exception $e) {
  echo $e->getMessage();
  exit;
}

$title = "新規登録";
require_once('header.php');
?>

<body>
  <?php require_once('modal_message.php') ?>
  <div class="container">
    <h2><?php echo h($title) ?></h2>
    <form method="post">
      <div class="form-group">
        <label for="last_name">名字</label>
        <input type="text" name="last_name" value="<?php echo h($last_name) ?>" required />
      </div>
      <div class="form-group">
        <label for="first_name">名前</label>
        <input type="text" name="first_name" value="<?php echo h($first_name) ?>" required />
      </div>
      <div class="form-group">
        <label for="nickname">ニックネーム</label>
        <input type="text" name="nickname" value="<?php echo h($nickname) ?>" required />
      </div>
      <div class="form-group">
        <label for="nickname">国籍</label>
        <div class="">
          <select id="searchCountry" name="country">
            <option value=""></option>
            <?php foreach (COUNTRY as $country) : ?>
              <option name="<?php echo h($country) ?>" value="<?php echo h($country) ?>"><?php echo h($country) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="form-group">
        <label for="email">メールアドレス</label>
        <input type="email" name="email" value="<?php echo h($email) ?>" required />
      </div>
      <div class="form-group">
        <label for="password">パスワード</label>
        <input type="password" name="password" required />
      </div>
      <div class="form-group">
        <label for="confirm_password">パスワード確認</label>
        <input type="password" name="confirm_password" required />
      </div>
      <input type="submit" name="submit" alue="登録する" />
    </form>
  </div>
</body>
<?php require_once('footer.php') ?>

</html>