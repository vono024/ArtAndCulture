<?php
session_start();
require_once 'db.php';
/** @var mysqli $conn */

$search = isset($_GET['search']) ? $_GET['search'] : '';
$category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;

// Отримуємо всі категорії
$categories_result = $conn->query("SELECT id, name FROM categories ORDER BY name ASC");

$sql = "SELECT posts.*, users.username, categories.name AS category_name FROM posts 
        JOIN users ON posts.user_id = users.id 
        JOIN categories ON posts.category_id = categories.id 
        WHERE 1";

if (!empty($search)) {
    $search_safe = $conn->real_escape_string($search);
    $sql .= " AND posts.title LIKE '%$search_safe%'";
}

if ($category_id > 0) {
    $sql .= " AND posts.category_id = $category_id";
}

$sql .= " ORDER BY posts.created_at DESC";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Блог: Мистецтво та культура</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
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

    <h1 class="mb-4 text-center">Блог про мистецтво та культуру</h1>

    <form method="get" class="row g-2 mb-5">
        <div class="col-md-5">
            <input type="text" name="search" class="form-control" placeholder="Пошук за назвою..." value="<?= htmlspecialchars($search) ?>">
        </div>
        <div class="col-md-4">
            <select name="category_id" class="form-select">
                <option value="0">Усі категорії</option>
                <?php while ($cat = $categories_result->fetch_assoc()): ?>
                    <option value="<?= $cat['id'] ?>" <?= ($cat['id'] == $category_id) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['name']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="col-md-3">
            <button class="btn btn-primary w-100">🔍 Пошук</button>
        </div>
    </form>

    <?php if (isset($_SESSION['user_id'])): ?>
        <div class="mb-4 text-end">
            <a href="create_post.php" class="btn btn-success me-2">➕ Додати пост</a>
            <a href="my_posts.php" class="btn btn-info">📋 Мої пости</a>
        </div>
    <?php endif; ?>

    <?php if ($result->num_rows === 0): ?>
        <p class="text-muted">Нічого не знайдено.</p>
    <?php else: ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="card mb-4 shadow-sm">
                <div class="row g-0">
                    <div class="col-md-4">
                        <?php if (!empty($row['image']) && file_exists('uploads/' . $row['image'])): ?>
                            <img src="uploads/<?= htmlspecialchars($row['image']) ?>" class="img-fluid rounded-start" alt="Зображення посту">
                        <?php else: ?>
                            <img src="https://via.placeholder.com/400x300?text=No+Image" class="img-fluid rounded-start" alt="Немає зображення">
                        <?php endif; ?>
                    </div>
                    <div class="col-md-8">
                        <div class="card-body">
                            <h4 class="card-title"><?= htmlspecialchars($row['title']) ?></h4>
                            <p class="card-text text-muted mb-2">
                                Автор: <?= htmlspecialchars($row['username']) ?> | <?= $row['created_at'] ?>
                            </p>
                            <p class="card-text"><strong>Категорія:</strong> <?= htmlspecialchars($row['category_name']) ?></p>
                            <p class="card-text"><?= nl2br(htmlspecialchars(mb_substr($row['content'], 0, 250))) ?>...</p>
                            <a href="post.php?id=<?= $row['id'] ?>" class="btn btn-outline-primary mt-2">Читати далі</a>
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
