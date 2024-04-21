<?php
$title = "ログアウト";
session_start();
require_once('db_class.php');

ini_set("display_errors", "1");
//  クッキーの削除
if (ini_get("session.use_cookies")) {
    setcookie(session_name(), '', time() - 42000);
}

$dbConnect = new dbConnect();
$url = $dbConnect->getURL();
$_SESSION['userType'] == "student";
// セッションの削除
if ($_SESSION['userType'] == "admin") {
    $_SESSION = [];
    session_destroy();
    header('Location:' . $url . "Admin");
    exit();
} elseif ($_SESSION['userType'] == "teacher") {
    $_SESSION = [];
    session_destroy();
    header('Location:' . $url . "Teacher");
    exit();
} elseif ($_SESSION['userType'] == "student") {
    $_SESSION = [];
    session_destroy();
    header('Location:' . $url . "Student");
    exit();
}
