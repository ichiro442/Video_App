<?php
session_start();
require_once('db_class.php');
require_once('mail_class.php');

try {
  $dbConnect = new dbConnect();
  // 正規のアクセスでなければ一つ前の画面にリダイレクト
  if (!$_GET["u"]) {
    $url = $dbConnect->getURL();
    $_SESSION['flash_message'] = "不正なアクセスです。";
    header("Location: " . $url);
  }
  // 登録ボタンが押された時の処理
  if (!empty($_POST["submit"])) {
    $dbConnect->initPDO();
    $userData = $_POST;
    $url = $dbConnect->getURL();
    $_SESSION['userData'] = $userData;

    // パスワードの一致を確認する
    $password = 0;
    if ($userData['password'] !== $userData['confirm_password']) {
      $_SESSION['flash_message'] = "パスワードが一致しません。もう一度入力してください。";
    } else {
      $password = 1;
    }

    // すでに登録済みのメールアドレスの利用者はリダイレクト
    if ($dbConnect->findAllUsersByMail($_POST["email"])) {
      $_SESSION['flash_message'] = "ご入力頂いたメールアドレスはすでに登録されています。";
      header("Location: " . $url);
      exit;
    }
    if ($password) {
      if (!empty($_POST["last_name"]) && !empty($_POST["first_name"]) && !empty($_POST["nickname"]) && !empty($_POST["email"]) && !empty($_POST["password"])) {
        $table = $_GET["u"] == "teacher" ? "teachers" : "students";
        $shash = $dbConnect->insertUser($_POST["first_name"], $_POST['last_name'], $_POST["nickname"], $_POST['email'], $_POST['password'], $table);

        $directry = $_GET["u"] == "teacher" ? "Teacher" : "Student";
        echo "<p class='flashMessage'>" . $_SESSION['flash_message'] . "</p>";
        $message = "仮登録が完了しました。以下のURLから本登録を完了させてください。\n" .
          $url . $directry . "/register_confirm.php?s=" . $shash . "&m=" . urlencode($_POST["email"]);
        $mailer = new mail();
        $mailer->setTo($_POST["email"], $_POST["last_name"]);
        $mailer->setSubject('【サービス名】　本登録のご案内');
        $mailer->setBody($message);
        $mailer->send();

        $_SESSION['flash_message'] = "仮登録完了。\nご登録頂いたメールアドレスに本登録ご案内メールをお送りしました。\n引き続き本登録を行ってください。";
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
    <h2>新規登録</h2>
    <form method="post">
      <div class="form-group">
        <label for="last_name">名字</label>
        <input type="text" id="name" name="last_name" value="<?php echo h($_POST["last_name"]) ?>" required />
      </div>
      <div class="form-group">
        <label for="first_name">名前</label>
        <input type="text" id="name" name="first_name" value="<?php echo h($_POST["first_name"]) ?>" required />
      </div>
      <div class="form-group">
        <label for="nickname">ニックネーム</label>
        <input type="text" id="nickname" name="nickname" value="<?php echo h($_POST["nickname"]) ?>" required />
      </div>
      <div class="form-group">
        <label for="email">メールアドレス</label>
        <input type="email" id="email" name="email" value="<?php echo h($_POST["email"]) ?>" required />
      </div>
      <div class="form-group">
        <label for="password">パスワード</label>
        <input type="password" id="password" name="password" required />
      </div>
      <div class="form-group">
        <label for="confirm_password">パスワード確認</label>
        <input type="password" id="confirm_password" name="confirm_password" required />
      </div>
      <input type="submit" name="submit" alue="登録する" />
    </form>
  </div>
</body>
<?php require_once('footer.php') ?>

</html>