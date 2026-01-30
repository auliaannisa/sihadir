<?php 
include 'config.php'; 
mysqli_query($conn, "SET time_zone = '+07:00'");
date_default_timezone_set('Asia/Jakarta');
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') { 
    header("Location: index.php"); 
    exit();
}
// --- FUNGSI HITUNG JARAK (HAVERSINE) ---
function hitungJarak($lat1, $lon1, $lat2, $lon2) {
    $earth_radius = 6371000; // Radius bumi dalam meter

    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);

    $a = sin($dLat/2) * sin($dLat/2) +
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
         sin($dLon/2) * sin($dLon/2);
    $c = 2 * atan2(sqrt($a), sqrt(1-$a));
    
    return $earth_radius * $c; // Hasil dalam meter
}

// --- KOORDINAT KANTOR BKPSDM TEGAL (Contoh) ---
// Silakan sesuaikan dengan koordinat kantor Anda yang sebenarnya
$kantor_lat = -6.996549163161386; 
$kantor_lon = 109.1268566878337;
// --- FUNGSI REKAP MINGGUAN ---
function hitungHadirSeminggu($conn, $id_peserta) {
    $tujuh_hari_lalu = date('Y-m-d H:i:s', strtotime('-7 days'));
    // Gunakan prepared statement jika memungkinkan untuk keamanan, namun ini versi perbaikan dari kode Anda
    $id_peserta = mysqli_real_escape_string($conn, $id_peserta);
    $q = mysqli_query($conn, "SELECT COUNT(*) as total FROM absensi 
                              WHERE (nim = '$id_peserta' OR id_peserta = '$id_peserta') 
                              AND status_hadir = 'Hadir' 
                              AND waktu_absen >= '$tujuh_hari_lalu'");
    $res = mysqli_fetch_assoc($q);
    return $res['total'] ?? 0;
}

// --- LOGIKA FILTER ---
$filter_keyword  = isset($_GET['f_keyword']) ? mysqli_real_escape_string($conn, $_GET['f_keyword']) : '';
$filter_asal     = isset($_GET['f_asal']) ? mysqli_real_escape_string($conn, $_GET['f_asal']) : '';
$filter_kategori = isset($_GET['f_kategori']) ? mysqli_real_escape_string($conn, $_GET['f_kategori']) : '';
$range           = isset($_GET['range']) ? $_GET['range'] : 'semua';

$query = "SELECT * FROM absensi WHERE 1=1";

if($filter_keyword != '') { 
    $query .= " AND (nama LIKE '%$filter_keyword%' OR nim LIKE '%$filter_keyword%' OR id_peserta LIKE '%$filter_keyword%')"; 
}
if($filter_asal != '') { 
    $query .= " AND sekolah_universitas LIKE '%$filter_asal%'"; 
}
if($filter_kategori != '') { 
    $query .= " AND kategori = '$filter_kategori'"; 
}

// Filter Waktu Terintegrasi
if($range == 'mingguan') {
    $query .= " AND YEARWEEK(waktu_absen, 1) = YEARWEEK(CURDATE(), 1)";
} elseif($range == 'bulanan') {
    $query .= " AND MONTH(waktu_absen) = MONTH(CURDATE()) AND YEAR(waktu_absen) = YEAR(CURDATE())";
}

$query .= " ORDER BY waktu_absen DESC";
$sql = mysqli_query($conn, $query);

// Hitung Statistik
$stat_hari_ini = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM absensi WHERE DATE(waktu_absen) = CURDATE() AND status_hadir = 'Hadir'"));
$stat_7_hari = mysqli_num_rows(mysqli_query($conn, "SELECT id FROM absensi WHERE waktu_absen >= DATE_SUB(NOW(), INTERVAL 7 DAY) AND status_hadir = 'Hadir'"));
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - SIHADIR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap');
        body { background-color: #f4f7fe; font-family: 'Poppins', sans-serif; color: #333; }
        
        .header-blue { 
            background: #00008b; 
            padding: 50px 0 100px; 
            color: white; 
            text-align: center; 
        }

        .container-custom { max-width: 1240px; margin: -60px auto 50px; }

        /* Card & Stats */
        .filter-card, .stat-card, .table-container { 
            background: white; border-radius: 20px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.05); margin-bottom: 25px; 
        }
        .filter-card { padding: 30px; }
        .stat-card { padding: 20px; transition: 0.3s; border: none; }
        .stat-card:hover { transform: translateY(-5px); }

        /* Buttons Pill */
        .btn-filter, .btn-reset { 
            background: linear-gradient(to right, #2979ff, #00008b); 
            color: white !important; border-radius: 50px; 
            font-weight: 700; padding: 10px 25px; border: none;
            box-shadow: 0 4px 15px rgba(0, 0, 81, 0.2); transition: 0.3s;
        }
        .btn-filter:hover { opacity: 0.9; transform: translateY(-2px); }

        /* Table Styles */
        .table thead th { 
            background: linear-gradient(to right, #2400ff, #05001a);
            color: white; font-size: 11px; text-transform: uppercase; padding: 15px; border: none;
        }

        /* Rekap & Progress */
        .rekap-box { background: #f1f3f9; padding: 6px 10px; border-radius: 12px; border-left: 5px solid #0000ff; min-width: 100px; }
        .progress-thin { height: 6px; border-radius: 10px; background: #eee; margin-top: 4px; }
        .progress-low { background: #ff416c; }
        .progress-mid { background: #f09819; }
        .progress-high { background: #00b09b; }

        .img-absensi { width: 45px; height: 45px; border-radius: 10px; object-fit: cover; }
        .badge-akurasi { display: inline-block; padding: 2px 10px; border-radius: 50px; font-size: 10px; font-weight: 700; margin-top: 5px; }
        .akurasi-tinggi { background-color: #e8f5e9; color: #2e7d32; border: 1px solid #c8e6c9; }
        .akurasi-rendah { background-color: #ffebee; color: #c62828; border: 1px solid #ffcdd2; }
        
        .logout-link { 
            position: absolute; top: 20px; right: 30px; 
            color: white; border: 1px solid rgba(255,255,255,0.4);
            padding: 5px 15px; border-radius: 8px; font-size: 12px; font-weight: bold; text-decoration: none;
        }
    </style>
</head>
<body>
    <a href="logout.php" class="logout-link">KELUAR SISTEM</a>

    <div class="header-blue">
        <h1 class="fw-bold">SIHADIR BKPSDM TEGAL</h1>
        <p class="opacity-75">Sistem Hadir Digital untuk peserta Magang, PKL dan Penelitian</p>
    </div>

    <div class="container container-custom">
        <div class="filter-card">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="small fw-bold text-muted mb-2">CARI PESERTA</label>
                    <input type="text" name="f_keyword" class="form-control" value="<?= $filter_keyword ?>" placeholder="Nama/NIM...">
                </div>
                <div class="col-md-3">
                    <label class="small fw-bold text-muted mb-2">INSTANSI</label>
                    <input type="text" name="f_asal" class="form-control" value="<?= $filter_asal ?>" placeholder="Sekolah/Univ...">
                </div>
                <div class="col-md-3">
                    <label class="small fw-bold text-muted mb-2">KATEGORI</label>
                    <select name="f_kategori" class="form-control">
                        <option value="">Semua Kategori</option>
                        <option value="Magang" <?= ($filter_kategori == 'Magang')?'selected':'' ?>>Magang</option>
                        <option value="PKL" <?= ($filter_kategori == 'PKL')?'selected':'' ?>>PKL</option>
                        <option value="Penelitian" <?= ($filter_kategori == 'Penelitian')?'selected':'' ?>>Penelitian</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-filter flex-fill">FILTER</button>
                    <a href="admin.php" class="btn btn-reset flex-fill text-center">RESET</a>
                </div>
            </form>

            <div class="range-tabs mt-4 d-flex justify-content-center border-top pt-3">
                <a href="admin.php?range=semua" class="btn btn-sm <?= ($range == 'semua') ? 'btn-primary' : 'btn-outline-primary' ?> rounded-pill mx-1">Semua Data</a>
                <a href="admin.php?range=mingguan" class="btn btn-sm <?= ($range == 'mingguan') ? 'btn-primary' : 'btn-outline-primary' ?> rounded-pill mx-1">Minggu Ini</a>
                <a href="admin.php?range=bulanan" class="btn btn-sm <?= ($range == 'bulanan') ? 'btn-primary' : 'btn-outline-primary' ?> rounded-pill mx-1">Bulan Ini</a>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="stat-card border-start border-primary border-5">
                    <span class="text-muted small fw-bold">HADIR HARI INI</span>
                    <h2 class="fw-bold text-primary mb-0"><?= $stat_hari_ini ?></h2>
                </div>
            </div>
            <div class="col-md-6">
                <div class="stat-card border-start border-info border-5">
                    <span class="text-muted small fw-bold">TOTAL KEHADIRAN (7 HARI TERAKHIR)</span>
                    <h2 class="fw-bold text-info mb-0"><?= $stat_7_hari ?></h2>
                </div>
            </div>
        </div>
        

        <form action="hapus_masal.php" method="POST">
            <div class="table-container">
                <div class="p-3 d-flex justify-content-between align-items-center border-bottom">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-list me-2 text-primary"></i>Riwayat Absensi</h5>
                    <div class="d-flex gap-2">
                        <a href="export_spreadsheet.php" class="btn btn-success btn-sm px-3 rounded-pill"><i class="fas fa-file-excel me-1"></i> EXPORT SPREADSHEET</a>
                        <button type="submit" name="btn_hapus_masal" class="btn btn-danger btn-sm px-3 rounded-pill" onclick="return confirm('Hapus data terpilih?')"><i class="fas fa-trash me-1"></i> HAPUS</button>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 text-center">
                        <thead>
                            <tr>
                                <th width="40"><input type="checkbox" id="checkAll" class="form-check-input"></th>
                                <th>ID / NIM</th>
                                <th class="text-start">NAMA PESERTA</th>
                                <th class="text-start">SEKOLAH ATAU UNIVERSITAS</th>
                                <th>KATEGORI</th>
                                 <th>STATUS</th>
                                 <th>KETERANGAN</th>
                                <th>REKAP 7H</th>
                                <th>WAKTU</th>
                                <th>LOKASI</th>
                                <th>FOTO</th>
                                <th>AI</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(mysqli_num_rows($sql) > 0): 
                                while($row = mysqli_fetch_array($sql)): 
                                    $id_p = !empty($row['nim']) ? $row['nim'] : ($row['id_peserta'] ?? '-');
                                    
                           // --- LOGIKA STATUS & WARNA (TAMBAHKAN INI) ---
        $status = $row['status_hadir'] ?? $row['status'] ?? 'Hadir';
        $badge_class = 'bg-secondary'; // Default
        if ($status == 'Hadir') $badge_class = 'bg-success';
        if ($status == 'Izin') $badge_class = 'bg-warning text-dark';
        if ($status == 'Sakit') $badge_class = 'bg-danger';
                           
                                    // Logic fallback nama
                                    $t_nama = $row['nama'];
                                    $t_univ = $row['sekolah_universitas'];
                                    if (empty($t_nama)) {
                                        $cek = mysqli_query($conn, "SELECT nama, sekolah_universitas FROM data_peserta WHERE nim = '$id_p' LIMIT 1");
                                        $d = mysqli_fetch_assoc($cek);
                                        $t_nama = $d['nama'] ?? 'User QR: '.$id_p;
                                        $t_univ = $d['sekolah_universitas'] ?? '-';
                                    }

                                    $jml_hadir = hitungHadirSeminggu($conn, $id_p);
                                    $persen = ($jml_hadir / 5) * 100;
                                    $p_class = ($persen <= 40) ? 'progress-low' : (($persen <= 75) ? 'progress-mid' : 'progress-high');
                            ?>
                            <tr>
                                <td><input type="checkbox" name="pilih[]" value="<?= $row['id']; ?>" class="form-check-input"></td>
                                <td class="small text-muted"><?= $id_p ?></td>
                                <td class="text-start fw-bold small"><?= strtoupper($t_nama) ?></td>
                                <td class="text-start small text-muted"><?= $t_univ ?></td>
                                <td><span class="badge bg-light text-dark border"><?= $row['kategori'] ?: '-' ?></span></td>
                                <td>
                    <span class="badge <?= $badge_class; ?> shadow-sm">
                        <?= $status; ?>
                    </span>
                </td>

                <td style="font-size: 0.85rem; max-width: 150px; white-space: normal;">
                    <?= !empty($row['keterangan']) ? $row['keterangan'] : '<i class="text-muted">-</i>'; ?>
                </td>
                                <td>
                                    <div class="rekap-box">
                                        <div class="d-flex justify-content-between" style="font-size: 9px; font-weight:700">
                                            <span><?= $jml_hadir ?>/5H</span>
                                            <span><?= round($persen) ?>%</span>
                                        </div>
                                        <div class="progress progress-thin">
                                            <div class="progress-bar <?= $p_class ?>" style="width: <?= min($persen, 100) ?>%"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="small">
                                    <div class="fw-bold text-primary"><?= date('H:i', strtotime($row['waktu_absen'])) ?></div>
                                    <div class="text-muted" style="font-size: 10px;"><?= date('d/m/y', strtotime($row['waktu_absen'])) ?></div>
                                </td>
                                <td class="text-start" style="font-size: 11px; min-width: 150px;">
    <div class="fw-bold text-dark">
        <i class="fas fa-map-marker-alt text-danger me-1"></i> 
        <span id="addr-<?= $row['id']; ?>">Memuat alamat...</span>
    </div>
    <div class="text-primary mt-1" style="font-size: 9px;"><?= $row['koordinat']; ?></div>

    <?php 
    // Ambil koordinat dari database
    $koordinat_user = explode(',', $row['koordinat']);
    
    if (count($koordinat_user) == 2) {
        $user_lat = trim($koordinat_user[0]);
        $user_lon = trim($koordinat_user[1]);

        // Hitung jarak ke kantor
        $jarak_meter = hitungJarak($user_lat, $user_lon, $kantor_lat, $kantor_lon);

        if ($jarak_meter <= 100) {
            // Jika di bawah 100 meter
            echo '<div class="badge-akurasi akurasi-tinggi">
                    <i class="fas fa-check-circle"></i> ±' . round($jarak_meter) . ' Meter (Akurat)
                  </div>';
        } else if ($jarak_meter > 100 && $jarak_meter <= 1000) {
            // Jika antara 100m - 1km
            echo '<div class="badge-akurasi bg-warning text-dark border" style="font-size:10px; padding:2px 8px; border-radius:50px; font-weight:700;">
                    <i class="fas fa-exclamation-triangle"></i> ±' . round($jarak_meter) . ' Meter (Diluar Area)
                  </div>';
        } else {
            // Jika lebih dari 1km
            $jarak_km = round($jarak_meter / 1000, 2);
            echo '<div class="badge-akurasi akurasi-rendah">
                    <i class="fas fa-times-circle"></i> ±' . $jarak_km . ' Km (Tidak Akurat)
                  </div>';
        }
    } else {
        echo '<div class="badge-akurasi bg-secondary text-white">Koordinat Tidak Valid</div>';
    }
    ?>
    <span class="data-geo d-none" data-id="<?= $row['id']; ?>" data-coords="<?= $row['koordinat']; ?>"></span>
</td>
                                <td>
                                    <?php if (!empty($row['foto'])): ?>
                                        <img src="uploads/<?= $row['foto'] ?>" class="img-absensi">
                                    <?php else: ?>
                                        <i class="fas fa-qrcode text-muted"></i>
                                    <?php endif; ?>
                                </td>
                                <td><?= (strpos(strtolower($row['analisis_ai'] ?? ''), 'terlambat') !== false) ? '⏰' : '✅'; ?></td>
                            </tr>
                            <?php endwhile; else: ?>
                                <tr><td colspan="10" class="p-5 text-muted">Data tidak ditemukan.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </form>
    </div>

    <footer class="text-center py-4">
        <h6 style="color: #2106e6; font-weight: 700;">Dibuat oleh Aulia Annisa</h6>
        <p style="color: #090142; font-size: 11px; opacity: 0.7;">SIHADIR BKPSDM TEGAL</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('checkAll').onclick = function() {
            var checkboxes = document.getElementsByName('pilih[]');
            for (var checkbox of checkboxes) { checkbox.checked = this.checked; }
        }

        document.addEventListener("DOMContentLoaded", function() {
            document.querySelectorAll('.data-geo').forEach(el => {
                const id = el.dataset.id;
                const coords = el.dataset.coords.split(',');
                if(coords.length === 2) {
                    fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${coords[0].trim()}&lon=${coords[1].trim()}&zoom=18`)
                        .then(r => r.json())
                        .then(data => {
                            const addr = data.display_name.split(',').slice(0, 2).join(',');
                            document.getElementById(`addr-${id}`).innerText = addr || "Lokasi Ditemukan";
                        }).catch(() => { document.getElementById(`addr-${id}`).innerText = "Lokasi Luar"; });
                }
            });
        });
    </script>
</body>
</html>
