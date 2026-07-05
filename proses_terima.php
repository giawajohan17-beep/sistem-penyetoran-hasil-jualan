<?php
include 'koneksi.php';

// Cek apakah parameter ID dan TGL ada di URL
if (!isset($_GET['id']) || !isset($_GET['tgl'])) {
    header("location:data_setoran.php?pesan=gagal_parameter");
    exit();
}

// PERBAIKAN: Nama fungsi yang benar adalah mysqli_real_escape_string (Tanpa 'with')
$id_karyawan = mysqli_real_escape_string($koneksi, $_GET['id']);
$tgl = mysqli_real_escape_string($koneksi, $_GET['tgl']);

/** * QUERY UPDATE
 * Mengubah semua status produk karyawan tersebut di tanggal terkait menjadi 1 (Diterima)
 */
$query = "UPDATE stok_harian SET 
            status_setoran = 1 
          WHERE id_karyawan = '$id_karyawan' 
          AND DATE(tanggal) = '$tgl'";

if(mysqli_query($koneksi, $query)){
    // Berhasil, balik ke halaman monitoring
    header("location:data_setoran.php?pesan=berhasil_diterima");
} else {
    // Tampilkan error jika query gagal
    echo "Gagal konfirmasi database: " . mysqli_error($koneksi);
}
?>