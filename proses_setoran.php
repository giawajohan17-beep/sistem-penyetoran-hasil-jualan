<?php
session_start();
// Tetap aktifkan ini untuk debugging jika masih ada kendala
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'koneksi.php';

if (isset($_POST['simpan'])) {
    
    // 1. Ambil data utama
    $id_karyawan = $_SESSION['id_karyawan'] ?? $_POST['id_karyawan'];
    $tgl = date('Y-m-d'); 
    $nominal_tunai = (int)($_POST['nominal_tunai'] ?? 0);
    $nominal_transfer = (int)($_POST['nominal_transfer'] ?? 0);
    
    // 2. AMANKAN DATA FOTO (Sangat Penting!)
    // mysqli_real_escape_string wajib digunakan agar karakter khusus di Base64 tidak merusak query
    $bukti_base64 = "";
    if (!empty($_POST['banyak_bukti_base64'])) {
        $bukti_base64 = mysqli_real_escape_string($koneksi, $_POST['banyak_bukti_base64']);
    }

    if (isset($_POST['terjual']) && is_array($_POST['terjual'])) {
        $berhasil = false;

        foreach ($_POST['terjual'] as $id_stok => $laku) {
            $laku = (int)$laku;
            $id_stok = mysqli_real_escape_string($koneksi, $id_stok);

            // Ambil Stok Awal
            $res = mysqli_query($koneksi, "SELECT jumlah_awal FROM stok_harian WHERE id_stok = '$id_stok'");
            $data = mysqli_fetch_assoc($res);
            
            if ($data) {
                $awal = (int)$data['jumlah_awal'];
                $sisa = $awal - $laku;

                // 3. Update database dengan query yang sudah diamankan
                $query_update = "UPDATE stok_harian SET 
                                    terjual = '$laku', 
                                    sisa_stok = '$sisa', 
                                    nominal_tunai = '$nominal_tunai', 
                                    nominal_transfer = '$nominal_transfer', 
                                    bukti_transfer = '$bukti_base64', 
                                    status_setoran = 1 
                                 WHERE id_stok = '$id_stok'";
                
                if (mysqli_query($koneksi, $query_update)) {
                    $berhasil = true;
                } else {
                    // Jika gagal, tampilkan error spesifik database
                    die("Gagal Update: " . mysqli_error($koneksi));
                }
            }
        }

        if ($berhasil) {
            // PROSES LOGOUT OTOMATIS
            session_unset();
            session_destroy();

            echo "<script>
                alert('Laporan berhasil terkirim! Anda otomatis logout demi keamanan.'); 
                window.location='index.php'; 
            </script>";
            exit();
        }
    } else {
        echo "<script>alert('Data terjual tidak ditemukan.'); window.history.back();</script>";
    }
} else {
    header("Location: index.php");
    exit();
}
?>