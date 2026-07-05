<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

// 1. Ambil Data List Karyawan & Menu
$karyawan_list = mysqli_query($koneksi, "SELECT * FROM karyawan ORDER BY no_sepeda ASC");
$menu_list = mysqli_query($koneksi, "SELECT * FROM menu ORDER BY kategori ASC");

// 2. Proses Simpan Stok
if (isset($_POST['simpan_stok'])) {
    $id_karyawan = $_POST['id_karyawan'];
    $tanggal = date('Y-m-d');
    $tipe_update = isset($_POST['tipe_update']) ? $_POST['tipe_update'] : 'set_awal';

    foreach ($_POST['jumlah_menu'] as $id_menu => $jumlah) {
        $jumlah = (int)$jumlah;
        if ($jumlah <= 0) continue; 

        if ($tipe_update == 'set_awal') {
            mysqli_query($koneksi, "DELETE FROM stok_harian WHERE id_karyawan='$id_karyawan' AND id_menu='$id_menu' AND DATE(tanggal)='$tanggal'");
            mysqli_query($koneksi, "INSERT INTO stok_harian (id_karyawan, id_menu, tanggal, jumlah_awal, terjual, sisa_stok) 
                                   VALUES ('$id_karyawan', '$id_menu', '$tanggal', '$jumlah', 0, 0)");
        } else {
            mysqli_query($koneksi, "UPDATE stok_harian SET jumlah_awal = jumlah_awal + $jumlah 
                                   WHERE id_karyawan='$id_karyawan' AND id_menu='$id_menu' AND DATE(tanggal)='$tanggal'");
        }
    }
    echo "<script>alert('Stok Berhasil Disimpan!'); window.location='stok_awal.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Input Stok - PT Swakarsa</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        /* Menggunakan warna background soft mint/cream agar mata tidak lelah */
        body { font-family: 'Segoe UI', sans-serif; background: #4e605b40; padding: 25px 15px; margin: 0; }
        
        /* Card utama dibuat bersih dengan border tipis bernuansa hijau */
        .card { 
            background: white; 
            padding: 30px; 
            border-radius: 14px; 
            box-shadow: 0 10px 25px rgba(46, 125, 50, 0.08); 
            border: 10px solid #032717;
            max-width: 700px; 
            margin: auto; 
        }
        
        /* Judul menggunakan warna hijau gelap yang tegas */
        h2 { text-align: center; color: #1b5e20; margin-top: 0; margin-bottom: 25px; font-weight: 700; }
        
        label strong { color: #02361c; font-size: 20px5px; }

        /* Style elemen Input & Select agar lebih modern & ada efek fokus */
        select, input[type="number"] { 
            padding: 12px; 
            border: 1px solid #ced4da; 
            border-radius: 8px; 
            font-size: 14px;
            color: #333;
            transition: all 0.3s ease;
        }
        select:focus, input[type="number"]:focus {
            border-color: #2e7d32;
            outline: none;
            box-shadow: 0 0 0 3px rgba(46, 125, 50, 0.15);
        }
        
        /* Desain Grid Menu */
        .menu-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px 35px;
            margin-top: 15px;
        }
        
        /* Item menu diselaraskan dengan teks berwarna netral gelap */
        .menu-item { 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            border-bottom: 1px solid #2d2b5a; 
            padding: 10px 0; 
        }
        .menu-item span { color: #0c567b; font-weight: 500; font-size: 15px; }
        
        /* Tombol Simpan Utama (Hijau Sukses Berjaya) */
        .btn-simpan { 
            background: #2e7d32; 
            color: white; 
            border: none; 
            padding: 14px; 
            border-radius: 8px; 
            cursor: pointer; 
            width: 100%; 
            font-weight: bold; 
            font-size: 16px; 
            margin-top: 30px; 
            transition: background 0.2s ease;
        }
        .btn-simpan:hover { background: #1b5e20; }
        
        /* Tombol Kembali disesuaikan menjadi warna outline soft agar tidak merusak fokus mata */
        .btn-kembali { 
            display: block; 
            background: transparent; 
            color: #607d8b; 
            text-align: center; 
            padding: 12px; 
            border-radius: 8px; 
            text-decoration: none; 
            font-weight: bold; 
            margin-top: 12px; 
            font-size: 14px; 
            border: 1px solid #cfd8dc;
            transition: all 0.2s ease;
        }
        .btn-kembali:hover { background: #eceff1; color: #455a64; border-color: #b0bec5; }

        /* Responsif untuk HP */
        @media (max-width: 600px) {
            .menu-grid { grid-template-columns: 1fr; gap: 5px; }
        }
    </style>
</head>
<body>

<div class="card">
    <h2>📦 Input Stok Sepeda</h2>
    <form action="" method="POST">
        <label><strong>Pilih Karyawan:</strong></label><br>
        <select name="id_karyawan" required style="width: 100%; margin: 10px 0;">
            <option value="">-- Pilih Karyawan --</option>
            <?php while($k = mysqli_fetch_assoc($karyawan_list)) { ?>
                <option value="<?= $k['id_karyawan']; ?>"><?= $k['no_sepeda']; ?> - <?= $k['nama_karyawan']; ?></option>
            <?php } ?>
        </select>

        <input type="hidden" name="tipe_update" value="set_awal">

        <h4 style="margin-top:25px; border-left: 4px solid #e67e22; padding-left: 10px; margin-bottom: 5px; color: #2c3e50;">Daftar Menu:</h4>
        
        <div class="menu-grid">
            <?php 
            if ($menu_list && mysqli_num_rows($menu_list) > 0) {
                mysqli_data_seek($menu_list, 0); 
                while($m = mysqli_fetch_assoc($menu_list)) { ?>
                    <div class="menu-item">
                        <span><?= $m['nama_kopi']; ?></span>
                        <input type="number" name="jumlah_menu[<?= $m['id_menu']; ?>]" value="0" min="0" style="width: 70px; text-align: center;">
                    </div>
                <?php } 
            } else {
                echo "<p style='color:#c62828; grid-column: span 2; font-weight: bold;'>Menu kosong! Cek database.</p>";
            } ?>
        </div>

        <button type="submit" name="simpan_stok" class="btn-simpan">SIMPAN DATA STOK ✅</button>
        
        <a href="dashboard_admin.php" class="btn-kembali">⬅️ KEMBALI KE DASHBOARD</a>
    </form>
</div>

</body>
</html>