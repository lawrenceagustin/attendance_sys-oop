<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
  header('Location: login.php');
  exit;
}
require_once __DIR__ . '/../core/models.php';
$admin = new Admin();

$course_id = isset($_GET['course_id']) ? intval($_GET['course_id']) : null;
$courses = $admin->getCourses();
$records = [];
$selected_course = null;

if ($course_id) {
  $selected_course = (new Course())->getById($course_id);
  $year = isset($_GET['year']) ? intval($_GET['year']) : 1;
  $records = $admin->getAttendanceByCourseYear($course_id, $year);
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8"><title>View Attendance</title>
<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet"></head>
<body class="bg-red-50 p-6">
  <div class="max-w-6xl mx-auto bg-white p-6 rounded shadow border border-red-200">
    <h2 class="text-lg font-semibold mb-4 text-red-700">View Attendance</h2>
    <form method="get" class="mb-4 flex space-x-2">
      <select name="course_id" class="border border-red-300 p-2 rounded focus:ring-red-500 focus:border-red-500">
        <option value="">-- select course --</option>
        <?php foreach($courses as $c): ?>
          <option value="<?=intval($c['id'])?>" <?=($course_id==intval($c['id'])?'selected':'')?>><?=htmlspecialchars($c['name'])?></option>
        <?php endforeach; ?>
      </select>
      <select name="year" class="border border-red-300 p-2 rounded focus:ring-red-500 focus:border-red-500">
        <option value="1" <?= (isset($_GET['year']) && $_GET['year']==1)?'selected':'' ?>>1</option>
        <option value="2" <?= (isset($_GET['year']) && $_GET['year']==2)?'selected':'' ?>>2</option>
        <option value="3" <?= (isset($_GET['year']) && $_GET['year']==3)?'selected':'' ?>>3</option>
        <option value="4" <?= (isset($_GET['year']) && $_GET['year']==4)?'selected':'' ?>>4</option>
      </select>
      <button class="bg-red-600 text-white px-3 rounded hover:bg-red-700">Show</button>
    </form>

    <?php if ($selected_course): ?>
      <h3 class="font-semibold mb-2 text-red-700">Course: <?=htmlspecialchars($selected_course['name'])?></h3>
      <table class="w-full text-sm border border-red-200">
        <thead>
          <tr class="bg-red-100 text-red-700">
            <th class="p-2 border border-red-200">Date</th>
            <th class="p-2 border border-red-200">Student</th>
            <th class="p-2 border border-red-200">Email</th>
            <th class="p-2 border border-red-200">Time In</th>
            <th class="p-2 border border-red-200">Late?</th>
            <th class="p-2 border border-red-200">Note</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($records)): ?>
            <tr><td colspan="6" class="p-4 text-center text-red-500">No records.</td></tr>
          <?php else: foreach($records as $r): ?>
            <tr class="border-t border-red-200 hover:bg-red-50">
              <td class="p-2 border border-red-100"><?=htmlspecialchars($r['date'])?></td>
              <td class="p-2 border border-red-100"><?=htmlspecialchars($r['full_name'])?></td>
              <td class="p-2 border border-red-100"><?=htmlspecialchars($r['email'])?></td>
              <td class="p-2 border border-red-100"><?=htmlspecialchars($r['time_in'])?></td>
              <td class="p-2 border border-red-100"><?= $r['is_late'] ? '<span class="text-red-600 font-bold">Yes</span>' : '<span class="text-green-600 font-bold">No</span>' ?></td>
              <td class="p-2 border border-red-100"><?=htmlspecialchars($r['note'])?></td>
            </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    <?php endif; ?>

    <div class="mt-4"><a href="admin_dashboard.php" class="text-red-600 hover:underline">Back</a></div>
  </div>
</body>
</html>
