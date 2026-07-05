<?php
session_start();
include 'koneksi.php';

// 1. Proteksi Admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// 2. Logika Filter Tanggal (Default ke Hari Ini)
$tgl_filter = isset($_GET['tgl_filter']) ? $_GET['tgl_filter'] : date('Y-m-d');
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Setoran Hari Ini - <?php echo $tgl_filter; ?></title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f4f4f4; padding: 20px; }
        .container { max-width: 1200px; margin: auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        h2 { color: #e67e22; border-bottom: 2px solid #e67e22; padding-bottom: 10px; margin-top: 0; }
        .filter-box { margin-bottom: 20px; background: #eee; padding: 15px; border-radius: 5px; display: flex; gap: 10px; align-items: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 14px; }
        th, td { padding: 12px; border: 1px solid #ddd; text-align: left; }
        th { background: #e67e22; color: white; }
        tr:nth-child(even) { background: #f9f9f9; }
        .nominal { font-family: 'Courier New', Courier, monospace; font-weight: bold; }
        .footer-total { background: #2c3e50 !important; color: white; font-weight: bold; }
        .btn { padding: 8px 12px; text-decoration: none; border-radius: 5px; color: white; font-size: 11px; font-weight: bold; display: inline-block; border: none; cursor: pointer; margin-right: 5px; }
        .btn-filter { background: #e67e22; }
        .btn-approve { background: #27ae60; }
        .btn-detail { background: #2980b9; } /* Tombol Biru untuk lihat foto */
        @media print { .filter-box, .btn, .tindakan-col, .back-link { display: none !important; } }
    </style>
</head>
<body>

<div class="container">
    <h2>Laporan Setoran Karyawan (PT Swakarsa)</h2>

    <div class="filter-box">
        <form method="GET" action="">
            <label>Pilih Tanggal:</label>
            <<input type="number" name="terjual[<?= $row['id_stok']; ?>]" value="0" min="0">
            <button type="submit" class="btn btn-filter">Tampilkan Data</button>
        </form>
        <button onclick="window.print()" class="btn btn-print" style="background:#34495e;">🖨️ Cetak PDF</button>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>Nama Karyawan</th>
                <th>Terjual</th>
                <th>Tunai</th>
                <th>Transfer</th>
                <th style="background:#d35400;">Total</th>
                <th>Status</th>
                <th class="tindakan-col">Tindakan & Bukti</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // PERBAIKAN QUERY: Ambil juga kolom bukti_transfer
            $query = "SELECT 
                        sh.id_karyawan, 
                        sh.tanggal, 
                        k.nama_karyawan, 
                        SUM(sh.terjual) as total_cup, 
                        SUM(sh.nominal_tunai) as total_tunai, 
                        SUM(sh.nominal_transfer) as total_transfer,
                        MAX(sh.bukti_transfer) as foto_bukti,
                        sh.status_setoran
                      FROM stok_harian sh 
                      JOIN karyawan k ON sh.id_karyawan = k.id_karyawan 
                      WHERE DATE(sh.tanggal) = '$tgl_filter'
                      GROUP BY sh.id_karyawan
                      ORDER BY k.nama_karyawan ASC";
            
            $sql = mysqli_query($koneksi, $query);

            $grand_total_cup = 0; $grand_total_tunai = 0; $grand_total_transfer = 0;

            if (mysqli_num_rows($sql) == 0) {
                echo "<tr><td colspan='7' style='text-align:center;'>Tidak ada data pada tanggal $tgl_filter</td></tr>";
            } else {
                while($d = mysqli_fetch_assoc($sql)) {
                    $total_per_orang = $d['total_tunai'] + $d['total_transfer'];
                    $grand_total_cup += $d['total_cup'];
                    $grand_total_tunai += $d['total_tunai'];
                    $grand_total_transfer += $d['total_transfer'];
                    $is_approved = ($d['status_setoran'] == 1);
            ?>
            <tr>
                <td><strong><?php echo $d['nama_karyawan']; ?></strong></td>
                <td><?php echo $d['total_cup']; ?> Cup</td>
                <td class="nominal">Rp <?php echo number_format($d['total_tunai'], 0, ',', '.'); ?></td>
                <td class="nominal">Rp <?php echo number_format($d['total_transfer'], 0, ',', '.'); ?></td>
                <td class="nominal" style="background: #fdf2e9;">Rp <?php echo number_format($total_per_orang, 0, ',', '.'); ?></td>
                <td><?php echo $is_approved ? '✅ Approved' : '⏳ Pending'; ?></td>
                <td class="tindakan-col">
                    <a href="verifikasi_detail.php?id=<?php echo $d['id_karyawan']; ?>&tgl=<?php echo $tgl_filter; ?>" class="btn btn-detail">🔍 Lihat Bukti</a>

                    <?php if (!$is_approved) : ?>
                        <a href="proses_terima.php?id=<?php echo $d['id_karyawan']; ?>&tgl=<?php echo $tgl_filter; ?>" class="btn btn-approve" onclick="return confirm('Yakin terima setoran ini?')">Terima</a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php } ?>

            <tr class="footer-total">
                <td colspan="2" style="text-align: right;">GRAND TOTAL:</td>
                <td><?php echo $grand_total_cup; ?> Cup</td>
                <td>Rp <?php echo number_format($grand_total_tunai, 0, ',', '.'); ?></td>
                <td>Rp <?php echo number_format($grand_total_transfer, 0, ',', '.'); ?></td>
                <td colspan="2" style="background:#d35400; text-align:center;">
                    Total Omzet: Rp <?php echo number_format($grand_total_tunai + $grand_total_transfer, 0, ',', '.'); ?>
                </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
    
    <p class="back-link"><a href="dashboard_admin.php" style="color: #e67e22; font-weight: bold;">« Kembali ke Dashboard</a></p>
</div>

</body>
</html>