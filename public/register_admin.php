<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Register Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-red-50">
  <div class="max-w-md mx-auto mt-12 p-6 bg-white rounded shadow border border-red-200">
    <h2 class="text-xl font-semibold mb-4 text-red-700">Admin Registration</h2>
    <form id="regForm">
      <label class="text-red-700">Full name</label>
      <input name="full_name" class="w-full border border-red-300 p-2 mb-3 focus:ring-red-500 focus:border-red-500" required>
      <label class="text-red-700">Email</label>
      <input name="email" type="email" class="w-full border border-red-300 p-2 mb-3 focus:ring-red-500 focus:border-red-500" required>
      <label class="text-red-700">Password</label>
      <input name="password" type="password" class="w-full border border-red-300 p-2 mb-3 focus:ring-red-500 focus:border-red-500" required>
      <input type="hidden" name="action" value="register_admin">
      <button class="bg-red-600 hover:bg-red-700 text-white p-2 rounded w-full">Register Admin</button>
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
  if (json.ok) setTimeout(()=>{ window.location='login.php' }, 800);
});
</script>
</body>
</html>
