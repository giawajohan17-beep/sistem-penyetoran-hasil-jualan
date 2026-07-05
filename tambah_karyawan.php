<?php
session_start();
include 'koneksi.php';

// Proteksi Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if (isset($_POST['simpan'])) {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $unit = mysqli_real_escape_string($koneksi, $_POST['unit']);
    $user = mysqli_real_escape_string($koneksi, $_POST['user']);
    $pass = mysqli_real_escape_string($koneksi, $_POST['pass']); 

    // Query sekarang menggunakan nama kolom yang pasti (username & password)
    // status_aktif diisi 1 (aktif) secara default
    $query = "INSERT INTO karyawan (nama_karyawan, no_sepeda, username, password, role, status_aktif) 
              VALUES ('$nama', '$unit', '$user', '$pass', 'karyawan', '1')";
    
    if (mysqli_query($koneksi, $query)) {
        echo "<script>alert('Karyawan Berhasil Ditambah!'); window.location='data_karyawan.php';</script>";
    } else {
        echo "<div style='color:red; background:#ffdada; padding:15px; border:1px solid red; margin:10px; border-radius:10px;'>
                <strong>Error Simpan:</strong> " . mysqli_error($koneksi) . "
              </div>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Karyawan - Swakarsa</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f4f7f6; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .card { background: white; padding: 30px; border-radius: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); width: 90%; max-width: 350px; }
        h2 { color: #34495e; text-align: center; margin: 0 0 20px 0; }
        label { font-size: 0.85em; color: #7f8c8d; font-weight: bold; }
        input { width: 100%; padding: 12px; margin: 8px 0 15px 0; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box; outline: none; }
        input:focus { border-color: #e67e22; }
        button { width: 100%; padding: 12px; background: #e67e22; border: none; color: white; border-radius: 8px; cursor: pointer; font-weight: bold; font-size: 1em; transition: 0.3s; }
        button:hover { background: #d35400; }
        .back { display: block; text-align: center; margin-top: 15px; color: #7f8c8d; text-decoration: none; font-size: 0.9em; }
    </style>
</head>
<body>
    <div class="card">
        <h2>Tambah Karyawan</h2>
        <form method="POST">
            <label>Nama Lengkap</label>
            <input type="text" name="nama" placeholder="Contoh: Surya Ramadhan" required>
            
            <label>Nomor Unit Sepeda</label>
            <input type="text" name="unit" placeholder="Contoh: 02" required>
            
            <label>Username Login</label>
            <input type="text" name="user" placeholder="Buat username" required>
            
            <label>Password</label>
            <input type="password" name="pass" placeholder="Buat password" required>
            
            <button type="submit" name="simpan">SIMPAN DATA</button>
            <a href="data_karyawan.php" class="back">← Kembali ke Daftar</a>
        </form>
    </div>
</body>
</html>