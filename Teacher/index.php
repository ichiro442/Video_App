<?php
session_start();
require_once('../db_class.php');
require_once('validation.php');
$userData = $_SESSION['userData'];

/**
 * 現在日以降の講師のスケジュールを取得する
 */
function searchCalendar()
{
    $dbConnect = new dbConnect();
    $dbConnect->initPDO();
    $pdo = $dbConnect->getPDO();
    $sql = "SELECT CASE available WHEN 1 THEN '○' ELSE '×' END as title";
    $sql .= " ,(start_time - INTERVAL 60 MINUTE) as start,(start_time - INTERVAL 30 MINUTE) as end "; //フィリピンの時間に修正
    $sql .= "from teacher_schedules where teacher_id = :id and (start_time - INTERVAL 60 MINUTE) >= DATE_FORMAT(CURRENT_DATE,'%Y-%m-%d')";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(":id", $_SESSION["userData"]["id"], PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
}

try {
    // 初期表示の場合
    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
        $dbConnect = new dbConnect();
        $dbConnect->initPDO();
        $pdo = $dbConnect->getPDO();
        $sql = "SELECT CASE available WHEN 1 THEN '○' ELSE '×' END as title";
        $sql .= " ,(start_time - INTERVAL 60 MINUTE) as start,(start_time - INTERVAL 30 MINUTE) as end "; //フィリピンの時間に修正
        $sql .= "from teacher_schedules where teacher_id = :id and (start_time - INTERVAL 60 MINUTE) >= DATE_FORMAT(CURRENT_DATE,'%Y-%m-%d')";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(":id", $_SESSION["userData"]["id"], PDO::PARAM_INT);
        $stmt->execute();
        $calendar = searchCalendar();
    }
    // 保存ボタンを押した場合
    if (!empty($_POST["submit"])) {
        $dbConnect = new dbConnect();
        $dbConnect->initPDO();
        $pdo = $dbConnect->getPDO();
        // 削除
        $removeEvents = json_decode($_POST['removeEvents'], true);
        // 0件以上の場合はinsertを実行
        if ($_POST['removeEvents'] != '' && count($removeEvents) > 0) {
            $sql = "DELETE FROM teacher_schedules WHERE ";
            foreach ($removeEvents as $index => $event) {
                if ($index !== 0) {
                    $sql .= 'or';
                }
                $sql .= " (teacher_id = '" . $_SESSION["userData"]["id"] . "' and start_time = " . "STR_TO_DATE('" . $event["start"] . "','%Y-%m-%d %H:%i:%s'))";
            }
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
        }

        // 追加
        $addEvents = json_decode($_POST['addEvents'], true);
        // 0件以上の場合はinsertを実行
        if ($_POST['addEvents'] != '' && count($addEvents) > 0) {
            $sql = "INSERT INTO teacher_schedules (teacher_id, start_time, available, created_at, updated_at) VALUES";
            foreach ($addEvents as $index => $event) {
                $sql .= "(" . $_SESSION["userData"]["id"] . "," . "STR_TO_DATE('" . $event["start"] . "','%Y-%m-%d %H:%i:%s')," . $event["available"] . ", now(), now())";
                if ($index !== array_key_last($addEvents)) {
                    $sql .= ',';
                }
            }
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
        }
        $calendar = searchCalendar();
    }
} catch (PDOException $e) {
    echo "エラー";
    echo $e->getMessage();
    exit;
}

$title = "講師マイページ";
require_once('header.php');
?>
<link rel="stylesheet" href="change.css">

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js'></script>
<style>
    #calendar {
        width: 50%;
        margin: 15px auto;
        margin-bottom: 100px;
        /* 画面の縦列の中央に配置 */
    }

    form {
        position: fixed;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        z-index: 100;
    }
</style>

<body>
    <?php require_once('../modal_message.php'); ?>
    <h1><?php echo h($title) ?></h1>
    <div class="profile-info flex">
        <div class="">
            <div class="row" style="border: none;">
                <div class="column-cente profile-picture">
                    <img src="../uploaded_pictures/<?php echo h($teacher["picture"]) ?>" alt="ユーザーの画像">
                </div>
                <div class="column-right" style="text-align: center;"><a href="change_picture">写真変更</a></div>
            </div>
        </div>
        <div class="profile-right">
            <div class="row flex">
                <div class="column-left"><span>ID:</span></div>
                <div class="column-center"><?php echo h($teacher["id"]) ?></div>
                <div class="column-right"></div>
            </div>
            <div class="row flex">
                <div class="column-left"><span>名前:</span></div>
                <div class="column-center"><?php echo h($teacher["first_name"]) ?> <?php echo h($teacher["last_name"]) ?></div>
                <div class="column-right"><a href="change_name"></a></div>
            </div>
            <div class="row flex">
                <div class="column-left"><span>ニックネーム:</span></div>
                <div class="column-center"><?php echo h($teacher["nickname"]) ?></div>
                <div class="column-right"><a href="change_nickname">変更</a></div>
            </div>
            <div class="row flex">
                <div class="column-left"><span>メールアドレス:</span></div>
                <div class="column-center"><?php echo h($teacher["email"]) ?></div>
                <div class="column-right"><a href="change_email">変更</a></div>
            </div>
            <div class="row flex">
                <div class="column-left"></div>
                <div class="column-center"></div>
                <div class="column-right"><a href="change_pass">パスワード変更</a></div>
            </div>
            <div class="row flex">
                <div class="column-left"><span>レッスン数:</span></div>
                <div class="column-center">100</div>
            </div>
            <div class="row flex">
                <div class="column-left"><span>トータル収入:</span></div>
                <div class="column-center">$5000</div>
            </div>
        </div>
    </div>
    <!-- スケジュールカレンダー -->
    <div class="">
        <div id='calendar'></div>
        <form method="post">
            <div class="flex">
                <input type="hidden" name="addEvents" id="addEvents" />
                <input type="hidden" name="removeEvents" id="removeEvents" />
                <input type="submit" name="submit" value="保存" />
            </div>
        </form>
    </div>

</body>
<?php require_once('schedule_register.php'); ?>
<?php require_once('../footer.php'); ?>

</html>