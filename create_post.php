<?php
session_start();
require_once 'db.php';
/** @var mysqli $conn */

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$title = '';
$content = '';
$image = '';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $user_id = $_SESSION['user_id'];

    if (!empty($_FILES['image']['name'])) {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($ext, $allowed)) {
            $image = uniqid() . '.' . $ext;
            move_uploaded_file($_FILES['image']['tmp_name'], 'uploads/' . $image);
        }
    }

    if (!empty($title) && !empty($content)) {
        $stmt = $conn->prepare("INSERT INTO posts (title, content, image, user_id, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->bind_param("sssi", $title, $content, $image, $user_id);
        $stmt->execute();
        $message = "✅ Пост успішно додано!";
        $title = $content = '';
    } else {
        $message = "❌ Заповніть всі поля!";
    }
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Новий пост</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">
    <h2 class="mb-4">➕ Додати новий пост</h2>

    <?php if (!empty($message)): ?>
        <div class="alert <?= strpos($message, 'успішно') ? 'alert-success' : 'alert-danger' ?>">
            <?= $message ?>
        </div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" class="bg-white p-4 shadow rounded">
        <div class="mb-3">
            <label class="form-label">Заголовок:</label>
            <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($title) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Вміст:</label>
            <textarea name="content" class="form-control" rows="6" required><?= htmlspecialchars($content) ?></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Зображення (необов'язково):</label>
            <input type="file" name="image" class="form-control">
        </div>
        <button class="btn btn-success">💾 Зберегти</button>
        <a href="index.php" class="btn btn-secondary">↩ Назад</a>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
