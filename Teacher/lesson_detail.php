<?php
session_start();
require_once('../db_class.php');
require_once('validation.php');
$userData = $_SESSION['userData'];

try {
    $dbConnect = new dbConnect();
    $dbConnect->initPDO();

    // 講師idを使って講師の持っているレッスン情報を取得する
    // 完了していない、現在の予約を取得。残り未完了予約数も取得する
    $uri =  $_SERVER["REQUEST_URI"];
    $lessons = $dbConnect->findLessonByID($userData["id"], $uri);
    // 取得したレッスンの数を数える
    $lesson_count = count($lessons);

    // var_dump($lesson_count);
    // // var_dump($lessons);
    // exit;
    if (!empty($_POST["submit"])) {

        $userData["nickname"] = $_POST["nickname"];
        $column = "nickname";
        $uri =  $_SERVER["REQUEST_URI"];
        $result = $dbConnect->updateOneColumn($userData["id"], $userData["nickname"], $column, $uri);
        // 更新結果を確認する
        if (!$result) {
            $_SESSION['flash_message'] =  FLASH_MESSAGE[3];
            unset($_POST);
        } else {
            $_SESSION['flash_message'] = FLASH_MESSAGE[4];
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

$title = "レッスン詳細";
require_once('header.php');
?>
<link rel="stylesheet" href="change.css">

<?php require_once('../modal_message.php'); ?>
<h1><?php echo h($title) ?></h1>
<div class="booked-lesson">
    <!-- <h2>予約レッスン</h2> -->
    <div class="flex">
        <span>未完了のレッスン数:&nbsp;<?php echo h($lesson_count) ?> </span>
    </div>
    <!-- ここに現在予約しているレッスンを表示する -->
    <?php foreach ($lessons as $lesson) : ?>
        <div class="container">
            <a href="../room?lesson=<?php echo h($lesson["hash"]) ?>">
                <div class="flex">
                    <!-- <div class="">
                        <div class="row" style="border: none;">
                            <div class="column-cente profile-picture">
                                <img src="../uploaded_pictures/<?php echo h($lesson["picture"]) ?>" alt="ユーザーの画像">
                            </div>
                        </div>
                    </div> -->
                    <div class="profile-right">
                        <div class="row flex">
                            <div class="column-left"><span>生徒名:&nbsp;</span></div>
                            <div class="column-center"><?php echo h($lesson["nickname"]) ?></div>
                        </div>
                        <div class="row flex">
                            <div class="column-left"><span>国籍:&nbsp;</span></div>
                            <div class="column-center"><?php echo h($lesson["country"]) ?></div>
                        </div>
                        <div class="row flex">
                            <div class="column-left"><span>レッスン日時:&nbsp;</span></div>
                            <div class="column-center"><?php echo h($lesson["start_time"]) ?></div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    <?php endforeach; ?>
</div>

</body>
<?php require_once('../footer.php'); ?>