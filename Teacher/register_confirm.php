<?php
session_start();
require_once('../db_class.php');
require_once('../mail_class.php');
require_once('../definition.php');


// 仮登録データを取得する
try {
    if (isset($_GET['m'])) {
        $dbConnect = new dbConnect();
        $dbConnect->initPDO();
        $uri =  $_SERVER["REQUEST_URI"];
        $teacher = $dbConnect->findByMail($_GET['m'], $uri);
        $selected = "selected";
        if ($teacher["status"] == 1) {
            $_SESSION["flash_message"] = FLASH_MESSAGE[20];
            $url = $dbConnect->getURL();
            header("Location: " . $url . "Teacher");
            exit;
        }
    }
    if (!isset($_GET['m'])) {
        echo "エラー";
        throw new Exception("GETが空です。");
    }
} catch (Exception $e) {
    echo $e->getMessage();
    exit;
}

try {
    if (!empty($_GET['m'])) {
        if (!empty($_POST['submit'])) {
            // ステータスを本登録にして講師データをアップデートする
            $_POST["status"] = REGISTER[1];
            $dbConnect->updateTeacher($teacher["id"], $_POST);
            $email = $teacher["email"];
            $name = $teacher["last_name"];

            //登録完了のメール送信処理
            $message = "〜【サービス名】ご登録完了のお知らせ〜\n" .
                "今後とも【サービス名】をよろしくお願い致します。\n" .
                "なお、このメールは自動で配信しております。\n" .
                "*********************************************\n" .
                "お名前: " . $name . "\n" .
                "メールアドレス: " . $email . "\n" .
                "パスワード: 登録時に入力されたパスワード\n" .
                "*********************************************";
            $mailer = new mail();
            $mailer->setTo($email, $name);
            $mailer->setSubject(MAIL[1]);
            $mailer->setBody($message);
            $mailer->send();
            $url = $dbConnect->getURL();
            $_SESSION["userType"] = "teacher";
            $_SESSION["userData"] = $teacher;
            $_SESSION["flash_message"] = FLASH_MESSAGE[18];
            header("Location: " . $url . "Teacher");
            exit;
        }
    }
} catch (PDOException $e) {
    echo $e->getMessage();
    exit;
}
require_once('header_function.php');
$title = "講師新規登録";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="style.css" />
    <title><?php echo h($title) ?></title>
</head>

<body>
    <?php if ($errorMessage) { ?>
        <p><?php echo $errorMessage ?></p>
    <?php } ?>

    <div class="container">
        <h2>登録内容確認画面</h2>
        <form method="post">
            <div class="form-group">
                <label for="last_name">名字</label>
                <input type="text" id="name" name="last_name" value=<?php echo h($teacher['last_name']) ?> required />
            </div>
            <div class="form-group">
                <label for="first_name">名前</label>
                <input type="text" id="name" name="first_name" value=<?php echo h($teacher['first_name']) ?> required />
            </div>
            <div class="form-group">
                <label for="nickname">ニックネーム</label>
                <input type="text" id="nickname" name="nickname" value=<?php echo h($teacher['nickname']) ?> required />
            </div>
            <div class="form-group">
                <label for="country">国籍</label>
                <div class="">
                    <select id="searchCountry" name="country">
                        <?php foreach (COUNTRY as $country) : ?>
                            <?php if ($country !== $students['country']) $selected = ""; ?>
                            <option name="<?php echo h($country) ?>" value="<?php echo h($country) ?>" selected><?php echo h($country) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label for="email">メールアドレス</label>
                <input type="email" id="email" name="email" value=<?php echo h($teacher['email']) ?> required />
            </div>
            <input type="text" name="password" value=<?php echo $teacher['password'] ?> hidden>
            <input type="text" name="shash" value=<?php echo $teacher['shash'] ?> hidden>
            <input type="submit" name="submit" value="登録する" />
        </form>
    </div>
</body>

</html>