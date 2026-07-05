<?php
session_start();
include 'koneksi.php';

// Cek apakah ada ID yang dikirim
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Proses hapus
    $query = "DELETE FROM karyawan WHERE id_karyawan = '$id'";
    
    if (mysqli_query($koneksi, $query)) {
        echo "<script>alert('Data Karyawan Berhasil Dihapus!'); window.location='data_karyawan.php';</script>";
    } else {
        echo "Gagal menghapus: " . mysqli_error($koneksi);
    }
} else {
    header("Location: data_karyawan.php");
}
?>