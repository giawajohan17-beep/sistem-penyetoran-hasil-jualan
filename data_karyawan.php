<?php
session_start();
include 'koneksi.php';

// Proteksi Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$query = mysqli_query($koneksi, "SELECT * FROM karyawan ORDER BY no_sepeda ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Karyawan - PT Swakarsa</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f4f7f6; margin: 0; display: flex; }
        .sidebar { width: 240px; height: 100vh; background: #1a252f; color: white; padding: 20px; position: fixed; }
        .sidebar a { color:white; text-decoration:none; display:block; padding:10px 0; transition: 0.3s; }
        .sidebar a:hover { color: #e67e22; }
        
        .main-content { margin-left: 260px; padding: 30px; width: calc(100% - 260px); }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .btn-tambah { background: #e67e22; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px; font-weight: bold; }
        
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 10px; overflow: hidden; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #34495e; color: white; }
        
        .btn-aksi { padding: 6px 12px; border-radius: 4px; text-decoration: none; font-size: 0.85em; color: white; margin-right: 5px; }
        .edit { background: #2980b9; }
        .hapus { background: #e74c3c; }

        /* Responsif HP */
        @media (max-width: 768px) {
            .sidebar { width: 60px; padding: 10px; overflow: hidden; }
            .sidebar h2, .sidebar span { display: none; }
            .main-content { margin-left: 80px; width: calc(100% - 80px); padding: 15px; }
            table { font-size: 0.8em; }
        }
    </style>
</head>
<body>

<div class="sidebar">
    <h2 style="color:#e67e22">SWAKARSA</h2>
    <nav>
        <a href="dashboard_admin.php">📊 <span>Dashboard</span></a>
        <a href="data_karyawan.php" style="font-weight:bold; color:#e67e22;">👥 <span>Data Karyawan</span></a>
        <a href="stok_awal.php">📦 <span>Stok Harian</span></a>
        <a href="data_setoran.php">💰 <span>Data Setoran</span></a>
        <a href="logout.php" style="color:#e74c3c; margin-top:20px;">🚪 <span>Keluar</span></a>
    </nav>
</div>

<div class="main-content">
    <div class="header">
        <h2>Daftar Karyawan Lapangan</h2>
        <a href="tambah_karyawan.php" class="btn-tambah">+ Tambah Karyawan</a>
    </div>

    <table>
        <thead>
            <tr>
                <th>Unit</th>
                <th>Nama Karyawan</th>
                <th>Username</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = mysqli_fetch_assoc($query)) { ?>
            <tr>
                <td><strong>Unit <?php echo $row['no_sepeda']; ?></strong></td>
                <td><?php echo $row['nama_karyawan']; ?></td>
                <td>
                    <?php 
                        // Cek apakah kolom bernama 'username' atau 'user'
                        if(isset($row['username'])) {
                            echo $row['username'];
                        } elseif(isset($row['user'])) {
                            echo $row['user'];
                        } else {
                            echo "<span style='color:red;'>Kolom Tidak Ada</span>";
                        }
                    ?>
                </td>
                <td>
                    <a href="edit_karyawan.php?id=<?php echo $row['id_karyawan']; ?>" class="btn-aksi edit">Edit</a>
                    <a href="hapus_karyawan.php?id=<?php echo $row['id_karyawan']; ?>" class="btn-aksi hapus" onclick="return confirm('Hapus karyawan ini?')">Hapus</a>
                </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

</body>
</html>