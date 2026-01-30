<?php 
include 'config.php'; 
session_start(); // Pastikan session dimulai
if(!isset($_SESSION['user']) || $_SESSION['role'] != 'peserta') {
    header("Location: index.php");
    exit();
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIHADIR MPP BKPSDM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/all.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <style>
        :root {
            --primary-blue: #070136ff;
            --secondary-blue: #1b05e4ff;
            --light-blue: #f2f8feff;
        }

        body {
            background-color: var(--light-blue);
            font-family: 'Segoe UI', sans-serif;
            color: #1626d8ff ;
        }

        /* Header Gradient Section */
        .header-section {
            background: linear-gradient(135deg, #1b05e4ff 0%, #070136ff 100%);
            padding: 50px 20px;
            border-bottom-left-radius: 40px;
            border-bottom-right-radius: 40px;
            text-align: center;
            color: white;
            box-shadow: 0 10px 20px rgba(9, 9, 224, 0.35);
            margin-bottom: -60px;
        }

        .header-section h1 { font-weight: 800; letter-spacing: 1px; margin-top: 10px; }
        .header-section p { font-size: 0.9rem; opacity: 0.9; }

        /* Main Card Container */
        .main-container {
            max-width: 1100px;
            margin: 0 auto 50px auto;
            padding: 0 15px;
        }

        .content-card {
            background: white;
            border-radius: 25px;
            padding: 40px;
            box-shadow: 0 10px 25px rgba(15, 3, 79, 0.61);
        }

        .form-label { font-weight: 700; font-size: 0.75rem; color: #032a84ff ; text-transform: uppercase; margin-bottom: 8px; }
        
        .form-control, .form-select {
            background-color: #f7f4f5ff;
            border: 1px solid #0a1177ff ;
            border-radius: 10px;
            padding: 12px 15px;
            font-size: 0.9rem;
        }

        /* Camera Viewfinder */
        #camera, #canvas {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: 15px;
            background: #2908fa96;
            margin: 10px auto;
            border: 3px solid #070136ff ;
            box-shadow: 0 5px 15px rgba(14, 2, 76, 0.87);
        }
        #canvas { display: none; }

        /* Custom Blue Buttons */
        .btn-blue { background-color: #1b05e4ff; color: white; border-radius: 10px; font-weight: 600; padding: 10px 20px; border: none; }
        .btn-blue:hover { background-color: #070136ff; color: white; }
        
        .btn-simpan {
            background: linear-gradient(to right, #1b05e4ff, #070136ff);
            border: none;
            padding: 15px;
            border-radius: 50px;
            font-weight: 600;
            width: 100%;
            margin-top: 20px;
            box-shadow: 0 4px 15px rgba(0,114,255,0.3);
        }

        .input-group-text { background-color: #0d15f8ff; color: white; border: none; border-radius: 10px !important; margin-left: 5px; cursor: pointer; }
        
        .btn-logout { position: absolute; top: 20px; right: 20px; color: white; text-decoration: none; font-weight: 600; }

    /* Container Tabel agar sudut membulat sempurna */
.history-container {
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(221, 210, 210, 0.9);
    margin-top: 30px;
}

/* Container Utama */
.history-container {
    width: 100%;
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(14, 5, 143, 0.86);
    margin-top: 30px;
    border: 1px solid #ddd;
}

.table-siampp thead {
    width: 100%;
    /* Gradasi dari Biru Terang ke Biru Tua */
    background: linear-gradient(to right, #1b05e4ff, #070136ff, #1b05e4ff, #070136ff) !important;
    color: white !important;
}

.table-siampp th {
    background: transparent !important; /* Agar warna gradient di thead terlihat */
    padding: 15px !important;
    text-align: center !important;
    font-size: 0.85rem !important;
    text-transform: uppercase !important;
    font-weight: bold !important;
    border: none !important;
    color: white !important;
}

/* Garis pemisah antar kolom di header*/
.table-siampp th:not(:last-child) {
    border-right: 1px solid rgba(242, 229, 229, 0.89) !important;
}

/* Baris Tabel */
.table-siampp td {
    padding: 12px 15px !important;
    border-bottom: 1px solid #1301b9ff !important;
    text-align: center !important;
    vertical-align: middle !important;
    color: #0b025cff !important;
}
/* Warna Navy Gelap untuk semua tombol aksi utama */
/* Warna Gradasi Biru untuk tombol aksi */
.btn-navy-custom {
    background: linear-gradient(135deg, #1b05e4ff 0%, #070136ff 100%) !important;
    color: white !important;
    border-radius: 50px !important; /* Dibuat lebih bulat (pill-shaped) */
    padding: 10px 25px !important;
    font-weight: 600 !important;
    border: none !important;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 140px;
    transition: all 0.3s ease;
    box-shadow: 0 4px 9px rgba(27, 5, 228, 0.4); 
}
/* Mengatur jarak ikon agar tidak menempel dengan teks */
.btn-navy-custom i {
    margin-right: 10px;
    font-size: 1.1rem;
}

.btn-navy-custom:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 15px rgba(27, 5, 228, 0.5);
    opacity: 0.9;
}

.btn-navy-custom:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(7, 1, 54, 0.5);
    opacity: 0.9;
}

.btn-navy-custom i {
    margin-right: 8px; /* Jarak antara ikon dan teks */
}

.btn-navy-custom:hover {
    background-color: #000055 !important;
    transform: translateY(-1px);
}

/* Mengatur jarak antar input group */
.gap-2 {
    gap: 0.75rem !important; /* Mengatur lebar spasi pemisah antar tombol/input */
}

/* Pastikan input tidak terlalu lebar sehingga memakan tempat tombol */
.form-control {
    flex: 1; /* Input akan mengisi ruang yang tersisa */
}

/* Container Header Riwayat */
.history-header {
    background: #ffffff;
    border-radius: 15px 15px 0 0; /* Bulat di atas saja agar menyatu dengan tabel */
    padding: 20px;
    display: flex;
    justify-content: space-between; /* Judul di kiri, tombol di kanan */
    align-items: center;
    border-bottom: 2px solid #f0f0f0;
}

.history-title {
    display: flex;
    align-items: center;
    gap: 12px;
    color: #1e3c72;
    font-weight: 800;
}

/* Mengunci ukuran ikon agar tidak meluber */
.history-title img {
    width: 35px !important;
    height: 35px !important;
    object-fit: contain;
}

.history-title span {
    font-size: 1.4rem;
    letter-spacing: -0.5px;
}

/* Container Tombol */
.action-buttons {
    display: flex;
    gap: 10px;
}

.btn-action {
    border-radius: 10px;
    padding: 8px 16px;
    font-size: 0.85rem;
    font-weight: 700;
    display: flex;
    align-items: center;
    gap: 8px;
    border: none;
    color: white;
    transition: 0.3s;
    text-decoration: none;
}

.btn-export { background-color: #ebb325e2; }
.btn-refresh { background-color: #b1da11ff; }

.btn-action:hover {
    opacity: 0.9;
    transform: translateY(-1px);
    color: white;
}
/* Container Tombol agar di tengah */
.method-switcher {
    display: flex;
    justify-content: center;
    gap: 15px;
    margin-bottom: 30px;
}

/* Gaya Tombol Gradasi */
.btn-method {
    padding: 12px 25px;
    border-radius: 50px;
    font-weight: 700;
    transition: all 0.3s ease;
    border: none;
    min-width: 160px;
    box-shadow: 0 4px 10px rgba(11, 39, 163, 0.91);
}

/* Warna saat Aktif (Gradasi Biru) */
.btn-method.active {
    background: linear-gradient(135deg, #1b05e4ff 0%, #070136ff 100%);
    color: white;
    box-shadow: 0 4px 9px hsla(246, 96%, 46%, 0.71);
}

/* Warna saat Tidak Aktif */
.btn-method.inactive {
    background: linear-gradient(135deg, #1b05e4ff 0%, #070136ff 100%);
    color: white;
    color: #f6f5f5ff;
}

.btn-method:hover {
    transform: translateY(-2px);
}
/* Gaya dasar tombol metode */
.btn-method {
    padding: 12px 25px;
    border-radius: 50px;
    font-weight: 700;
    transition: all 0.3s ease;
    border: none;
    min-width: 160px;
    cursor: pointer;
    background: #230bc3ff; /* Warna default saat tidak aktif */
    color: #0f0696ff;
}

/* Gaya saat tombol AKTIF (Gradasi Biru) */
.btn-method.active {
    background: linear-gradient(135deg, #1b05e4ff 0%, #070136ff 100%) !important;
    color: white !important;
    box-shadow: 0 4px 9px hsla(246, 96%, 46%, 0.71);
}
        #reader {
    min-height: 300px;
    background: #000;
}
        #camera {
    width: 100%;
    max-width: 320px; /* Ukuran pas untuk HP */
    height: auto;
    border-radius: 15px;
    background: #000;
    /* Memberikan efek cermin pada preview video */
    transform: scaleX(-1); 
    -webkit-transform: scaleX(-1);
}

#canvas {
    width: 100%;
    max-width: 320px;
    height: auto;
    border-radius: 15px;
    border: 3px solid #1b05e4;
}
    </style>
<style>
  .button-container {
    display: flex;
    gap: 15px;
    font-family: sans-serif;
  }

  .btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px; /* Jarak antara icon dan teks */
    padding: 12px 25px;
    border: none;
    border-radius: 50px; /* Bentuk lonjong/pill */
    color: white;
    font-weight: bold;
    font-size: 16px;
    cursor: pointer;
    text-decoration: none;
    
    /* Efek Gradasi sesuai gambar */
    background: linear-gradient(to right, #2400ff, #1a0066, #05001a);
    
    /* Efek Bayangan (Shadow) */
    box-shadow: 0 8px 15px rgba(36, 0, 255, 0.2);
    transition: transform 0.2s, box-shadow 0.2s;
  }

  /* Efek saat tombol ditekan */
  .btn:active {
    transform: scale(0.95);
  }

  /* Warna khusus untuk tombol Hapus (opsional jika ingin sedikit berbeda) */
  .btn-hapus {
    background: linear-gradient(to right, #1a00e6, #0d004d, #000000);
  }
  justify-content: center; /* Tengah secara horizontal */
    align-items: center;     /* Tengah secara vertikal (jika ada tinggi) */
    #reader {
        width: 100% !important;
        max-width: 400px !important;
        margin: auto;
        border: 3px solid #1b05e4ff !important;
        border-radius: 15px;
        overflow: hidden;
        background-color: #000;
    }
   
</style>

</head>
<body>

    <a href="logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Logout</a>

    <div class="header-section">
        <img src="https://cdn-icons-png.flaticon.com/512/2666/2666505.png" alt="logo" width="60">
        <h1>SIHADIR BKPSDM TEGAL</h1>
        <p>Sistem Hadir Digital untuk Peserta Magang, PKL, dan Penelitian</p>
    </div>
    
<div class="main-container">
        <div class="content-card">
        <h5 class="text-center fw-bold fst-italic mb-4" style="color: #1f06c3ff; font-size: 25px;">INPUT ABSENSI</h5>
        <div class="method-switcher">
        <button type="button" onclick="showManual()" id="btn-manual" class="btn-method active">
        <i class="fas fa-edit me-2"></i> Input Manual
        </button>
        <button type="button" onclick="showQR()" id="btn-qr" class="btn-method inactive">
        <i class="fas fa-qrcode me-2"></i> Scan QR Code
        </button>
    </div>

    <hr>
            
   <div id="qr-section" style="display:none;" class="text-center my-4">
    <h5 class="text-primary fw-bold">Arahkan Kamera ke QR Code</h5>
    <div id="reader"></div>
    <button type="button" class="btn btn-sm btn-outline-primary mt-2" onclick="switchCamera()">
        <i class="fas fa-sync"></i> Ganti Kamera (Depan/Belakang)
    </button>
    <p class="text-muted small mt-2">Pastikan QR Code terlihat jelas di dalam bingkai.</p>
</div>
            
<form action="proses_absen.php" method="POST" enctype="multipart/form-data">
    <div id="qr-section" style="display:none;" class="text-center my-4">
        </div>

              <div class="mb-3">
    <label class="form-label">ID ATAU NIM PESERTA</label>
    <input type="text" name="id_peserta" class="form-control" placeholder="Masukkan ID atau NIM" required>
</div>

<div class="mb-3">
    <label class="form-label">Nama</label>
    <input type="text" name="nama" class="form-control" placeholder="Masukkan Nama Lengkap" required>
</div>

<div class="mb-3">
    <label class="form-label">Kategori</label>
    <select name="kategori" class="form-select">
        <option value="Magang">Magang</option>
        <option value="PKL">PKL</option>
        <option value="Penelitian">Penelitian</option>
    </select>
</div>
             <div class="mb-3">
                <label class="form-label">Sekolah ATAU Universitas</label>
                <input type="text" name="sekolah_universitas" class="form-control" placeholder="Masukan nama sekolah atau univ" required>
            </div>


                <div class="mb-3">
                    <label class="form-label">Status</label>
                    <select name="status_hadir" id="status_hadir" class="form-select" onchange="toggleForm()">
                        <option value="Hadir">Hadir ✅</option>
                        <option value="Izin">Izin 📩</option>
                        <option value="Sakit">Sakit 🏥</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Keterangan</label>
                    <input type="text" name="keterangan" class="form-control" placeholder="...">
                </div>

                        <div class="mb-3">
                <label class="form-label">Bukti Kehadiran / Surat Izin</label>
                <div class="d-flex align-items-center gap-2 mb-2"> 
                    <input type="file" name="foto" id="file_surat" class="form-control" accept="image/*,application/pdf">
                    <button type="button" id="snap" class="btn btn-navy-custom text-nowrap"><i class="fas fa-camera"></i> Foto</button>       
                    <button type="button" id="btnHapus" class="btn btn-navy-custom text-nowrap"><i class="fa-solid fa-trash"></i> Hapus</button>
                </div>
                <div class="text-center">
                    <video id="camera" autoplay muted playsinline></video>
                    <canvas id="canvas" style="display:none;"></canvas>
                    <input type="hidden" name="foto_base64" id="foto_base64">
                </div>
            </div>

                           <div class="mb-3">
    <label class="form-label">Lokasi & Alamat Terdeteksi</label>
    <div class="d-flex align-items-center gap-2">
        <div class="flex-grow-1">
            <input type="text" id="display_lokasi" class="form-control mb-1" placeholder="Klik ambil lokasi..." readonly>
            
            <div id="alamat_wrapper" class="p-2 border rounded bg-light mb-1" style="font-size: 0.85rem; min-height: 38px;">
                <input type="hidden" name="alamat_asli" id="alamat_asli">
                <i class="fas fa-map-marked-alt text-primary"></i> 
                <span id="display_alamat" class="text-muted">Alamat belum terdeteksi</span>
            </div>

            <div id="akurasi_visual" style="display:none;">
                <span id="badge_akurasi" class="badge rounded-pill shadow-sm" style="font-size: 11px; padding: 5px 12px;"></span>
            </div>
        </div>
        <button type="button" class="btn btn-navy-custom text-nowrap" onclick="getLocation()">
            <i class="fas fa-map-marker-alt"></i> Ambil Lokasi
        </button>
    </div>
    <input type="hidden" name="koordinat" id="koordinat">
    <input type="hidden" name="akurasi" id="akurasi">
</div>
            
            <button type="submit" class="btn-simpan text-white">
                <i class="fas fa-save me-2"></i> Simpan Presensi
            </button>
        </form>
    </div>

        <div class="main-container" style="max-width: 1300px;"> <div class="history-header mt-5">
        <div class="history-title">
            <img src="https://cdn-icons-png.flaticon.com/512/3502/3502688.png" alt="icon">
            <span>Riwayat Absensi</span>
        </div>
    </div>

    <div class="history-container" style="border-radius: 15px 15px;">
        <div class="table-responsive">
            <table class="table-siampp">
                <thead>
<tr>
                <th>NO</th>
                <th>ID ATAU NIM</th>
                <th>NAMA</th>
                <th>KATEGORI</th>
                <th>SEKOLAH ATAU UNIVERSITAS</th>
                <th>STATUS</th>
                <th>KETERANGAN</th>
                <th>LOKASI</th>
                <th>WAKTU</th>
                <th>FOTO</th>
                <th>STATUS AI</th>
            </tr>
            </thead>
            <tbody>
                   <?php
            $n = 1;
            // Gunakan ORDER BY waktu_absen agar yang terbaru di atas
            $query = mysqli_query($conn, "SELECT * FROM absensi ORDER BY waktu_absen DESC");
            while($row = mysqli_fetch_assoc($query)){
                // Logika Ikon AI
                $ai_icon = (strpos($row['analisis_ai'], 'Terlambat') !== false) ? '⏰' : '✅';
                $status_label = (strpos($row['analisis_ai'], 'Terlambat') !== false) ? 'Telat' : 'Tepat';
            ?>
                
                <tr>
    <td><?= $n++; ?></td>
  <td><?= $row['id_peserta'] ?? $row['id']; ?></td>
    <td><strong><?= $row['nama']; ?></strong></td>
    <td><?= $row['kategori']; ?></td>
    <td><?= $row['sekolah_universitas']; ?></td>
    <td><?= $row['status_hadir']; ?></td>
    <td><?= $row['keterangan']; ?></td>

                 <td class="text-center" style="min-width: 160px;">
    <div class="d-flex flex-column align-items-center">
        <small id="alamat-<?= $row['id']; ?>" class="fw-bold text-primary mb-1" style="font-size: 10px;">
          
        </small>
        
       <a href="https://www.google.com/maps?q=<?= $row['koordinat']; ?>" target="_blank" class="text-decoration-none" style="font-size: 11px;">
    📍 <?= $row['koordinat']; ?>
</a>
        
        <small class="text-muted" style="font-size: 9px;">
            (Akurasi ±<?= round($row['akurasi_meter']); ?>m)
        </small>

        <span class="data-lokasi d-none" 
              data-id="<?= $row['id']; ?>" 
              data-latlong="<?= $row['koordinat']; ?>"></span>
    </div>
</td>

                   <td>
        <span style="color:blue;font-size:12px;">
            <?= date('H:i', strtotime($row['waktu_absen'])); ?> WIB
        </span><br>
        <small><?= date('d/m/Y', strtotime($row['waktu_absen'])); ?></small>
    </td>
                    
                          <td style="text-align:center;">
        <?php if (!empty($row['foto'])) : ?>
            <img src="uploads/<?= $row['foto']; ?>" width="60" style="border-radius:5px;">
        <?php else : ?>
            -
        <?php endif; ?>
    </td>

    <td style="text-align:center; vertical-align:middle;">
        <div class="status-ai" style="font-size:11px;">
            <?= $ai_icon; ?> <?= $status_label; ?>
        </div>
    </td>
</tr>

          <?php } ?>
        </tbody>
    </table>
</div>
</div>
 <footer class="text-center py-4">
            <h6 style="font-family: 'Poppins', sans-serif; color: #2106e6ff; font-weight: 700; letter-spacing: 0.5px;">
                Dibuat oleh Aulia Annisa
            </h6>
            <p style="color: #090142ff; font-size: 11px; opacity: 0.7; font-family: 'Inter', sans-serif;">
                SIHADIR BKPSDM TEGAL
            </p>
        </footer>

<script src="https://unpkg.com/html5-qrcode"></script>
<script>
   const video = document.getElementById('camera');
const canvas = document.getElementById('canvas');
const fotoBase64 = document.getElementById('foto_base64');
let stream = null;
let html5QrCode = null;
let currentFacingMode = "user"; // Default kamera depan

// 1. FUNGSI MEMBERSIHKAN KAMERA
async function matikanKamera() {
    if (html5QrCode && html5QrCode.isScanning) {
        await html5QrCode.stop();
    }
    if (stream) {
        stream.getTracks().forEach(track => track.stop());
        stream = null;
    }
    video.srcObject = null;
    video.style.display = 'none';
    return new Promise(resolve => setTimeout(resolve, 300));
}

// 2. MODE MANUAL (SELFIE) - Dioptimalkan untuk HP
async function showManual() {
    await matikanKamera(); // Pastikan kamera lain mati total
    document.getElementById('qr-section').style.display = 'none';
    document.getElementById('btn-manual').classList.replace('inactive', 'active');
    document.getElementById('btn-qr').classList.replace('active', 'inactive');

    const constraints = {
        video: {
            // "exact" memaksa browser menggunakan kamera depan
            facingMode: { exact: "user" }, 
            width: { ideal: 1280 },
            height: { ideal: 720 }
        }
    };

    try {
        stream = await navigator.mediaDevices.getUserMedia(constraints);
        video.srcObject = stream;
        video.style.display = 'block';
        canvas.style.display = 'none';
    } catch (err) {
        console.error("Gagal akses kamera depan:", err);
        // Fallback jika 'exact' tidak didukung oleh browser lama
        try {
            stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: "user" } });
            video.srcObject = stream;
            video.style.display = 'block';
        } catch (retryErr) {
            alert("Kamera depan tidak ditemukan atau akses ditolak.");
        }
    }
}
// 3. MODE SCAN QR DENGAN FITUR SWITCH
async function showQR() {
    await matikanKamera();
    document.getElementById('qr-section').style.display = 'block';
    document.getElementById('btn-qr').classList.replace('inactive', 'active');
    document.getElementById('btn-manual').classList.replace('active', 'inactive');

    if (!html5QrCode) {
        html5QrCode = new Html5Qrcode("reader");
    }

    try {
        await html5QrCode.start(
            { facingMode: currentFacingMode }, 
            { fps: 10, qrbox: { width: 250, height: 250 } },
            (decodedText) => {
                // Parsing Data QR
                const dataObj = {};
                decodedText.split(';').forEach(part => {
                    if (part.includes('=')) {
                        const [key, val] = part.split('=').map(s => s.trim());
                        if (key) dataObj[key.toLowerCase()] = val;
                    }
                });

                // Isi Form
                if (dataObj.id) document.getElementsByName('id_peserta')[0].value = dataObj.id;
                if (dataObj.nama) document.getElementsByName('nama')[0].value = dataObj.nama;
                if (dataObj.kategori) document.getElementsByName('kategori')[0].value = dataObj.kategori;
                if (dataObj.sekolah) document.getElementsByName('sekolah_universitas')[0].value = dataObj.sekolah;

                alert("Data Berhasil Dimuat!");
                getLocation();
            }
        );
    } catch (err) {
        console.error(err);
    }
}
    async function matikanKamera() {
    // Berhentikan QR Scanner jika sedang jalan
    if (html5QrCode && html5QrCode.isScanning) {
        await html5QrCode.stop();
    }
    // Berhentikan semua track kamera yang aktif
    if (stream) {
        const tracks = stream.getTracks();
        tracks.forEach(track => track.stop());
        stream = null;
    }
    video.srcObject = null;
    video.style.display = 'none';
    // Berikan jeda 500ms agar hardware kamera di HP punya waktu untuk 'istirahat' sebelum switch
    return new Promise(resolve => setTimeout(resolve, 500));
}

// Fungsi untuk Ganti Kamera saat mode QR
async function switchCamera() {
    currentFacingMode = (currentFacingMode === "user") ? "environment" : "user";
    await showQR();
}

// 4. FUNGSI AMBIL FOTO (SELFIE MODE)
document.getElementById('snap').addEventListener('click', () => {
    const ctx = canvas.getContext('2d');
    let sourceElement = null;

    if (html5QrCode && html5QrCode.isScanning) {
        sourceElement = document.querySelector('#reader video');
    } else if (stream) {
        sourceElement = video;
    }

    if (sourceElement) {
        // Atur ukuran canvas sesuai resolusi video
        canvas.width = sourceElement.videoWidth;
        canvas.height = sourceElement.videoHeight;

        // PROSES MIRRORING: Membalikkan gambar secara horizontal
        ctx.translate(canvas.width, 0);
        ctx.scale(-1, 1);

        // Gambar foto ke canvas
        ctx.drawImage(sourceElement, 0, 0, canvas.width, canvas.height);
        
        // Reset transformasi agar tidak mengganggu proses berikutnya
        ctx.setTransform(1, 0, 0, 1, 0, 0);

        // Ubah ke Base64 (JPEG dengan kualitas 0.8 untuk menghemat storage)
        fotoBase64.value = canvas.toDataURL('image/jpeg', 0.8);
        
        canvas.style.display = 'block';
        video.style.display = 'none';
        
        if (html5QrCode && html5QrCode.isScanning) {
            document.getElementById('reader').style.display = 'none';
        }
        
        alert("Foto berhasil diambil!");
    } else {
        alert("Kamera belum aktif. Silakan tunggu atau muat ulang halaman.");
    }
});
// 5. HAPUS FOTO
document.getElementById('btnHapus').addEventListener('click', () => {
    canvas.style.display = 'none';
    fotoBase64.value = '';
    if (html5QrCode && html5QrCode.isScanning) {
        document.getElementById('reader').style.display = 'block';
    } else {
        video.style.display = 'block';
    }
});

    // 6. FUNGSI LOKASI
    function getLocation() {
    if (!navigator.geolocation) {
        alert("Browser tidak mendukung GPS");
        return;
    }

    navigator.geolocation.getCurrentPosition(
        showPosition,
        () => alert("Gagal mengambil lokasi. Aktifkan GPS."),
        { enableHighAccuracy: true }
    );
}

function showPosition(position) {
    const lat = position.coords.latitude;
    const lon = position.coords.longitude;
    const acc = position.coords.accuracy;

    document.getElementById('koordinat').value = lat + "," + lon;
    document.getElementById('akurasi').value = acc;
    document.getElementById('display_lokasi').value =
        lat.toFixed(6) + ", " + lon.toFixed(6);

    document.getElementById('akurasi_visual').style.display = 'block';
    const badge = document.getElementById('badge_akurasi');

    badge.innerText = acc < 100 ? "Lokasi Akurat" : "Akurasi Rendah";
    badge.className = acc < 100 ? "badge bg-success" : "badge bg-warning";

    fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lon}`)
        .then(res => res.json())
        .then(data => {
            document.getElementById('display_alamat').innerText = data.display_name;
            document.getElementById('alamat_asli').value = data.display_name;
        });
}
    // Jalankan selfie saat startup
    window.onload = showManual;
</script>
</body>
</html>
