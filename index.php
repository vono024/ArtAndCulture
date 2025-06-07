<?php
session_start();
require_once 'db.php';
/** @var mysqli $conn */

$search = isset($_GET['search']) ? $_GET['search'] : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';

$sql = "SELECT posts.*, users.username FROM posts 
        JOIN users ON posts.user_id = users.id 
        WHERE 1";

if (!empty($search)) {
    $search_safe = $conn->real_escape_string($search);
    $sql .= " AND posts.title LIKE '%$search_safe%'";
}

if (!empty($category)) {
    $category_safe = $conn->real_escape_string($category);
    $sql .= " AND posts.title LIKE '%$category_safe%'";
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
            <select name="category" class="form-select">
                <option value="">Усі категорії</option>
                <option <?= $category == 'Кіно' ? 'selected' : '' ?>>Кіно</option>
                <option <?= $category == 'Книги' ? 'selected' : '' ?>>Книги</option>
                <option <?= $category == 'Мистецтво' ? 'selected' : '' ?>>Мистецтво</option>
            </select>
        </div>
        <div class="col-md-3">
            <button class="btn btn-primary w-100">🔍 Пошук</button>
        </div>
    </form>

    <?php if (isset($_SESSION['user_id'])): ?>
        <div class="mb-4 text-end">
            <a href="create_post.php" class="btn btn-success">➕ Додати пост</a>
        </div>
    <?php endif; ?>

    <?php if ($result->num_rows === 0): ?>
        <p class="text-muted">Нічого не знайдено.</p>
    <?php else: ?>
        <?php while ($row = $result->fetch_assoc()): ?>
            <div class="card mb-4 shadow-sm">
                <div class="row g-0 align-items-center" style="min-height: 180px;">
                    <?php if (!empty($row['image']) && file_exists('uploads/' . $row['image'])): ?>
                        <div class="col-md-4">
                            <div class="ratio ratio-16x9">
                                <img src="uploads/<?= htmlspecialchars($row['image']) ?>" class="img-fluid rounded-start object-fit-cover" style="object-fit: cover;">
                            </div>
                        </div>
                    <?php endif; ?>
                    <div class="col-md-8">
                        <div class="card-body py-3 px-4">
                            <h5 class="card-title mb-1"><?= htmlspecialchars($row['title']) ?></h5>
                            <p class="card-text text-muted mb-1">
                                Автор: <?= htmlspecialchars($row['username']) ?> | <?= $row['created_at'] ?>
                            </p>
                            <p class="card-text text-truncate"><?= htmlspecialchars($row['content']) ?></p>
                            <div class="d-flex justify-content-between align-items-center mt-2">
                                <a href="post.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-primary">Читати далі</a>
                                <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $row['user_id']): ?>
                                    <a href="delete_post.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Видалити пост?')">Видалити</a>
                                <?php endif; ?>
                            </div>
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
