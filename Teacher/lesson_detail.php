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

    date_default_timezone_set('Asia/Tokyo');
    // 予約されたすべてのレッスンに対して処理を行う
    foreach ($lessons as $key => $lesson) {
        // 生徒IDを使って講師の情報を取得する
        $student = $dbConnect->findByOneColumn("id", $lesson['student_id'], "Student");

        // レッスンが終了していない場合、取得した講師情報を配列に追加する
        $finished_flg_int = intval($lesson["finished_flg"]);

        $current_time = new DateTime();
        $start_time = new DateTime($lesson["start_time"]);

        // 定数からキャンセル時間を取得する
        $cancel_time = LESSON["cancel_time"];
        $cancel_time = $start_time->modify("+$cancel_time minutes");
        $students_array = [];
        if ($finished_flg_int !== 1 && ($current_time <= $cancel_time)) {
            $students_array[] = [
                "picture" => $student['picture'],
                "nickname" => $student['nickname'],
                "country" => $student['country'],
                "start_time" => $lesson['start_time'],
                "hash" => $lesson['hash'],
                "finished_flg" => $lesson['finished_flg']
            ];
        } elseif ($finished_flg_int == 0 && ($current_time > $cancel_time)) {
            // 未完了のレッスンがレッスン開始時刻からLESSON["cancel_time"]分過ぎた場合、キャンセルする
            $finished_flg_cancel = LESSON["finished_flg_cancel"];
            $dbConnect->updateLesson($lesson['hash'], "finished_flg", $finished_flg_cancel);
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
    <?php foreach ($students_array as $lesson) : ?>
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