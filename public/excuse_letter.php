<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
  header('Location: login.php');
  exit;
}
require_once __DIR__ . '/../core/models.php';
$stu = new Student();
$profile = $stu->loadProfileByUserId($_SESSION['user_id']);
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8"><title>Excuse Letter</title>
<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">

<!-- Notyf CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.css">
</head>
<body class="bg-white p-6">
  <div class="max-w-3xl mx-auto">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-red-700">Excuse Letter</h1>
      <div>
        <span class="mr-4 text-red-600"><?=htmlspecialchars($_SESSION['full_name'])?></span>
        <a href="student_dashboard.php" class="text-red-600 font-semibold mr-3">Dashboard</a>
        <a href="logout.php" class="text-red-600 font-semibold">Logout</a>
      </div>
    </div>

    <!-- Excuse Letter Form -->
    <div class="bg-red-50 p-4 rounded shadow mb-6">
      <h2 class="font-semibold text-red-700 mb-2">Submit New Excuse Letter</h2>
      <form id="excuseForm" class="space-y-3">
        <textarea name="reason" id="reason" placeholder="Write your excuse here..." class="w-full border rounded p-2" required></textarea>
        <input type="hidden" name="course_id" id="course_id" value="<?=intval($profile['course_id'])?>">
        <button type="button" id="submitBtn" class="bg-red-600 text-white px-4 py-2 rounded">Submit</button>
      </form>
    </div>

    <!-- My Excuse Letters -->
    <div class="bg-red-50 p-4 rounded shadow">
      <h2 class="font-semibold text-red-700 mb-2">My Excuse Letters</h2>
      <table class="w-full text-sm">
        <thead>
          <tr class="bg-red-100 text-red-700">
            <th>Date</th>
            <th>Reason</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody id="myLetters"></tbody>
      </table>
    </div>
  </div>

<!-- Notyf JS -->
<script src="https://cdn.jsdelivr.net/npm/notyf@3/notyf.min.js"></script>
<script>
const notyf = new Notyf({
  duration: 3000,
  position: { x: 'right', y: 'top' }
});

// Submit excuse letter
document.getElementById("submitBtn").onclick = function() {
  var reason = document.getElementById("reason").value.trim();
  var course_id = document.getElementById("course_id").value;
  if (reason=="") { 
    notyf.error("Please enter your excuse."); 
    return; 
  }

  var fd = new FormData();
  fd.append("action","submit_excuse_letter");
  fd.append("reason",reason);
  fd.append("course_id",course_id);

  var xhr = new XMLHttpRequest();
  xhr.open("POST","../core/handleForms.php",true);
  xhr.onload = function() {
    if (xhr.status==200) {
      var res = JSON.parse(xhr.responseText);
      if (res.ok) {
        notyf.success(res.message);
        document.getElementById("reason").value = "";
        loadMyLetters();
      } else {
        notyf.error(res.message);
      }
    }
  }
  xhr.send(fd);
};

// Load student’s own letters
function loadMyLetters() {
  var fd = new FormData();
  fd.append("action","view_my_excuse_letters");

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
          rows += "<td>"+(l.created_at || "")+"</td>";
          rows += "<td>"+(l.reason || "")+"</td>";
          rows += "<td class='capitalize'>"+(l.status || "")+"</td>";
          rows += "</tr>";
        }
        document.getElementById("myLetters").innerHTML = rows;
      }
    }
  }
  xhr.send(fd);
}

// Load immediately
loadMyLetters();
</script>
</body>
</html>
