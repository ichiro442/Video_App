<?php
session_start();
require_once('../db_class.php');
require_once('validation.php');
$userData = $_SESSION['userData'];

try {
    if (!empty($_POST["submit"])) { //変更ボタンが押され方どうかを確認
        $dbConnect = new dbConnect();
        $dbConnect->initPDO();
        // ユーザーが登録しているパスワードを取得する
        $uri =  $_SERVER["REQUEST_URI"];
        $userData = $dbConnect->findByMail($userData['email'], $uri);

        // 取得したもの、旧と新の３つのパスワード
        $oldPassHash = $_POST["oldPassword"];
        $newPassHash = password_hash($_POST["newPassword"], PASSWORD_DEFAULT);
        $passCheck = password_verify($oldPassHash, $userData["password"]);

        // パスワードの整合
        if ($passCheck) {
            $userData["password"] = $newPassHash;
            $column = "password";
            $result = $dbConnect->updateOneColumn($userData["id"], $userData["password"], $column, $uri);

            // パスワードの一致を確認する
            if ($_POST['newPassword'] !== $_POST['newPasswordConfirm']) {
                $_SESSION['flash_message'] =  "新しいパスワードと新しいパスワードの確認が一致しません。もう一度入力してください。";
                unset($_POST);
            } else {
                $_SESSION['flash_message'] = "パスワードを更新しました。";
                $_SESSION['userData']["password"] = $_POST['newPassword'];
                $url = $dbConnect->getURL();
                header('Location:' . $url . "Teacher");
                exit;
            }
        } else {
            $_SESSION['flash_message'] = "パスワードが違います。";
            header('Location: ' . $_SERVER['REQUEST_URI']);
            exit;
        }
    }
} catch (PDOException $e) {
    echo "エラー";
    echo $e->getMessage();
    exit;
}

$title = "パスワード変更画面";
require_once('header.php');
?>
<link rel="stylesheet" href="change.css">

<?php require_once('../modal_message.php'); ?>
<form method="post">
    <h2><?php echo h($title) ?></h2>
    <label for="password">旧パスワード:</label>
    <input type="password" name="oldPassword" required />
    <label for="password">新パスワード:</label>
    <input type="password" name="newPassword" required />
    <label for="password">新パスワードの確認:</label>
    <input type="password" name="newPasswordConfirm" required />
    <div class="flex">
        <input type="button" name="back" onclick="history.back(-1);" value="戻る" />
        <input type="submit" name="submit" value="変更" />
    </div>
</form>
</body>
<?php require_once('../footer.php'); ?>