<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
  header('Location: login.php');
  exit;
}
require_once __DIR__ . '/../core/models.php';
$adm = new Admin();
$id = intval($_GET['id'] ?? 0);
$course = $adm->getCourseById($id);
if (!$course) { die("Course not found"); }

$timeValue = $course['start_time'] ?? '08:00:00';
if (strlen($timeValue) === 8) $timeValue = substr($timeValue,0,5);
?>
<!doctype html>
<html><head>
<meta charset="utf-8"><title>Edit Course</title>
<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head><body class="bg-gray-50 p-6">
  <div class="max-w-lg mx-auto bg-white p-6 rounded shadow">
    <h2 class="text-lg font-semibold mb-4">Edit Course / Program</h2>
    <form id="courseForm">
      <label class="block mb-1">Name</label>
      <input name="name" class="w-full border p-2 mb-3" required value="<?=htmlspecialchars($course['name'])?>">
      <label class="block mb-1">Code</label>
      <input name="code" class="w-full border p-2 mb-3" value="<?=htmlspecialchars($course['code'])?>">
      <label class="block mb-1">Start Time (HH:MM)</label>
      <input name="start_time" type="time" class="w-full border p-2 mb-3" value="<?=htmlspecialchars($timeValue)?>">
      <label class="block mb-1">Description</label>
      <textarea name="description" class="w-full border p-2 mb-3"><?=htmlspecialchars($course['description'] ?? '')?></textarea>

      <input type="hidden" name="action" value="edit_course">
      <input type="hidden" name="id" value="<?=$course['id']?>">
      <div class="flex gap-2">
        <button class="bg-yellow-600 text-white px-4 py-2 rounded">Update Course</button>
        <a href="admin_dashboard.php" class="px-4 py-2 rounded border">Cancel</a>
      </div>
    </form>
    <div id="msg" class="mt-3 text-green-600"></div>
  </div>

<script>
document.getElementById('courseForm').addEventListener('submit', async function(e){
  e.preventDefault();
  const fd = new FormData(this);
  const res = await fetch('../core/handleForms.php', {method:'POST', body:fd});
  const json = await res.json();
  document.getElementById('msg').innerText = json.message;
  if (json.ok) setTimeout(()=>{ window.location='admin_dashboard.php' }, 700);
});
</script>
</body></html>
