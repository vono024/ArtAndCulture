<?php
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'simple_blog';

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Помилка з'єднання з БД: " . $conn->connect_error);
}
?>
