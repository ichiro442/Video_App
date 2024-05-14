<?php
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
    /**
     * =======================
     * || 共通メソッド 検索 ||
     * =======================
     */

    // 【共通】【検索】メールアドレスでユーザーを検索する
    public function findByMail($email, $uri)
    {
        $table = preg_match('/Teacher/', $uri) ? "teachers" : "students";
        $stmt = $this->pdo->prepare("SELECT * FROM `$table` WHERE email=:email");
        $stmt->bindValue(":email", $email);
        $stmt->execute();
        $user = $stmt->fetch();
        return $user;
    }
    // 【共通】【検索】講師IDで一週間のレッスン予約を検索する
    public function findLessonByTeacherID($teacher_id)
    {
        // 今日の日付を取得
        $today = date('Y-m-d');
        // 1週間後の日付を計算
        // $end_date = date('Y-m-d', strtotime('+1 week'));

        $stmt = $this->pdo->prepare("SELECT * FROM lessons WHERE teacher_id = :teacher_id AND start_time >= :today");
        // $stmt = $this->pdo->prepare("SELECT * FROM lessons WHERE teacher_id = :teacher_id AND start_time BETWEEN :today AND :end_date");
        $stmt->bindValue(':teacher_id', $teacher_id);
        $stmt->bindValue(':today', $today);
        // $stmt->bindValue(':end_date', $end_date);
        $stmt->execute();

        // 検索結果を取得
        $lessons = $stmt->fetchAll();
        return $lessons;
    }

    // 【共通】【検索】何か１つのデータでユーザーを検索する
    public function findByOneColumn($column, $data, $uri)
    {
        if ($column = "id") $data = intval($data);
        $table = preg_match('/Teacher/', $uri) ? "teachers" : "students";
        $stmt = $this->pdo->prepare("SELECT * FROM `$table` WHERE $column=:$column");
        $stmt->bindValue(":$column", $data);
        $stmt->execute();
        $user = $stmt->fetch();
        return $user;
    }

    // 【共通】【検索】メールアドレスですべてのユーザーを検索する
    public function findAllUsersByMail($email)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM teachers WHERE email=:email UNION SELECT * FROM students WHERE email=:email");
        $stmt->bindValue(":email", $email);
        $stmt->execute();
        $user = $stmt->fetch();
        return $user;
    }

    // 【共通】【検索】生徒or講師idでレッスンを取得する
    public function findLessonByID($id, $uri)
    {
        $serch_id = preg_match('/Teacher/', $uri) ? "teacher_id" : "student_id";

        // 今日の日付を取得
        $today = date('Y-m-d');

        $stmt = $this->pdo->prepare("SELECT * FROM lessons WHERE $serch_id = :serch_id AND start_time >= :today AND finished_flg =0 ORDER BY start_time ASC");
        $stmt->bindValue(':serch_id', $id);
        $stmt->bindValue(':today', $today);
        $stmt->execute();

        // 検索結果を取得
        $lessons = $stmt->fetchAll();
        return $lessons;
    }

    // 【共通】【検索】ハッシュでレッスン情報を取得する
    public function findLessonByHash($hash)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM lessons WHERE hash = :hash");
        $stmt->bindValue(':hash', $hash);
        $stmt->execute();

        // 検索結果を取得
        $lessons = $stmt->fetchAll();
        return $lessons;
    }
    /**
     * =======================
     * || 共通メソッド 登録 ||
     * =======================
     */

    // 【共通】【登録】ユーザーを仮登録する
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
    // 【共通】【登録】生徒→講師or講師→生徒の評価を登録する
    public function insertRating($lesson_id, $student_id, $teacher_id, $rating_target, $rating_value, $comment)
    {
        $stmt = $this->pdo->prepare("INSERT INTO ratings (lesson_id,student_id,teacher_id,rating_target,rating_value,comment) VALUE (:lesson_id,:student_id,:teacher_id,:rating_target,:rating_value,:comment)");
        $stmt->bindvalue(":lesson_id", $lesson_id, PDO::PARAM_INT);
        $stmt->bindvalue(":student_id", $student_id, PDO::PARAM_INT);
        $stmt->bindvalue(":teacher_id", $teacher_id, PDO::PARAM_INT);
        $stmt->bindvalue(":rating_target", $rating_target, PDO::PARAM_STR);
        $stmt->bindvalue(":rating_value", $rating_value, PDO::PARAM_INT);
        $stmt->bindvalue(":comment", $comment, PDO::PARAM_STR);
        return $stmt->execute();
    }
    /**
     * =======================
     * || 共通メソッド 更新 ||
     * =======================
     */

    // 【共通】【更新】講師か生徒の一つのデータを更新
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

    // 【共通】【更新】レッスンの一つのデータを更新
    public function updateLesson($hash, $column, $value)
    {
        $query = "update lessons set";
        $query .= " `$column`=:value";
        $query .= " WHERE hash=:hash";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindvalue(":value", $value);
        $stmt->bindvalue(":hash", $hash);
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

    // 現在日時よりも未来の講師スケジュールだけを取得する（日本時間）
    public function findTeacherScheduleByID($id)
    {
        date_default_timezone_set('Asia/Tokyo');
        $current_date_time = date('Y-m-d H:i');

        $pdo = $this->getPDO();
        $sql = "SELECT CASE available WHEN 1 THEN '○' ELSE '×' END as title";
        $sql .= " ,start_time as start,(start_time + INTERVAL 30 MINUTE) as end ";
        $sql .= "from teacher_schedules where teacher_id = :id and start_time >= :current_date_time";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->bindValue(":current_date_time", $current_date_time);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // 今日以降の講師のスケジュールを取得する（フィリピン時間）
    public function findTeacherSchedulePhilippinesTimeByID($id)
    {
        $pdo = $this->getPDO();
        $sql = "SELECT CASE available WHEN 1 THEN '○' ELSE '×' END as title";
        //フィリピンの時間に修正
        $sql .= " ,(start_time - INTERVAL 60 MINUTE) as start,(start_time - INTERVAL 30 MINUTE) as end ";
        $sql .= "from teacher_schedules where teacher_id = :id and (start_time - INTERVAL 60 MINUTE) >= DATE_FORMAT(CURRENT_DATE,'%Y-%m-%d')";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // 講師のスケジュールとレッスン予約を照合して、合致したら◯→✕にする
    public function addBookedLesson($id, $calendar, $booked_lesson)
    {
        // 合致したスケジュールのインデックスを保持する配列
        $matchedIndexes = [];

        // 講師のスケジュールとレッスン予約を照合
        foreach ($calendar as $calendarIndex => $calendarEvent) {
            foreach ($booked_lesson as $lessonIndex => $lesson) {
                // スケジュールの日時を日本時間から1時間引いてフィリピン時間に変換
                $lesson['start_time'] = date('Y-m-d H:i:s', strtotime($lesson['start_time'] . ' -1 hour'));
                // スケジュールとレッスンのstart_timeを比較
                if ($calendarEvent['start'] === $lesson['start_time']) {
                    // 合致した場合、スケジュールのタイトルを✕に変更する
                    $calendar[$calendarIndex]['title'] = 'Booked';
                    // 合致したスケジュールのインデックスを記録
                    $matchedIndexes[] = $calendarIndex;
                    // 一度合致したらループを抜ける
                    break;
                }
            }
        }

        // カレンダーの中で合致したスケジュール以外を◯にする
        foreach ($calendar as $index => $event) {
            if (!in_array($index, $matchedIndexes)) {
                $calendar[$index]['title'] = '◯';
            }
        }

        // 修正されたカレンダーを返す
        return $calendar;
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

    // 【生徒】レッスンを登録する
    public function insertLesson($student_id, $teacher_id, $start_time, $hash)
    {
        $stmt = $this->pdo->prepare("INSERT INTO lessons (student_id,teacher_id,start_time,hash) VALUE (:student_id,:teacher_id,:start_time,:hash)");
        $stmt->bindvalue(":student_id", $student_id);
        $stmt->bindvalue(":teacher_id", $teacher_id);
        $stmt->bindvalue(":start_time", $start_time);
        $stmt->bindvalue(":hash", $hash);
        return $stmt->execute();;
    }
}
