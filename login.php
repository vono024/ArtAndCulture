<?php
session_start();
require_once 'db.php';

/** @var mysqli $conn */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $user = $conn->query("SELECT * FROM users WHERE email = '$email'")->fetch_assoc();
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = $user;
        header("Location: index.php");
        exit;
    } else {
        $error = "Невірна пошта або пароль.";
    }
}
?>

<form method="post">
    <h2>Вхід</h2>
    <input name="email" type="email" placeholder="Email" required>
    <input name="password" type="password" placeholder="Пароль" required>
    <button type="submit">Увійти</button>
    <?php if (!empty($error)) echo "<p>$error</p>"; ?>
</form>
