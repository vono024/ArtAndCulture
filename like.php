<?php
session_start();
require_once 'db.php';
/** @var mysqli $conn */

if (!isset($_SESSION['user_id']) || !isset($_GET['post_id'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$post_id = (int)$_GET['post_id'];

$stmt = $conn->prepare("SELECT id FROM likes WHERE user_id = ? AND post_id = ?");
$stmt->bind_param("ii", $user_id, $post_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $stmt = $conn->prepare("DELETE FROM likes WHERE user_id = ? AND post_id = ?");
    $stmt->bind_param("ii", $user_id, $post_id);
    $stmt->execute();
} else {
    $stmt = $conn->prepare("INSERT INTO likes (user_id, post_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $user_id, $post_id);
    $stmt->execute();
}

header("Location: post.php?id=$post_id");
exit;
