<?php
session_start();
include 'koneksi.php';

// Ambil ID dan Tanggal dari URL
$id_karyawan = $_GET['id'] ?? '';
$tgl = $_GET['tgl'] ?? '';

// 1. Ambil data setoran
$query = mysqli_query($koneksi, "SELECT sh.*, k.nama_karyawan 
                                 FROM stok_harian sh 
                                 JOIN karyawan k ON sh.id_karyawan = k.id_karyawan 
                                 WHERE sh.id_karyawan = '$id_karyawan' 
                                 AND DATE(sh.tanggal) = '$tgl' 
                                 LIMIT 1");
$data = mysqli_fetch_assoc($query);

// 2. Ambil detail menu yang laku
$detail_menu = mysqli_query($koneksi, "SELECT sh.*, m.nama_kopi 
                                       FROM stok_harian sh 
                                       JOIN menu m ON sh.id_menu = m.id_menu 
                                       WHERE sh.id_karyawan = '$id_karyawan' 
                                       AND DATE(sh.tanggal) = '$tgl'");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Detail Verifikasi - <?php echo $data['nama_karyawan'] ?? 'Karyawan'; ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        /* Background utama menggunakan warna abu-abu terang yang bersih */
        body { 
            font-family: 'Segoe UI', sans-serif; 
            background: #f1f5f9; 
            color: #1e293b; 
            padding: 25px 15px; 
            margin: 0; 
        }
        
        /* Card utama berwarna putih bersih dengan shadow lembut */
        .card-detail { 
            max-width: 680px; 
            margin: 0 auto; 
            background: #ffffff; 
            padding: 30px; 
            border-radius: 16px; 
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
            border: 1px solid #e2e8f0;
        }
        
        /* Bagian Header Nama Karyawan */
        .header { text-align: center; margin-bottom: 25px; }
        .header h2 { margin: 0; color: #0284c7; font-size: 24px; font-weight: 700; }
        .header small { color: #64748b; font-size: 14px; margin-top: 5px; display: block; font-weight: 500; }
        
        /* Grid Layout untuk Membagi 2 Kolom */
        .menu-grid-container {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 0px 35px;
            margin-top: 15px;
        }

        //* --- DESAIN TABEL MINI DENGAN GARIS KOTAK PEMBATAS PENUH --- */
        .mini-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            /* Memberikan border luar pembungkus tabel */
            border: 1px solid #94a3b8; 
            background: #ffffff;
        }

        /* Desain Baris Kepala Tabel */
        .mini-table th { 
            text-align: left; 
            color: #1e293b; 
            background: #f1f5f9; /* Background kepala tabel sedikit abu-abu */
            padding: 10px 8px; 
            font-size: 11px; 
            text-transform: uppercase; 
            letter-spacing: 0.05em; 
            /* Garis pembatas kotak tebal untuk header */
            border: 1px solid #94a3b8; 
            border-bottom: 2px solid #64748b;
        }

        /* Desain Kolom Isi Tabel (Menampilkan garis pembatas horizontal dan vertikal) */
        .mini-table td { 
            padding: 10px 8px; 
            color: #334155; 
            font-size: 14px; 
            /* Memberikan garis di sekeliling kotak td (atas, bawah, kiri, kanan) */
            border: 1px solid #cbd5e1; 
        }

        /* Membuat warna baris selang-seling agar data makin mudah diverifikasi */
        .mini-table tbody tr:nth-child(even) {
            background-color: #f8fafc;
        }
        
        /* Status Warna Cerah & Kontras Tinggi */
        .status-laku { color: #16a34a; font-weight: 600; } /* Hijau Daun Segar */
        .status-sisa { color: #dc2626; font-weight: 600; } /* Merah Cerah */
        .status-habis { color: #94a3b8; font-weight: 400; } /* Abu-abu terang jika 0 */
        
        /* Box Ringkasan Finansial Cerah */
        .omzet-box { 
            background: #f8fafc; 
            padding: 18px; 
            border-radius: 12px; 
            margin-top: 25px; 
            border: 1px solid #e2e8f0;
        }
        .omzet-row { display: flex; justify-content: space-between; margin-bottom: 8px; color: #475569; font-size: 15px; }
        .omzet-row strong { color: #0f172a; }
        
        /* Total Akhir Menggunakan Aksen Oranye Kopi */
        .grand-total { 
            display: flex; 
            justify-content: space-between; 
            font-size: 18px; 
            color: #ea580c; 
            border-top: 2px dashed #e2e8f0; 
            margin-top: 10px; 
            padding-top: 12px; 
        }
        
        /* Bukti Transfer */
        .foto-container { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 15px; }
        .foto-bukti { 
            width: 100px; height: 100px; object-fit: cover; 
            border-radius: 8px; cursor: pointer; border: 2px solid #cbd5e1;
            transition: all 0.2s ease;
        }
        .foto-bukti:hover { transform: scale(1.05); border-color: #0284c7; }

        /* Modal Preview Gambar (Latar tetap gelap agar foto bukti terlihat kontras) */
        .modal {
            display: none; position: fixed; z-index: 1000; 
            padding-top: 60px; left: 0; top: 0; width: 100%; height: 100%; 
            background-color: rgba(15, 23, 42, 0.9); overflow: auto;
        }
        .modal-content {
            margin: auto; display: block; width: 90%; max-width: 450px;
            border-radius: 12px; animation: zoom 0.2s ease-out;
        }
        @keyframes zoom { from {transform: scale(0.95); opacity: 0;} to {transform: scale(1); opacity: 1;} }
        .close {
            position: absolute; top: 20px; right: 35px; color: #94a3b8;
            font-size: 40px; font-weight: bold; cursor: pointer;
        }

        /* Tombol Kembali Ringan & Bersih */
        .btn-back { 
            display: block; text-align: center; margin-top: 30px; 
            color: #64748b; text-decoration: none; font-weight: 600; 
            font-size: 14px; padding: 12px; border: 1px solid #cbd5e1; 
            border-radius: 8px; transition: all 0.2s ease;
            background: #ffffff;
        }
        .btn-back:hover { background: #f8fafc; color: #1e293b; border-color: #94a3b8; }

        /* Otomatis 1 kolom jika dibuka di layar HP */
        @media (max-width: 650px) {
            .menu-grid-container { grid-template-columns: 1fr; gap: 0px; }
        }
    </style>
</head>
<body>

<div class="card-detail">
    <div class="header">
        <h2>📊 <?php echo htmlspecialchars($data['nama_karyawan'] ?? 'Data Tidak Ditemukan'); ?></h2>
        <small>📅 <?php echo !empty($tgl) ? date('d F Y', strtotime($tgl)) : ''; ?></small>
    </div>

    <div class="menu-grid-container">
        <?php 
        if ($detail_menu && mysqli_num_rows($detail_menu) > 0) {
            $all_items = [];
            while($row = mysqli_fetch_assoc($detail_menu)) {
                $all_items[] = $row;
            }
            $total_menu = count($all_items);
            $setengah_menu = ceil($total_menu / 2);

            // --- KOLOM KIRI ---
            echo '<table class="mini-table">';
            echo '<thead><tr><th>MENU</th><th class="text-center">AWAL</th><th class="text-center">LAKU</th><th class="text-center">SISA</th></tr></thead>';
            echo '<tbody>';
            for ($i = 0; $i < $setengah_menu; $i++) {
                $m = $all_items[$i];
                $sisa_style = ($m['sisa_stok'] == 0) ? 'status-habis' : 'status-sisa';
                echo '<tr>';
                echo '<td>'.htmlspecialchars($m['nama_kopi']).'</td>';
                echo '<td class="text-center">'.$m['jumlah_awal'].'</td>';
                echo '<td class="text-center status-laku">'.$m['terjual'].'</td>';
                echo '<td class="text-center '.$sisa_style.'">'.$m['sisa_stok'].'</td>';
                echo '</tr>';
            }
            echo '</tbody></table>';

            // --- KOLOM KANAN ---
            echo '<table class="mini-table">';
            echo '<thead><tr><th>MENU</th><th class="text-center">AWAL</th><th class="text-center">LAKU</th><th class="text-center">SISA</th></tr></thead>';
            echo '<tbody>';
            for ($i = $setengah_menu; $i < $total_menu; $i++) {
                $m = $all_items[$i];
                $sisa_style = ($m['sisa_stok'] == 0) ? 'status-habis' : 'status-sisa';
                echo '<tr>';
                echo '<td>'.htmlspecialchars($m['nama_kopi']).'</td>';
                echo '<td class="text-center">'.$m['jumlah_awal'].'</td>';
                echo '<td class="text-center status-laku">'.$m['terjual'].'</td>';
                echo '<td class="text-center '.$sisa_style.'">'.$m['sisa_stok'].'</td>';
                echo '</tr>';
            }
            echo '</tbody></table>';

        } else {
            echo '<p style="color:#64748b; grid-column: span 2; text-align:center;">Tidak ada data stok untuk tanggal ini.</p>';
        } 
        ?>
    </div>

    <div class="omzet-box">
        <div class="omzet-row">
            <span>💵 Tunai:</span>
            <strong>Rp <?php echo number_format($data['nominal_tunai'] ?? 0, 0, ',', '.'); ?></strong>
        </div>
        <div class="omzet-row">
            <span>💳 Transfer:</span>
            <strong>Rp <?php echo number_format($data['nominal_transfer'] ?? 0, 0, ',', '.'); ?></strong>
        </div>
        <div class="grand-total">
            <strong>TOTAL DEPOSIT:</strong>
            <strong>Rp <?php echo number_format(($data['nominal_tunai'] ?? 0) + ($data['nominal_transfer'] ?? 0), 0, ',', '.'); ?></strong>
        </div>
    </div>

    <h4 style="margin-top:25px; margin-bottom:10px; color: #64748b; font-size: 14px;">🖼️ Bukti Transfer:</h4>
    <div class="foto-container">
        <?php 
        if (!empty($data['bukti_transfer'])) {
            $fotos = explode('*', $data['bukti_transfer']);
            foreach ($fotos as $img) {
                if (!empty($img)) {
                    echo '<img src="'.htmlspecialchars($img).'" class="foto-bukti" onclick="tampilModal(this.src)" alt="Bukti Transfer">';
                }
            }
        } else {
            echo '<p style="color:#94a3b8; font-size:13px; margin: 0;">(Tidak ada lampiran bukti transfer)</p>';
        }
        ?>
    </div>

    <a href="dashboard_admin.php" class="btn-back">⬅ Kembali ke Dashboard</a>
</div>

<div id="myModal" class="modal" onclick="tutupModal()">
    <span class="close">&times;</span>
    <img class="modal-content" id="img01">
</div>

<script>
function tampilModal(src) {
    var modal = document.getElementById("myModal");
    var modalImg = document.getElementById("img01");
    modal.style.display = "block";
    modalImg.src = src;
}
function tutupModal() {
    document.getElementById("myModal").style.display = "none";
}
</script>

</body>
</html>