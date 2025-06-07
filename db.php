<?php
$servername = "localhost";
$username = "root";
$password = ""; // або твій пароль
$dbname = "simple_blog"; // старе ім'я бази
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
