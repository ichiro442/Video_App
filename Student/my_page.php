<?php
session_start();
require_once('../db_class.php');
require_once('validation.php');

try {
    if (!empty($_SESSION["userData"])) {
        //データベースへ接続
        $dbConnect->initPDO();
        $uri =  $_SERVER["REQUEST_URI"];
        $user = $dbConnect->findByMail($_SESSION["userData"]["email"], $uri);
        $uri =  $_SERVER["REQUEST_URI"];
        // student_idを使って今日以降にこの生徒が予約しているレッスンすべてを取得する
        $lessons = $dbConnect->findLessonByID($_SESSION["userData"]["id"], $uri);

        date_default_timezone_set('Asia/Tokyo');
        $teachers_array = [];
        // 予約されたすべてのレッスンに対して処理を行う
        foreach ($lessons as $key => $lesson) {
            // 講師IDを使って講師の情報を取得する
            $teacher = $dbConnect->findByOneColumn("id", $lesson['teacher_id'], "Teacher");

            // レッスンが終了していない場合、取得した講師情報を配列に追加する
            $finished_flg_int = intval($lesson["finished_flg"]);

            $current_time = new DateTime();
            $start_time = new DateTime($lesson["start_time"]);

            // 定数からキャンセル時間を取得する
            $cancel_time = LESSON["cancel_time"];
            $cancel_time = $start_time->modify("+$cancel_time minutes");
            if ($finished_flg_int !== 1 && ($current_time <= $cancel_time)) {
                $teachers_array[] = [
                    "picture" => $teacher['picture'],
                    "nickname" => $teacher['nickname'],
                    "country" => $teacher['country'],
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
    }
} catch (Exception $e) {
    echo $e->getMessage();
    exit;
}

$title = "マイページ";
require_once('header.php');

?>
<!-- <link rel="stylesheet" href="change.css"> -->
<style>
    .booked-lesson {
        margin: 10px auto;
        width: 800px;
    }

    .container {
        border: solid 1px darkgray;
        border-radius: 20px;
        margin: 10px;
        max-width: unset;
    }

    .booked-lesson img {
        max-width: 90px;
    }

    .booked-lesson .row {
        justify-content: flex-start;
    }

    .booked-lesson a {
        color: unset;
    }

    h3 {
        text-align: center;
    }
</style>

<body>
    <?php require_once('../modal_message.php'); ?>
    <h2><?php echo h($title) ?></h2>
    <div class="profile-info flex">
        <div class="">
            <div class="row" style="border: none;">
                <div class="column-cente profile-picture">
                    <img src="../uploaded_pictures/<?php echo h($user["picture"]) ?>" alt="ユーザーの画像">
                </div>
                <div class="column-right" style="text-align: center;"><a href="change_picture">写真変更</a></div>
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
                <div class="column-right"><a href="change_name"></a></div>
            </div>
            <div class="row flex">
                <div class="column-left"><span>ニックネーム:</span></div>
                <div class="column-center"><?php echo h($user["nickname"]) ?></div>
                <div class="column-right"><a href="change_nickname">変更</a></div>
            </div>
            <div class="row flex">
                <div class="column-left"><span>メールアドレス:</span></div>
                <div class="column-center"><?php echo h($user["email"]) ?></div>
                <div class="column-right"><a href="change_email">変更</a></div>
            </div>
            <div class="row flex">
                <div class="column-left"></div>
                <div class="column-center"></div>
                <div class="column-right"><a href="change_pass">パスワード変更</a></div>
            </div>
        </div>
    </div>
    <div class="booked-lesson">
        <h3>予約レッスン</h3>
        <!-- ここに現在予約しているレッスンを表示する -->
        <?php foreach ($teachers_array as $lesson) : ?>
            <div class="container">
                <a href="../room?lesson=<?php echo h($lesson["hash"]) ?>">
                    <div class="flex">
                        <div class="">
                            <div class="row" style="border: none;">
                                <div class="column-cente profile-picture">
                                    <img src="../uploaded_pictures/<?php echo h($lesson["picture"]) ?>" alt="ユーザーの画像">
                                </div>
                            </div>
                        </div>
                        <div class="profile-right">
                            <div class="row flex">
                                <div class="column-left"><span>講師名:&nbsp;</span></div>
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

</html>