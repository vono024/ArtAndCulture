<?php
session_start();
require_once 'db.php';

/** @var mysqli $conn */

$search = isset($_GET['search']) ? $_GET['search'] : '';
$filter = isset($_GET['category']) ? $_GET['category'] : '';

$query = "SELECT posts.*, categories.name AS category_name, users.username 
          FROM posts 
          JOIN categories ON posts.category_id = categories.id 
          JOIN users ON posts.user_id = users.id";

$conditions = [];
if (!empty($search)) {
    $conditions[] = "posts.title LIKE '%" . $conn->real_escape_string($search) . "%'";
}
if (!empty($filter)) {
    $conditions[] = "categories.name = '" . $conn->real_escape_string($filter) . "'";
}
if ($conditions) {
    $query .= " WHERE " . implode(" AND ", $conditions);
}
$query .= " ORDER BY posts.created_at DESC";

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Блог: Мистецтво та культура</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<h1>Блог про мистецтво та культуру</h1>

<?php if (isset($_SESSION['user'])): ?>
    <p>Вітаємо, <?= htmlspecialchars($_SESSION['user']['username']) ?> |
        <a href="logout.php">Вийти</a>
        <?php if ($_SESSION['user']['role'] === 'admin'): ?>
            | <a href="admin.php">Адмін-панель</a>
        <?php endif; ?>
    </p>
<?php else: ?>
    <p><a href="login.php">Увійти</a> | <a href="register.php">Реєстрація</a></p>
<?php endif; ?>

<form method="get">
    <input type="text" name="search" placeholder="Пошук за назвою..." value="<?= htmlspecialchars($search) ?>">
    <select name="category">
        <option value="">Усі категорії</option>
        <?php
        $cats = $conn->query("SELECT * FROM categories");
        while ($cat = $cats->fetch_assoc()) {
            $selected = $filter === $cat['name'] ? 'selected' : '';
            echo "<option $selected>{$cat['name']}</option>";
        }
        ?>
    </select>
    <button type="submit">🔍 Пошук</button>
</form>

<hr>

<?php while ($row = $result->fetch_assoc()): ?>
    <div class="post">
        <h2><a href="post.php?id=<?= $row['id'] ?>"><?= htmlspecialchars($row['title']) ?></a></h2>
        <p><small>Автор: <?= htmlspecialchars($row['username']) ?> | Категорія: <?= htmlspecialchars($row['category_name']) ?> | <?= $row['created_at'] ?></small></p>
        <?php if (!empty($row['image'])): ?>
            <img src="uploads/<?= htmlspecialchars($row['image']) ?>" width="300">
        <?php endif; ?>
        <p><?= nl2br(htmlspecialchars(mb_substr($row['content'], 0, 200))) ?>...</p>
        <a href="post.php?id=<?= $row['id'] ?>">Читати далі</a>
    </div>
    <hr>
<?php endwhile; ?>

</body>
</html>
