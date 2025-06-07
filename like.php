<?php
session_start();
require_once 'db.php';

/** @var mysqli $conn */

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

$post_id = (int)$_POST['post_id'];
$user_id = $_SESSION['user']['id'];

// перевірка чи вже лайкали
$res = $conn->query("SELECT * FROM likes WHERE post_id = $post_id AND user_id = $user_id");
if ($res->num_rows === 0) {
    $stmt = $conn->prepare("INSERT INTO likes (post_id, user_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $post_id, $user_id);
    $stmt->execute();
}

header("Location: post.php?id=$post_id");
exit;
