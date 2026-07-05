<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$tgl_pilih = isset($_GET['tgl']) ? $_GET['tgl'] : date('Y-m-d');

// QUERY FINAL: Mengambil 'nama_kopi' dari tabel menu (m)
$query = "SELECT 
            k.no_sepeda, 
            k.nama_karyawan, 
            GROUP_CONCAT(CONCAT('• ', m.nama_kopi, ': ', s.jumlah_awal, ' Cup') SEPARATOR '<br>') as rincian_stok,
            SUM(s.jumlah_awal) as total_cup
          FROM stok_harian s
          JOIN karyawan k ON s.id_karyawan = k.id_karyawan 
          JOIN menu m ON s.id_menu = m.id_menu
          WHERE DATE(s.tanggal) = '$tgl_pilih'
          GROUP BY k.id_karyawan
          ORDER BY k.no_sepeda ASC";

$result = mysqli_query($koneksi, $query);

if (!$result) {
    die("<b>Gagal Memuat Data:</b> " . mysqli_error($koneksi));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Monitoring Stok - Swakarsa</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f4f7f6; margin: 0; display: flex; }
        .sidebar { width: 240px; height: 100vh; background: #1a252f; color: white; padding: 20px; position: fixed; }
        .sidebar h2 { color: #e67e22; text-align: center; margin-bottom: 30px; }
        .sidebar nav a { color: #bdc3c7; text-decoration: none; display: block; padding: 12px; border-radius: 8px; margin-bottom: 5px; transition: 0.3s; }
        .sidebar nav a:hover { background: #2c3e50; color: #e67e22; }
        
        .main-content { margin-left: 260px; padding: 30px; width: calc(100% - 260px); }
        .card { background: white; padding: 25px; border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { background: #e67e22; color: white; padding: 15px; text-align: left; }
        td { padding: 15px; border-bottom: 1px solid #eee; vertical-align: top; }
        
        .badge-unit { background: #34495e; color: white; padding: 5px 12px; border-radius: 6px; font-weight: bold; }
        .stok-list { line-height: 1.6; background: #fff9f4; padding: 15px; border-radius: 10px; border-left: 5px solid #e67e22; color: #2c3e50; }
        .total-badge { background: #27ae60; color: white; padding: 6px 15px; border-radius: 50px; font-weight: bold; }
        
        .filter-box { background: #f9f9f9; padding: 15px; border-radius: 10px; margin-bottom: 20px; }
        input[type="date"] { padding: 8px; border-radius: 5px; border: 1px solid #ddd; outline: none; }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>SWAKARSA</h2>
    <nav>
        <a href="dashboard_admin.php">📊 Dashboard</a>
        <a href="stok_awal.php">📦 Stok Harian</a>
        <a href="data_stok.php" style="background: #2c3e50; color: #e67e22; font-weight: bold;">🔍 Monitoring Stok</a>
        <a href="data_setoran.php">💰 Data Setoran</a>
        <a href="logout.php" style="color:#e74c3c; margin-top:30px;">🚪 Keluar</a>
    </nav>
</div>

<div class="main-content">
    <div class="card">
        <h2 style="margin-top:0;">📋 Ringkasan Stok Keluar Unit</h2>
        <p style="color: #7f8c8d;">Data muatan awal harian tiap unit karyawan.</p>
        
        <form method="GET" class="filter-box">
            <label><b>Pilih Tanggal:</b> </label>
            <input type="date" name="tgl" value="<?php echo $tgl_pilih; ?>" onchange="this.form.submit()">
        </form>

        <table>
            <thead>
                <tr>
                    <th width="100">Unit</th>
                    <th width="200">Nama Karyawan</th>
                    <th>Rincian Menu (Stok Awal)</th>
                    <th width="150">Total Muatan</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if(mysqli_num_rows($result) > 0) {
                    while($row = mysqli_fetch_assoc($result)) { 
                ?>
                <tr>
                    <td><span class="badge-unit">Unit <?php echo $row['no_sepeda']; ?></span></td>
                    <td><strong><?php echo $row['nama_karyawan']; ?></strong></td>
                    <td>
                        <div class="stok-list">
                            <?php echo $row['rincian_stok']; ?>
                        </div>
                    </td>
                    <td><span class="total-badge"><?php echo $row['total_cup']; ?> Cup</span></td>
                </tr>
                <?php 
                    } 
                } else {
                    echo "<tr><td colspan='4' style='text-align:center; padding:50px; color:#95a5a6;'>Belum ada data stok untuk tanggal " . date('d-m-Y', strtotime($tgl_pilih)) . ".</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>