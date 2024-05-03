<?php
session_start();
require_once('../db_class.php');
require_once('validation.php');

try {
    $dbConnect->initPDO();
    $pdo = $dbConnect->getPDO();

    // 検索ボタンが押された時の処理
    if (!empty($_GET["submit"])) {
        $query = $_GET["query"];
        $sql = "SELECT * FROM teachers WHERE";
        $andcount = 0;

        // ANDをつけてSQLを作成する
        foreach ($query as $key => $value) { // nicknameとcountry
            if (!empty($value)) {
                if ($andcount > 0) {
                    $sql .= " AND";
                }
                $sql .= " (";
                if ($key == "nickname") {
                    $sql .= " $key LIKE :$key";
                } else {
                    $sql .= " $key = :$key";
                }
                $sql .= ")";
                $andcount++;
            }
        }
        // 上で作成したSQLに値をバインドする
        if ($andcount > 0) {
            $stmt = $pdo->prepare($sql);

            foreach ($query as $key => $value) {
                if (!empty($value)) {
                    if ($key == "nickname") {
                        $stmt->bindValue(":$key", "%" . $value . "%", PDO::PARAM_STR);
                    } else {
                        $stmt->bindParam(":$key", $value);
                    }
                }
            }
        } else {
            $sql = "SELECT * FROM teachers";
            $stmt = $pdo->prepare($sql);
        }
    } else {
        $_GET = [];
        $sql = "SELECT * FROM teachers";
        $stmt = $pdo->prepare($sql);
    }
    $stmt->execute();
} catch (PDOException $e) {
    echo $e->getMessage();
    exit;
}

$title = "講師検索";
require_once('header.php');
?>
<style>
    .container {
        display: flex;
        justify-content: center;
        max-width: 1400px;
    }

    .search-container input[type="text"] {
        padding: 10px 0;
    }
</style>
</head>

<body>
    <?php require_once('../modal_message.php'); ?>

    <div class="container">
        <div class="search-container">
            <h2>講師を検索する</h2>
            <div class="flex-vertical">
                <form action="" method="GET">
                    <div class="">
                        <label for="">講師名</label>
                    </div>
                    <div class="">
                        <input type="text" name="query[nickname]" id="searchName">
                    </div>
                    <div class="">
                        <label for="">国籍</label>
                    </div>
                    <div class="">
                        <select id="searchCountry" name="query[country]">
                            <option value=""></option>
                            <?php foreach (COUNTRY as $country) : ?>
                                <option value="<?php echo h($country) ?>"><?php echo h($country) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <input type="submit" name="submit" value="検索" />
                </form>
            </div>
        </div>
        <div class="teacher-list">
            <h2>講師一覧</h2>
            <div class="flex" style="justify-content: unset;">
                <?php foreach ($stmt as $teacher) : ?>
                    <div class="teacher-block">
                        <a href="teacher_detail?id=<?php echo h($teacher['id']) ?>" target="_blank">
                            <img src="../uploaded_pictures/<?php echo h($teacher["picture"]) ?>" alt="講師の画像">
                            <div><?php echo h($teacher['nickname']) ?></div>
                            <div><?php echo h($teacher['country']) ?></div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</body>
<?php require_once('../footer.php'); ?>

</html>