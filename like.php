<?php
session_start();
require_once 'db.php';
/** @var mysqli $conn */

if (!isset($_SESSION['user_id']) || !isset($_POST['post_id'])) {
    header("Location: index.php");
    exit;
}

$post_id = (int)$_POST['post_id'];
$user_id = $_SESSION['user_id'];

$check = $conn->prepare("SELECT * FROM likes WHERE post_id = ? AND user_id = ?");
$check->bind_param("ii", $post_id, $user_id);
$check->execute();
$res = $check->get_result();

if ($res->num_rows === 0) {
    $stmt = $conn->prepare("INSERT INTO likes (post_id, user_id, created_at) VALUES (?, ?, NOW())");
    $stmt->bind_param("ii", $post_id, $user_id);
    $stmt->execute();
}

header("Location: post.php?id=" . $post_id);
exit;
