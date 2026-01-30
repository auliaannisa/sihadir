<?php
date_default_timezone_set('Asia/Jakarta');

$host = "sql100.infinityfree.com"; // ganti dengan host dari cPanel
$user = "if0_40965023";           // ganti dengan username dari cPanel
$pass = "Sihadir123";       // ganti dengan password dari cPanel
$db   = "if0_40965023_sihadirmpp";// ganti dengan nama database dari cPanel

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}


if (session_status() === PHP_SESSION_NONE) { session_start(); }
?>
