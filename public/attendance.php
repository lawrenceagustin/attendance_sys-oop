<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
  header('Location: login.php');
  exit;
}
require_once __DIR__ . '/../core/models.php';
$stuModel = new Student();
$profile = $stuModel->loadProfileByUserId($_SESSION['user_id']);
$courses = (new Admin())->getCourses();
?>
<!doctype html>
<html><head><meta charset="utf-8"><title>File Attendance</title>
<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet"></head>
<body class="bg-gray-50 p-6">
  <div class="max-w-lg mx-auto bg-white p-6 rounded shadow">
    <h2 class="text-lg font-semibold mb-4">File Attendance</h2>
    <form id="attForm">
      <label>Date</label>
      <input name="date" type="date" value="<?=date('Y-m-d')?>" class="w-full border p-2 mb-3">
      <label>Time In</label>
      <input name="time_in" type="time" value="<?=date('H:i')?>" class="w-full border p-2 mb-3">
      <label>Course (Program)</label>
      <select name="course_id" class="w-full border p-2 mb-3" required>
        <option value="<?=intval($profile['course_id'])?>"><?=htmlspecialchars($profile['course_name'])?></option>
      </select>
      <label>Year Level</label>
      <input name="year_level" class="w-full border p-2 mb-3" value="<?=intval($profile['year_level'])?>" readonly>
      <label>Note (optional)</label>
      <input name="note" class="w-full border p-2 mb-3">
      <input type="hidden" name="action" value="file_attendance">
      <button class="bg-green-600 text-white p-2 rounded">File Attendance</button>
    </form>
    <div id="msg" class="mt-3 text-red-600"></div>
  </div>

<script>
document.getElementById('attForm').addEventListener('submit', async function(e){
  e.preventDefault();
  const fd = new FormData(this);
  const res = await fetch('../core/handleForms.php', {method:'POST', body:fd});
  const json = await res.json();
  const msgEl = document.getElementById('msg');
  msgEl.innerText = json.message;
  if (json.ok) msgEl.className = 'mt-3 text-green-600';
  else msgEl.className = 'mt-3 text-red-600';
});
</script>
</body></html>
