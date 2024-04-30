<?php
session_start();
require_once('../db_class.php');
$userData = $_SESSION['userData'];

/**
 * アップロード処理を行う関数
 * $filedata: $_FILES[key]を渡す。
 */
function uploadFile($filedata)
{
    // 空なら処理しない
    if (empty($filedata)) return false;
    $dbConnect = new dbConnect();

    // アップするディレクトリ
    $uploadDir = $dbConnect->getdir();

    if (!file_exists($uploadDir)) {
        if (!mkdir($uploadDir, 0777, true)) {
            throw new Exception("フォルダを作成できません");
        }
    }
    // ファイルサイズチェック
    $fileSize = $filedata['size'];

    // ファイルサイズが1MB(1KB = 1024byte, 1MB = 1024KB)未満かどうか
    if ($fileSize > (1024 * 1024)) {
        throw new Exception("ファイルサイズは1MB未満にしてください。");
    }

    // オリジナルファイル名
    $fileName =  basename($filedata['name']);
    // 一時保存先
    $tmp_path = $filedata['tmp_name'];

    // オリジナルのファイル名から拡張子を取得してチェックする
    $file_ext = pathinfo($fileName, PATHINFO_EXTENSION);
    if (!in_array(strtolower($file_ext), ["jpg", "jpeg", "png"])) {
        throw new Exception("画像ファイルを添付してください。");
    }

    //ファイル名のハッシュ化
    $newSaveFilename = date("YmdHis") . substr(md5($fileName), 0, 10) . "." . $file_ext;
    $newSaveFilePath = $uploadDir . $newSaveFilename;

    //ファイルを移動する
    if (is_uploaded_file($tmp_path)) {
        if (move_uploaded_file($tmp_path, $newSaveFilePath)) {
            return  $newSaveFilename;
        } else {
            throw new Exception("ファイルが移動できません。");
        }
    } else {
        throw new Exception("ファイルが検証できません。");
    }
    return false;
}

try {
    // 変更ボタンが押された時の処理を記述します。
    if (!empty($_POST["submit"])) {
        $dbConnect = new dbConnect();
        $dbConnect->initPDO();
        $user = $_POST;
        // 画像アップロード処理
        // 初期化。アップロードできなかった場合であってもそれ以外を保存するようにする
        $user["picture"] = "";

        if (!empty($_FILES['picture'])) {
            $user["picture"] = uploadFile($_FILES['picture']);
            if ($user["picture"] === false) {
                $user["picture"] = "";
                throw new Exception("画像の保存処理で失敗しました。");
            }
        }
        $column = "picture";
        $uri =  $_SERVER["REQUEST_URI"];

        $dbConnect->updateOneColumn($userData["id"], $user["picture"], $column, $uri);
        $_SESSION['userData'] = $userData;
        $_SESSION['flash_message'] = "画像を更新しました。";
        $url = $dbConnect->getURL();

        header('Location:' . $url . "Student/my_page");
        exit;
    }
} catch (PDOException $e) {
    echo "エラー";
    echo $e->getMessage();
    exit;
}

$title = "画像変更";
require_once('header.php');
?>

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

    /* プレビュー画像の高さと幅を指定 */
    #preview {
        max-height: 200px;
        max-width: 200px;
    }
</style>

<?php require_once('../modal_message.php'); ?>
<form method="post" enctype="multipart/form-data">
    <h2>画像変更</h2>
    <div class="register-item flex">
        <div class="form-item-label flex">
            <label>画像登録<span class="red-text">*</span></label>
        </div>
        <div class="form-item-input">
            <div class="">
                <img id="preview" src="<?php echo h($userData["picture"]) ?>" alt="">
                <?php
                // if(!empty($userData['picture'])){
                //     echo '<img src="/uploaded_pictures/'.h($userData['picture']).'">';
                // }
                ?>
            </div>
            <input type="hidden" name="MAX_FILE_SIZE" value="1048576">
            <input type="file" name="picture" accept="image/*" required="required" onchange="previewImage(this);">
            <input type="button" name="back" onclick="history.back(-1);" value="戻る" />
            <input type="submit" name="submit" value="変更" />
        </div>
</form>
</body>
<script>
    //選んだ画像をプレビューするメソッド
    function previewImage(obj) {
        var fileReader = new FileReader();
        fileReader.onload = (function() {
            document.getElementById('preview').src = fileReader.result;
        });
        fileReader.readAsDataURL(obj.files[0]);
    }
</script>
<?php require_once('../footer.php'); ?>