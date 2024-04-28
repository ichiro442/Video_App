<?php
session_start();
require_once('../db_class.php');

try {
    if (!empty($_SESSION["userData"])) {
        //データベースへ接続
        $dbConnect = new dbConnect();
        $dbConnect->initPDO();
        $uri =  $_SERVER["REQUEST_URI"];
        $user = $dbConnect->findByMail($_SESSION["userData"]["email"], $uri);
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
    <div class="container">
        <h1>生徒のマイページ</h1>
        <div class="profile-info">
            <p><span>ID: </span><?php echo h($user["id"]) ?></p>
            <p><span>名前: </span><?php echo h($user["first_name"]) ?> <?php echo h($user["last_name"]) ?></p>
            <p><span>ニックネーム: </span><?php echo h($user["nickname"]) ?></p>
            <p><span><a href="change_pass.php">パスワード変更</a></span></p>
        </div>
    </div>
</body>

</html>