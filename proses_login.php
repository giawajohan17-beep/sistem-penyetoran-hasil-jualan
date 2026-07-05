<?php
session_start();
include 'koneksi.php';

if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = mysqli_real_escape_string($koneksi, $_POST['password']);

    // --- PINTU 1: Cek di Tabel Users (Biasanya Akun Admin) ---
    $query_u = "SELECT * FROM users WHERE username='$username' AND password='$password'";
    $res_u = mysqli_query($koneksi, $query_u);

    if (mysqli_num_rows($res_u) > 0) {
        $data = mysqli_fetch_assoc($res_u);
        
        $_SESSION['status']        = "login";
        $_SESSION['username']      = $data['username'];
        $_SESSION['role']          = strtolower(trim($data['role']));
        $_SESSION['id_user']       = $data['id_user'];
        // Jika admin tidak punya id_karyawan, kita set 0 agar tidak error di dashboard
        $_SESSION['id_karyawan']   = isset($data['id_karyawan']) ? $data['id_karyawan'] : 0;

        if ($_SESSION['role'] == "admin") {
            header("Location: dashboard_admin.php");
        } else {
            header("Location: dashboard_karyawan.php");
        }
        exit();
    }

    // --- PINTU 2: Cek di Tabel Karyawan (Jika di tabel users tidak ada) ---
    $query_k = "SELECT * FROM karyawan WHERE username='$username' AND password='$password'";
    $res_k = mysqli_query($koneksi, $query_k);

    if (mysqli_num_rows($res_k) > 0) {
        $data = mysqli_fetch_assoc($res_k);
        
        $_SESSION['status']        = "login";
        $_SESSION['id_karyawan']   = $data['id_karyawan'];
        $_SESSION['username']      = $data['username'];
        $_SESSION['nama_karyawan'] = $data['nama_karyawan'];
        $_SESSION['role']          = "karyawan"; // Paksa role jadi karyawan

        header("Location: dashboard_karyawan.php");
        exit();
    }

    // --- JIKA KEDUANYA GAGAL ---
    echo "<script>alert('Username atau Password Salah!'); window.location='index.php';</script>";
    exit();

} else {
    header("Location: index.php");
    exit();
}
?>