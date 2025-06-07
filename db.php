<?php
/** @var mysqli $conn */
$conn = new mysqli('localhost', 'root', '', 'simple_blog');
if ($conn->connect_error) {
    die('Помилка з’єднання з БД: ' . $conn->connect_error);
}
$conn->set_charset("utf8mb4");
