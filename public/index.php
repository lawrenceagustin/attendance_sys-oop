<?php
session_start();

if (isset($_SESSION['role'])) {
  if ($_SESSION['role'] === 'admin') {
    header('Location: admin_dashboard.php');
    exit;
  }
  if ($_SESSION['role'] === 'student') {
    header('Location: student_dashboard.php');
    exit;
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1.0" />
  <title>Attendance System</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-white min-h-screen flex items-center justify-center">
  <div class="w-full max-w-lg bg-white shadow-lg rounded-2xl p-8 text-center border border-red-200">
    <h1 class="text-3xl font-bold text-red-600 mb-2">Attendance System</h1>
    <p class="text-red-500 mb-8">Choose an option to get started</p>

    <a href="login.php"
       class="block w-full bg-red-600 text-white py-3 rounded-xl font-semibold hover:bg-red-700 transition mb-6">
      Login
    </a>

    <div class="grid grid-cols-1 gap-3">
      <a href="register_student.php"
         class="block w-full bg-red-500 text-white py-3 rounded-xl font-semibold hover:bg-red-600 transition">
        Register as Student
      </a>
      <a href="register_admin.php"
         class="block w-full bg-red-400 text-white py-3 rounded-xl font-semibold hover:bg-red-500 transition">
        Register as Admin
      </a>
    </div>

    <p class="text-xs text-red-300 mt-6">Tip: After logging in, you'll be redirected to your dashboard automatically.</p>
  </div>
</body>
</html>
