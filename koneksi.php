<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "db_swakarsa_setoran"; // Pastikan nama ini sama persis dengan di phpMyAdmin

$koneksi = mysqli_connect($host, $user, $pass, $db);

if (!$koneksi) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>