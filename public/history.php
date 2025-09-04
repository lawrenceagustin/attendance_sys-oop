<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
  header('Location: login.php');
  exit;
}
require_once __DIR__ . '/../core/models.php';
$stuModel = new Student();
$history = $stuModel->getAttendanceHistory($_SESSION['user_id']);
?>
<!doctype html>
<html><head><meta charset="utf-8"><title>Attendance History</title>
<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet"></head>
<body class="bg-gray-50 p-6">
  <div class="max-w-4xl mx-auto bg-white p-6 rounded shadow">
    <h2 class="text-lg font-semibold mb-4">Attendance History</h2>
    <table class="w-full text-sm">
      <thead><tr><th>Date</th><th>Course</th><th>Time In</th><th>Status</th><th>Late?</th><th>Note</th></tr></thead>
      <tbody>
      <?php if (empty($history)): ?>
        <tr><td colspan="6" class="p-4">No records yet.</td></tr>
      <?php else: foreach($history as $h): ?>
        <tr class="border-t">
          <td><?=htmlspecialchars($h['date'])?></td>
          <td><?=htmlspecialchars($h['course_name'])?></td>
          <td><?=htmlspecialchars($h['time_in'])?></td>
          <td><?=htmlspecialchars($h['status'])?></td>
          <td><?= $h['is_late'] ? '<span class="text-red-600">Yes</span>' : '<span class="text-green-600">No</span>' ?></td>
          <td><?=htmlspecialchars($h['note'])?></td>
        </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table>
    <div class="mt-4"><a href="student_dashboard.php" class="text-blue-600">Back</a></div>
  </div>
</body></html>
