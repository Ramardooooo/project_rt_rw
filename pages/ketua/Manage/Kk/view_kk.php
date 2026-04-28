<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'ketua') {
    header("Location: ../../home.php");
    exit();
}

include '../../../../config/database.php';

$kk_id = (int)$_GET['id'];

// Get KK details
$kk_query = "SELECT * FROM kk WHERE id = ?";
$stmt = mysqli_prepare($conn, $kk_query);
mysqli_stmt_bind_param($stmt, "i", $kk_id);
mysqli_stmt_execute($stmt);
$kk_result = mysqli_stmt_get_result($stmt);
$kk = mysqli_fetch_assoc($kk_result);

if (!$kk) {
    header("Location: manage_kk");
    exit();
}

// Get KK members
$members_query = "SELECT * FROM warga WHERE kk_id = ? ORDER BY id";
$stmt = mysqli_prepare($conn, $members_query);
mysqli_stmt_bind_param($stmt, "i", $kk_id);
mysqli_stmt_execute($stmt);
$members_result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Kartu Keluarga - <?php echo htmlspecialchars($kk['no_kk']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50">
    <div class="min-h-screen">
        <!-- Header -->
        <div class="bg-white shadow-sm border-b">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center py-6">
                    <div class="flex items-center space-x-4">
                        <a href="manage_kk.php" class="text-gray-600 hover:text-gray-800">
                            <i class="fas fa-arrow-left mr-2"></i>Kembali
                        </a>
                        <h1 class="text-2xl font-bold text-gray-900">Detail Kartu Keluarga</h1>
                    </div>
                    <div class="flex space-x-2">
                        <a href="export_kk.php?id=<?php echo $kk_id; ?>" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                            <i class="fas fa-download mr-2"></i>Export KK Ini
                        </a>
                    </div>
                </div>
            </div>
</div>

        <!-- KK Card -->
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="bg-white rounded-lg shadow-lg border border-gray-200 overflow-hidden">
                <!-- KK Header -->
                <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-xl font-bold text-white">Kartu Keluarga</h2>
                            <p class="text-blue-100">No. <?php echo htmlspecialchars($kk['no_kk']); ?></p>
                        </div>
                        <div class="text-right">
                            <div class="text-blue-100 text-sm">Kepala Keluarga</div>
                            <div class="text-white font-semibold"><?php echo htmlspecialchars($kk['kepala_keluaraga']); ?></div>
                        </div>
                    </div>
                </div>

                <!-- KK Details -->
                <div class="px-6 py-4 bg-gray-50 border-b">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Nomor KK</label>
                            <p class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($kk['no_kk']); ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Kepala Keluarga</label>
                            <p class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($kk['kepala_keluaraga']); ?></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Tanggal Lahir</label>
                            <p class="mt-1 text-sm text-gray-900"><?php echo $kk['tanggal_lahir'] ? date('d/m/Y', strtotime($kk['tanggal_lahir'])) : '-'; ?></p>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Alamat</label>
                            <p class="mt-1 text-sm text-gray-900"><?php echo htmlspecialchars($kk['alamat'] ?? '-'); ?></p>
                        </div>
                    </div>
                </div>

                <!-- Members Section -->
                <div class="px-6 py-4">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Anggota Keluarga</h3>
                        <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded">
                            <?php echo mysqli_num_rows($members_result); ?> Anggota
                        </span>
                    </div>

                    <?php if (mysqli_num_rows($members_result) > 0): ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No.</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NIK</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Lengkap</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis Kelamin</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Lahir</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>

                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php
                                    $no = 1;
                                    while ($member = mysqli_fetch_assoc($members_result)):
                                    ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo $no++; ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($member['nik'] ?? '-'); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($member['nama'] ?? '-'); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full <?php echo $member['jk'] == 'L' ? 'bg-blue-100 text-blue-800' : 'bg-pink-100 text-pink-800'; ?>">
                                                <?php echo $member['jk'] == 'L' ? 'Laki-laki' : 'Perempuan'; ?>
                                            </span>
                                        </td>
                            
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?php echo $member['tanggal_lahir'] ? date('d/m/Y', strtotime($member['tanggal_lahir'])) : '-'; ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                                <?php
                                                switch($member['status']) {
                                                    case 'aktif': echo 'bg-green-100 text-green-800'; break;
                                                    case 'tidak_aktif': echo 'bg-red-100 text-red-800'; break;
                                                    case 'meninggal': echo 'bg-gray-100 text-gray-800'; break;
                                                    case 'pindah': echo 'bg-yellow-100 text-yellow-800'; break;
                                                    default: echo 'bg-gray-100 text-gray-800';
                                                }
                                                ?>">
                                                <?php
                                                switch($member['status']) {
                                                    case 'aktif': echo 'Aktif'; break;
                                                    case 'tidak_aktif': echo 'Tidak Aktif'; break;
                                                    case 'meninggal': echo 'Meninggal'; break;
                                                    case 'pindah': echo 'Pindah'; break;
                                                    default: echo ucfirst($member['status'] ?? '');
                                                }
                                                ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-8">
                            <i class="fas fa-users text-gray-400 text-4xl mb-4"></i>
                            <p class="text-gray-500">Belum ada anggota keluarga yang terdaftar.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
