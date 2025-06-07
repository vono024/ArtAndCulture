<?php
session_start();
require_once 'db.php';

/** @var mysqli $conn */

$post_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$post = $conn->query("SELECT posts.*, users.username, categories.name AS category 
                      FROM posts 
                      JOIN users ON posts.user_id = users.id 
                      JOIN categories ON posts.category_id = categories.id 
                      WHERE posts.id = $post_id")->fetch_assoc();

if (!$post) die("Пост не знайдено");

// Кількість лайків
$likes = $conn->query("SELECT COUNT(*) AS total FROM likes WHERE post_id = $post_id")->fetch_assoc()['total'];
$comments = $conn->query("SELECT comments.*, users.username FROM comments 
                          JOIN users ON comments.user_id = users.id 
                          WHERE post_id = $post_id ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($post['title']) ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<a href="index.php">← Назад</a>
<h1><?= htmlspecialchars($post['title']) ?></h1>
<p><small>Автор: <?= htmlspecialchars($post['username']) ?> | Категорія: <?= htmlspecialchars($post['category']) ?> | <?= $post['created_at'] ?></small></p>
<?php if ($post['image']): ?>
    <img src="uploads/<?= htmlspecialchars($post['image']) ?>" width="400"><br>
<?php endif; ?>
<p><?= nl2br(htmlspecialchars($post['content'])) ?></p>

<form method="post" action="like.php">
    <input type="hidden" name="post_id" value="<?= $post_id ?>">
    <button type="submit">❤️ Вподобати (<?= $likes ?>)</button>
</form>

<hr>
<h3>Коментарі</h3>
<?php if (isset($_SESSION['user'])): ?>
    <form method="post" action="comment.php">
        <textarea name="text" placeholder="Ваш коментар..." required></textarea>
        <input type="hidden" name="post_id" value="<?= $post_id ?>">
        <button type="submit">Надіслати</button>
    </form>
<?php else: ?>
    <p><a href="login.php">Увійдіть</a>, щоб залишити коментар.</p>
<?php endif; ?>

<?php while ($c = $comments->fetch_assoc()): ?>
    <p><b><?= htmlspecialchars($c['username']) ?></b>: <?= nl2br(htmlspecialchars($c['text'])) ?><br><small><?= $c['created_at'] ?></small></p>
<?php endwhile; ?>
</body>
</html>
