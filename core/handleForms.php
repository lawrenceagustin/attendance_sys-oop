<?php
session_start();
require_once __DIR__ . '/models.php';

function respond($ok, $msg = '', $extra = []) {
    header('Content-Type: application/json');
    echo json_encode(array_merge(['ok'=>$ok,'message'=>$msg], $extra));
    exit;
}

$action = $_POST['action'] ?? null;
if (!$action) respond(false, 'No action specified.');

switch ($action) {
    case 'register_student': {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $full_name = trim($_POST['full_name'] ?? '');
        $course_id = intval($_POST['course_id'] ?? 0);
        $year_level = intval($_POST['year_level'] ?? 0);
        $student_number = trim($_POST['student_number'] ?? '') ?: null;

        if (!$email || !$password || !$full_name || !$course_id || !$year_level) {
            respond(false, 'Please fill all required fields.');
        }

        $u = new User();
        $exists = $u->selectOne("SELECT id FROM users WHERE email=:email", [':email'=>$email]);
        if ($exists) respond(false, 'Email already registered.');

        $user = new User(['email'=>$email,'password'=>'','role'=>'student','full_name'=>$full_name]);
        $user->setPassword($password);
        $userId = $user->save();

        $student = new Student();
        $student->registerProfile($userId, $course_id, $year_level, $student_number);

        respond(true, 'Student registered successfully.');
        break;
    }

    case 'register_admin': {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $full_name = trim($_POST['full_name'] ?? '');
        if (!$email || !$password || !$full_name) respond(false, 'Please fill all fields.');

        $u = new User();
        $exists = $u->selectOne("SELECT id FROM users WHERE email=:email", [':email'=>$email]);
        if ($exists) respond(false, 'Email already registered.');

        $user = new User(['email'=>$email,'password'=>'','role'=>'admin','full_name'=>$full_name]);
        $user->setPassword($password);
        $user->save();
        respond(true, 'Admin registered.');
        break;
    }

    case 'login': {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $u = new User();
        $auth = $u->authenticate($email, $password);
        if (!$auth) respond(false, 'Invalid credentials.');

        $_SESSION['user_id'] = $auth->getId();
        $_SESSION['role'] = $auth->getRole();
        $_SESSION['full_name'] = $auth->getFullName();
        respond(true, 'Logged in.', ['role'=>$_SESSION['role']]);
        break;
    }

    case 'add_course': {
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') respond(false, 'Unauthorized.');
        $name = trim($_POST['name'] ?? '');
        $code = trim($_POST['code'] ?? '') ?: null;
        $desc = trim($_POST['description'] ?? '') ?: null;
        $start_time = ($_POST['start_time'] ?? '08:00');
        if (strlen($start_time) === 5) $start_time .= ':00';

        if (!$name) respond(false, 'Course name is required.');

        $adm = new Admin();
        $adm->addCourse($name, $code, $desc, $start_time);
        respond(true, 'Course added.');
        break;
    }

    case 'edit_course': {
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') respond(false, 'Unauthorized.');
        $id = intval($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $code = trim($_POST['code'] ?? '') ?: null;
        $desc = trim($_POST['description'] ?? '') ?: null;
        $start_time = ($_POST['start_time'] ?? '08:00');
        if (strlen($start_time) === 5) $start_time .= ':00';

        if (!$id || !$name) respond(false, 'Missing course id or name.');

        $adm = new Admin();
        $ok = $adm->updateCourse($id, $name, $code, $desc, $start_time);
        respond($ok, $ok ? 'Course updated!' : 'Error updating course');
        break;
    }

    case 'delete_course': {
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') respond(false, 'Unauthorized.');
        $id = intval($_POST['id'] ?? 0);
        if (!$id) respond(false, 'Missing course id.');

        $adm = new Admin();
        $result = $adm->deleteCourse($id);
        respond($result['ok'], $result['message']);
        break;
    }

    case 'file_attendance': {
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') respond(false, 'Unauthorized.');

        $student_id = $_SESSION['user_id'];
        $course_id = intval($_POST['course_id'] ?? 0);
        $year_level = intval($_POST['year_level'] ?? 0);
        $date = $_POST['date'] ?? date('Y-m-d');
        $time_in = $_POST['time_in'] ?? date('H:i:s');
        $note = trim($_POST['note'] ?? '') ?: null;

        if (!$course_id || !$year_level) respond(false, 'Course and Year Level are required.');

        $att = new AttendanceModel();
        $ok = $att->fileAttendance($student_id, $course_id, $year_level, $date, $time_in, 'present', $note);
        respond($ok, $ok ? 'Attendance filed.' : 'Attendance filing failed (maybe already filed).');
        break;
    }

    // Student submits an excuse letter
    case 'submit_excuse_letter': {
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
            respond(false, 'Unauthorized.');
        }

        $student_id = $_SESSION['user_id']; // users.id
        $course_id  = intval($_POST['course_id'] ?? 0);
        $reason     = trim($_POST['reason'] ?? '');
        $attachment = trim($_POST['attachment'] ?? null);

        if (!$course_id || !$reason) respond(false, 'Course and reason are required.');

        $model = new ExcuseLetterModel();
        $id = $model->submit($student_id, $course_id, $reason, $attachment);

        respond(true, 'Excuse letter submitted.', ['id'=>$id]);
        break;
    }

    // Student views their own excuse letters
    case 'view_my_excuse_letters': {
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
            respond(false, 'Unauthorized.');
        }

        $student_id = $_SESSION['user_id']; // users.id
        $model = new ExcuseLetterModel();
        $rows = $model->getByStudent($student_id);

        respond(true, 'Fetched successfully.', ['letters'=>$rows]);
        break;
    }
    // Admin views all excuse letters (filterable by course/program)
    case 'view_all_excuse_letters': {
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            respond(false, 'Unauthorized.');
        }

        $course_id = intval($_POST['course_id'] ?? 0);
        $model = new ExcuseLetterModel();
        $rows = $model->getAll($course_id ?: null);

        respond(true, 'Fetched successfully.', ['letters'=>$rows]);
        break;
    }
    // Admin approves or rejects an excuse letter
    case 'update_excuse_status': {
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            respond(false, 'Unauthorized.');
        }

        $id     = intval($_POST['id'] ?? 0);
        $status = $_POST['status'] ?? '';
        if (!$id || !in_array($status, ['approved','rejected'])) {
            respond(false, 'Invalid request.');
        }

        $model = new ExcuseLetterModel();
        $ok = $model->updateStatus($id, $status, $_SESSION['user_id']);

        respond($ok, $ok ? "Excuse letter $status." : 'Update failed.');
        break;
    }

    default:
        respond(false, 'Unknown action.');
}
