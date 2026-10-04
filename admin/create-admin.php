<?php

require_once "../db.php";

$name = "Admin";
$email = "admin@gmail.com";
$password = "password";

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

$delete = $conn->prepare("DELETE FROM users WHERE email = ?");
$delete->bind_param("s", $email);
$delete->execute();

$stmt = $conn->prepare("
    INSERT INTO users
    (name, email, password, role, status)
    VALUES (?, ?, ?, 'admin', 'active')
");

$stmt->bind_param(
    "sss",
    $name,
    $email,
    $hashedPassword
);

if ($stmt->execute()) {
    echo "<h2>Admin Created Successfully</h2>";
    echo "<p>Email: admin@gmail.com</p>";
    echo "<p>Password: password</p>";
} else {
    echo "Error: " . $stmt->error;
}

?>