<?php
session_start();
require_once('../db_class.php');
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
            $dbConnect->updatePass($userData["id"], $userData, $uri);
            // パスワードの一致を確認する
            if ($_POST['newPassword'] !== $_POST['newPasswordConfirm']) {
                $_SESSION['flash_message'] =  "新しいパスワードと新しいパスワードの確認が一致しません。もう一度入力してください。";
                unset($_POST);
            } else {
                $_SESSION['flash_message'] = "パスワードを更新しました。";
                $url = $dbConnect->getURL();
                header('Location:' . $url . "Student/my_page");
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

<style>
    body {
        font-family: Arial, sans-serif;
    }

    form {
        width: 300px;
        margin: 0 auto;
        padding: 20px;
        border: 1px solid #ccc;
        border-radius: 5px;
        background-color: #f9f9f9;
    }

    input[type="text"],
    input[type="password"],
    input[type="submit"] {
        width: 100%;
        padding: 10px;
        margin-top: 5px;
        margin-bottom: 10px;
        box-sizing: border-box;
    }

    input[type="submit"] {
        background-color: #4caf50;
        color: white;
        border: none;
        cursor: pointer;
    }

    input[type="submit"]:hover {
        background-color: #45a049;
    }
</style>

<?php require_once('../modal_message.php'); ?>
<form method="post">
    <h2>パスワード変更</h2>
    <label for="password">旧パスワード:</label>
    <input type="password" name="oldPassword" required />
    <label for="password">新パスワード:</label>
    <input type="password" name="newPassword" required />
    <label for="password">新パスワードの確認:</label>
    <input type="password" name="newPasswordConfirm" required />
    <input type="button" name="back" onclick="history.back(-1);" value="戻る" />
    <input type="submit" name="submit" value="変更" />
</form>
</body>
<?php require_once('../footer.php'); ?>