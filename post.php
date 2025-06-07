<?php
session_start();
require_once 'db.php';
/** @var mysqli $conn */

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$post_id = (int)$_GET['id'];

$post_stmt = $conn->prepare("SELECT posts.*, users.username FROM posts JOIN users ON posts.user_id = users.id WHERE posts.id = ?");
$post_stmt->bind_param("i", $post_id);
$post_stmt->execute();
$post_result = $post_stmt->get_result();
$post = $post_result->fetch_assoc();

if (!$post) {
    echo "Пост не знайдено.";
    exit;
}

// Отримати коментарі
$comments = $conn->query("SELECT comments.*, users.username FROM comments JOIN users ON comments.user_id = users.id WHERE post_id = $post_id ORDER BY created_at DESC");

// Отримати кількість лайків
$likes_result = $conn->query("SELECT COUNT(*) as total FROM likes WHERE post_id = $post_id");
$likes = $likes_result->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($post['title']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5">
    <a href="index.php" class="btn btn-secondary mb-4">← Назад</a>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <h2 class="card-title"><?= htmlspecialchars($post['title']) ?></h2>
            <p class="text-muted">Автор: <?= htmlspecialchars($post['username']) ?> | <?= $post['created_at'] ?></p>

            <?php if (!empty($post['image']) && file_exists('uploads/' . $post['image'])): ?>
                <img src="uploads/<?= htmlspecialchars($post['image']) ?>" class="img-fluid rounded mb-3">
            <?php endif; ?>

            <p class="card-text"><?= nl2br(htmlspecialchars($post['content'])) ?></p>

            <div class="mt-4">
                <form method="post" action="like.php" class="d-inline">
                    <input type="hidden" name="post_id" value="<?= $post_id ?>">
                    <button class="btn btn-outline-danger">❤️ Лайк (<?= $likes ?>)</button>
                </form>
            </div>
        </div>
    </div>

    <h4 class="mb-3">Коментарі</h4>

    <?php if ($comments->num_rows === 0): ?>
        <p class="text-muted">Коментарів ще немає.</p>
    <?php else: ?>
        <?php while ($c = $comments->fetch_assoc()): ?>
            <div class="border rounded p-3 mb-2 bg-white shadow-sm">
                <strong><?= htmlspecialchars($c['username']) ?></strong>
                <span class="text-muted small"> | <?= $c['created_at'] ?></span>
                <p class="mb-0"><?= nl2br(htmlspecialchars($c['text'])) ?></p>
            </div>
        <?php endwhile; ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['user_id'])): ?>
        <form method="post" action="comment.php" class="mt-4">
            <input type="hidden" name="post_id" value="<?= $post_id ?>">
            <div class="mb-3">
                <label class="form-label">Залишити коментар:</label>
                <textarea name="text" class="form-control" rows="4" required></textarea>
            </div>
            <button class="btn btn-primary">💬 Додати</button>
        </form>
    <?php else: ?>
        <p class="mt-4">Щоб додати коментар — <a href="login.php">увійдіть</a>.</p>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
