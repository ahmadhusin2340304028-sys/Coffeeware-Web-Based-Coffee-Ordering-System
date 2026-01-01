<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "kopiware";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
  die("Koneksi gagal: " . $conn->connect_error);
}

date_default_timezone_set('Asia/Makassar');

?>
