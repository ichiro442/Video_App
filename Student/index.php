<?php
session_start();
require_once('../db_class.php');
// データベースから講師データを取得する
// htmlで表示する

try {
    $dbConnect = new dbConnect();
    $dbConnect->initPDO();
    $pdo = $dbConnect->getPDO();

    // すべての講師を取得する
    $teachers = $dbConnect->findAllTeachers();
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
        padding: 0;
    }

    .container {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100vh;
    }

    .search-container {
        margin-right: 20px;
    }

    .search-container h2 {
        margin-bottom: 10px;
    }

    .search-container input,
    .search-container select,
    .search-container button {
        margin-bottom: 10px;
        display: block;
    }

    .teacher-list {
        border: 1px solid #ccc;
        padding: 20px;
        max-width: 600px;
    }

    .teacher-list h2 {
        margin-bottom: 10px;
    }

    .teacher-list div {
        margin-bottom: 5px;
    }
</style>
</head>

<body>
    <div class="container">
        <div class="search-container">
            <h2>講師を検索する</h2>
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
        <div class="teacher-list">
            <h2>講師一覧</h2>
            <?php
            // 講師一覧を表示
            foreach ($teachers as $teacher) {
                echo '<div><a href="teacher_detail.php?id=' . $teacher['id'] . '" target="blank">' . $teacher['nickname']  . '</a></div>';
            }
            ?>
        </div>
    </div>
</body>

</html>