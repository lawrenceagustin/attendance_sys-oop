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
<html>
<head>
<meta charset="utf-8"><title>Admin - Dashboard</title>
<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>
<script>
  var notyf = new Notyf({duration:3000, position:{x:'right',y:'top'}});
</script>
</head>
<body class="bg-red-50 p-6">
  <div class="max-w-5xl mx-auto">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-red-700">Admin Dashboard</h1>
      <div>
        <span class="mr-4 text-red-700">Hello, <?=htmlspecialchars($_SESSION['full_name'])?></span>
        <a href="logout.php" class="text-red-600 font-semibold">Logout</a>
      </div>
    </div>

    <!-- Courses Section -->
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

    <!-- Excuse Letters Section -->
    <div class="bg-white p-4 rounded shadow border border-red-200">
      <h2 class="font-semibold mb-2 text-red-700">Excuse Letters</h2>
      <label class="block mb-2 text-sm">Filter by Course:</label>
      <select id="courseFilter" class="border rounded p-2 mb-3">
        <option value="">All Courses</option>
        <?php foreach($courses as $c): ?>
          <option value="<?=$c['id']?>"><?=htmlspecialchars($c['name'])?></option>
        <?php endforeach; ?>
      </select>
      <table class="w-full text-sm">
        <thead>
          <tr class="bg-red-100 text-red-700">
            <th>Student</th>
            <th>Course</th>
            <th>Reason</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody id="excuseTable"></tbody>
      </table>
    </div>
  </div>

<script>
function loadExcuseLetters() {
  var courseId = document.getElementById("courseFilter").value;
  var fd = new FormData();
  fd.append("action","view_all_excuse_letters");
  fd.append("course_id",courseId);

  var xhr = new XMLHttpRequest();
  xhr.open("POST","../core/handleForms.php",true);
  xhr.onload = function() {
    if (xhr.status==200) {
      var res = JSON.parse(xhr.responseText);
      if (res.ok) {
        var rows = "";
        for (var i=0; i<res.letters.length; i++) {
          var l = res.letters[i];
          rows += "<tr class='border-t hover:bg-red-50'>";
          rows += "<td>"+(l.student_name || "")+"</td>";
          rows += "<td>"+(l.course_name || "")+"</td>";
          rows += "<td>"+(l.reason || "")+"</td>";
          rows += "<td class='capitalize'>"+(l.status || "")+"</td>";
          rows += "<td>";
          if (l.status=="pending") {
            rows += "<button class='bg-green-600 text-white px-2 py-1 rounded mr-1' onclick='updateLetter("+l.id+",\"approved\")'>Approve</button>";
            rows += "<button class='bg-red-600 text-white px-2 py-1 rounded' onclick='updateLetter("+l.id+",\"rejected\")'>Reject</button>";
          }
          rows += "</td></tr>";
        }
        document.getElementById("excuseTable").innerHTML = rows;
      } else {
        document.getElementById("excuseTable").innerHTML = "<tr><td colspan='5' class='p-3'>No data</td></tr>";
      }
    }
  }
  xhr.send(fd);
}

function updateLetter(id,status) {
  var fd = new FormData();
  fd.append("action","update_excuse_status");
  fd.append("id",id);
  fd.append("status",status);
  var xhr = new XMLHttpRequest();
  xhr.open("POST","../core/handleForms.php",true);
  xhr.onload = function() {
    if (xhr.status==200) {
      var res = JSON.parse(xhr.responseText);
      if (res.ok) {
        notyf.success(res.message);
        loadExcuseLetters();
      } else {
        notyf.error(res.message);
      }
    }
  }
  xhr.send(fd);
}

document.getElementById("courseFilter").addEventListener("change", function(){
  loadExcuseLetters();
});

loadExcuseLetters();

function deleteCourse(id) {
  var fd = new FormData();
  fd.append("action","delete_course");
  fd.append("id",id);
  var xhr = new XMLHttpRequest();
  xhr.open("POST","../core/handleForms.php",true);
  xhr.onload = function() {
    if (xhr.status==200) {
      var res = JSON.parse(xhr.responseText);
      if (res.ok) {
        notyf.success(res.message);
        setTimeout(function(){ location.reload(); },1500);
      } else {
        notyf.error(res.message);
      }
    }
  }
  xhr.send(fd);
}
</script>
</body>
</html>
