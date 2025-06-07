<?php
session_start();
require_once 'db.php';

/** @var mysqli $conn */


if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $category_id = (int)$_POST['category_id'];
    $user_id = $_SESSION['user']['id'];
    $image = '';

    if (!empty($_FILES['image']['name'])) {
        $image = basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], "uploads/$image");
    }

    $stmt = $conn->prepare("INSERT INTO posts (user_id, category_id, title, content, image) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iisss", $user_id, $category_id, $title, $content, $image);
    $stmt->execute();
    header("Location: index.php");
    exit;
}
?>

<h2>Додати новий пост</h2>
<form method="post" enctype="multipart/form-data">
    <input name="title" placeholder="Заголовок" required><br>
    <textarea name="content" placeholder="Вміст поста" required></textarea><br>
    <select name="category_id" required>
        <option value="">Оберіть категорію</option>
        <?php
        $res = $conn->query("SELECT * FROM categories");
        while ($cat = $res->fetch_assoc()) {
            echo "<option value='{$cat['id']}'>{$cat['name']}</option>";
        }
        ?>
    </select><br>
    <input type="file" name="image"><br>
    <button type="submit">Опублікувати</button>
</form>
