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

$stmt = $conn->prepare("SELECT id FROM posts WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $post_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: my_posts.php");
    exit;
}

$stmt = $conn->prepare("DELETE FROM likes WHERE post_id = ?");
$stmt->bind_param("i", $post_id);
$stmt->execute();

$stmt = $conn->prepare("DELETE FROM comments WHERE post_id = ?");
$stmt->bind_param("i", $post_id);
$stmt->execute();

$stmt = $conn->prepare("DELETE FROM posts WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $post_id, $user_id);
$stmt->execute();

header("Location: my_posts.php");
exit;
?>
