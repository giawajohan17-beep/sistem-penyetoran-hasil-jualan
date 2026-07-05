<?php 
session_start();
include 'koneksi.php';

if (!isset($_SESSION['status']) || $_SESSION['status'] != "login") {
    header("location:index.php?pesan=belum_login");
    exit();
}

$id_karyawan = $_SESSION['id_karyawan'];
$tanggal = date('Y-m-d'); 

$query_stok = mysqli_query($koneksi, "SELECT sh.*, m.nama_kopi, m.harga_per_cup 
                                      FROM stok_harian sh 
                                      JOIN menu m ON sh.id_menu = m.id_menu 
                                      WHERE sh.id_karyawan = '$id_karyawan' 
                                      AND DATE(sh.tanggal) = '$tanggal'");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setoran - PT Swakarsa</title>
    <script src="https://cdn.jsdelivr.net/npm/heic2any@0.0.3/dist/heic2any.min.js"></script>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: #f4f7f6; padding: 15px; margin: 0; }
        
        /* CONTAINER UTAMA: Dilebarkan sedikit ke 700px agar muat 2 kolom kotak bersampingan */
        .main-wrapper { 
            max-width: 700px; 
            margin: 0 auto;   
            background: #fff;
            padding: 25px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(162, 179, 175, 0.15);
            border: 1px solid #e2e8f0;
        }

        h3 { text-align: center; color: #0f766e; margin-bottom: 20px; border-bottom: 2px solid #ffedd5; padding-bottom: 10px; font-weight: 700; }

        /* KOTAK PEMBUNGKUS MENU: Otomatis membagi item menjadi 2 kolom grid bersampingan */
        .menu-grid-container {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
            margin-bottom: 20px;
        }

        /* DESAIN ITEM MENU: Berbentuk kotak bergaris penuh (substitusi tabel) */
        .card { 
            background: #ffffff; 
            padding: 12px 15px; 
            border-radius: 10px; 
            border: 1px solid #cbd5e1; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            box-sizing: border-box;
            transition: background 0.15s;
        }
        .card:hover { background-color: #fff7ed; } /* Efek hover estetik warm orange */

        .input-terjual { 
            width: 65px; 
            padding: 6px; 
            text-align: center; 
            border: 2px solid #e67e22; 
            border-radius: 8px; 
            font-weight: bold; 
            font-size: 16px; 
            background: #f8fafc;
        }
        .input-terjual:focus { border-color: #c2410c; background: #ffffff; outline: none; }

        .input-error { border-color: #e74c3c !important; background-color: #ffdada !important; }
        
        .total-box { 
            background: #2c3e50; 
            color: white; 
            padding: 15px; 
            border-radius: 12px; 
            text-align: center; 
            margin: 15px 0; 
            border-bottom: 4px solid #e67e22; 
        }
        .total-box h2 { margin: 5px 0 0 0; color: #ff9f43; font-size: 28px; }

        .form-control { width: 100%; padding: 12px; margin: 8px 0; border: 1px solid #ddd; border-radius: 8px; box-sizing: border-box; font-size: 15px; background: #f8fafc; }
        .form-control:focus { border-color: #e67e22; background: #ffffff; outline: none; }
        
        .btn-kirim { width: 100%; padding: 15px; background: #27ae60; color: white; border: none; border-radius: 10px; font-weight: bold; font-size: 16px; cursor: pointer; margin-top: 10px; box-shadow: 0 4px 12px rgba(39, 174, 96, 0.2); }
        .btn-logout { width: 100%; padding: 12px; background: #e74c3c; color: white; border: none; border-radius: 10px; font-weight: bold; font-size: 14px; cursor: pointer; margin-top: 15px; display: block; text-align: center; text-decoration: none; }
        
        .status-foto { font-size: 12px; font-weight: bold; color: blue; display: block; margin-top: 5px; }
        #container-preview img { width: 70px; height: 70px; object-fit: cover; border-radius: 8px; border: 2px solid #e67e22; }

        /* Responsif: Jika dibuka di HP otomatis kembali menjadi 1 kolom memanjang */
        @media (max-width: 650px) { 
            .menu-grid-container { grid-template-columns: 1fr; gap: 10px; } 
        }
    </style>
</head>
<body>

<div class="main-wrapper">
    <h3>Setoran: <?php echo $_SESSION['nama_karyawan']; ?></h3>

    <form action="proses_setoran.php" method="POST" onsubmit="return cekFinal()">
        
        <div class="menu-grid-container">
            <?php while($row = mysqli_fetch_assoc($query_stok)) { ?>
            <div class="card">
                <div>
                    <strong style="font-size: 14px; color: #1e293b;"><?php echo $row['nama_kopi']; ?></strong><br>
                    <small style="color: #64748b; font-weight: 500;">Stok: <?php echo $row['jumlah_awal']; ?> Cup</small>
                </div>
                <input type="number" name="terjual[<?php echo $row['id_stok']; ?>]" class="input-terjual hitung-cup" 
                       value="0" min="0" data-stok="<?php echo $row['jumlah_awal']; ?>" 
                       data-harga="<?php echo $row['harga_per_cup']; ?>" oninput="hitungOtomatis()">
            </div>
            <?php } ?>
        </div>

        <div class="total-box">
            <span style="font-size: 12px;">ESTIMASI TOTAL OMZET:</span>
            <h2 id="display_total">Rp 0</h2>
        </div>

        <div style="padding: 15px; border: 1px solid #cbd5e1; border-radius: 12px; background: #fff;">
            <label>💰 <b>Uang Tunai:</b></label>
            <input type="number" name="nominal_tunai" class="form-control" placeholder="0">
            
            <label>💳 <b>Transfer / QRIS:</b></label>
            <input type="number" id="inputTransfer" name="nominal_transfer" class="form-control" placeholder="0">
            
            <label>📸 <b>Bukti Transfer:</b></label>
            <input type="file" id="fileInputMulti" class="form-control" accept="image/*" multiple>
            <input type="hidden" name="banyak_bukti_base64" id="banyak_base64">
            
            <span id="pesanProses" class="status-foto"></span>
            <div id="container-preview" style="display: flex; flex-wrap: wrap; gap: 8px; margin-top: 10px;"></div>
        </div>

        <button type="submit" id="tombolKirim" name="simpan" class="btn-kirim">KIRIM LAPORAN</button>
    </form>

    <a href="logout.php" class="btn-logout" onclick="return confirm('Mau keluar aplikasi, Bang?')">LOGOUT / KELUAR</a>
</div>

<script>
function hitungOtomatis() {
    let totalSemua = 0;
    let adaError = false;
    const semuaInput = document.querySelectorAll('.hitung-cup');
    const btn = document.getElementById('tombolKirim');
    
    semuaInput.forEach(input => {
        const jumlah = parseInt(input.value) || 0;
        const stokMaks = parseInt(input.getAttribute('data-stok')) || 0;
        let harga = parseInt(input.getAttribute('data-harga')) || 0;
        
        if (jumlah > stokMaks) {
            input.classList.add('input-error');
            adaError = true;
        } else {
            input.classList.remove('input-error');
        }
        totalSemua += (jumlah * harga);
    });

    document.getElementById('display_total').innerText = "Rp " + totalSemua.toLocaleString('id-ID');
    btn.disabled = adaError;
    btn.style.background = adaError ? "#95a5a6" : "#27ae60";
    btn.innerText = adaError ? "STOK TIDAK CUKUP!" : "KIRIM LAPORAN";
}

let kumpulanBase64 = [];
document.getElementById('fileInputMulti').addEventListener('change', async function(e) {
    const files = e.target.files;
    if (files.length === 0) return;

    const btn = document.getElementById('tombolKirim');
    const pesan = document.getElementById('pesanProses');
    const wadahPreview = document.getElementById('container-preview');
    const hiddenInput = document.getElementById('banyak_base64');
    
    btn.disabled = true;
    pesan.innerText = "⏳ Memproses foto...";
    wadahPreview.innerHTML = ""; 
    kumpulanBase64 = [];

    for (let i = 0; i < files.length; i++) {
        let file = files[i];
        if (file.name.toLowerCase().endsWith(".heic")) {
            try {
                const blob = await heic2any({ blob: file, toType: "image/jpeg", quality: 0.6 });
                file = new File([blob], "foto_" + i + ".jpg", { type: "image/jpeg" });
            } catch (err) { console.error("HEIC Error"); }
        }

        const reader = new FileReader();
        reader.onload = function(event) {
            const img = new Image();
            img.onload = function() {
                const canvas = document.createElement('canvas');
                const MAX_WIDTH = 600;
                const scaleSize = MAX_WIDTH / img.width;
                canvas.width = MAX_WIDTH;
                canvas.height = img.height * scaleSize;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
                
                const base64 = canvas.toDataURL('image/jpeg', 0.7);
                kumpulanBase64.push(base64);
                hiddenInput.value = kumpulanBase64.join('*');

                const thumb = document.createElement('img');
                thumb.src = base64;
                wadahPreview.appendChild(thumb);

                if (kumpulanBase64.length === files.length) {
                    btn.disabled = false;
                    pesan.innerText = "✅ " + files.length + " Foto siap!";
                    pesan.style.color = "green";
                }
            };
            img.src = event.target.result;
        };
        reader.readAsDataURL(file);
    }
});

function cekFinal() {
    const transfer = parseInt(document.getElementById('inputTransfer').value) || 0;
    const foto = document.getElementById('banyak_base64').value;
    if (transfer > 0 && !foto) {
        alert("Wajib upload bukti transfer!");
        return false;
    }
    return confirm("Kirim sekarang?");
}
window.onload = hitungOtomatis;
</script>

</body>
</html>