<?php
require_once('../definition.php');
$dbConnect = new dbConnect();

if ((!isset($_SESSION["userData"]) ||
        !empty($_SESSION["userData"])) && $_SESSION["userType"] == "student" ||
    $_GET["u"] == "un"
) {
    $dbConnect->initPDO();
    // すべての講師を取得する
    $teachers = $dbConnect->findAllTeachers();
} else if ($_SESSION["userType"] == "teacher") {
    $_SESSION['flash_message'] = FLASH_MESSAGE[11];
    $url = $dbConnect->getURL();
    header('Location:' . $url . "Teacher");
} else {
    $_SESSION['flash_message'] = FLASH_MESSAGE[12];
    $url = $dbConnect->getURL();
    header('Location:' . $url . "Student/login");
}
