<?php
// sesuaikan credential jika perlu
$host = 'localhost';
$db   = 'esteh_db';
$user = 'root';
$pass = '';
$mysqli = new mysqli($host, $user, $pass, $db);
if ($mysqli->connect_errno) {
    die("Koneksi gagal: " . $mysqli->connect_error);
}
$mysqli->set_charset("utf8mb4");
