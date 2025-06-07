<?php
session_start();
require_once 'db.php';
/** @var mysqli $conn */

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM posts WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Мої пости</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand" href="index.php">Блог</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item me-3">
                    <a class="btn btn-sm btn-outline-light" href="create_post.php">➕ Додати пост</a>
                </li>
                <li class="nav-item">
                    <a class="btn btn-sm btn-outline-light" href="logout.php">Вихід</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container py-4">
    <h2 class="mb-4">📋 Мої пости</h2>

    <?php if ($result->num_rows === 0): ?>
        <p class="text-muted">У вас поки що немає постів.</p>
    <?php else: ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="card mb-4 shadow-sm">
                <div class="row g-0">
                    <?php if (!empty($row['image']) && file_exists('uploads/' . $row['image'])): ?>
                        <div class="col-md-4">
                            <img src="uploads/<?= htmlspecialchars($row['image']) ?>" class="img-fluid rounded-start" style="height: 100%; object-fit: cover;">
                        </div>
                    <?php endif; ?>
                    <div class="col-md-8">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($row['title']) ?></h5>
                            <p class="card-text"><small class="text-muted"><?= $row['created_at'] ?> | Категорія: <?= htmlspecialchars($row['category']) ?></small></p>
                            <p class="card-text"><?= nl2br(htmlspecialchars(mb_substr($row['content'], 0, 200))) ?>...</p>
                            <a href="edit_post.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-primary">✏ Редагувати</a>
                            <a href="delete_post.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Ви впевнені, що хочете видалити пост?');">🗑 Видалити</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
