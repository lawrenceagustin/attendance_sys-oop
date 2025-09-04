<?php
require_once __DIR__ . '/../core/models.php';
$c = new Course();
$courses = $c->getCourses();
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Register Student</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-white">
  <div class="max-w-xl mx-auto mt-12 p-6 bg-white rounded shadow border border-red-600">
    <h2 class="text-xl font-semibold mb-4 text-red-700">Student Registration</h2>
    <form id="regForm">
      <label class="block mb-2 text-red-700">Full name</label>
      <input name="full_name" class="w-full border border-red-300 p-2 mb-3 focus:border-red-500" required>

      <label class="block mb-2 text-red-700">Email</label>
      <input name="email" type="email" class="w-full border border-red-300 p-2 mb-3 focus:border-red-500" required>

      <label class="block mb-2 text-red-700">Password</label>
      <input name="password" type="password" class="w-full border border-red-300 p-2 mb-3 focus:border-red-500" required>

      <label class="block mb-2 text-red-700">Course / Program</label>
      <select name="course_id" class="w-full border border-red-300 p-2 mb-3 focus:border-red-500" required>
        <option value="">-- Select course --</option>
        <?php foreach($courses as $course): ?>
          <option value="<?=htmlspecialchars($course['id'])?>"><?=htmlspecialchars($course['name'])?></option>
        <?php endforeach; ?>
      </select>

      <label class="block mb-2 text-red-700">Year Level</label>
      <select name="year_level" class="w-full border border-red-300 p-2 mb-3 focus:border-red-500" required>
        <option value="">-- year --</option>
        <option value="1">1</option><option value="2">2</option><option value="3">3</option><option value="4">4</option>
      </select>

      <label class="block mb-2 text-red-700">Student number (optional)</label>
      <input name="student_number" class="w-full border border-red-300 p-2 mb-3 focus:border-red-500">

      <input type="hidden" name="action" value="register_student">
      <button class="bg-red-600 hover:bg-red-700 text-white p-2 rounded w-full">Register</button>
      <button type="button" onclick="window.location.href='index.php';" class="w-full bg-red-600 hover:bg-red-700 text-white mt-2 p-2 rounded">Return</button>
    </form>

    <div id="msg" class="mt-3 text-red-600"></div>
  </div>

<script>
document.getElementById('regForm').addEventListener('submit', async function(e){
  e.preventDefault();
  const fd = new FormData(this);
  const res = await fetch('../core/handleForms.php', {method:'POST', body:fd});
  const json = await res.json();
  document.getElementById('msg').innerText = json.message;
  if (json.ok) {
    setTimeout(()=>{ window.location = 'login.php'; }, 800);
  }
});
</script>
</body>
</html>
