<?php
function h($s)
{
    return htmlspecialchars($s, ENT_QUOTES, "UTF-8");
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo h($title) ?></title>
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
</head>

<body>
    <header class="flex">
        <div class="header-left">
            <a class="" href="/"><img src="/Img/logo-3.jpg" alt="ロゴ"></a>
        </div>
        <div class="header-right">
            <ul class="header-right-bottom flex">
                <?php
                require_once('../db_class.php');
                $dbConnect = new dbConnect();
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