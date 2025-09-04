<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
  header('Location: login.php');
  exit;
}
?>
<!doctype html>
<html><head>
<meta charset="utf-8"><title>Add Course</title>
<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head><body class="bg-white p-6">
  <div class="max-w-lg mx-auto bg-white p-6 rounded shadow border border-red-600">
    <h2 class="text-lg font-semibold mb-4 text-red-700">Add Course / Program</h2>
    <form id="courseForm">
      <label class="text-red-700">Name</label>
      <input name="name" class="w-full border border-red-300 p-2 mb-3 focus:border-red-500" required>
      <label class="text-red-700">Code</label>
      <input name="code" class="w-full border border-red-300 p-2 mb-3 focus:border-red-500">
      <label class="text-red-700">Start Time (HH:MM)</label>
      <input name="start_time" type="time" class="w-full border border-red-300 p-2 mb-3 focus:border-red-500" value="08:00">
      <label class="text-red-700">Description</label>
      <textarea name="description" class="w-full border border-red-300 p-2 mb-3 focus:border-red-500"></textarea>
      <input type="hidden" name="action" value="add_course">
      <button class="bg-red-600 hover:bg-red-700 text-white p-2 rounded w-full">Add Course</button>
      
    </form>
    <div id="msg" class="mt-3 text-red-600"></div>
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
