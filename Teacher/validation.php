<?php
require_once('../definition.php');

$dbConnect = new dbConnect();

if ((!isset($_SESSION["userData"]) || !empty($_SESSION["userData"])) && $_SESSION["userType"] == "teacher") {
    $dbConnect->initPDO();
    $uri =  $_SERVER["REQUEST_URI"];
    // すべての講師を取得する
    $teacher = $dbConnect->findByMail($_SESSION["userData"]["email"], $uri);
} else if ($_SESSION["userType"] == "student") {
    $_SESSION['flash_message'] = FLASH_MESSAGE[19];
    $url = $dbConnect->getURL();
    header('Location:' . $url . "Student");
} else {
    $_SESSION['flash_message'] = FLASH_MESSAGE[12];
    $url = $dbConnect->getURL();
    header('Location:' . $url . "Teacher/login");
}
