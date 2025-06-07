<?php
session_start();
require_once 'db.php';

/** @var mysqli $conn */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $exists = $conn->query("SELECT * FROM users WHERE email = '$email'")->num_rows;
    if ($exists) {
        $error = "Такий email вже зареєстровано!";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'user')");
        $stmt->bind_param("sss", $username, $email, $password);
        $stmt->execute();
        $_SESSION['user'] = ['username' => $username, 'role' => 'user'];
        header("Location: index.php");
        exit;
    }
}
?>

<form method="post">
    <h2>Реєстрація</h2>
    <input name="username" placeholder="Імʼя" required>
    <input name="email" type="email" placeholder="Email" required>
    <input name="password" type="password" placeholder="Пароль" required>
    <button type="submit">Зареєструватися</button>
    <?php if (!empty($error)) echo "<p>$error</p>"; ?>
</form>
