<?php
session_start();
require_once('info.php');

class dbConnect
{
    private $pdo = null;

    // ローカル環境のURL
    private $localURL = "http://localhost:8888/video_app/";

    // 本番環境のURL
    private $prodURL = "http://moriyama-programming.tokyo/";

    // ローカル環境の写真をアップロードする先のディレクトリ
    private $localUploadDir = "/Applications/MAMP/htdocs/video_app/uploaded_pictures/";

    // 本番環境の写真をアップロードする先のディレクトリ
    private $prodUploadDir = "/var/www/video_app/uploaded_pictures/";

    /**
     * =======================
     * || 共通メソッド ||
     * =======================
     */

    // 【共通】データベースに接続する
    public function initPDO()
    {
        $dsn = "mysql:dbname=" . DATABASE . ";host=" . HOST . ";charset=utf8mb4";
        $options = [];
        $this->pdo = new PDO($dsn, USERNAME, PASSWORD, $options);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    // 【共通】pdoを取得する
    public function getPDO()
    {
        return $this->pdo;
    }

    // 【共通】ドメインにlocalhostが含まれているかどうかを確認する
    public function containsLocalhost($domain)
    {
        return preg_match('/localhost/', $domain);
    }

    // 【共通】URLを取得する
    public function getURL()
    {
        if ($this->containsLocalhost($_SERVER['HTTP_HOST'])) {
            return $this->localURL;
        } else {
            return $this->prodURL;
        }
    }
    // 【共通】写真をアップロードするする先のディレクトリの取得
    public function getdir()
    {
        if ($this->containsLocalhost($_SERVER['HTTP_HOST'])) {
            return $this->localUploadDir;
        } else {
            return $this->prodUploadDir;
        }
    }

    // 【共通】ログインする
    public function login($email, $password, $uri)
    {

        $table = preg_match('/Teacher/', $uri) ? "teachers" : "students";
        $stmt = $this->pdo->prepare("SELECT * FROM `$table` WHERE email=:email"); //SQL文の骨子を準備
        $stmt->bindvalue(":email", $email);
        $stmt->execute();
        $userData = $stmt->fetch();
        if (
            !isset($userData['email']) ||
            !password_verify($password, $userData['password'])
        ) {
            return false;
        }
        unset($userData['password']);
        return $userData;
    }

    // 【共通】メールアドレスでユーザーを検索する
    public function findByMail($email, $uri)
    {
        $table = preg_match('/Teacher/', $uri) ? "teachers" : "students";
        $stmt = $this->pdo->prepare("SELECT * FROM `$table` WHERE email=:email");
        $stmt->bindValue(":email", $email);
        $stmt->execute();
        $user = $stmt->fetch();
        return $user;
    }
    // 【共通】メールアドレスですべてのユーザーを検索する
    public function findAllUsersByMail($email)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM teachers WHERE email=:email UNION SELECT * FROM students WHERE email=:email");
        $stmt->bindValue(":email", $email);
        $stmt->execute();
        $user = $stmt->fetch();
        return $user;
    }

    // 【共通】ユーザーを仮登録する
    public function insertUser($first_name, $last_name, $nickname, $email, $password, $table)
    {
        $stmt = $this->pdo->prepare("INSERT INTO `$table`(first_name,last_name,nickname,email,password,shash) VALUE (:first_name,:last_name,:nickname,:email,:password,:shash)");
        $stmt->bindvalue(":first_name", $first_name);
        $stmt->bindvalue(":last_name", $last_name);
        $stmt->bindvalue(":nickname", $nickname);
        if ($this->findAllUsersByMail($email)) return false;

        $stmt->bindvalue(":email", $email);
        $phash = password_hash($password, PASSWORD_DEFAULT);
        $stmt->bindvalue(":password", $phash);
        $shash = substr(md5($last_name . $email), 0, 10);
        $stmt->bindvalue(":shash", $shash);
        $stmt->execute();
        return $shash;
    }

    // 【共通】一つのデータを更新
    public function updateOneColumn($userId, $value, $column, $uri)
    {
        $table = preg_match('/Teacher/', $uri) ? "teachers" : "students";
        $query = "update `$table` set";
        $query .= " `$column`=:value";
        $query .= " WHERE id=:id";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindvalue(":value", $value);
        $stmt->bindvalue(":id", $userId);
        return $stmt->execute();
    }

    /**
     * =======================
     * || 講師関連 ||
     * =======================
     */

    // 【講師】データを更新する
    public function updateTeacher($teacherId, $teacher)
    {
        $query = "update teachers set";
        $query .= " first_name=:first_name";
        $query .= ", last_name=:last_name";
        $query .= ", nickname=:nickname";
        $query .= ", picture=:picture";
        $query .= ", email=:email";
        $query .= ", country=:country";
        $query .= ", password=:password";
        $query .= ", status=:status";
        $query .= ", shash=:shash";
        $query .= " WHERE id=:id";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindvalue(":first_name", $teacher["first_name"]);
        $stmt->bindvalue(":last_name", $teacher["last_name"]);
        $stmt->bindvalue(":nickname", $teacher["nickname"]);
        if ($this->findAllUsersByMail($teacher["email"])) return false;

        $stmt->bindvalue(":email", $teacher["email"]);
        $stmt->bindvalue(":country", $teacher["country"]);
        $stmt->bindvalue(":password", $teacher["password"]);
        $stmt->bindvalue(":status", $teacher["status"]);
        $stmt->bindvalue(":shash", $teacher["shash"]);
        $stmt->bindvalue(":id", $teacherId);
        return $stmt->execute();
    }

    // 【講師】すべてのを取得するメソッド
    public function findAllTeachers()
    {
        $pdo = $this->getPDO();
        $sql = "SELECT * FROM teachers";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * =======================
     * || 生徒関連 ||
     * =======================
     */
    // 【生徒】データを更新する
    public function updateStudent($studentId, $student)
    {
        $query = "update students set";
        $query .= " first_name=:first_name";
        $query .= ", last_name=:last_name";
        $query .= ", nickname=:nickname";
        $query .= ", picture=:picture";
        $query .= ", email=:email";
        $query .= ", country=:country";
        $query .= ", password=:password";
        $query .= ", status=:status";
        $query .= ", shash=:shash";
        $query .= " WHERE id=:id";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindvalue(":first_name", $student["first_name"]);
        $stmt->bindvalue(":last_name", $student["last_name"]);
        $stmt->bindvalue(":nickname", $student["nickname"]);
        $stmt->bindvalue(":picture", $student["picture"]);
        if ($this->findAllUsersByMail($student["email"])) return false;

        $stmt->bindvalue(":email", $student["email"]);
        $stmt->bindvalue(":country", $student["country"]);
        $stmt->bindvalue(":password", $student["password"]);
        $stmt->bindvalue(":status", $student["status"]);
        $stmt->bindvalue(":shash", $student["shash"]);
        $stmt->bindvalue(":id", $studentId);
        return $stmt->execute();
    }
}
