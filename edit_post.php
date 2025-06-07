<?php
session_start();
require_once 'db.php';
/** @var mysqli $conn */

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: my_posts.php");
    exit;
}

$post_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

$message = '';

$stmt = $conn->prepare("SELECT * FROM posts WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $post_id, $user_id);
$stmt->execute();
$post_result = $stmt->get_result();

if ($post_result->num_rows === 0) {
    header("Location: my_posts.php");
    exit;
}

$post = $post_result->fetch_assoc();

$categories_result = $conn->query("SELECT id, name FROM categories ORDER BY name ASC");

$title = $post['title'];
$content = $post['content'];
$current_category_id = $post['category_id'];
$image = $post['image'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $category_id = (int)$_POST['category_id'];

    if (!empty($_FILES['image']['name'])) {
        $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($ext, $allowed)) {
            $new_image = uniqid() . '.' . $ext;
            move_uploaded_file($_FILES['image']['tmp_name'], 'uploads/' . $new_image);
            if (!empty($image) && file_exists('uploads/' . $image)) {
                unlink('uploads/' . $image);
            }
            $image = $new_image;
        }
    }

    if (!empty($title) && !empty($content) && $category_id) {
        $stmt_update = $conn->prepare("UPDATE posts SET title = ?, content = ?, image = ?, category_id = ? WHERE id = ? AND user_id = ?");
        $stmt_update->bind_param("sssiii", $title, $content, $image, $category_id, $post_id, $user_id);
        $stmt_update->execute();
        $message = "Пост успішно оновлено!";
        $current_category_id = $category_id;
    } else {
        $message = "Заповніть всі поля!";
    }
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8" />
    <title>Редагувати пост</title>
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="bg-light">
<div class="container py-5">
    <h2 class="mb-4">Редагувати пост</h2>

    <?php if (!empty($message)): ?>
        <div class="alert <?= strpos($message, 'успішно') !== false ? 'alert-success' : 'alert-danger' ?>">
            <?= $message ?>
        </div>
    <?php endif; ?>

    <form method="post" enctype="multipart/form-data" class="bg-white p-4 shadow rounded">
        <div class="mb-3">
            <label class="form-label">Заголовок:</label>
            <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($title) ?>" required />
        </div>

        <div class="mb-3">
            <label class="form-label">Категорія:</label>
            <select name="category_id" class="form-select" required>
                <option value="">Оберіть категорію</option>
                <?php while ($cat = $categories_result->fetch_assoc()): ?>
                    <option value="<?= $cat['id'] ?>" <?= ($cat['id'] == $current_category_id) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['name']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Вміст:</label>
            <textarea name="content" class="form-control" rows="6" required><?= htmlspecialchars($content) ?></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Нове зображення (за бажанням):</label>
            <input type="file" name="image" class="form-control" />
        </div>

        <?php if (!empty($image) && file_exists('uploads/' . $image)): ?>
            <div class="mb-3">
                <label class="form-label">Поточне зображення:</label><br />
                <img src="uploads/<?= htmlspecialchars($image) ?>" alt="Поточне зображення" class="img-fluid rounded" style="max-width: 300px; height: auto;" />
            </div>
        <?php endif; ?>

        <button class="btn btn-success">💾 Оновити</button>
        <a href="my_posts.php" class="btn btn-secondary">↩ Назад</a>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
