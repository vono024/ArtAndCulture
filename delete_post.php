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

// Перевірка власника поста
$stmt = $conn->prepare("SELECT image FROM posts WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $post_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Пост не знайдено або не власник
    header("Location: my_posts.php");
    exit;
}

$post = $result->fetch_assoc();

// Видаляємо файл зображення, якщо є
if (!empty($post['image']) && file_exists('uploads/' . $post['image'])) {
    unlink('uploads/' . $post['image']);
}

// Видаляємо пост з БД
$del = $conn->prepare("DELETE FROM posts WHERE id = ? AND user_id = ?");
$del->bind_param("ii", $post_id, $user_id);
$del->execute();

header("Location: my_posts.php");
exit;
