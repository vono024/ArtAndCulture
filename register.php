<?php
session_start();
require_once 'db.php';
/** @var mysqli $conn */

$error = '';
$username = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $password_confirm = isset($_POST['password_confirm']) ? $_POST['password_confirm'] : '';

    if (empty($username) || empty($email) || empty($password) || empty($password_confirm)) {
        $error = "Всі поля є обов'язковими для заповнення.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Невірний формат email.";
    } elseif ($password !== $password_confirm) {
        $error = "Паролі не співпадають.";
    } else {
        // Перевірка, чи email вже зареєстровано
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "Користувач з таким email вже існує.";
        } else {
            $stmt->close();
            // Хешування пароля
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            // Вставка користувача в БД
            $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $email, $password_hash);

            if ($stmt->execute()) {
                header("Location: login.php");
                exit;
            } else {
                $error = "Помилка при реєстрації. Спробуйте пізніше.";
            }
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Реєстрація</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">
    <h1 class="mb-4">Реєстрація</h1>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post" class="bg-white p-4 shadow rounded">
        <div class="mb-3">
            <label for="username" class="form-label">Ім'я користувача:</label>
            <input type="text" id="username" name="username" class="form-control" value="<?= htmlspecialchars($username) ?>" required>
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Email:</label>
            <input type="email" id="email" name="email" class="form-control" value="<?= htmlspecialchars($email) ?>" required>
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Пароль:</label>
            <input type="password" id="password" name="password" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="password_confirm" class="form-label">Підтвердження пароля:</label>
            <input type="password" id="password_confirm" name="password_confirm" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-success">Зареєструватися</button>
        <a href="login.php" class="btn btn-secondary ms-2">Увійти</a>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
