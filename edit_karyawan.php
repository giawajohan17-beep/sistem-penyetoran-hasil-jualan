<?php
session_start();
include 'koneksi.php';

$id = $_GET['id'];
$data = mysqli_query($koneksi, "SELECT * FROM karyawan WHERE id_karyawan = '$id'");
$row = mysqli_fetch_assoc($data);

if (isset($_POST['update'])) {
    $nama = $_POST['nama'];
    $unit = $_POST['unit'];
    $user = $_POST['user'];
    $pass = $_POST['pass'];

    $sql = "UPDATE karyawan SET 
            nama_karyawan='$nama', 
            no_sepeda='$unit', 
            username='$user', 
            password='$pass' 
            WHERE id_karyawan='$id'";

    if (mysqli_query($koneksi, $sql)) {
        echo "<script>alert('Data Berhasil Diupdate!'); window.location='data_karyawan.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Karyawan - Swakarsa</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f4f7f6; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .card { background: white; padding: 30px; border-radius: 15px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); width: 350px; }
        input { width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box; }
        button { width: 100%; padding: 12px; background: #2980b9; border: none; color: white; border-radius: 8px; cursor: pointer; font-weight: bold; }
    </style>
</head>
<body>
    <div class="card">
        <h2 style="text-align:center;">Edit Data</h2>
        <form method="POST">
            <label>Nama Karyawan</label>
            <input type="text" name="nama" value="<?php echo $row['nama_karyawan']; ?>" required>
            <label>Nomor Unit</label>
            <input type="text" name="unit" value="<?php echo $row['no_sepeda']; ?>" required>
            <label>Username</label>
            <input type="text" name="user" value="<?php echo $row['username']; ?>" required>
            <label>Password</label>
            <input type="text" name="pass" value="<?php echo $row['password']; ?>" required>
            <button type="submit" name="update">SIMPAN PERUBAHAN</button>
            <a href="data_karyawan.php" style="display:block; text-align:center; margin-top:15px; color:#7f8c8d; text-decoration:none;">Batal</a>
        </form>
    </div>
</body>
</html>