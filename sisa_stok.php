<?php
session_start();
include 'koneksi.php'; 

if (!isset($_SESSION['status']) || $_SESSION['status'] != 'login') {
    header("Location: index.php");
    exit();
}

// Ambil tanggal filter
$tgl_pilih = isset($_GET['tgl']) ? $_GET['tgl'] : date('Y-m-d');

// QUERY DIPERBAIKI: Mengambil MAX(bukti_transfer) agar jika ada salah satu yang terisi, 
// admin tahu bahwa karyawan sudah upload bukti.
$query = "SELECT 
            k.id_karyawan,
            k.nama_karyawan, 
            COUNT(s.id_menu) as total_jenis_kopi,
            SUM(s.sisa_stok) as total_sisa,
            MAX(s.status_setoran) as status_akhir,
            MAX(s.bukti_transfer) as file_bukti
          FROM stok_harian s
          INNER JOIN karyawan k ON s.id_karyawan = k.id_karyawan
          WHERE DATE(s.tanggal) = '$tgl_pilih'
          GROUP BY k.id_karyawan
          ORDER BY k.nama_karyawan ASC";

$result = mysqli_query($koneksi, $query);

if (!$result) {
    die("Kesalahan Query Database: " . mysqli_error($koneksi));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Retur - Swakarsa</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: 'Segoe UI', Tahoma, sans-serif; background: #f4f7f6; padding: 15px; margin: 0; }
        .container { background: white; padding: 20px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); max-width: 600px; margin: auto; }
        h2 { color: #2c3e50; border-bottom: 3px solid #e67e22; padding-bottom: 10px; text-align: center; margin-top: 0; }
        .filter-box { margin-bottom: 20px; background: #f9f9f9; padding: 15px; border-radius: 10px; }
        
        .karyawan-list { margin-top: 15px; }
        .karyawan-item { 
            background: #fff; 
            border: 1px solid #eee; 
            margin-bottom: 12px; 
            padding: 15px; 
            border-radius: 10px; 
            display: flex; 
            justify-content: space-between; 
            align-items: center;
            text-decoration: none;
            color: #2c3e50;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
            transition: all 0.2s ease;
        }
        .item-selesai { border-left: 5px solid #27ae60; background: #f0fff4; }
        .item-pending { border-left: 5px solid #f1c40f; }
        
        .karyawan-item:hover { 
            border-color: #e67e22; 
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.05);
        }
        .nama { font-size: 1.1em; font-weight: bold; color: #2c3e50; display: flex; align-items: center; gap: 5px; }
        .info-singkat { font-size: 0.85em; color: #7f8c8d; margin-top: 4px; }
        .total-badge { background: #e67e22; color: white; padding: 2px 8px; border-radius: 20px; font-weight: bold; }
        .status-cek { color: #27ae60; font-weight: bold; }
        .status-foto { font-size: 0.8em; color: #2980b9; font-weight: bold; margin-top: 3px; display: block; }
        .btn-lihat { font-weight: bold; font-size: 0.8em; border: 1px solid #e67e22; padding: 6px 12px; border-radius: 5px; background: white; color: #e67e22; }
    </style>
</head>
<body>

<div class="container">
    <h2>📊 Retur Harian</h2>
    
    <div class="filter-box">
        <form method="GET" style="display: flex; gap: 8px; width: 100%;">
            <input type="date" name="tgl" value="<?php echo $tgl_pilih; ?>" style="flex: 1; padding: 10px; border-radius: 8px; border: 1px solid #ddd;">
            <button type="submit" style="background:#e67e22; color:white; border:none; padding:10px 15px; border-radius:8px; cursor:pointer; font-weight: bold;">Cek</button>
        </form>
    </div>

    <div class="karyawan-list">
        <?php 
        if(mysqli_num_rows($result) > 0) {
            while($row = mysqli_fetch_assoc($result)) { 
                $sudah_setor = ($row['status_akhir'] == 1);
                $ada_foto = !empty($row['file_bukti']);
        ?>
            <a href="verifikasi_detail.php?id=<?php echo $row['id_karyawan']; ?>&tgl=<?php echo $tgl_pilih; ?>" 
               class="karyawan-item <?php echo $sudah_setor ? 'item-selesai' : 'item-pending'; ?>">
                <div>
                    <div class="nama">
                        <?php echo $row['nama_karyawan']; ?> 
                        <?php if($sudah_setor) echo "<span class='status-cek'>✅</span>"; ?>
                    </div>
                    <div class="info-singkat">
                        Sisa: <span class="total-badge"><?php echo $row['total_sisa']; ?> Cup</span> 
                        <br>Daftar: <?php echo $row['total_jenis_kopi']; ?> Produk
                        <?php if($ada_foto): ?>
                            <span class="status-foto">📷 Bukti Transfer Tersedia</span>
                        <?php else: ?>
                            <span class="status-foto" style="color: #e74c3c;">❌ Belum Upload Bukti</span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="btn-lihat">
                    <?php echo $sudah_setor ? 'LIHAT' : 'PROSES'; ?>
                </div>
            </a>
        <?php 
            } 
        } else {
            echo "<div style='text-align:center; color:#999; padding:40px;'>
                    <p>📭 Tidak ada data setoran/retur<br>pada tanggal ini.</p>
                  </div>";
        }
        ?>
    </div>

    <hr style="border: 0; border-top: 1px solid #eee; margin: 20px 0;">
    <a href="dashboard_admin.php" style="text-decoration:none; color:#7f8c8d; font-weight: bold; display: block; text-align: center; font-size: 0.9em;">← Kembali ke Dashboard</a>
</div>

</body>
</html>