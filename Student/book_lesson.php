<?php
session_start();
require_once('../db_class.php');
require_once('validation.php');

try {
    if (!empty($_GET["t_id"]) && !empty($_GET["strDate"])) {
        $dbConnect->initPDO();
        $teacher = $dbConnect->findByOneColumn("id", $_GET["t_id"], "Teacher");
        $strDate = $_GET["strDate"];
    } else {
        $_SESSION['flash_message'] = FLASH_MESSAGE["LESSON"][1];
        $url = $dbConnect->getURL();
        header('Location:' . $url . "Student");
        exit;
    }


    if (!empty($_POST["submit"])) {
        $dbConnect->initPDO();

        // レッスンテーブルにデータを格納する
        $student_id = $_SESSION['userData']["id"];
        $teaher_id = $_GET["t_id"];
        $start_time = $_GET["strDate"];
        $current_time = new DateTime();
        $current_time = $current_time->format("Y-m-d H:i:s");
        // ハッシュの生成
        $hash = substr(md5($student_id . $teaher_id . $current_time), 0, 20);
        $result = $dbConnect->insertLesson($student_id, $teaher_id, $start_time, $hash);

        // 結果を確認する
        if (!$result) {
            $_SESSION['flash_message'] =  FLASH_MESSAGE["LESSON"][2];
            unset($_POST);
        } else {
            $_SESSION['flash_message'] = FLASH_MESSAGE["LESSON"][3];
            $url = $dbConnect->getURL();
            header('Location:' . $url . "Student");
            exit;
        }
    }
} catch (PDOException $e) {
    echo $e->getMessage();
    exit;
}

$title = "レッスン予約";
require_once('header.php');
?>
<link rel="stylesheet" href="change.css">
<style>
    .row {
        justify-content: flex-start;
    }

    form {
        width: 450px;
    }
</style>

<body>
    <?php require_once('../modal_message.php'); ?>
    <h2><?php echo h($title) ?></h2>
    <form method="POST">
        <div class="flex">
            <div class="">
                <div class="row" style="border: none;">
                    <div class="column-cente profile-picture">
                        <img src="../uploaded_pictures/<?php echo h($teacher["picture"]) ?>" alt="ユーザーの画像">
                    </div>
                </div>
            </div>
            <div class="profile-right">
                <div class="row flex">
                    <div class="column-left"><span>講師名: </span></div>
                    <div class="column-center"><?php echo h($teacher["nickname"]) ?></div>
                </div>
                <div class="row flex">
                    <div class="column-left"><span>レッスン日時: </span></div>
                    <div class="column-center"><?php echo h($strDate) ?></div>
                </div>
            </div>
        </div>
        <div class="flex">
            <input type="button" name="back" onclick="history.back(-1);" value="戻る" />
            <input type="submit" name="submit" value="予約する" />
        </div>
    </form>
</body>
<?php require_once('../footer.php'); ?>