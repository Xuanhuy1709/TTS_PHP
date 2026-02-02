<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'php_practice';

$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) {
  die('Kết nối DB thất bại: ' . mysqli_connect_error());
}
mysqli_set_charset($conn, 'utf8mb4');
