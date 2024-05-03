<?php
session_start();
require_once('../db_class.php');
require_once('validation.php');

$userData = $_SESSION['userData'];

try {
    if (!empty($_POST["submit"])) { //変更ボタンが押され方どうかを確認
        $dbConnect = new dbConnect();
        $dbConnect->initPDO();

        $userData["email"] = $_POST["email"];
        $column = "email";
        $uri =  $_SERVER["REQUEST_URI"];
        $result = $dbConnect->updateOneColumn($userData["id"], $userData["email"], $column, $uri);

        // 結果を確認する
        if (!$result) {
            $_SESSION['flash_message'] =  FLASH_MESSAGE[1];
            unset($_POST);
        } else {
            $_SESSION['flash_message'] = FLASH_MESSAGE[2];
            $_SESSION['userData']["nickname"] = $_POST["email"];
            $url = $dbConnect->getURL();
            header('Location:' . $url . "Student/my_page");
            exit;
        }
    }
} catch (PDOException $e) {
    echo "エラー";
    echo $e->getMessage();
    exit;
}

$title = "メールアドレス変更";
require_once('header.php');
?>
<link rel="stylesheet" href="change.css">

<?php require_once('../modal_message.php'); ?>
<form method="post">
    <h2><?php echo h($title) ?></h2>
    <input type="email" name="email" value="<?php echo h($userData["email"]) ?>" required />
    <div class="flex">
        <input type="button" name="back" onclick="history.back(-1);" value="戻る" />
        <input type="submit" name="submit" value="変更" />
    </div>
</form>
</body>
<?php require_once('../footer.php'); ?>