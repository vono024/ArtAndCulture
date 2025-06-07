<?php
session_start();
require_once 'db.php';

/** @var mysqli $conn */


if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$post_id = (int)$_POST['post_id'];
$text = trim($_POST['text']);
$user_id = $_SESSION['user']['id'];

if (!empty($text)) {
    $stmt = $conn->prepare("INSERT INTO comments (post_id, user_id, text) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $post_id, $user_id, $text);
    $stmt->execute();
}

header("Location: post.php?id=$post_id");
exit;
