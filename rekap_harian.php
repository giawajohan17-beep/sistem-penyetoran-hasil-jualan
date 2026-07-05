<?php
session_start();
include 'koneksi.php';

$hari_ini = date('Y-m-d');

// 1. Ambil data setoran masuk hari ini
$query = "SELECT p.*, k.nama_karyawan 
          FROM penjualan p 
          JOIN karyawan k ON p.id_karyawan = k.id_karyawan 
          WHERE DATE(p.tanggal) = '$hari_ini' AND p.status = 'Selesai'";
$result = mysqli_query($koneksi, $query);

// 2. Hitung Total Keseluruhan
$sql_total = "SELECT SUM(total_omzet) as omzet, SUM(nominal_tunai) as tunai, SUM(nominal_transfer) as transfer 
              FROM penjualan WHERE DATE(tanggal) = '$hari_ini' AND status = 'Selesai'";
$total_semua = mysqli_query($koneksi, $sql_total);

if (!$total_semua) {
    die("Error Database: " . mysqli_error($koneksi));
}

$t = mysqli_fetch_assoc($total_semua);

// PENTING: Tutup tag PHP sebelum masuk ke HTML
?>

<!DOCTYPE html>
<html>
<head>
    <title>Rekap Harian - PT Swakarsa</title>
    <style>
        body { font-family: sans-serif; padding: 20px; color: #333; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background: #f4f4f4; }
        .total-section { background: #2c3e50; color: white; padding: 15px; border-radius: 8px; }
        .btn-print { background: #27ae60; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>

<div class="header" style="text-align: center;">
    <h2>REKAP PENJUALAN HARIAN</h2>
    <p>PT. SWAKARSA ABADI</p>
    <p>Tanggal: <b><?php echo date('d F Y', strtotime($hari_ini)); ?></b></p>
</div>

<div class="no-print" style="margin-bottom: 20px; text-align: right;">
    <a href="dashboard_admin.php" class="btn-print" style="background: #7f8c8d;">Kembali</a>
    <button onclick="window.print()" class="btn-print">🖨️ Cetak Laporan</button>
</div>

<table>
    <thead>
        <tr>
            <th>Karyawan</th>
            <th>Tunai</th>
            <th>Transfer</th>
            <th>Total</th>
        </tr>
    </thead>
    <tbody>
        <?php if (mysqli_num_rows($result) > 0) { ?>
            <?php while($row = mysqli_fetch_assoc($result)) : ?>
            <tr>
                <td><?php echo $row['nama_karyawan']; ?></td>
                <td>Rp <?php echo number_format($row['nominal_tunai']); ?></td>
                <td>Rp <?php echo number_format($row['nominal_transfer']); ?></td>
                <td><b>Rp <?php echo number_format($row['total_omzet']); ?></b></td>
            </tr>
            <?php endwhile; ?>
        <?php } else { ?>
            <tr><td colspan="4" style="text-align:center;">Belum ada data setoran selesai hari ini.</td></tr>
        <?php } ?>
    </tbody>
</table>

<div class="total-section">
    <p>Total Tunai: Rp <?php echo number_format($t['tunai'] ?? 0); ?></p>
    <p>Total Transfer: Rp <?php echo number_format($t['transfer'] ?? 0); ?></p>
    <hr>
    <h3>Grand Total: Rp <?php echo number_format($t['omzet'] ?? 0); ?></h3>
</div>

</body>
</html>