<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'ketua') {
    header("Location: home");
    exit();
}

include '../../config/database.php';
include '../../layouts/ketua/header.php';
include '../../layouts/ketua/sidebar.php';

// Get user info
$user_id = $_SESSION['user_id'] ?? null;
$user_name = $_SESSION['username'] ?? 'Ketua RT';

// Statistics Queries
$total_warga = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM warga"))['total'];
$total_kk = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM kk"))['total'];
$total_rt = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM rt"))['total'];
$total_rw = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM rw"))['total'];

// Gender statistics
$laki_laki = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM warga WHERE jk = 'L' AND status = 'aktif'"))['total'];
$perempuan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM warga WHERE jk = 'P' AND status = 'aktif'"))['total'];

// Mutasi statistics (30 days)
$mutasi_datang = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM mutasi_warga WHERE jenis_mutasi = 'datang' AND tanggal_mutasi >= DATE_SUB(NOW(), INTERVAL 30 DAY)"))['total'];
$mutasi_pindah = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM mutasi_warga WHERE jenis_mutasi = 'pindah' AND tanggal_mutasi >= DATE_SUB(NOW(), INTERVAL 30 DAY)"))['total'];
$mutasi_meninggal = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM mutasi_warga WHERE jenis_mutasi = 'meninggal' AND tanggal_mutasi >= DATE_SUB(NOW(), INTERVAL 30 DAY)"))['total'];

// Recent activities
$recent_activities = mysqli_query($conn, "SELECT * FROM activities ORDER BY created_at DESC LIMIT 5");
$recent_announcements = mysqli_query($conn, "SELECT * FROM announcements ORDER BY created_at DESC LIMIT 3");
$recent_warga = mysqli_query($conn, "SELECT nama, jk, status FROM warga ORDER BY id DESC LIMIT 5");

// Calculate percentages
$total_jk = $laki_laki + $perempuan;
$persen_laki = $total_jk > 0 ? round(($laki_laki / $total_jk) * 100) : 0;
$persen_perempuan = $total_jk > 0 ? round(($perempuan / $total_jk) * 100) : 0;

// Warga status distribution
$warga_aktif = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM warga WHERE status = 'aktif'"))['total'];
$warga_tidak_aktif = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM warga WHERE status = 'tidak_aktif'"))['total'];
$warga_meninggal = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM warga WHERE status = 'meninggal'"))['total'];
$warga_pindah = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM warga WHERE status = 'pindah'"))['total'];

// Latest mutasi
$latest_mutasi = mysqli_query($conn, "SELECT mw.*, COALESCE(mw.nama_warga, w.nama) as nama_warga, w.nik FROM mutasi_warga mw LEFT JOIN warga w ON mw.warga_id = w.id ORDER BY mw.tanggal_mutasi DESC LIMIT 5");

$total_gallery = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM gallery"))['total'];
$warga_baru_bulan_ini = 0;

// Stats array for loop (mirip admin dashboard)
$stats = [
    ['Total Warga', $total_warga, 'users', 'blue', 'fa-users'],
    ['Total KK', $total_kk, 'home', 'green', 'fa-address-card'],
    ['Total RT', $total_rt, 'map-marker-alt', 'purple', 'fa-building'],
    ['Total RW', $total_rw, 'building', 'yellow', 'fa-city'],
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Ketua RT - Lurahgo</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(25px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes slideInLeft {
            from { opacity: 0; transform: translateX(-30px); }
            to { opacity: 1; transform: translateX(0); }
        }
        @keyframes scaleIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }
        
        .animate-fade-in { animation: fadeInUp 0.6s ease-out forwards; }
        .animate-slide-left { animation: slideInLeft 0.6s ease-out forwards; }
        .animate-scale-in { animation: scaleIn 0.5s ease-out forwards; }
        
        .stat-card {
            transition: all 0.35s cubic-bezier(0.2, 0.9, 0.4, 1.1);
        }
        .stat-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 25px 40px -12px rgba(0, 0, 0, 0.2);
        }
        
        .card-hover {
            transition: all 0.3s ease;
        }
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 35px -10px rgba(0, 0, 0, 0.12);
        }
        
        .progress-bar {
            transition: width 1.2s cubic-bezier(0.22, 0.97, 0.36, 1);
        }
        
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.7rem;
            font-weight: 600;
        }
        
        .table-row-hover:hover {
            background-color: #F8FAFC;
            transition: background 0.2s;
        }
        
        ::-webkit-scrollbar {
            width: 5px;
            height: 5px;
        }
        ::-webkit-scrollbar-track {
            background: #E2E8F0;
            border-radius: 10px;
        }
        ::-webkit-scrollbar-thumb {
            background: #94A3B8;
            border-radius: 10px;
        }
        
        .gradient-blue { background: linear-gradient(135deg, #2563EB 0%, #7C3AED 100%); }
        .gradient-green { background: linear-gradient(135deg, #059669 0%, #10B981 100%); }
        .gradient-purple { background: linear-gradient(135deg, #7C3AED 0%, #A855F7 100%); }
        .gradient-orange { background: linear-gradient(135deg, #EA580C 0%, #F97316 100%); }
        .gradient-teal { background: linear-gradient(135deg, #0D9488 0%, #14B8A6 100%); }
        .gradient-indigo { background: linear-gradient(135deg, #4F46E5 0%, #6366F1 100%); }
    </style>
</head>
<body>
<div id="mainContent" class="ml-64 min-h-screen bg-gradient-to-br from-gray-50 via-gray-50 to-slate-100">
    <div class="p-7 lg:p-8">
        
        <!-- Header Welcome - Tanpa teks sambutan dan tanggal (mirip admin) -->
        <div class="mb-8 animate-fade-in">
            <div class="flex items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800 tracking-tight">Dashboard Ketua RT</h1>
                    <p class="text-gray-500 text-sm mt-1">Ringkasan data kependudukan wilayah Anda</p>
                </div>
            </div>
        </div>

        <!-- Stats Cards Premium (Mirip gaya admin) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-10">
            <?php foreach ($stats as $index => $s): 
                $colorMap = [
                    'blue' => 'from-blue-500 to-blue-600',
                    'green' => 'from-emerald-500 to-teal-600',
                    'purple' => 'from-purple-500 to-indigo-600',
                    'yellow' => 'from-amber-500 to-orange-600'
                ];
            ?>
            <div class="stat-card bg-white rounded-2xl shadow-lg hover:shadow-2xl p-5 border border-gray-100 animate-fade-in" style="animation-delay: <?= $index * 0.05 ?>s">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1"><?= $s[0] ?></p>
                        <p class="text-2xl lg:text-3xl font-extrabold text-gray-800"><?= number_format($s[1]) ?></p>
                        <div class="flex items-center mt-2">
                            <span class="text-[10px] font-medium text-gray-400">Total Keseluruhan</span>
                        </div>
                    </div>
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br <?= $colorMap[$s[3]] ?> flex items-center justify-center shadow-lg">
                        <i class="fas <?= $s[4] ?> text-white text-2xl"></i>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Charts Row: Gender + Mutasi -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-7 mb-9">
            <!-- Gender Statistics with Pie Chart -->
            <div class="card-hover bg-white rounded-2xl shadow-xl overflow-hidden animate-slide-left" style="animation-delay: 0.1s">
                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-6 py-4 border-b border-blue-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-blue-500 rounded-xl flex items-center justify-center shadow-md">
                            <i class="fas fa-venus-mars text-white text-sm"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-800">Statistik Jenis Kelamin</h3>
                            <p class="text-xs text-gray-500">Distribusi warga berdasarkan gender</p>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <div class="flex flex-col md:flex-row items-center gap-6">
                        <div class="w-48 h-48 relative">
                            <canvas id="genderChart"></canvas>
                        </div>
                        <div class="flex-1 space-y-4">
                            <div>
                                <div class="flex justify-between mb-1">
                                    <span class="text-sm font-semibold text-blue-700"><i class="fas fa-mars mr-1"></i> Laki-laki</span>
                                    <span class="text-sm font-semibold text-blue-700"><?= $laki_laki ?> (<?= $persen_laki ?>%)</span>
                                </div>
                                <div class="w-full bg-blue-100 rounded-full h-2.5">
                                    <div class="bg-blue-600 h-2.5 rounded-full progress-bar" style="width: <?= $persen_laki ?>%"></div>
                                </div>
                            </div>
                            <div>
                                <div class="flex justify-between mb-1">
                                    <span class="text-sm font-semibold text-pink-700"><i class="fas fa-venus mr-1"></i> Perempuan</span>
                                    <span class="text-sm font-semibold text-pink-700"><?= $perempuan ?> (<?= $persen_perempuan ?>%)</span>
                                </div>
                                <div class="w-full bg-pink-100 rounded-full h-2.5">
                                    <div class="bg-pink-500 h-2.5 rounded-full progress-bar" style="width: <?= $persen_perempuan ?>%"></div>
                                </div>
                            </div>
                            <div class="pt-3 mt-2 border-t border-gray-100">
                                <div class="flex justify-between text-sm">
                                    <span class="text-gray-500">Total Terdata:</span>
                                    <span class="font-bold text-gray-800"><?= $total_jk ?> jiwa</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Mutasi Statistics -->
            <div class="card-hover bg-white rounded-2xl shadow-xl overflow-hidden animate-slide-left" style="animation-delay: 0.2s">
                <div class="bg-gradient-to-r from-emerald-50 to-teal-50 px-6 py-4 border-b border-emerald-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-emerald-500 rounded-xl flex items-center justify-center shadow-md">
                            <i class="fas fa-exchange-alt text-white text-sm"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-800">Mutasi Warga</h3>
                            <p class="text-xs text-gray-500">30 hari terakhir</p>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-4 rounded-xl bg-gradient-to-r from-emerald-50 to-green-50 border border-emerald-200">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 bg-emerald-500 rounded-xl flex items-center justify-center shadow-md">
                                    <i class="fas fa-sign-in-alt text-white text-lg"></i>
                                </div>
                                <div>
                                    <p class="font-bold text-gray-800">Pendatang Baru</p>
                                    <p class="text-xs text-gray-500">Warga pindah masuk</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-2xl font-extrabold text-emerald-600">+<?= $mutasi_datang ?></p>
                                <p class="text-[10px] text-emerald-500 font-medium">orang</p>
                            </div>
                        </div>

                        <div class="flex items-center justify-between p-4 rounded-xl bg-gradient-to-r from-rose-50 to-red-50 border border-rose-200">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 bg-rose-500 rounded-xl flex items-center justify-center shadow-md">
                                    <i class="fas fa-sign-out-alt text-white text-lg"></i>
                                </div>
                                <div>
                                    <p class="font-bold text-gray-800">Pindah Keluar</p>
                                    <p class="text-xs text-gray-500">Warga pindah</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-2xl font-extrabold text-rose-600">-<?= $mutasi_pindah ?></p>
                                <p class="text-[10px] text-rose-500 font-medium">orang</p>
                            </div>
                        </div>

                        <div class="flex items-center justify-between p-4 rounded-xl bg-gradient-to-r from-gray-50 to-slate-100 border border-gray-200">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 bg-gray-500 rounded-xl flex items-center justify-center shadow-md">
                                    <i class="fas fa-dove text-white text-lg"></i>
                                </div>
                                <div>
                                    <p class="font-bold text-gray-800">Meninggal Dunia</p>
                                    <p class="text-xs text-gray-500">Warga wafat</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-2xl font-extrabold text-gray-600"><?= $mutasi_meninggal ?></p>
                                <p class="text-[10px] text-gray-500 font-medium">orang</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Status Warga + Recent Activities Row -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-7 mb-9">
            <!-- Status Warga Distribution -->
            <div class="card-hover bg-white rounded-2xl shadow-xl overflow-hidden animate-fade-in" style="animation-delay: 0.25s">
                <div class="bg-gradient-to-r from-indigo-50 to-purple-50 px-6 py-4 border-b border-indigo-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-indigo-500 rounded-xl flex items-center justify-center shadow-md">
                            <i class="fas fa-chart-pie text-white text-sm"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-800">Status Warga</h3>
                            <p class="text-xs text-gray-500">Distribusi status kependudukan</p>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="text-center p-4 bg-emerald-50 rounded-xl border border-emerald-200">
                            <div class="w-14 h-14 bg-emerald-500 rounded-2xl flex items-center justify-center mx-auto mb-2 shadow-md">
                                <i class="fas fa-user-check text-white text-2xl"></i>
                            </div>
                            <p class="text-2xl font-bold text-emerald-700"><?= $warga_aktif ?></p>
                            <p class="text-xs font-semibold text-gray-600 mt-1">Aktif</p>
                        </div>
                        <div class="text-center p-4 bg-amber-50 rounded-xl border border-amber-200">
                            <div class="w-14 h-14 bg-amber-500 rounded-2xl flex items-center justify-center mx-auto mb-2 shadow-md">
                                <i class="fas fa-user-clock text-white text-2xl"></i>
                            </div>
                            <p class="text-2xl font-bold text-amber-700"><?= $warga_tidak_aktif ?></p>
                            <p class="text-xs font-semibold text-gray-600 mt-1">Tidak Aktif</p>
                        </div>
                        <div class="text-center p-4 bg-gray-100 rounded-xl border border-gray-200">
                            <div class="w-14 h-14 bg-gray-400 rounded-2xl flex items-center justify-center mx-auto mb-2 shadow-md">
                                <i class="fas fa-dove text-white text-2xl"></i>
                            </div>
                            <p class="text-2xl font-bold text-gray-700"><?= $warga_meninggal ?></p>
                            <p class="text-xs font-semibold text-gray-600 mt-1">Meninggal</p>
                        </div>
                        <div class="text-center p-4 bg-blue-50 rounded-xl border border-blue-200">
                            <div class="w-14 h-14 bg-blue-500 rounded-2xl flex items-center justify-center mx-auto mb-2 shadow-md">
                                <i class="fas fa-exchange-alt text-white text-2xl"></i>
                            </div>
                            <p class="text-2xl font-bold text-blue-700"><?= $warga_pindah ?></p>
                            <p class="text-xs font-semibold text-gray-600 mt-1">Pindah</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Activities -->
            <div class="card-hover bg-white rounded-2xl shadow-xl overflow-hidden animate-fade-in" style="animation-delay: 0.3s">
                <div class="bg-gradient-to-r from-blue-50 to-cyan-50 px-6 py-4 border-b border-blue-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-blue-500 rounded-xl flex items-center justify-center shadow-md">
                            <i class="fas fa-history text-white text-sm"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-800">Aktivitas Terbaru</h3>
                            <p class="text-xs text-gray-500">Riwayat kegiatan sistem</p>
                        </div>
                    </div>
                </div>
                <div class="p-4 max-h-80 overflow-y-auto">
                    <?php if (mysqli_num_rows($recent_activities) > 0): ?>
                        <?php while ($activity = mysqli_fetch_assoc($recent_activities)): ?>
                            <div class="flex items-start gap-3 p-3 rounded-xl hover:bg-gray-50 transition-all duration-200">
                                <div class="w-9 h-9 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-circle text-blue-500 text-xs"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-800"><?= htmlspecialchars($activity['description'] ?? 'Aktivitas sistem') ?></p>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full"><?= htmlspecialchars($activity['action'] ?? 'aksi') ?></span>
                                        <span class="text-xs text-gray-400"><i class="far fa-clock mr-1"></i><?= date('d/m H:i', strtotime($activity['created_at'])) ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="text-center py-10 text-gray-400">
                            <i class="fas fa-inbox text-5xl mb-3 text-gray-300"></i>
                            <p>Belum ada aktivitas</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Recent Announcements & Latest Warga -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-7 mb-9">
            <!-- Recent Announcements -->
            <div class="card-hover bg-white rounded-2xl shadow-xl overflow-hidden animate-slide-left" style="animation-delay: 0.35s">
                <div class="bg-gradient-to-r from-rose-50 to-pink-50 px-6 py-4 border-b border-rose-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-rose-500 rounded-xl flex items-center justify-center shadow-md">
                            <i class="fas fa-bullhorn text-white text-sm"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-800">Pengumuman Terbaru</h3>
                            <p class="text-xs text-gray-500">Informasi untuk warga</p>
                        </div>
                    </div>
                </div>
                <div class="p-5 space-y-3">
                    <?php if (mysqli_num_rows($recent_announcements) > 0): ?>
                        <?php while ($announcement = mysqli_fetch_assoc($recent_announcements)): ?>
                            <div class="p-4 rounded-xl bg-gradient-to-r from-rose-50 to-orange-50 border border-rose-200 hover:shadow-md transition-all">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <h4 class="font-bold text-gray-800"><?= htmlspecialchars($announcement['title']) ?></h4>
                                        <p class="text-sm text-gray-600 mt-1 line-clamp-2"><?= htmlspecialchars(substr($announcement['content'], 0, 100)) ?>...</p>
                                        <p class="text-xs text-gray-400 mt-2"><i class="far fa-calendar-alt mr-1"></i><?= date('d F Y', strtotime($announcement['created_at'])) ?></p>
                                    </div>
                                    <i class="fas fa-chevron-right text-gray-300 mt-2"></i>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="text-center py-10 text-gray-400">
                            <i class="fas fa-bullhorn text-5xl mb-3 text-gray-300"></i>
                            <p>Belum ada pengumuman</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Latest Warga Added -->
            <div class="card-hover bg-white rounded-2xl shadow-xl overflow-hidden animate-slide-left" style="animation-delay: 0.4s">
                <div class="bg-gradient-to-r from-teal-50 to-green-50 px-6 py-4 border-b border-teal-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-teal-500 rounded-xl flex items-center justify-center shadow-md">
                            <i class="fas fa-user-plus text-white text-sm"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-800">Warga Terbaru</h3>
                            <p class="text-xs text-gray-500">Data warga yang baru ditambahkan</p>
                        </div>
                    </div>
                </div>
                <div class="divide-y divide-gray-100">
                    <?php if (mysqli_num_rows($recent_warga) > 0): ?>
                        <?php while ($warga = mysqli_fetch_assoc($recent_warga)): ?>
                            <div class="flex items-center justify-between p-4 hover:bg-gray-50 transition-colors">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-gray-200 to-gray-300 flex items-center justify-center">
                                        <i class="fas fa-user text-gray-600"></i>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-gray-800"><?= htmlspecialchars($warga['nama']) ?></p>
                                        <div class="flex items-center gap-2 mt-0.5">
                                            <span class="text-xs text-gray-500"><i class="fas <?= $warga['jk'] == 'L' ? 'fa-mars' : 'fa-venus' ?> mr-1"></i><?= $warga['jk'] == 'L' ? 'Laki-laki' : 'Perempuan' ?></span>
                                            <span class="status-badge <?= $warga['status'] == 'aktif' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-600' ?>">
                                                <i class="fas <?= $warga['status'] == 'aktif' ? 'fa-check-circle' : 'fa-pause-circle' ?> text-xs"></i>
                                                <?= ucfirst($warga['status']) ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="text-center py-10 text-gray-400">
                            <i class="fas fa-users-slash text-5xl mb-3 text-gray-300"></i>
                            <p>Belum ada data warga</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Latest Mutasi Table -->
        <div class="card-hover bg-white rounded-2xl shadow-xl overflow-hidden mb-9 animate-fade-in" style="animation-delay: 0.45s">
            <div class="bg-gradient-to-r from-slate-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between flex-wrap gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-slate-600 rounded-xl flex items-center justify-center shadow-md">
                            <i class="fas fa-list-ul text-white text-sm"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-800">Riwayat Mutasi Terbaru</h3>
                            <p class="text-xs text-gray-500">5 data mutasi terakhir</p>
                        </div>
                    </div>
                    <a href="Manage/mutasi_warga.php" class="text-indigo-600 hover:text-indigo-700 text-sm font-medium flex items-center gap-1">
                        Lihat Semua <i class="fas fa-arrow-right text-xs"></i>
                    </a>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Nama Warga</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Jenis Mutasi</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Tanggal</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if (mysqli_num_rows($latest_mutasi) > 0): ?>
                            <?php while ($mutasi = mysqli_fetch_assoc($latest_mutasi)): 
                                $badge_class = 'bg-emerald-100 text-emerald-700';
                                $icon = 'fa-arrow-right';
                                if($mutasi['jenis_mutasi'] == 'pindah') {
                                    $badge_class = 'bg-rose-100 text-rose-700';
                                    $icon = 'fa-arrow-left';
                                } elseif($mutasi['jenis_mutasi'] == 'meninggal') {
                                    $badge_class = 'bg-gray-100 text-gray-600';
                                    $icon = 'fa-dove';
                                }
                            ?>
                                <tr class="table-row-hover">
                                    <td class="px-6 py-3 font-medium text-gray-800"><?= htmlspecialchars($mutasi['nama_warga']) ?></td>
                                    <td class="px-6 py-3">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold <?= $badge_class ?>">
                                            <i class="fas <?= $icon ?> text-xs"></i> <?= ucfirst($mutasi['jenis_mutasi']) ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-3 text-gray-600"><?= date('d/m/Y', strtotime($mutasi['tanggal_mutasi'])) ?></td>
                                    <td class="px-6 py-3 text-gray-500 max-w-xs truncate"><?= htmlspecialchars($mutasi['keterangan'] ?? '-') ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center text-gray-400">
                                    <i class="fas fa-inbox text-5xl mb-2 text-gray-300"></i>
                                    <p>Belum ada data mutasi</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Quick Access Section -->
        <div class="card-hover bg-white rounded-2xl shadow-xl overflow-hidden animate-fade-in" style="animation-delay: 0.5s">
            <div class="bg-gradient-to-r from-amber-50 to-yellow-50 px-6 py-4 border-b border-amber-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-amber-500 rounded-xl flex items-center justify-center shadow-md">
                        <i class="fas fa-bolt text-white text-sm"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-800">Akses Cepat</h3>
                        <p class="text-xs text-gray-500">Fitur yang sering digunakan</p>
                    </div>
                </div>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
                    <a href="Manage/manage_warga.php" class="group flex flex-col items-center p-4 rounded-xl bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-200 hover:shadow-lg transition-all duration-300 hover:-translate-y-1">
                        <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center mb-2 shadow-md group-hover:scale-110 transition-transform">
                            <i class="fas fa-users text-white text-lg"></i>
                        </div>
                        <span class="text-xs font-semibold text-gray-700 text-center">Kelola Warga</span>
                    </a>
                    <a href="Manage/manage_kk.php" class="group flex flex-col items-center p-4 rounded-xl bg-gradient-to-br from-emerald-50 to-teal-50 border border-emerald-200 hover:shadow-lg transition-all duration-300 hover:-translate-y-1">
                        <div class="w-12 h-12 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-xl flex items-center justify-center mb-2 shadow-md group-hover:scale-110 transition-transform">
                            <i class="fas fa-home text-white text-lg"></i>
                        </div>
                        <span class="text-xs font-semibold text-gray-700 text-center">Kelola KK</span>
                    </a>
                    <a href="Manage/manage_wilayah.php" class="group flex flex-col items-center p-4 rounded-xl bg-gradient-to-br from-purple-50 to-fuchsia-50 border border-purple-200 hover:shadow-lg transition-all duration-300 hover:-translate-y-1">
                        <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-fuchsia-600 rounded-xl flex items-center justify-center mb-2 shadow-md group-hover:scale-110 transition-transform">
                            <i class="fas fa-map-marked-alt text-white text-lg"></i>
                        </div>
                        <span class="text-xs font-semibold text-gray-700 text-center">Wilayah</span>
                    </a>
                    <a href="Manage/mutasi_warga.php" class="group flex flex-col items-center p-4 rounded-xl bg-gradient-to-br from-orange-50 to-amber-50 border border-orange-200 hover:shadow-lg transition-all duration-300 hover:-translate-y-1">
                        <div class="w-12 h-12 bg-gradient-to-br from-orange-500 to-amber-600 rounded-xl flex items-center justify-center mb-2 shadow-md group-hover:scale-110 transition-transform">
                            <i class="fas fa-exchange-alt text-white text-lg"></i>
                        </div>
                        <span class="text-xs font-semibold text-gray-700 text-center">Mutasi</span>
                    </a>
                    <a href="laporan.php" class="group flex flex-col items-center p-4 rounded-xl bg-gradient-to-br from-rose-50 to-red-50 border border-rose-200 hover:shadow-lg transition-all duration-300 hover:-translate-y-1">
                        <div class="w-12 h-12 bg-gradient-to-br from-rose-500 to-red-600 rounded-xl flex items-center justify-center mb-2 shadow-md group-hover:scale-110 transition-transform">
                            <i class="fas fa-chart-line text-white text-lg"></i>
                        </div>
                        <span class="text-xs font-semibold text-gray-700 text-center">Laporan</span>
                    </a>
                    <a href="../admin/announcements.php" class="group flex flex-col items-center p-4 rounded-xl bg-gradient-to-br from-sky-50 to-blue-50 border border-sky-200 hover:shadow-lg transition-all duration-300 hover:-translate-y-1">
                        <div class="w-12 h-12 bg-gradient-to-br from-sky-500 to-blue-600 rounded-xl flex items-center justify-center mb-2 shadow-md group-hover:scale-110 transition-transform">
                            <i class="fas fa-bullhorn text-white text-lg"></i>
                        </div>
                        <span class="text-xs font-semibold text-gray-700 text-center">Pengumuman</span>
                    </a>
                </div>
            </div>
        </div>

        <div class="text-center text-xs text-gray-400 py-6 mt-4">
            <i class="fas fa-shield-alt mr-1"></i> Sistem Informasi Terintegrasi | Lurahgo.id
        </div>
    </div>
</div>

<script>
// Gender Chart - Pie
const ctx = document.getElementById('genderChart').getContext('2d');
new Chart(ctx, {
    type: 'doughnut',
    data: {
        labels: ['Laki-laki', 'Perempuan'],
        datasets: [{
            data: [<?= $laki_laki ?>, <?= $perempuan ?>],
            backgroundColor: ['#3B82F6', '#EC4899'],
            borderColor: ['#FFFFFF', '#FFFFFF'],
            borderWidth: 3,
            cutout: '60%',
            hoverOffset: 10
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: '#1E293B',
                titleColor: '#F1F5F9',
                bodyColor: '#CBD5E1',
                cornerRadius: 8,
                callbacks: {
                    label: function(context) {
                        const label = context.label || '';
                        const value = context.raw || 0;
                        const total = <?= $total_jk ?>;
                        const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                        return `${label}: ${value} jiwa (${percentage}%)`;
                    }
                }
            }
        }
    }
});
</script>
</body>
</html>