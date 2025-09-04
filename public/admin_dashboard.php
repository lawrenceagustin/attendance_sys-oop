<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
  header('Location: login.php');
  exit;
}
require_once __DIR__ . '/../core/models.php';
$adm = new Admin();
$courses = $adm->getCourses();
?>
<!doctype html>
<html><head>
<meta charset="utf-8"><title>Admin - Dashboard</title>
<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head><body class="bg-red-50 p-6">
  <div class="max-w-5xl mx-auto">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-red-700">Admin Dashboard</h1>
      <div>
        <span class="mr-4 text-red-700">Hello, <?=htmlspecialchars($_SESSION['full_name'])?></span>
        <a href="logout.php" class="text-red-600 font-semibold">Logout</a>
      </div>
    </div>

    <div class="mb-6 bg-white p-4 rounded shadow border border-red-200">
      <h2 class="font-semibold mb-2 text-red-700">Courses / Programs</h2>
      <a href="add_course.php" class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700">Add Course</a>
      <table class="w-full mt-3 text-sm">
        <thead>
          <tr class="bg-red-100 text-red-700">
            <th>Name</th>
            <th>Code</th>
            <th>Start Time</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($courses as $c): ?>
            <tr class="border-t border-red-200 hover:bg-red-50">
              <td><?=htmlspecialchars($c['name'])?></td>
              <td><?=htmlspecialchars($c['code'])?></td>
              <td><?=htmlspecialchars($c['start_time'])?></td>
              <td>
                <a class="text-red-700 mr-2 underline" href="view_attendance.php?course_id=<?=intval($c['id'])?>">View Attendance</a>
                <a class="text-red-500 mr-2 underline" href="edit_course.php?id=<?=intval($c['id'])?>">Edit</a>
                <a href="#" class="text-red-600 underline" onclick="deleteCourse(<?=intval($c['id'])?>)">Delete</a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

  </div>

<script>
async function deleteCourse(id) {
  if (!confirm("Are you sure you want to delete this course?")) return;
  const fd = new FormData();
  fd.append("action", "delete_course");
  fd.append("id", id);
  const res = await fetch("../core/handleForms.php", {method:"POST", body:fd});
  const json = await res.json();
  alert(json.message);
  if (json.ok) location.reload();
}
</script>
</body></html>
