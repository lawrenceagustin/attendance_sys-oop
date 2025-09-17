<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
  header('Location: login.php');
  exit;
}
require_once __DIR__ . '/../core/models.php';
$stu = new Student();
$profile = $stu->loadProfileByUserId($_SESSION['user_id']);
$courses = (new Course())->getById($profile['course_id']);
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8"><title>Student Dashboard</title>
<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>
<script>
  var notyf = new Notyf({duration:3000, position:{x:'right',y:'top'}});
</script>
</head>
<body class="bg-white p-6">
  <div class="max-w-3xl mx-auto">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-red-700">Student Dashboard</h1>
      <div>
        <span class="mr-4 text-red-600"><?=htmlspecialchars($_SESSION['full_name'])?></span>
        <a href="logout.php" class="text-red-600 font-semibold">Logout</a>
      </div>
    </div>

    <div class="bg-red-50 p-4 rounded shadow">
      <h2 class="font-semibold text-red-700">Attendance</h2>
      <p class="text-sm mb-3 text-red-600">Program: <?=htmlspecialchars($profile['course_name'])?> • Year <?=intval($profile['year_level'])?></p>
      <a class="bg-red-600 text-white px-3 py-1 rounded" href="attendance.php">File Attendance</a>
      <a class="bg-white text-red-600 border border-red-600 px-3 py-1 rounded ml-2" href="history.php">View History</a>
      <a class="bg-yellow-500 text-white px-3 py-1 rounded ml-2" href="excuse_letter.php">Excuse Letters</a>
    </div>
  </div>
</body>
</html>
