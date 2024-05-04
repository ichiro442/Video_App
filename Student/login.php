<?php
session_start();
require_once('../db_class.php');
require_once('../definition.php');

try {
    if (!empty($_POST["submit"])) {
        //データベースへ接続
        $dbConnect = new dbConnect();
        $dbConnect->initPDO();
        $userData = array();
        $url = $dbConnect->getURL();

        if (!empty($_POST["email"]) && !empty($_POST["password"])) {
            $uri =  $_SERVER["REQUEST_URI"];
            $userData = $dbConnect->login($_POST["email"], $_POST["password"], $uri);
        }
        if (empty($userData)) {
            $_SESSION['flash_message'] = FLASH_MESSAGE[9];
            header('Location:' . $url . "Student/login");
            exit;
        }
        session_regenerate_id(true); //session_idを新しく生成し、置き換える
        $_SESSION['userData'] = $userData;
        $_SESSION['userType'] = "student";
        $_SESSION['flash_message'] = FLASH_MESSAGE[10];

        header('Location:' . $url . "Student");
        exit;
    }
} catch (Exception $e) {
    echo $e->getMessage();
    exit;
}

$title = "ログイン";
require_once('header_function.php');
?>
<link rel="stylesheet" href="change.css">
<style>
    input[type="submit"] {
        width: 100%;
    }
</style>
</head>

<body>
    <?php require_once('../modal_message.php'); ?>
    <form method="post">
        <h2>ログイン</h2>
        <label for="email">メールアドレス:</label>
        <input type="email" id="email" name="email" required />
        <label for="password">パスワード:</label>
        <input type="password" id="password" name="password" required />
        <input type="submit" name="submit" value="ログイン" />
        <a href="../signup?u=student">生徒ご登録はこちらです。</a><br>
        <a href="../signup?u=teacher">講師ご登録はこちらです。</a>
    </form>
</body>
<?php require_once('../footer.php'); ?>

</html>