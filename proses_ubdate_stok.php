<?php
session_start();
include 'koneksi.php';

if (isset($_POST['id_menu']) && isset($_POST['jumlah'])) {
    
    $id_menu = mysqli_real_escape_string($koneksi, $_POST['id_menu']);
    $jumlah  = (int)$_POST['jumlah'];
    $tipe    = mysqli_real_escape_string($koneksi, $_POST['tipe']);
    $ket     = isset($_POST['keterangan']) ? mysqli_real_escape_string($koneksi, $_POST['keterangan']) : "Update Stok Admin";

    // 1. Cek stok saat ini dulu (penting untuk tipe 'keluar')
    $cek_stok = mysqli_query($koneksi, "SELECT jumlah_awal FROM menu WHERE id_menu = '$id_menu'");
    $data_stok = mysqli_fetch_assoc($cek_stok);
    $stok_sekarang = $data_stok['jumlah_awal'];

    if ($tipe == 'keluar' && $jumlah > $stok_sekarang) {
        // Jika mau ngurangin 10 tapi stok cuma ada 5, batalkan!
        echo "<script>alert('Gagal! Stok tidak mencukupi untuk dikurangi.'); window.location='data_stok.php';</script>";
        exit();
    }

    // Mulai Transaksi Database (Menjamin kedua query berhasil semua atau gagal semua)
    mysqli_begin_transaction($koneksi);

    try {
        // A. Catat Riwayat
        mysqli_query($koneksi, "INSERT INTO log_stok (id_menu, jumlah, tipe, keterangan) 
                               VALUES ('$id_menu', '$jumlah', '$tipe', '$ket')");

        // B. Update Stok di Tabel Menu
        if ($tipe == 'masuk') {
            mysqli_query($koneksi, "UPDATE menu SET jumlah_awal = jumlah_awal + $jumlah WHERE id_menu = '$id_menu'");
        } else {
            mysqli_query($koneksi, "UPDATE menu SET jumlah_awal = jumlah_awal - $jumlah WHERE id_menu = '$id_menu'");
        }

        // Jika sampai sini tidak ada error, simpan perubahan secara permanen
        mysqli_commit($koneksi);
        header("Location: data_stok.php?pesan=berhasil");

    } catch (Exception $e) {
        // Jika ada yang gagal, batalkan semua perubahan
        mysqli_rollback($koneksi);
        echo "Gagal memperbarui stok: " . $e->getMessage();
    }

} else {
    header("Location: data_stok.php");
}
?>