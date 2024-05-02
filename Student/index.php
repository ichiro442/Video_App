<?php
session_start();
require_once('../db_class.php');

try {
    require_once('validation.php');
} catch (PDOException $e) {
    echo $e->getMessage();
    exit;
}

$title = "講師検索";
require_once('header.php');
?>
<style>
    body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding-top: 80px;
    }

    .container {
        display: flex;
        align-items: flex-start;
        justify-content: center;
        /* height: 100vh; */
        max-width: 1400px;
    }

    .search-container {
        margin-right: 20px;
        border: 1px solid #ccc;
        width: 20%;
        align-items: normal;
    }

    .search-container h2 {
        margin-bottom: 10px;
    }

    .search-container input,
    .search-container select,
    .search-container button {
        margin-bottom: 10px;
        display: block;
        width: 80%;
    }

    .teacher-list {
        border: 1px solid #ccc;
        padding: 20px;
        max-width: 1030px;
        flex-wrap: wrap;
        width: 100%;
    }

    .teacher-list h2 {
        margin-bottom: 10px;
    }

    /* 講師リストのブロックごとのスタイル */
    .teacher-list div {
        margin-bottom: 10px;
        flex-wrap: wrap;
        margin: 3px;
        /* ブロックの下部に余白を追加 */
    }

    .teacher-block {
        border: solid 1px black;
    }

    /* 画像のスタイル */
    .teacher-block img {
        max-width: 100px;
        max-height: 100px;
    }
</style>
</head>

<body>
    <?php require_once('../modal_message.php'); ?>

    <div class="container">
        <div class="search-container">
            <h2>講師を検索する</h2>
            <div class="flex-vertical">
                <input type="text" id="searchName" placeholder="名前で検索">
                <select id="searchCountry">
                    <option value="">国名で検索</option>
                    <option value="USA">USA</option>
                    <option value="UK">UK</option>
                    <option value="Japan">Japan</option>
                    <option value="Spain">Spain</option>
                    <option value="Pakistan">Pakistan</option>
                </select>
                <button onclick="searchTeachers()">検索</button>
            </div>
        </div>
        <div class="teacher-list">
            <h2>講師一覧</h2>
            <div class="flex" style="justify-content: unset;">
                <?php foreach ($teachers as $teacher) { ?>
                    <div class="teacher-block">
                        <a href="teacher_detail.php?id=<?php echo h($teacher['id']) ?>" target="_blank">
                            <img src="../uploaded_pictures/<?php echo h($teacher["picture"]) ?>" alt="講師の画像">
                            <div><?php echo h($teacher['nickname']) ?></div>
                        </a>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
</body>
<?php require_once('../footer.php'); ?>

</html>