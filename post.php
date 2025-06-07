<?php
session_start();
require_once 'db.php';
/** @var mysqli $conn */

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$post_id = (int)$_GET['id'];

// Отримуємо пост з автором та назвою категорії
$stmt = $conn->prepare("
    SELECT posts.*, users.username, categories.name AS category_name
    FROM posts
    JOIN users ON posts.user_id = users.id
    JOIN categories ON posts.category_id = categories.id
    WHERE posts.id = ?
");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$post_result = $stmt->get_result();

if ($post_result->num_rows === 0) {
    echo "Пост не знайдено.";
    exit;
}

$post = $post_result->fetch_assoc();

// Отримуємо кількість лайків для поста
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM likes WHERE post_id = ?");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$like_result = $stmt->get_result();
$likes = $like_result->fetch_assoc()['total'];

// Перевіряємо, чи лайкнув користувач
$liked = false;
if (isset($_SESSION['user_id'])) {
    $stmt = $conn->prepare("SELECT id FROM likes WHERE user_id = ? AND post_id = ?");
    $stmt->bind_param("ii", $_SESSION['user_id'], $post_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $liked = $res->num_rows > 0;
}

// Обробка додавання коментаря
$message = '';
if (isset($_SESSION['user_id']) && $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment'])) {
    $comment_text = trim($_POST['comment']);
    if (!empty($comment_text)) {
        $user_id = $_SESSION['user_id'];
        $stmt = $conn->prepare("INSERT INTO comments (content, user_id, post_id, created_at) VALUES (?, ?, ?, NOW())");
        $stmt->bind_param("sii", $comment_text, $user_id, $post_id);
        $stmt->execute();
        $message = "✅ Коментар додано!";
    } else {
        $message = "❌ Коментар не може бути пустим.";
    }
}

// Отримуємо коментарі
$stmt = $conn->prepare("
    SELECT comments.*, users.username 
    FROM comments 
    JOIN users ON comments.user_id = users.id 
    WHERE comments.post_id = ? 
    ORDER BY comments.created_at DESC
");
$stmt->bind_param("i", $post_id);
$stmt->execute();
$comments_result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($post['title']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand" href="index.php">Блог</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav ms-auto">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <li class="nav-item me-3">
                        <span class="navbar-text text-white">
                            Вітаємо, <?= htmlspecialchars($_SESSION['username']) ?>
                        </span>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-sm btn-outline-light" href="logout.php">Вихід</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="btn btn-sm btn-outline-light me-2" href="login.php">Увійти</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-sm btn-outline-success" href="register.php">Реєстрація</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<div class="container">
    <h1><?= htmlspecialchars($post['title']) ?></h1>
    <p class="text-muted">
        Автор: <?= htmlspecialchars($post['username']) ?> |
        Дата: <?= $post['created_at'] ?> |
        Категорія: <?= htmlspecialchars($post['category_name']) ?>
    </p>

    <?php if (!empty($post['image']) && file_exists('uploads/' . $post['image'])): ?>
        <img src="uploads/<?= htmlspecialchars($post['image']) ?>" alt="Зображення поста" class="img-fluid mb-4" style="max-height: 400px; object-fit: cover;">
    <?php endif; ?>

    <p><?= nl2br(htmlspecialchars($post['content'])) ?></p>

    <!-- Блок лайків -->
    <div class="mb-3">
        <?php if (isset($_SESSION['user_id'])): ?>
            <form method="get" action="like.php">
                <input type="hidden" name="post_id" value="<?= $post_id ?>">
                <button type="submit" class="btn <?= $liked ? 'btn-danger' : 'btn-outline-primary' ?>">
                    <?= $liked ? '👎 Прибрати лайк' : '👍 Лайкнути' ?> (<?= $likes ?>)
                </button>
            </form>
        <?php else: ?>
            <p>Лайки: <?= $likes ?>. <a href="login.php">Увійдіть</a>, щоб поставити лайк.</p>
        <?php endif; ?>
    </div>

    <hr>

    <h4>Коментарі</h4>

    <?php if ($message): ?>
        <div class="alert <?= strpos($message, '✅') !== false ? 'alert-success' : 'alert-danger' ?>">
            <?= $message ?>
        </div>
    <?php endif; ?>

    <?php if ($comments_result->num_rows === 0): ?>
        <p class="text-muted">Коментарів ще немає.</p>
    <?php else: ?>
        <?php while ($comment = $comments_result->fetch_assoc()): ?>
            <div class="mb-3 border rounded p-3 bg-white shadow-sm">
                <p><strong><?= htmlspecialchars($comment['username']) ?></strong> <small class="text-muted"><?= $comment['created_at'] ?></small></p>
                <p><?= nl2br(htmlspecialchars($comment['content'])) ?></p>
            </div>
        <?php endwhile; ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['user_id'])): ?>
        <form method="post" class="mb-5">
            <div class="mb-3">
                <label for="comment" class="form-label">Додати коментар</label>
                <textarea name="comment" id="comment" class="form-control" rows="3" required></textarea>
            </div>
            <button class="btn btn-primary">Відправити</button>
        </form>
    <?php else: ?>
        <p><a href="login.php">Увійдіть</a>, щоб додати коментар.</p>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
