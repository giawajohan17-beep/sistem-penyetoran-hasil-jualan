<?php 
session_start();
date_default_timezone_set('Asia/Jakarta');
include 'koneksi.php';

if (!isset($_SESSION['status']) || $_SESSION['status'] != 'login') {
    header("Location: index.php?pesan=belum_login");
    exit();
}

$filter_type = isset($_GET['type']) ? $_GET['type'] : 'harian';
$tgl_pilih = isset($_GET['tgl']) ? $_GET['tgl'] : date('Y-m-d');

// Logika Penentuan Rentang Waktu
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

// Query Rekap Penjualan
$query = "SELECT 
            k.id_karyawan, 
            k.nama_karyawan, 
            IFNULL(SUM(rekap_harian.jml_stok), 0) as total_stok,
            IFNULL(SUM(rekap_harian.tunai), 0) as total_tunai,
            IFNULL(SUM(rekap_harian.transfer), 0) as total_transfer
          FROM karyawan k
          LEFT JOIN (
            SELECT 
                id_karyawan,
                tanggal,
                SUM(jumlah_awal) as jml_stok,
                MAX(nominal_tunai) as tunai,
                MAX(nominal_transfer) as transfer
            FROM stok_harian 
            WHERE $stok_clause
            GROUP BY id_karyawan, DATE(tanggal)
          ) rekap_harian ON k.id_karyawan = rekap_harian.id_karyawan
          GROUP BY k.id_karyawan 
          ORDER BY k.nama_karyawan ASC";

$result = mysqli_query($koneksi, $query);

$labels = []; $data_omzet = []; $grand_tunai = 0; $grand_transfer = 0; $data_rekap = [];

while($row = mysqli_fetch_assoc($result)) {
    $tunai = (float)$row['total_tunai'];
    $transfer = (float)$row['total_transfer'];
    $omzet = $tunai + $transfer;

    $data_rekap[] = [
        'id' => $row['id_karyawan'],
        'nama' => $row['nama_karyawan'],
        'stok' => $row['total_stok'],
        'omzet' => $omzet
    ];

    $labels[] = $row['nama_karyawan']; 
    $data_omzet[] = $omzet;
    $grand_tunai += $tunai;
    $grand_transfer += $transfer;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin - PT Swakarsa Berjaya</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: 'Segoe UI', sans-serif; margin: 0; display: flex; background: #f4f7f6; color: #333; }
        .sidebar { width: 240px; height: 100vh; background: #1a252f; color: white; padding: 20px; position: fixed; }
        .sidebar h2 { color: #e67e22; text-align: center; margin-bottom: 30px; border-bottom: 2px solid #2c3e50; padding-bottom: 10px; font-size: 1.2em; }
        .sidebar nav a { color: #bdc3c7; text-decoration: none; display: block; padding: 12px 15px; border-radius: 8px; margin-bottom: 5px; transition: 0.3s; font-size: 0.9em; }
        .sidebar nav a:hover, .sidebar nav a.active { background: #e67e22; color: white; }
        
        .main-content { margin-left: 260px; padding: 30px; width: calc(100% - 260px); }
        .header-box { background: white; padding: 20px; border-radius: 12px; border-left: 6px solid #e67e22; margin-bottom: 25px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .filter-card { background: white; padding: 15px; border-radius: 10px; margin-bottom: 25px; display: flex; gap: 10px; align-items: center; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }
        
        .chart-container { display: flex; gap: 20px; margin-bottom: 25px; height: 320px; }
        .chart-box { background: white; padding: 20px; border-radius: 12px; flex: 1; box-shadow: 0 4px 6px rgba(0,0,0,0.05); position: relative; }

        .table-wrapper { background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
        table { width: 100%; border-collapse: collapse; }
        th { background: #2c3e50; color: white; padding: 15px; text-align: left; font-size: 0.9em; }
        td { padding: 15px; border-bottom: 1px solid #eee; font-size: 0.9em; }
        tr:hover { background-color: #f9f9f9; }
        
        .btn-detail { background: #3498db; color: white; text-decoration: none; padding: 6px 12px; border-radius: 4px; font-size: 0.8em; font-weight: bold; transition: 0.3s; }
        .btn-detail:hover { background: #2980b9; }
        .btn-print { background: #27ae60; color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; margin-left: auto; font-weight: bold; }

        @media print { .sidebar, .filter-card, .btn-detail { display: none; } .main-content { margin: 0; width: 100%; } }
    </style>
</head>
<body>

<div class="sidebar">
    <h2>PT SWAKARSA</h2>
    <nav>
        <a href="dashboard_admin.php" class="active">📊 Dashboard</a>
        <a href="data_karyawan.php">👥 Data Karyawan</a> <!-- Tambahkan Baris Ini -->
        <a href="stok_awal.php">📦 Input Stok</a>
        <a href="data_stok.php">🔍 Monitoring</a>
        <a href="sisa_stok.php">📉 Retur</a> 
        <a href="data_setoran.php">💰 Setoran</a>
        <a href="logout.php" style="color:#e74c3c; margin-top:30px;">🚪 Keluar</a>
    </nav>
</div>

<div class="main-content">
    <div class="header-box">
        <h2 style="margin:0;">Ringkasan Penjualan</h2>
        <p style="color:#7f8c8d; margin-top:5px;">Periode: <strong><?php echo $label_periode; ?></strong></p>
    </div>

    <div class="filter-card">
        <form method="GET" style="display:flex; gap:10px;">
            <select name="type" style="padding:8px; border:1px solid #ddd; border-radius:6px;">
                <option value="harian" <?php echo $filter_type == 'harian' ? 'selected' : ''; ?>>Harian</option>
                <option value="mingguan" <?php echo $filter_type == 'mingguan' ? 'selected' : ''; ?>>Mingguan</option>
                <option value="bulanan" <?php echo $filter_type == 'bulanan' ? 'selected' : ''; ?>>Bulanan</option>
            </select>
            <input type="date" name="tgl" value="<?php echo $tgl_pilih; ?>" style="padding:8px; border:1px solid #ddd; border-radius:6px;">
            <button type="submit" style="background:#e67e22; color:white; border:none; padding:8px 15px; border-radius:6px; cursor:pointer; font-weight:bold;">Tampilkan</button>
        </form>
        <button onclick="window.print()" class="btn-print">🖨️ Cetak</button>
    </div>

    <div class="chart-container">
        <div class="chart-box">
            <h4 style="margin:0 0 10px 0; text-align:center; font-size:0.9em;">Omzet per Karyawan</h4>
            <canvas id="barChart"></canvas>
        </div>
        <div class="chart-box">
            <h4 style="margin:0 0 10px 0; text-align:center; font-size:0.9em;">Metode Pembayaran</h4>
            <canvas id="pieChart"></canvas>
        </div>
    </div>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Nama Karyawan</th>
                    <th style="text-align:center;">Stok (Cup)</th>
                    <th style="text-align:right;">Total Uang</th>
                    <th style="text-align:center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $total_akhir = 0;
                if(count($data_rekap) > 0){
                    foreach($data_rekap as $rekap) { 
                        $total_akhir += $rekap['omzet'];
                    ?>
                    <tr>
                        <td><strong><?php echo $rekap['nama']; ?></strong></td>
                        <td align="center"><?php echo number_format($rekap['stok'], 0, ',', '.'); ?></td>
                        <td align="right"><strong>Rp <?php echo number_format($rekap['omzet'], 0, ',', '.'); ?></strong></td>
                        <td align="center">
                        <a href="verifikasi_detail.php?id=<?php echo $rekap['id']; ?>&tgl=<?php echo $tgl_pilih; ?>" class="btn-detail">👁️ Detail
                        </a>
                    </tr>
                    <?php } 
                } else {
                    echo "<tr><td colspan='4' align='center'>Data tidak ditemukan untuk periode ini.</td></tr>";
                }
                ?>
                <tr style="background:#2c3e50; color:white; font-weight:bold;">
                    <td colspan="2" style="text-align:right;">TOTAL KESELURUHAN:</td>
                    <td style="text-align:right; font-size:1.1em;">Rp <?php echo number_format($total_akhir, 0, ',', '.'); ?></td>
                    <td></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script>
const commonOptions = { 
    responsive: true, 
    maintainAspectRatio: false,
    plugins: {
        legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 10 } } }
    }
};

new Chart(document.getElementById('barChart'), {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($labels); ?>,
        datasets: [{ 
            label: 'Omzet (Rp)', 
            data: <?php echo json_encode($data_omzet); ?>, 
            backgroundColor: '#e67e22',
            borderRadius: 4
        }]
    },
    options: commonOptions
});

new Chart(document.getElementById('pieChart'), {
    type: 'doughnut',
    data: {
        labels: ['Tunai', 'Transfer/QRIS'],
        datasets: [{ 
            data: [<?php echo $grand_tunai; ?>, <?php echo $grand_transfer; ?>], 
            backgroundColor: ['#27ae60', '#2980b9'],
            borderWidth: 0
        }]
    },
    options: commonOptions
});
</script>
</body>
</html>