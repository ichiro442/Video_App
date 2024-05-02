<?php
$dbConnect = new dbConnect();

if ((!isset($_SESSION["userData"]) || !empty($_SESSION["userData"])) && $_SESSION["userType"] == "teacher") {
    // $dbConnect->initPDO();
    // // すべての講師を取得する
    // $teachers = $dbConnect->findAllTeachers();
} else if ($_SESSION["userType"] == "student") {
    $_SESSION['flash_message'] = "講師は生徒画面にログインできません。";
    $url = $dbConnect->getURL();
    header('Location:' . $url . "Teacher");
} else {
    $_SESSION['flash_message'] = "ログインまたは登録を完了してください。";
    $url = $dbConnect->getURL();
    header('Location:' . $url . "Student/login");
}
