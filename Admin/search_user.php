<?php
session_start();
require_once('../db_class.php');
require_once('../definition.php');

function h($s)
{
    return htmlspecialchars($s, ENT_QUOTES, "UTF-8");
}

$dbConnect = new dbConnect();
// if (empty($_SESSION['userType']) && $_SESSION['userType'] !== "admin") {
//     $_SESSION['flash_message'] = FLASH_MESSAGE[22];
//     $url = $dbConnect->getURL();
//     header('Location:' . $url);
//     exit;
// }
try {
    $dbConnect->initPDO();
    if (!empty($_GET["submit"])) {
        $query = $_GET["query"];
        $user_category = $_GET["user_category"];
        $users = $dbConnect->searchUsers($query, $user_category);
    } else {
        // $users = $dbConnect->getAllDealers();
    }
} catch (PDOException $e) {
    echo $e->getMessage();
    exit;
}
// var_dump($user_category);
// exit;
$selected = "";
$title = "ユーザー検索";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo h($title) ?></title>
    <link rel="stylesheet" href="style.css">
    <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
</head>
<style>
    .container {
        display: flex;
        /* max-width: 1700px; */
    }

    .search-container input[type="text"],
    .search-container input[type="number"] {
        padding: 10px 0;
    }

    .teacher-block {
        display: flex;
        padding: 10px 5px;
    }

    .user-id {
        width: 50px;
    }
</style>
</head>

<body>
    <?php require_once('../modal_message.php'); ?>
    <div class="container">
        <div class="search-container">
            <h2>ユーザー検索</h2>
            <div class="flex-vertical">
                <form action="" method="GET">
                    <div class="">
                        <label for="">ユーザーカテゴリ</label>
                    </div>
                    <div class="custom-select">
                        <select id="searchCountry" name="user_category">
                            <option value=""></option>
                            <?php foreach (USER_CATEGORY as $key => $category) : ?>
                                <?php $selected = "";
                                if (isset($_GET["user_category"]) && $key == $_GET["user_category"]) $selected = "selected" ?>
                                <option value="<?php echo h($key) ?>" <?php echo h($selected) ?>><?php echo h($category) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="">
                        <label for="">ID</label>
                    </div>
                    <div class="">
                        <input type="number" name="query[id]" id="searchName" value="<?php echo h($_GET["query"]["id"]) ?>">
                    </div>
                    <div class="">
                        <label for="">ニックネーム</label>
                    </div>
                    <div class="">
                        <input type="text" name="query[nickname]" id="searchName" value="<?php echo h($_GET["query"]["nickname"]) ?>">
                    </div>
                    <div class="">
                        <label for="">国籍</label>
                    </div>
                    <div class="custom-select">
                        <select id="searchCountry" name="query[country]">
                            <option value=""></option>
                            <?php foreach (COUNTRY as $country) : ?>
                                <?php $selected = "";
                                if ($country == $_GET["query"]["country"]) $selected = "selected" ?>
                                <option value="<?php echo h($country) ?>" <?php echo h($selected) ?>><?php echo h($country) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="">
                        <label for="">ステータス</label>
                    </div>
                    <div class="custom-select">
                        <select id="searchCountry" name="query[status]">
                            <option value=""></option>
                            <?php foreach (REGISTER as $key => $status) : ?>
                                <?php $selected = "";
                                if (isset($_GET["query"]["status"]) && !empty($_GET["query"]["status"]) && $status == $_GET["query"]["status"]) $selected = "selected" ?>
                                <?php $status = REGISTER[0] == $status ? "仮登録" : "本登録" ?>
                                <option value="<?php echo h($key) ?>" <?php echo h($selected) ?>><?php echo h($status) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <input type="submit" name="submit" value="検索" />
                </form>
            </div>
        </div>

        <!-- 検索結果表示 -->
        <div class="teacher-list">
            <div class="flex" style="justify-content: unset;">
                <div class="teacher-block column-name">
                    <div class="id">
                        <span>ID</span>
                    </div>
                    <div class="user-category">
                        <span>ユーザー</span>
                    </div>
                    <div class="last-name">
                        <span>名字</span>
                    </div>
                    <div class="first-name">
                        <span>名前</span>
                    </div>
                    <div class="nickname">
                        <span>ニックネーム</span>
                    </div>
                    <div class="country">
                        <span>国籍</span>
                    </div>
                    <div class="email">
                        <span>メール</span>
                    </div>
                    <div class="status">
                        <span>ステータス</span>
                    </div>
                </div>
                <?php foreach ($users as $user) : ?>
                    <div class="teacher-block">
                        <div class="id">
                            <span><a href="user_edit?id=<?php echo h($user['id']) ?>" target="_blank"><?php echo h($user['id']) ?></a></span>
                        </div>
                        <div class="user-category overflow-hidden">
                            <?php
                            if ($user_category == "0") {
                                $user_category = USER_CATEGORY[0];
                            } else if ($user_category == "1") {
                                $user_category = USER_CATEGORY[1];
                            }
                            ?>
                            <span><?php echo h($user_category) ?></span>
                        </div>
                        <div class="last-name overflow-hidden">
                            <span><?php echo h($user['last_name']) ?></span>
                        </div>
                        <div class="first-name overflow-hidden">
                            <span><?php echo h($user['first_name']) ?></span>
                        </div>
                        <div class="nickname overflow-hidden">
                            <span><?php echo h($user['nickname']) ?></span>
                        </div>
                        <div class="country overflow-hidden">
                            <span><?php echo h($user['country']) ?></span>
                        </div>
                        <div class="email overflow-hidden">
                            <span><?php echo h($user['email']) ?></span>
                        </div>
                        <div class="status">
                            <?php $status = REGISTER[0] == $user['status'] ? "仮登録" : "本登録" ?>
                            <span><?php echo h($status) ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

    </div>
</body>

</html>