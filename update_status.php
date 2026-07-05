<?php
session_start();
include 'koneksi.php';

// Pastikan hanya admin yang bisa akses
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

if (isset($_GET['id']) && isset($_GET['status'])) {
    $id = $_GET['id'];
    $status = $_GET['status'];

    // Update status di tabel penjualan
    $query = "UPDATE penjualan SET status = '$status' WHERE id_penjualan = '$id'";
    $exec = mysqli_query($koneksi, $query);

    if ($exec) {
        echo "<script>alert('Status berhasil diperbarui!'); window.location='data_setoran.php';</script>";
    } else {
        echo "Gagal memperbarui status: " . mysqli_error($koneksi);
    }
} else {
    header("Location: data_setoran.php");
}
?>