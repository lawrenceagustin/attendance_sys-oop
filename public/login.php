<?php
session_start();
if (isset($_SESSION['user_id'])) {
  $role = $_SESSION['role'];
  header("Location: " . ($role === 'admin' ? 'admin_dashboard.php' : 'student_dashboard.php'));
  exit;
}
?>

<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Login - Attendance</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-red-50">
  <div class="max-w-md mx-auto mt-20 bg-white p-8 rounded shadow border border-red-300">
  <h1 class="text-2xl font-semibold mb-4 text-red-700">Login</h1>
  <form id="loginForm">
    <label class="block mb-2 text-red-700">Email</label>
    <input name="email" type="email" class="w-full border border-red-300 p-2 mb-3 rounded focus:border-red-500 focus:ring-red-500" required>
    <label class="block mb-2 text-red-700">Password</label>
    <input name="password" type="password" class="w-full border border-red-300 p-2 mb-3 rounded focus:border-red-500 focus:ring-red-500" required>
    <input type="hidden" name="action" value="login">
    <button class="w-full bg-red-600 hover:bg-red-700 text-white p-2 rounded">Login</button>
    <button type="button" onclick="window.location.href='index.php';" class="w-full bg-red-600 hover:bg-red-700 text-white mt-2 p-2 rounded">Return</button>
  </form>
  <div class="mt-4 text-sm">
    <a href="register_student.php" class="text-red-600 hover:underline">Register as Student</a> |
    <a href="register_admin.php" class="text-red-600 hover:underline">Register as Admin</a>
  </div>
  <div id="msg" class="mt-3 text-sm text-red-600"></div>
  </div>

<script>
document.getElementById('loginForm').addEventListener('submit', async function(e){
  e.preventDefault();
  const fd = new FormData(this);
  const res = await fetch('../core/handleForms.php', {method:'POST', body:fd});
  const json = await res.json();
  if (json.ok) {
  window.location.href = json.role === 'admin' ? 'admin_dashboard.php' : 'student_dashboard.php';
  } else {
  document.getElementById('msg').innerText = json.message;
  }
});
</script>
</body>
</html>
