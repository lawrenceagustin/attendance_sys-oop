<?php
require_once __DIR__ . '/db_connection.php';

class BaseModel {
    protected $db;
    protected $pdo;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->pdo = $this->db->pdo();
    }

    public function select($sql, $params = []) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function selectOne($sql, $params = []) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }

    public function execute($sql, $params = []) {
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    public function lastInsertId() {
        return $this->pdo->lastInsertId();
    }
}

class User extends BaseModel {
    private $id;
    private $email;
    private $password;
    private $role;
    private $full_name;

    public function __construct($data = null) {
        parent::__construct();
        if ($data) {
            $this->id = $data['id'] ?? null;
            $this->email = $data['email'] ?? null;
            $this->password = $data['password'] ?? null;
            $this->role = $data['role'] ?? null;
            $this->full_name = $data['full_name'] ?? null;
        }
    }

    public function getId() { return $this->id; }
    public function getEmail() { return $this->email; }
    public function getFullName() { return $this->full_name; }
    public function getRole() { return $this->role; }

    public function setPassword($plainPassword) {
        $this->password = password_hash($plainPassword, PASSWORD_DEFAULT);
    }

    public function save() {
        $sql = "INSERT INTO users (email,password,role,full_name) VALUES (:email,:password,:role,:full_name)";
        $this->execute($sql, [
            ':email' => $this->email,
            ':password' => $this->password,
            ':role' => $this->role,
            ':full_name' => $this->full_name
        ]);
        $this->id = $this->lastInsertId();
        return $this->id;
    }

    public function authenticate($email, $plainPassword) {
        $row = $this->selectOne("SELECT * FROM users WHERE email = :email", [':email' => $email]);
        if ($row && password_verify($plainPassword, $row['password'])) {
            return new User($row);
        }
        return null;
    }

    public function findById($id) {
        $row = $this->selectOne("SELECT * FROM users WHERE id = :id", [':id' => $id]);
        return $row ? new User($row) : null;
    }
}

class Student extends User {
    private $course_id;
    private $year_level;
    private $student_number;

    public function __construct($userData = null, $profileData = null) {
        parent::__construct($userData);
        if ($profileData) {
            $this->course_id = $profileData['course_id'] ?? null;
            $this->year_level = $profileData['year_level'] ?? null;
            $this->student_number = $profileData['student_number'] ?? null;
        }
    }

    public function registerProfile($user_id, $course_id, $year_level, $student_number = null) {
        $this->execute("INSERT INTO student_profiles (user_id, course_id, year_level, student_number) VALUES (:uid,:cid,:y,:sn)", [
            ':uid'=>$user_id, ':cid'=>$course_id, ':y'=>$year_level, ':sn'=>$student_number
        ]);
        return $this->lastInsertId();
    }

    public function loadProfileByUserId($user_id) {
        return $this->selectOne("SELECT sp.*, c.name as course_name FROM student_profiles sp JOIN courses c ON sp.course_id=c.id WHERE sp.user_id = :uid", [':uid'=>$user_id]);
    }

    public function getAttendanceHistory($student_id) {
        $sql = "SELECT a.*, c.name as course_name FROM attendance a JOIN courses c ON a.course_id=c.id WHERE a.student_id = :sid ORDER BY a.date DESC";
        return $this->select($sql, [':sid'=>$student_id]);
    }
}

class Admin extends User {
    public function __construct($data = null) {
        parent::__construct($data);
    }

    public function addCourse($name, $code = null, $description = null, $start_time = '08:00:00') {
        $sql = "INSERT INTO courses (name,code,description,start_time) VALUES (:name,:code,:desc,:st)";
        $this->execute($sql, [':name'=>$name, ':code'=>$code, ':desc'=>$description, ':st'=>$start_time]);
        return $this->lastInsertId();
    }

    public function getCourses() {
        return $this->select("SELECT * FROM courses ORDER BY name");
    }

    public function getCourseById($id) {
        return $this->selectOne("SELECT * FROM courses WHERE id=:id", [':id'=>$id]);
    }

    public function updateCourse($id, $name, $code, $description, $start_time) {
        $sql = "UPDATE courses 
                   SET name=:name, code=:code, description=:desc, start_time=:st 
                 WHERE id=:id";
        return $this->execute($sql, [
            ':name'=>$name, ':code'=>$code, ':desc'=>$description, ':st'=>$start_time, ':id'=>$id
        ]);
    }

    public function getAttendanceByCourseYear($course_id, $year_level, $date_from = null, $date_to = null) {
        $sql = "SELECT a.*, u.full_name, u.email 
                  FROM attendance a 
                  JOIN users u ON a.student_id=u.id 
                 WHERE a.course_id = :cid AND a.year_level = :y";
        $params = [':cid'=>$course_id, ':y'=>$year_level];
        if ($date_from) { $sql .= " AND a.date >= :df"; $params[':df'] = $date_from; }
        if ($date_to)   { $sql .= " AND a.date <= :dt"; $params[':dt'] = $date_to; }
        $sql .= " ORDER BY a.date DESC, u.full_name ASC";
        return $this->select($sql, $params);
    }

    public function deleteCourse($id) {
        $sp = $this->selectOne("SELECT COUNT(*) AS cnt FROM student_profiles WHERE course_id=:id", [':id'=>$id]);
        $enrolled = intval($sp['cnt'] ?? 0);

        $att = $this->selectOne("SELECT COUNT(*) AS cnt FROM attendance WHERE course_id=:id", [':id'=>$id]);
        $logs = intval($att['cnt'] ?? 0);

        if ($enrolled > 0 || $logs > 0) {
            $parts = [];
            if ($enrolled > 0) $parts[] = "$enrolled student(s) enrolled";
            if ($logs > 0) $parts[] = "$logs attendance record(s)";
            return ['ok'=>false, 'message'=>"Cannot delete: " . implode(' and ', $parts) . " exist."];
        }

        $this->execute("DELETE FROM courses WHERE id=:id", [':id'=>$id]);
        return ['ok'=>true, 'message'=>"Course deleted successfully!"];
    }
}

class Course extends BaseModel {
    public function getById($id) {
        return $this->selectOne("SELECT * FROM courses WHERE id=:id", [':id'=>$id]);
    }

    public function getCourses() {
        return $this->select("SELECT * FROM courses ORDER BY name");
    }
}

class AttendanceModel extends BaseModel {
    public function fileAttendance($student_id, $course_id, $year_level, $date, $time_in, $status = 'present', $note = null) {
        $course = $this->selectOne("SELECT start_time FROM courses WHERE id = :id", [':id'=>$course_id]);
        $start_time = $course['start_time'] ?? '08:00:00';
        $is_late = (strtotime($time_in) > strtotime($start_time)) ? 1 : 0;

        try {
            $sql = "INSERT INTO attendance (student_id, course_id, year_level, date, time_in, status, is_late, note) 
                    VALUES (:sid,:cid,:y,:date,:time_in,:status,:is_late,:note)";
            $this->execute($sql, [
                ':sid'=>$student_id, ':cid'=>$course_id, ':y'=>$year_level, ':date'=>$date,
                ':time_in'=>$time_in, ':status'=>$status, ':is_late'=>$is_late, ':note'=>$note
            ]);
            return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function markAttendanceManual($student_id, $course_id, $year_level, $date, $time_in, $status, $is_late, $note = null) {
        $sql = "INSERT INTO attendance (student_id, course_id, year_level, date, time_in, status, is_late, note)
                VALUES (:sid,:cid,:y,:date,:time_in,:status,:is_late,:note)";
        return $this->execute($sql, [
            ':sid'=>$student_id, ':cid'=>$course_id, ':y'=>$year_level, ':date'=>$date,
            ':time_in'=>$time_in, ':status'=>$status, ':is_late'=>$is_late, ':note'=>$note
        ]);
    }
}
