<?php
session_start();
require_once 'db.php';
/** @var mysqli $conn */

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$post_id = (int)$_GET['id'];

// Отримуємо пост для редагування
$stmt = $conn->prepare("SELECT * FROM posts WHERE id = ?");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$post_result = $stmt->get_result();

if ($post_result->num_rows === 0) {
    echo "Пост не знайдено.";
    exit;
}

$post = $post_result->fetch_assoc();

$title = isset($_POST['title']) ? $_POST['title'] : $post['title'];
$category_id = isset($_POST['category']) ? (int)$_POST['category'] : (int)$post['category_id'];
$content = isset($_POST['content']) ? $_POST['content'] : $post['content'];
$image = isset($_FILES['image']) ? $_FILES['image'] : null;

$errors = [];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty(trim($title))) {
        $errors[] = 'Заголовок обов\'язковий';
    }
    if (empty($category_id)) {
        $errors[] = 'Категорія обов\'язкова';
    }
    if (empty(trim($content))) {
        $errors[] = 'Вміст обов\'язковий';
    }

    if (empty($errors)) {
        $image_name = $post['image'];
        if ($image && $image['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($image['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            if (in_array($ext, $allowed)) {
                $image_name = uniqid() . '.' . $ext;
                move_uploaded_file($image['tmp_name'], 'uploads/' . $image_name);
            } else {
                $errors[] = 'Недопустимий формат зображення.';
            }
        }

        if (empty($errors)) {
            $stmt = $conn->prepare("UPDATE posts SET title = ?, category_id = ?, content = ?, image = ? WHERE id = ?");
            $stmt->bind_param("sissi", $title, $category_id, $content, $image_name, $post_id);

            if ($stmt->execute()) {
                $message = "Пост оновлено успішно";
                // Оновити $post, щоб відобразити нові дані в формі після оновлення
                $post['title'] = $title;
                $post['category_id'] = $category_id;
                $post['content'] = $content;
                $post['image'] = $image_name;
            } else {
                $errors[] = "Помилка при оновленні посту: " . $stmt->error;
            }
        }
    }
}

// Отримуємо категорії для select
$categories_result = $conn->query("SELECT * FROM categories ORDER BY name ASC");
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Редагувати пост</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <h1>Редагувати пост</h1>

    <?php if (!empty($message)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $error): ?>
                <div><?= htmlspecialchars($error) ?></div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form action="edit_post.php?id=<?= $post_id ?>" method="post" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="title" class="form-label">Заголовок:</label>
            <input type="text" id="title" name="title" class="form-control" value="<?= htmlspecialchars($post['title']) ?>" required>
        </div>
        <div class="mb-3">
            <label for="category" class="form-label">Категорія:</label>
            <select id="category" name="category" class="form-select" required>
                <option value="">Оберіть категорію</option>
                <?php while ($row = $categories_result->fetch_assoc()): ?>
                    <option value="<?= $row['id'] ?>" <?= ($row['id'] == $post['category_id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($row['name']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="content" class="form-label">Вміст:</label>
            <textarea id="content" name="content" class="form-control" rows="5" required><?= htmlspecialchars($post['content']) ?></textarea>
        </div>
        <div class="mb-3">
            <label for="image" class="form-label">Нове зображення (за бажанням):</label>
            <input type="file" id="image" name="image" class="form-control">
            <?php if (!empty($post['image']) && file_exists('uploads/' . $post['image'])): ?>
                <img src="uploads/<?= htmlspecialchars($post['image']) ?>" alt="Поточне зображення" class="img-fluid mt-2" style="max-height: 150px;">
            <?php endif; ?>
        </div>
        <button type="submit" class="btn btn-primary">Оновити пост</button>
        <a href="index.php" class="btn btn-secondary">Назад</a>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
