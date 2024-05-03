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
    $pdo = $dbConnect->getPDO();

    $stmt = $pdo->prepare("SELECT * FROM teachers WHERE id=:id ");
    $stmt->bindvalue(":id", $_GET["id"]);
    $stmt->execute();
    $teacher = $stmt->fetch();
} catch (PDOException $e) {
    echo $e->getMessage();
    exit;
}

$title = "講師詳細";
require_once('header.php');
?>

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
            <!-- <div class="search-items">
            <p>カレンダー</p>
            <div class="detail-item"></div>
        </div> -->
        </div>
    </div>
</div>
<div class="">
    <p>カレンダー</p>
    <div class=""></div>
</div>
</div>
<div>
    </body>

    </html>