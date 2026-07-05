<?php 
session_start();
include 'koneksi.php';

if (!isset($_SESSION['status']) || $_SESSION['status'] != 'login') {
    header("Location: index.php?pesan=belum_login");
    exit();
}

$filter_type = isset($_GET['type']) ? $_GET['type'] : 'harian';
$tgl_pilih = isset($_GET['tgl']) ? $_GET['tgl'] : date('Y-m-d');

// Logika Rentang Waktu
if ($filter_type == 'mingguan') {
    $start_date = date('Y-m-d', strtotime('monday this week', strtotime($tgl_pilih)));
    $end_date = date('Y-m-d', strtotime('sunday this week', strtotime($tgl_pilih)));
    $stok_clause = "DATE(tanggal) BETWEEN '$start_date' AND '$end_date'";
    $label_periode = "Mingguan: " . date('d M', strtotime($start_date)) . " s/d " . date('d M Y', strtotime($end_date));
} elseif ($filter_type == 'bulanan') {
    $bulan = date('m', strtotime($tgl_pilih));
    $tahun = date('Y', strtotime($tgl_pilih));
    $stok_clause = "MONTH(tanggal) = '$bulan' AND YEAR(tanggal) = '$tahun'";
    $label_periode = "Bulanan: " . date('F Y', strtotime($tgl_pilih));
} else {
    $stok_clause = "DATE(tanggal) = '$tgl_pilih'";
    $label_periode = "Harian: " . date('d F Y', strtotime($tgl_pilih));
}

// QUERY UTAMA
$query = "SELECT 
            k.id_karyawan, 
            k.nama_karyawan, 
            rekap.total_stok,
            rekap.total_tunai,
            rekap.total_transfer,
            rekap.foto_bukti
          FROM karyawan k
          LEFT JOIN (
            SELECT 
                id_karyawan,
                SUM(jumlah_awal) as total_stok,
                MAX(nominal_tunai) as total_tunai,
                MAX(nominal_transfer) as total_transfer,
                MAX(bukti_transfer) as foto_bukti 
            FROM stok_harian 
            WHERE $stok_clause
            GROUP BY id_karyawan
          ) rekap ON k.id_karyawan = rekap.id_karyawan
          ORDER BY k.nama_karyawan ASC";

$result = mysqli_query($koneksi, $query);

$data_rekap = [];
while($row = mysqli_fetch_assoc($result)) {
    $tunai = (float)($row['total_tunai'] ?? 0);
    $transfer = (float)($row['total_transfer'] ?? 0);
    $omzet = $tunai + $transfer;

    $data_rekap[] = [
        'id' => $row['id_karyawan'],
        'nama' => $row['nama_karyawan'],
        'stok' => $row['total_stok'] ?? 0,
        'omzet' => $omzet,
        'foto' => $row['foto_bukti']
    ];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Stok - PT Swakarsa</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; margin: 0; display: flex; background: #f4f7f6; }
        .sidebar { width: 240px; height: 100vh; background: #1a252f; color: white; padding: 20px; position: fixed; }
        .sidebar h2 { color: #e67e22; text-align: center; margin-bottom: 30px; border-bottom: 2px solid #2c3e50; padding-bottom: 10px; }
        .sidebar nav a { color: #bdc3c7; text-decoration: none; display: block; padding: 12px 15px; border-radius: 8px; margin-bottom: 5px; transition: 0.3s; }
        .sidebar nav a:hover, .sidebar nav a.active { background: #e67e22; color: white; }
        
        .main-content { margin-left: 260px; padding: 30px; width: calc(100% - 260px); }
        .header-box { background: white; padding: 20px; border-radius: 12px; border-left: 6px solid #e67e22; margin-bottom: 25px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .filter-card { background: white; padding: 15px; border-radius: 10px; margin-bottom: 25px; display: flex; gap: 10px; align-items: center; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        
        .table-wrapper { background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
        table { width: 100%; border-collapse: collapse; }
        th { background: #2c3e50; color: white; padding: 15px; text-align: left; }
        td { padding: 15px; border-bottom: 1px solid #eee; font-size: 14px; }
        
        .img-preview { width: 50px; height: 50px; object-fit: cover; border-radius: 6px; border: 1px solid #ddd; }
        .btn-detail { text-decoration: none; color: #2980b9; font-weight: bold; font-size: 13px; border: 1px solid #2980b9; padding: 5px 10px; border-radius: 5px; transition: 0.2s; }
        .btn-detail:hover { background: #2980b9; color: white; }
        .btn-print { background: #27ae60; color: white; border: none; padding: 10px 20px; border-radius: 8px; cursor: pointer; margin-left: auto; font-weight: bold; }
        
        @media print { .sidebar, .filter-card, .btn-detail { display: none; } .main-content { margin: 0; width: 100%; } }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>SWAKARSA</h2>
    <nav>
        <a href="dashboard_admin.php">📊 Dashboard</a>
        <a href="stok_awal.php">📦 Input Stok</a>
        <a href="laporan_stok.php" class="active">🔍 Laporan Stok</a>
        <a href="data_setoran.php">💰 Setoran</a>
        <a href="logout.php" style="color:#e74c3c; margin-top:30px;">🚪 Keluar</a>
    </nav>
</div>

<div class="main-content">
    <div class="header-box">
        <h2 style="margin:0;">Laporan Stok & Penjualan</h2>
        <p style="color:#7f8c8d; margin-top:5px;">Periode: <strong><?php echo $label_periode; ?></strong></p>
    </div>

    <div class="filter-card">
        <form method="GET" action="laporan_stok.php" style="display:flex; gap:10px;">
            <select name="type" style="padding:10px; border:1px solid #ddd; border-radius:8px;">
                <option value="harian" <?php echo $filter_type == 'harian' ? 'selected' : ''; ?>>Harian</option>
                <option value="mingguan" <?php echo $filter_type == 'mingguan' ? 'selected' : ''; ?>>Mingguan</option>
                <option value="bulanan" <?php echo $filter_type == 'bulanan' ? 'selected' : ''; ?>>Bulanan</option>
            </select>
            <input type="date" name="tgl" value="<?php echo $tgl_pilih; ?>" style="padding:10px; border:1px solid #ddd; border-radius:8px;">
            <button type="submit" style="background:#e67e22; color:white; border:none; padding:10px 20px; border-radius:8px; cursor:pointer; font-weight:bold;">Filter</button>
        </form>
        <button onclick="window.print()" class="btn-print">🖨️ Cetak</button>
    </div>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Nama Karyawan</th>
                    <th style="text-align:center;">Stok (Cup)</th>
                    <th style="text-align:right;">Total Omzet</th>
                    <th style="text-align:center;">Bukti</th>
                    <th style="text-align:center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $total_akhir = 0;
                foreach($data_rekap as $rekap) { 
                    $total_akhir += $rekap['omzet'];
                ?>
                <tr>
                    <td><strong><?php echo $rekap['nama']; ?></strong></td>
                    <td align="center"><?php echo number_format($rekap['stok'], 0, ',', '.'); ?></td>
                    <td align="right"><strong>Rp <?php echo number_format($rekap['omzet'], 0, ',', '.'); ?></strong></td>
                    <td align="center">
                        <?php 
                        $foto = trim($rekap['foto']);
                        $path = "img_bukti/" . $foto;
                        $ext = strtolower(pathinfo($foto, PATHINFO_EXTENSION));

                        if(!empty($foto) && file_exists($path)): ?>
                            <?php if($ext == 'heic'): ?>
                                <small style="color:#e67e22;">📱 HEIC</small>
                            <?php else: ?>
                                <a href="<?php echo $path; ?>" target="_blank">
                                    <img src="<?php echo $path; ?>" class="img-preview">
                                </a>
                            <?php endif; ?>
                        <?php else: ?>
                            <small style="color:#ccc;">-</small>
                        <?php endif; ?>
                    </td>
                    <td align="center">
                        <a href="verifikasi_detail.php?id=<?php echo $rekap['id']; ?>&tgl=<?php echo $tgl_pilih; ?>" class="btn-detail">Detail</a>
                    </td>
                </tr>
                <?php } ?>
                <tr style="background:#2c3e50; color:white; font-weight:bold;">
                    <td colspan="2" style="text-align:right;">TOTAL KESELURUHAN:</td>
                    <td style="text-align:right;">Rp <?php echo number_format($total_akhir, 0, ',', '.'); ?></td>
                    <td colspan="2"></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>