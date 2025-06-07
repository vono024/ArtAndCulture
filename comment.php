<?php
session_start();
require_once 'db.php';
/** @var mysqli $conn */

if (!isset($_SESSION['user_id']) || !isset($_POST['post_id']) || empty($_POST['text'])) {
    header("Location: index.php");
    exit;
}

$post_id = (int)$_POST['post_id'];
$user_id = $_SESSION['user_id'];
$text = trim($_POST['text']);

if (!empty($text)) {
    $stmt = $conn->prepare("INSERT INTO comments (post_id, user_id, text, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("iis", $post_id, $user_id, $text);
    $stmt->execute();
}

header("Location: post.php?id=" . $post_id);
exit;
