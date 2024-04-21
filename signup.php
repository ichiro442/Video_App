<?php
session_start();
require_once('db_class.php');
require_once('mail_class.php');

try {
  // 登録ボタンが押された時の処理
  if (!empty($_POST["submit"])) {
    $dbConnect = new dbConnect();
    $dbConnect->initPDO();
    $userData = $_POST;
    $url = $dbConnect->getURL();
    $_SESSION['userData'] = $userData;

    // パスワードの一致を確認する
    if ($userData['password'] !== $userData['confirm_password']) {
      // echo "<p>パスワードが一致しません。もう一度入力してください。</p>";
      $errorMessage = "パスワードが一致しません。もう一度入力してください。";
    }

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
      header("Location: " . $url . $directry);
      exit;
    } else {
      throw new Exception("未入力項目があります。");
    }
    header("Location: " . $url);
    exit;
  }
} catch (Exception $e) {
  echo $e->getMessage();
  exit;
}

$title = "登録画面";
require_once('header.php');
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="style.css" />
  <title>講師新規登録</title>
</head>

<body>
  <?php if ($errorMessage) { ?>
    <p><?php echo $errorMessage ?></p>
  <?php } ?>

  <div class="container">
    <h2>講師新規登録</h2>
    <form method="post">
      <div class="form-group">
        <label for="last_name">名字</label>
        <input type="text" id="name" name="last_name" required />
      </div>
      <div class="form-group">
        <label for="first_name">名前</label>
        <input type="text" id="name" name="first_name" required />
      </div>
      <div class="form-group">
        <label for="nickname">ニックネーム</label>
        <input type="text" id="nickname" name="nickname" required />
      </div>
      <div class="form-group">
        <label for="email">メールアドレス</label>
        <input type="email" id="email" name="email" required />
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

</html>