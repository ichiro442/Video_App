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
    public function insertUser($first_name, $last_name, $nickname, $email, $country, $password, $table)
    {
        $stmt = $this->pdo->prepare("INSERT INTO `$table`(first_name,last_name,nickname,email,country,password,shash) VALUE (:first_name,:last_name,:nickname,:email,:country,:password,:shash)");
        $stmt->bindvalue(":first_name", $first_name);
        $stmt->bindvalue(":last_name", $last_name);
        $stmt->bindvalue(":nickname", $nickname);
        if ($this->findAllUsersByMail($email)) return false;

        $stmt->bindvalue(":email", $email);
        $stmt->bindvalue(":country", $country);
        $phash = password_hash($password, PASSWORD_DEFAULT);
        $stmt->bindvalue(":password", $phash);
        $shash = substr(md5($last_name . $email), 0, 10);
        $stmt->bindvalue(":shash", $shash);
        $stmt->execute();
        return $shash;
    }

    // 【共通】一つのデータを更新
    public function updateOneColumn($userId, $value, $column, $table)
    {
        $query = "update `$table` set";
        $query .= " `$column`=:value";
        $query .= " WHERE id=:id";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindvalue(":value", $value);
        $stmt->bindvalue(":id", $userId);
        return $stmt->execute();
    }
    // 【共通】【動的SQL】SELECT * FROM houses WHERE (AAA = :AAA OR BBB = :BBB) AND CCC = :CCC;
    // ABCをすべて満たすユーザーを取得する
    // WHERE句を生成する
    private function buildSearchWhereQuery($words)
    {
        $sql = "";
        $andcount = 0;
        foreach ($words as $key => $value) {
            // 配列の場合はこの（）の中を生成する
            if (!empty($value) && is_array($value)) {

                if ($andcount > 0) $sql .= " AND";

                $sql .= " (";

                $orcount = 0;
                foreach ($value as $key2 => $value2) {

                    if ($value2 == "") continue;

                    if ($orcount > 0) $sql .= " OR";

                    $sql .= " $key LIKE :$key$key2";
                    $orcount++;
                }

                // ORがない場合は1=1を末尾に追記する(WEHRE 1＝1にする)
                if ($orcount == 0) $sql .= "1=1";

                $sql .= ")";
                $andcount++;
            } elseif (isset($value) && $value !== "") { // 配列じゃない場合
                //指定なしの場合はスキップする
                if ($value == "") continue;

                //最初のターン以外はANDを末尾に追記する
                if ($andcount > 0) $sql .= " AND";

                $sql .= " $key LIKE :$key";
                $andcount++;
            }
        }
        return $sql;
    }
    // 【共通】【動的SQL】検索条件をSQLクエリにバインドする
    private function bindSearchParams($words, $sql)
    {
        $stmt = $this->pdo->prepare($sql);
        if ($this->checkSearchWords($words)) {
            //渡されたを連想配列で取り出す
            foreach ($words as $key => $value) {

                //値が「空でない・配列」だった場合の処理
                if (!empty($value) && is_array($value)) {
                    foreach ($value as $key2 => $value2) {
                        if ($value2 == "") continue;
                        $stmt->bindValue(":$key$key2", "%" . $value2 . "%", PDO::PARAM_STR);
                        //デバック用
                        // $sql = str_replace(":$key$key2","%".$value2."%",$sql);
                    }
                } elseif (isset($value) && $value !== "") {
                    // if ($value == "指定なし") continue;
                    if ($value == "") continue;
                    $stmt->bindValue(":$key",  "%" . $value . "%", PDO::PARAM_STR);
                    //デバック用
                    // $sql = str_replace(":$key", $value, $sql);
                }
            }
        }
        return $stmt;
    }
    // 【共通】【動的SQL】与えられたデータが有効な検索条件を持っているかを確認する
    public function checkSearchWords($words)
    {
        if (empty($words) && $words !== 0) return false;
        foreach ($words as $key => $value) {
            if (isset($value) && $value !== "") {
                return true;
            }
        }
        return false;
    }
    // 【共通】【動的SQL】渡されたデータを使ってユーザーの検索をするメソッド
    public function searchUsers($words, $user_category)
    {
        $table = $user_category == 0 ? "students" : "teachers";
        $sql = "SELECT * FROM $table";
        $sql .= " WHERE";
        if ($this->checkSearchWords($words)) {

            $sql .= $this->buildSearchWhereQuery($words);
        } else {
            $sql .= " 1=1";
        }
        $stmt = $this->bindSearchParams($words, $sql);
        $stmt->execute();
        return $stmt->fetchAll();
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
        // $query .= ", picture=:picture";
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
        // $stmt->bindvalue(":picture", $teacher["picture"]);
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
        // $query .= ", picture=:picture";
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
        // $stmt->bindvalue(":picture", $student["picture"]);
        $stmt->bindvalue(":email", $student["email"]);
        $stmt->bindvalue(":country", $student["country"]);
        $stmt->bindvalue(":password", $student["password"]);
        $stmt->bindvalue(":status", $student["status"]);
        $stmt->bindvalue(":shash", $student["shash"]);
        $stmt->bindvalue(":id", $studentId);
        return $stmt->execute();
    }
}
