<?php
session_start();
require_once 'db.php';
/** @var mysqli $conn */

if (!isset($_SESSION['user_id'], $_GET['id'])) {
    header("Location: index.php");
    exit();
}

$post_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

// Перевірка, чи пост належить цьому користувачу
$stmt = $conn->prepare("SELECT user_id FROM posts WHERE id = ?");
if ($stmt) {
    $stmt->bind_param("i", $post_id);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($row = $res->fetch_assoc()) {
        if ($row['user_id'] == $user_id) {
            $del = $conn->prepare("DELETE FROM posts WHERE id = ?");
            if ($del) {
                $del->bind_param("i", $post_id);
                $del->execute();
            }
        }
    }
}
header("Location: index.php");
exit();
