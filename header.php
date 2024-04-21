<?php
// 特殊文字をエスケープするために使うメソッド
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
</head>