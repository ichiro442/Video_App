<?php
session_start();
require_once('db_class.php');
// データベースから講師データを取得する
// htmlで表示する

// 特殊文字をエスケープするために使うメソッド
function h($s)
{
    return htmlspecialchars($s, ENT_QUOTES, "UTF-8");
}
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

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>講師検索</title>
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Serif+JP:wght@200&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.0.0.min.js"></script>
    <script>
        //ヘッダーメニューの処理
        $(function() {
            $('.menu-btn').click(function() {
                $(this).toggleClass('active');

                if ($(this).hasClass('active')) {
                    $('.gnavi__sp-style').addClass('active');
                    $('.gnavi__sp-style').css('visibility', 'visible');
                } else {
                    $('.gnavi__sp-style').removeClass('active');
                    $('.gnavi__sp-style').css('visibility', 'hidden');
                }
            });
        });
    </script>
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
<header class="flex">
    <div class="header-left">
        <a class="" href="/"><img src="/Img/logo-3.jpg" alt="ロゴ"></a>
    </div>
    <div class="header-right">
        <ul class="header-right-bottom flex">
            <?php
            $url = $dbConnect->getURL();

            if (!empty($_SESSION['userData'])) {
                echo '<li><a id="login" class="btn login-btn" href="' . $url . 'logout">ログアウト</a></li>';
            } else {
                echo '<li><a class="btn register-btn" href="' . $url . 'signup?u=student">生徒登録</a></li>';
                echo '<li><a class="btn register-btn" href="' . $url . '/signup?u=teacher">講師登録</a></li>';
                echo '<li><a id="login" class="btn login-btn" href="' . $url . 'Student/login.php">ログイン</a></li>';
            }

            if (!empty($_SESSION['userData'])) {
                echo '<li><a class="btn mypage-btn" href="' . $url . 'Student/my_page.php">' . h($_SESSION['userData']['first_name']) . '</a></li>';
            } else {
                echo '';
            }
            ?>
        </ul>
    </div>

    <!--768px以下で表示-->
    <div class="menu-box">
        <div class="menu-btn">
            <span></span>
            <span></span>
            <span></span>
        </div>
        <nav class="gnavi__sp-style">
            <ul>
                <?php
                // echo '<li><a class="btn" href="/register.php">登録</a></li>';

                if (!empty($_SESSION['userData'])) {
                    echo '<li><a id="login" class="btn login-btn" href="' . $url . 'logout">ログアウト</a></li>';
                } else {
                    echo '<li><a id="login" class="btn login-btn" href="' . $url . 'Student/login.php">ログイン</a></li>';
                }

                if (!empty($_SESSION['userData'])) {
                    echo '<li><a class="btn" href="/my_page.php">' . h($_SESSION['userData']['name']) . '</a></li>';
                } else {
                    echo '';
                }
                ?>
            </ul>
        </nav>
    </div>
    <!--/768px以下で表示-->
</header>

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