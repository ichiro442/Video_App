<?php
session_start();
require_once('../db_class.php');
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
            $_SESSION['flash_message'] =  "メールアドレスが更新できませんでした。もう一度入力してください。";
            unset($_POST);
        } else {
            $_SESSION['flash_message'] = "メールアドレスを更新しました。";
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
    <h2>メールアドレス変更</h2>
    <input type="email" name="email" value="<?php echo h($userData["email"]) ?>" required />
    <input type="button" name="back" onclick="history.back(-1);" value="戻る" />
    <input type="submit" name="submit" value="変更" />
</form>
</body>
<?php require_once('../footer.php'); ?>