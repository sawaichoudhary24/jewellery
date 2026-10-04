<?php
session_start();
require_once "../db.php";

if (isset($_SESSION["admin_id"])) {
    header("Location: dashboard.php");
    exit;
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    $stmt = $conn->prepare(
        "SELECT id, name, email, password, role
         FROM users
         WHERE email = ? AND role = 'admin'"
    );

    $stmt->bind_param("s", $email);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows === 1) {

        $admin = $result->fetch_assoc();

        if (password_verify($password, $admin["password"])) {

            $_SESSION["admin_id"] = $admin["id"];
            $_SESSION["admin_name"] = $admin["name"];
            $_SESSION["admin_email"] = $admin["email"];

            header("Location: dashboard.php");
            exit;

        } else {
            $message = "Incorrect password.";
        }

    } else {
        $message = "Admin account not found.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Login</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: #f7f3ed;
            font-family: Arial, sans-serif;
        }

        .login-box {
            width: 400px;
            max-width: 90%;
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,.12);
        }

        .login-box h1 {
            text-align: center;
            margin-bottom: 30px;
            color: #222;
        }

        input {
            width: 100%;
            padding: 14px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        button {
            width: 100%;
            padding: 14px;
            border: none;
            background: #b8860b;
            color: white;
            cursor: pointer;
            border-radius: 5px;
            font-size: 16px;
        }

        .error {
            color: #c00;
            text-align: center;
            margin-bottom: 15px;
        }

    </style>

</head>

<body>

<div class="login-box">

    <h1>Admin Login</h1>

    <?php if ($message): ?>
        <div class="error">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <form method="POST">

        <input
            type="email"
            name="email"
            placeholder="Admin Email"
            required
        >

        <input
            type="password"
            name="password"
            placeholder="Admin Password"
            required
        >

        <button type="submit">
            Login
        </button>

    </form>

</div>

</body>
</html>