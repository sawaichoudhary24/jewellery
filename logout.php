<?php
session_start();
$_SESSION = [];
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), "", time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
}
session_destroy();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Signed Out | Khushbu Jewellery</title>
<link rel="stylesheet" href="assets/css/style.css">
<style>
.auth-page{min-height:100vh;background:linear-gradient(135deg,#faf8f3,#f3eee5);display:flex;align-items:center;justify-content:center;padding:30px}
.logout-card{width:min(520px,100%);background:#fff;text-align:center;padding:65px 40px;box-shadow:0 22px 70px rgba(23,23,23,.12)}
.logout-mark{font-family:"Cormorant Garamond",Georgia,serif;font-size:30px;letter-spacing:6px;color:#171717}
.logout-line{width:55px;height:1px;background:#b08a45;margin:22px auto}
.logout-card h1{font-family:"Cormorant Garamond",Georgia,serif;font-size:42px;font-weight:500;color:#171717;margin-bottom:12px}
.logout-card p{color:#777;font-size:13px;margin-bottom:28px}
.logout-actions{display:flex;gap:12px;justify-content:center;flex-wrap:wrap}
.logout-actions a{display:inline-flex;align-items:center;justify-content:center;min-width:150px;height:48px;padding:0 22px;text-transform:uppercase;letter-spacing:1.5px;font-size:10px}
.logout-login{background:#171717;color:#fff}.logout-home{border:1px solid #d8d1c6;color:#555}
</style>
</head>
<body>
<main class="auth-page">
  <section class="logout-card">
    <div class="logout-mark">KHUSHBU</div>
    <div class="logout-line"></div>
    <h1>You're signed out</h1>
    <p>Thank you for visiting Khushbu Jewellery. Your session has been securely ended.</p>
    <div class="logout-actions">
      <a class="logout-login" href="login.php">Sign In Again</a>
      <a class="logout-home" href="index.php">Back to Home</a>
    </div>
  </section>
</main>
</body>
</html>
