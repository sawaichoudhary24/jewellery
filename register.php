<?php
session_start();
require_once "db.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirm = $_POST["confirm_password"] ?? "";

    if ($name === "" || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Please enter your name and a valid email address.";
    } elseif (strlen($password) < 6) {
        $message = "Password must be at least 6 characters.";
    } elseif ($password !== $confirm) {
        $message = "Passwords do not match.";
    } else {
        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows > 0) {
            $message = "An account with this email already exists.";
        } else {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $name, $email, $hashed);
            if ($stmt->execute()) {
                header("Location: login.php?registered=1");
                exit;
            }
            $message = "Registration could not be completed. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account | Khushbu Jewellery</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .auth-page{min-height:100vh;background:linear-gradient(135deg,#faf8f3 0%,#f3eee5 100%);display:flex;align-items:center;justify-content:center;padding:40px 20px}
        .auth-shell{width:min(1050px,100%);min-height:650px;background:#fff;display:grid;grid-template-columns:42% 58%;box-shadow:0 22px 70px rgba(23,23,23,.12);overflow:hidden}
        .auth-brand{background:#171717;color:#fff;padding:55px 45px;display:flex;flex-direction:column;justify-content:center;position:relative}
        .auth-brand:after{content:"";position:absolute;width:220px;height:220px;border:1px solid rgba(176,138,69,.35);border-radius:50%;right:-100px;bottom:-80px}
        .auth-brand .mark{font-family:"Cormorant Garamond",Georgia,serif;font-size:34px;letter-spacing:6px;color:#fff}
        .auth-brand .line{width:55px;height:1px;background:#b08a45;margin:22px 0}
        .auth-brand h1{font-family:"Cormorant Garamond",Georgia,serif;font-size:42px;font-weight:500;line-height:1.05;margin-bottom:18px}
        .auth-brand p{color:#c9c9c9;font-size:13px;line-height:1.9;max-width:300px}
        .auth-content{padding:60px clamp(30px,7vw,85px);display:flex;flex-direction:column;justify-content:center}
        .auth-kicker{color:#b08a45;font-size:10px;letter-spacing:3px;text-transform:uppercase;margin-bottom:10px}
        .auth-content h2{font-family:"Cormorant Garamond",Georgia,serif;font-size:42px;font-weight:500;color:#171717;margin-bottom:8px}
        .auth-sub{color:#777;font-size:13px;margin-bottom:30px}
        .auth-alert{padding:12px 14px;border:1px solid #eadfd0;background:#faf7f1;color:#8d6b32;font-size:12px;margin-bottom:20px}
        .auth-alert.success{border-color:#d7e6d8;background:#f4faf4;color:#397047}
        .auth-field{margin-bottom:18px}
        .auth-field label{display:block;font-size:10px;letter-spacing:1.2px;text-transform:uppercase;color:#555;margin-bottom:8px}
        .auth-field input{width:100%;height:50px;border:1px solid #ded9d0;background:#fff;padding:0 15px;font-size:13px;color:#222;outline:none;transition:.2s}
        .auth-field input:focus{border-color:#b08a45;box-shadow:0 0 0 3px rgba(176,138,69,.09)}
        .auth-submit{width:100%;height:52px;border:0;background:#171717;color:#fff;text-transform:uppercase;letter-spacing:2px;font-size:11px;cursor:pointer;transition:.25s;margin-top:5px}
        .auth-submit:hover{background:#b08a45}
        .auth-bottom{text-align:center;color:#777;font-size:12px;margin-top:24px}
        .auth-bottom a{color:#8d6b32;font-weight:600}
        .auth-home{display:block;text-align:center;color:#777;font-size:11px;margin-top:13px}
        @media(max-width:760px){.auth-shell{grid-template-columns:1fr}.auth-brand{min-height:260px;padding:40px 30px}.auth-brand h1{font-size:34px}.auth-content{padding:45px 28px}.auth-content h2{font-size:36px}}
    </style>
</head>
<body>
<main class="auth-page">
  <section class="auth-shell">
    <div class="auth-brand">
      <div class="mark">KHUSHBU</div>
      <div class="line"></div>
      <h1>Jewellery<br>with meaning.</h1>
      <p>Timeless pieces, thoughtfully crafted for the moments you want to remember.</p>
    </div>
    <div class="auth-content">
      <div class="auth-kicker">Join Khushbu</div>
      <h2>Create account</h2>
      <p class="auth-sub">Create your account and keep your jewellery journey in one place.</p>
      <?php if ($message): ?>
        <div class="auth-alert <?= $messageType === "success" ? "success" : "" ?>"><?= htmlspecialchars($message) ?></div>
      <?php endif; ?>
      <form method="POST" novalidate>
        <div class="auth-field">
          <label for="name">Full name</label>
          <input id="name" type="text" name="name" placeholder="Your full name" autocomplete="name" required>
        </div>
        <div class="auth-field">
          <label for="email">Email address</label>
          <input id="email" type="email" name="email" placeholder="you@example.com" autocomplete="email" required>
        </div>
        <div class="auth-field">
          <label for="password">Password</label>
          <input id="password" type="password" name="password" placeholder="At least 6 characters" autocomplete="new-password" required>
        </div>
        <div class="auth-field">
          <label for="confirm_password">Confirm password</label>
          <input id="confirm_password" type="password" name="confirm_password" placeholder="Re-enter your password" autocomplete="new-password" required>
        </div>
        <button class="auth-submit" type="submit">Create Account</button>
      </form>
      <p class="auth-bottom">Already have an account? <a href="login.php">Sign in</a></p>
      <a class="auth-home" href="index.php">← Continue shopping</a>
    </div>
  </section>
</main>
</body>
</html>
