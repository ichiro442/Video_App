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
        padding-top: 80px;
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
        width: 800px;
        margin: 0 auto;
        padding: 20px;
        border: 1px solid #ccc;
        border-radius: 5px;
        background-color: #f9f9f9;
    }

    .profile-info p {
        margin: 5px 0;
    }

    .profile-info p span {
        font-weight: bold;
    }

    .row {
        justify-content: space-between;
        align-items: center;
        border-bottom: 1px solid #ccc;
        padding: 10px;

    }

    .profile-right {
        width: 100%;
    }

    /* 画像のスタイル */
    .profile-picture img {
        max-width: 200px;
        max-height: 200px;
    }
</style>

<body>
    <?php require_once('../modal_message.php'); ?>
    <div class="profile-info flex">
        <div class="">
            <div class="row" style="border: none;">
                <div class="column-cente profile-picture">
                    <img src="../uploaded_pictures/<?php echo h($user["picture"]) ?>" alt="ユーザーの画像">
                </div>
                <div class="column-right"><a href="change_picture.php">写真変更</a></div>
            </div>
        </div>
        <div class="profile-right">
            <div class="row flex">
                <div class="column-left"><span>ID:</span></div>
                <div class="column-center"><?php echo h($user["id"]) ?></div>
                <div class="column-right"></div>
            </div>
            <div class="row flex">
                <div class="column-left"><span>名前:</span></div>
                <div class="column-center"><?php echo h($user["first_name"]) ?> <?php echo h($user["last_name"]) ?></div>
                <div class="column-right"><a href="change_name.php"></a></div>
            </div>
            <div class="row flex">
                <div class="column-left"><span>ニックネーム:</span></div>
                <div class="column-center"><?php echo h($user["nickname"]) ?></div>
                <div class="column-right"><a href="change_nickname.php">変更</a></div>
            </div>
            <div class="row flex">
                <div class="column-left"><span>メールアドレス:</span></div>
                <div class="column-center"><?php echo h($user["email"]) ?></div>
                <div class="column-right"><a href="change_email.php">変更</a></div>
            </div>
            <div class="row flex">
                <div class="column-left"></div>
                <div class="column-center"></div>
                <div class="column-right"><a href="change_pass.php">パスワード変更</a></div>
            </div>
        </div>
    </div>
</body>
<?php require_once('../footer.php'); ?>

</html>