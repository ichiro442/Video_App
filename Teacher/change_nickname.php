<?php
session_start();
require_once('../db_class.php');
require_once('validation.php');
$userData = $_SESSION['userData'];

try {
    if (!empty($_POST["submit"])) { //変更ボタンが押され方どうかを確認
        $dbConnect = new dbConnect();
        $dbConnect->initPDO();

        $userData["nickname"] = $_POST["nickname"];
        $column = "nickname";
        $uri =  $_SERVER["REQUEST_URI"];
        $result = $dbConnect->updateOneColumn($userData["id"], $userData["nickname"], $column, $uri);
        // 更新結果を確認する
        if (!$result) {
            $_SESSION['flash_message'] =  "ニックネームが更新できませんでした。もう一度入力してください。";
            unset($_POST);
        } else {
            $_SESSION['flash_message'] = "ニックネームを更新しました。";
            $_SESSION['userData']["nickname"] = $_POST["nickname"];
            $url = $dbConnect->getURL();
            header('Location:' . $url . "Teacher");
            exit;
        }
    }
} catch (PDOException $e) {
    echo "エラー";
    echo $e->getMessage();
    exit;
}

$title = "ニックネーム変更";
require_once('header.php');
?>
<link rel="stylesheet" href="change.css">

<?php require_once('../modal_message.php'); ?>
<form method="post">
    <h2><?php echo h($title) ?></h2>
    <input type="text" name="nickname" value="<?php echo h($userData["nickname"]) ?>" required />
    <div class="flex">
        <input type="button" name="back" onclick="history.back(-1);" value="戻る" />
        <input type="submit" name="submit" value="変更" />
    </div>
</form>
</body>
<?php require_once('../footer.php'); ?>