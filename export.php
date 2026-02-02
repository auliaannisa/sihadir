<?php
include 'config.php';
header('Content-Type: text/csv');
header('Content-Disposition: attachment; filename="Rekap_SIHADIR MPP.csv"');

$output = fopen('php://output', 'w');
fputcsv($output, array('ID', 'Nama', 'Kategori', 'Koordinat', 'Analisis AI', 'Waktu'));

$query = "SELECT id, nama, kategori, koordinat, analisis_ai, waktu_absen FROM absensi";
$result = mysqli_query($conn, $query);

while ($row = mysqli_fetch_assoc($result)) {
    fputcsv($output, $row);
}
fclose($output);
?>
