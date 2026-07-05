<?php
session_start();
include 'koneksi.php';

// Ambil bulan dan tahun saat ini
$bulan_ini = date('m');
$tahun_ini = date('Y');
$nama_bulan = date('F Y');

// Query ambil data penjualan selama bulan ini yang sudah Selesai
$query = "SELECT p.*, k.nama_karyawan 
          FROM penjualan p 
          JOIN karyawan k ON p.id_karyawan = k.id_karyawan 
          WHERE MONTH(p.tanggal) = '$bulan_ini' 
          AND YEAR(p.tanggal) = '$tahun_ini' 
          AND p.status = 'Selesai'
          ORDER BY p.tanggal ASC";
$result = mysqli_query($koneksi, $query);

// Hitung Total Bulanan
$total_query = mysqli_query($koneksi, "SELECT SUM(total_omzet) as omzet, SUM(nominal_tunai) as tunai, SUM(nominal_transfer) as transfer 
                                       FROM penjualan 
                                       WHERE MONTH(tanggal) = '$bulan_ini' 
                                       AND YEAR(tanggal) = '$tahun_ini' 
                                       AND status = 'Selesai'");
$t = mysqli_fetch_assoc($total_query);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Rekap Bulanan - PT Swakarsa</title>
    <style>
        body { font-family: sans-serif; padding: 30px; background: #f9f9f9; }
        .card { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
        th { background: #2c3e50; color: white; }
        .summary-box { display: flex; gap: 20px; margin-top: 20px; }
        .box { flex: 1; padding: 15px; border-radius: 8px; color: white; }
        .btn-print { background: #27ae60; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>

<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center;">
        <h2>LAPORAN BULANAN: <?php echo strtoupper($nama_bulan); ?></h2>
        <div class="no-print">
            <button onclick="window.print()" class="btn-print">🖨️ Cetak Laporan</button>
            <a href="dashboard_admin.php" class="btn-print" style="background: #7f8c8d;">Kembali</a>
        </div>
    </div>

    <div class="summary-box">
        <div class="box" style="background: #27ae60;">
            <small>Total Tunai</small>
            <h3>Rp <?php echo number_format($t['tunai']); ?></h3>
        </div>
        <div class="box" style="background: #2980b9;">
            <small>Total Transfer</small>
            <h3>Rp <?php echo number_format($t['transfer']); ?></h3>
        </div>
        <div class="box" style="background: #d35400;">
            <small>Grand Total Omzet</small>
            <h3>Rp <?php echo number_format($t['omzet']); ?></h3>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Karyawan</th>
                <th>Tunai</th>
                <th>Transfer</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = mysqli_fetch_assoc($result)) : ?>
            <tr>
                <td><?php echo date('d/m/Y', strtotime($row['tanggal'])); ?></td>
                <td><?php echo $row['nama_karyawan']; ?></td>
                <td>Rp <?php echo number_format($row['nominal_tunai']); ?></td>
                <td>Rp <?php echo number_format($row['nominal_transfer']); ?></td>
                <td><b>Rp <?php echo number_format($row['total_omzet']); ?></b></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

</body>
</html>