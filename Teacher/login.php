<?php
session_start();
require_once('../db_class.php');

try {
  if (!empty($_POST["submit"])) {
    //データベースへ接続
    $dbConnect = new dbConnect();
    $dbConnect->initPDO();
    $userData = array();
    $url = $dbConnect->getURL();

    if (!empty($_POST["email"]) && !empty($_POST["password"])) {
      $uri =  $_SERVER["REQUEST_URI"];
      $userData = $dbConnect->login($_POST["email"], $_POST["password"], $uri);
    }

    if (empty($userData)) {
      $_SESSION['flash_message'] = "メールアドレスまたはパスワードが違います。";
      header('Location:' . $url . "Teacher/login");
      exit;
    }
    session_regenerate_id(true); //session_idを新しく生成し、置き換える
    $_SESSION['userData'] = $userData;
    $_SESSION['userType'] = "teacher";

    header('Location:' . $url . "Teacher");
    exit;
  }
} catch (Exception $e) {
  echo $e->getMessage();
  exit;
}
$title = "ログイン画面";
require_once('header.php');
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="style.css">
  <title>ログイン</title>
  <style>
    body {
      font-family: Arial, sans-serif;
    }

    form {
      width: 300px;
      margin: 0 auto;
      padding: 20px;
      border: 1px solid #ccc;
      border-radius: 5px;
      background-color: #f9f9f9;
    }

    input[type="text"],
    input[type="password"],
    input[type="submit"] {
      width: 100%;
      padding: 10px;
      margin-top: 5px;
      margin-bottom: 10px;
      box-sizing: border-box;
    }

    input[type="submit"] {
      background-color: #4caf50;
      color: white;
      border: none;
      cursor: pointer;
    }

    input[type="submit"]:hover {
      background-color: #45a049;
    }
  </style>
</head>

<body>
  <form method="post">
    <h2>ログイン</h2>
    <label for="email">メールアドレス:</label>
    <input type="email" id="email" name="email" required />
    <label for="password">パスワード:</label>
    <input type="password" id="password" name="password" required />
    <input type="submit" name="submit" value="ログイン" />
  </form>
  <?php
  if (!empty($_SESSION['flash_message'])) {
    echo  "<p class='flashMessage' id='flash_message'>" . nl2br(h($_SESSION['flash_message'])) . "</p>";
    unset($_SESSION['flash_message']);
  }
  ?>

</body>

</html>