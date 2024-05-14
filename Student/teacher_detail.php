<?php
session_start();
require_once('../db_class.php');
require_once('validation.php');

// 公開ページからの遷移でない場合
if ($_GET["u"] !== "un") {
    // 登録者でなければ講師検索画面にリダイレクト
    if ($_SESSION['userType'] !== "student") {
        $_SESSION['flash_message'] = FLASH_MESSAGE[13];
        $dbConnect = new dbConnect();
        $url = $dbConnect->getURL();
        header("Location: " . $url . "Student");
        exit;
    }
}

try {
    $dbConnect = new dbConnect();
    $dbConnect->initPDO();
    $teacher = $dbConnect->findByOneColumn("id", $_GET["id"], "Teacher");

    // 現在日時よりも未来の講師スケジュールだけを取り出す
    $calendar = $dbConnect->findTeacherScheduleByID($_GET["id"]);

    // 今日から１週間分の講師のレッスン予約を取得する
    $lessons_week = $dbConnect->findLessonByTeacherID($_GET["id"]);

    // 現在時刻日本時間を取得する
    date_default_timezone_set('Asia/Tokyo');

    // 現在時刻を取得
    $JapanCurrentDateTime = date('Y-m-d H:i:s');
} catch (PDOException $e) {
    echo $e->getMessage();
    exit;
}

$title = "講師詳細";
require_once('header.php');
?>
<html>

<body>
    <div class="teacher-detail-box flex">
        <div class="container flex">
            <div class="teacher-left">
                <div class="img-box">
                    <?php
                    if (isset($teacher["picture"]) || !is_null($teacher["picture"])) {
                        echo '<img src="../uploaded_pictures/' . h($teacher["picture"]) . '" alt="講師の画像"> ';
                    } else {
                        echo '<img src="../Img/person.jpg" alt="デフォルトの画像"> ';
                    }
                    ?>
                </div>
                <!-- <div class="">
                <p><?php echo h($teacher["nickname"]) ?></p>
            </div> -->
            </div>
            <div class="teacher-right">
                <div class="item flex">
                    <p>名前</p>
                    <div class="detail-item"><?php echo h($teacher["nickname"]) ?></div>
                </div>
                <div class="item flex">
                    <p>国籍</p>
                    <div class="detail-item">フィリピン</div>
                </div>
            </div>
        </div>
    </div>
    <div class="calendar">
        <h2>スケジュール</h2>
        <div class=""></div>
        <table>
            <thead>
                <!-- ここにJavaScriptで日付と曜日が追加されます -->

            </thead>
            <tbody id="calendar-body">
                <!-- ここにJavaScriptで出勤入力欄が追加されます -->
            </tbody>
        </table>
    </div>
</body>
<script>
    const JapanCurrentDateTime = new Date('<?php echo $JapanCurrentDateTime; ?>');
</script>
<?php require_once('display_schedule.php'); ?>

</html>