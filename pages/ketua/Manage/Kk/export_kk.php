<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'ketua') {
    header("Location: ../../home.php");
    exit();
}

include '../../../../config/database.php';
require_once '../../../../vendor/autoload.php';

use Dompdf\Dompdf;

$kk_id = isset($_GET['id']) ? (int)$_GET['id'] : null;

if ($kk_id) {
    $kk_result = mysqli_query($conn, "
        SELECT kk.id, kk.no_kk, kk.kepala_keluaraga, kk.alamat,
               w.id as warga_id, w.nik, w.nama, w.jk, w.tanggal_lahir, w.status
        FROM kk
        LEFT JOIN warga w ON kk.id = w.kk_id
        WHERE kk.id = $kk_id
        ORDER BY kk.id, w.id
    ");
} else {
    $kk_result = mysqli_query($conn, "
        SELECT kk.id, kk.no_kk, kk.kepala_keluaraga, kk.alamat,
               w.id as warga_id, w.nik, w.nama, w.jk, w.tanggal_lahir, w.status
        FROM kk
        LEFT JOIN warga w ON kk.id = w.kk_id
        ORDER BY kk.id, w.id
    ");
}

// Group data by KK
$kk_data = [];
while ($row = mysqli_fetch_assoc($kk_result)) {
    $current_kk_id = $row['id'];
    if (!isset($kk_data[$current_kk_id])) {
        $kk_data[$current_kk_id] = [
            'no_kk' => $row['no_kk'],
            'kepala_keluaraga' => $row['kepala_keluaraga'],
            'alamat' => $row['alamat'],
            'anggota' => []
        ];
    }
    if ($row['warga_id']) {
        $kk_data[$current_kk_id]['anggota'][] = [
            'nik' => $row['nik'],
            'nama' => $row['nama'],
            'jk' => $row['jk'],
            'tanggal_lahir' => $row['tanggal_lahir'],
            'status' => $row['status']
        ];
    }
}

$filename = $kk_id ? 'kartu_keluarga_' . $kk_data[$kk_id]['no_kk'] . '.pdf' : 'daftar_kartu_keluarga.pdf';
$title = $kk_id ? 'Kartu Keluarga' : 'Daftar Kartu Keluarga';
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$dompdf = new Dompdf();
$html = '
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; }
        h1 { text-align: center; }
        .kk-card { margin-bottom: 40px; page-break-inside: avoid; }
        .kk-header { background-color: #f2f2f2; padding: 10px; margin-bottom: 10px; }
        .kk-header h2 { margin: 0; }
        .kk-header p { margin: 5px 0; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .center { text-align: center; }
    </style>
</head>
<body>
    <h1>' . $title . '</h1>';

foreach ($kk_data as $kk) {
    $html .= '
    <div class="kk-card">
        <div class="kk-header">
            <h2>Kartu Keluarga No. ' . htmlspecialchars($kk['no_kk']) . '</h2>
            <p><strong>Kepala Keluarga:</strong> ' . htmlspecialchars($kk['kepala_keluaraga']) . '</p>
            <p><strong>Alamat:</strong> ' . htmlspecialchars(preg_replace('/[^\x20-\x7E\x0A\x0D]/u', '', $kk['alamat']), ENT_QUOTES, 'UTF-8') . '</p>
        </div>
        <table>
            <thead>
                <tr>
                    <th>No.</th>
                    <th>NIK</th>
                    <th>Nama Lengkap</th>
                    <th>Jenis Kelamin</th>
                    <th>Tanggal Lahir</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>';

    $anggota_no = 1;
    foreach ($kk['anggota'] as $anggota) {
        $html .= '
                <tr>
                    <td class="center">' . $anggota_no++ . '</td>
                    <td>' . htmlspecialchars($anggota['nik']) . '</td>
                    <td>' . htmlspecialchars($anggota['nama']) . '</td>
                    <td class="center">' . htmlspecialchars($anggota['jk']) . '</td>
                    <td>' . htmlspecialchars($anggota['tanggal_lahir']) . '</td>
                    <td>' . htmlspecialchars($anggota['status']) . '</td>
                </tr>';
    }

    $html .= '
            </tbody>
        </table>
    </div>';
}

$html .= '
</body>
</html>';

try {
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    $dompdf->stream($filename, array('Attachment' => 1));
} catch (Exception $e) {
    // Log error and show user-friendly message
    error_log("PDF Generation Error: " . $e->getMessage());
    header('Content-Type: text/html; charset=utf-8');
    echo "<h1>Error generating PDF</h1><p>Please try again or contact administrator.</p>";
}
exit();

