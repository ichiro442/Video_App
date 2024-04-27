<?php
session_start();
require_once('../db_class.php');

try {
    $dbConnect = new dbConnect();
    if (!empty($_SESSION["userData"])) {
        //データベースへ接続
        $dbConnect->initPDO();
        $uri =  $_SERVER["REQUEST_URI"];
        $teacher = $dbConnect->findByMail($_SESSION["userData"]["email"], $uri);
    } else {
        $_SESSION['flash_message'] = "ログインまたは登録を完了してください。";
        $url = $dbConnect->getURL();
        header('Location:' . $url . "Teacher/login");
    }
} catch (Exception $e) {
    echo $e->getMessage();
    exit;
}

$title = "マイページ";
require_once('header.php');

?>
<style>
    body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 0;
        background-color: #f4f4f4;
    }

    .container {
        width: 80%;
        margin: auto;
        padding: 20px;
        background-color: #fff;
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        margin-top: 50px;
    }

    h1 {
        text-align: center;
        margin-bottom: 20px;
    }

    .profile-info {
        margin-bottom: 20px;
    }

    .profile-info p {
        margin: 5px 0;
    }

    .profile-info p span {
        font-weight: bold;
    }
</style>

<body>
    <?php require_once('../modal_message.php'); ?>
    <div class="container">
        <h1>講師のマイページ</h1>
        <div class="profile-info">
            <p><span>ID: </span><?php echo h($teacher["id"]) ?></p>
            <p><span>名前: </span><?php echo h($teacher["first_name"]) ?> <?php echo h($teacher["last_name"]) ?></p>
            <p><span>ニックネーム: </span><?php echo h($teacher["nickname"]) ?></p>
            <p><span>レッスン数: </span>100</p>
            <p><span>トータル収入: </span>$5000</p>
        </div>
    </div>

</body>
<?php require_once('../footer.php'); ?>

</html>