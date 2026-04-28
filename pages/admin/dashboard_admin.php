<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: home");
    exit();
}

include '../../config/database.php';
include '../../layouts/admin/header.php';
include '../../layouts/admin/sidebar.php';

if ($_SESSION['role'] === 'admin') {

    $queries = [
        'warga' => "SELECT COUNT(*) total FROM warga",
        'kk'    => "SELECT COUNT(*) total FROM kk",
        'rt'    => "SELECT COUNT(*) total FROM rt",
        'rw'    => "SELECT COUNT(*) total FROM rw",
        'users' => "SELECT COUNT(*) total FROM users",
        'rt_aktif' => "SELECT COUNT(*) total FROM rt WHERE status = 'aktif'",
        'rt_tidak_aktif' => "SELECT COUNT(*) total FROM rt WHERE status = 'tidak aktif'"
    ];

    foreach ($queries as $k => $q) {
        $data[$k] = mysqli_fetch_assoc(mysqli_query($conn, $q))['total'];
    }

    // Get recent audit logs
    $audit_logs = mysqli_query($conn, "SELECT * FROM audit_log ORDER BY created_at DESC LIMIT 8");
    $recent_users = mysqli_query($conn, "SELECT username, role, created_at FROM users ORDER BY created_at DESC LIMIT 5");

    $stats = [
        ['Total Warga', $data['warga'], 'users', 'blue', 'fa-users'],
        ['Total KK', $data['kk'], 'home', 'green', 'fa-address-card'],
        ['Total RT', $data['rt'], 'map-marker-alt', 'purple', 'fa-building'],
        ['Total RW', $data['rw'], 'building', 'yellow', 'fa-city'],
        ['Total Users', $data['users'], 'user', 'red', 'fa-user-circle'],
    ];

    $days = [];
    $traffic = [];
    $indonesian_days = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];

    for ($i = 6; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $day_num = date('w', strtotime($date));
        $days[] = $indonesian_days[$day_num];
        $query = "SELECT COUNT(*) as count FROM users WHERE DATE(created_at) = '$date'";
        $result = mysqli_query($conn, $query);
        $count = mysqli_fetch_assoc($result)['count'];
        $traffic[] = $count;
    }
} else {
    header("Location: home");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Lurahgo</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes pulseGlow {
            0%, 100% { opacity: 0.6; }
            50% { opacity: 1; }
        }
        
        @keyframes shimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }

        .animate-fade-in {
            animation: fadeIn 0.6s ease-out forwards;
        }
        
        .stat-card {
            transition: all 0.3s cubic-bezier(0.2, 0.9, 0.4, 1.1);
        }
        
        .stat-card:hover {
            transform: translateY(-6px);
        }
        
        .hover-scale {
            transition: transform 0.2s ease;
        }
        
        .hover-scale:hover {
            transform: scale(1.02);
        }
        
        .section-divider {
            background: linear-gradient(90deg, transparent, rgba(0,0,0,0.08), transparent);
            height: 1px;
            margin: 2rem 0;
        }
        
        /* custom scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }
        
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
        
        .table-hover tbody tr:hover {
            background: #f8fafc;
            transition: background 0.2s;
        }
        
        .quick-action-card {
            position: relative;
            overflow: hidden;
        }
        
        .quick-action-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left 0.5s;
        }
        
        .quick-action-card:hover::before {
            left: 100%;
        }
    </style>
</head>
<body>
<div id="mainContent" class="ml-64 min-h-screen bg-gradient-to-br from-gray-50 via-gray-50 to-slate-100">
    <div class="p-6 lg:p-8">
        
        <!-- Header Welcome -->
        <div class="mb-8 animate-fade-in">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800 tracking-tight">Dashboard Admin</h1>
                    <p class="text-gray-500 mt-1">Selamat datang kembali! Berikut ringkasan sistem Anda.</p>
                </div>
                <div class="flex items-center gap-3">
                    <div class="bg-white rounded-full px-4 py-2 shadow-sm border border-gray-200">
                        <i class="fas fa-calendar-alt text-blue-500 mr-2"></i>
                        <span class="text-sm text-gray-600"><?= date('d F Y') ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards Premium -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-5 mb-10">
            <?php foreach ($stats as $index => $s): 
                $colorMap = [
                    'blue' => 'from-blue-500 to-blue-600',
                    'green' => 'from-emerald-500 to-teal-600',
                    'purple' => 'from-purple-500 to-indigo-600',
                    'yellow' => 'from-amber-500 to-orange-600',
                    'red' => 'from-rose-500 to-red-600'
                ];
                $bgMap = [
                    'blue' => 'bg-blue-50',
                    'green' => 'bg-emerald-50',
                    'purple' => 'bg-purple-50',
                    'yellow' => 'bg-amber-50',
                    'red' => 'bg-rose-50'
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

        <!-- Audit Log & Recent Users - 2 Columns Premium -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <!-- Audit Log - Lebar penuh di grid -->
            <div class="lg:col-span-2 bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden hover:shadow-2xl transition-all duration-300 animate-fade-in" style="animation-delay: 0.1s">
                <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-5 border-b border-gray-200">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center">
                            <div class="w-11 h-11 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-md">
                                <i class="fas fa-history text-white text-lg"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-bold text-gray-800">Audit Log</h3>
                                <p class="text-xs text-gray-500">Riwayat aktivitas sistem terkini</p>
                            </div>
                        </div>
                        <a href="export_audit_log" class="bg-gradient-to-r from-blue-500 to-indigo-600 text-white px-4 py-2 rounded-xl hover:from-blue-600 hover:to-indigo-700 flex items-center gap-2 text-sm font-medium shadow-md transition-all hover:shadow-lg">
                            <i class="fas fa-download text-xs"></i> Export
                        </a>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-200">
                                <th class="px-5 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Aksi</th>
                                <th class="px-5 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Tabel</th>
                                <th class="px-5 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">User</th>
                                <th class="px-5 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Waktu</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php
                            if (mysqli_num_rows($audit_logs) > 0) {
                                while ($log = mysqli_fetch_assoc($audit_logs)) {
                                    $badge_class = 'bg-blue-100 text-blue-700';
                                    $icon = 'fa-circle-info';
                                    if($log['action'] == 'insert') { $badge_class = 'bg-emerald-100 text-emerald-700'; $icon = 'fa-plus-circle'; }
                                    elseif($log['action'] == 'update') { $badge_class = 'bg-amber-100 text-amber-700'; $icon = 'fa-pen'; }
                                    elseif($log['action'] == 'delete') { $badge_class = 'bg-rose-100 text-rose-700'; $icon = 'fa-trash-alt'; }
                                    elseif($log['action'] == 'login') { $badge_class = 'bg-purple-100 text-purple-700'; $icon = 'fa-sign-in-alt'; }
                                    
                                    echo "<tr class='hover:bg-gray-50 transition-colors'>";
                                    echo "<td class='px-5 py-3'><span class='inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold {$badge_class}'><i class='fas {$icon} text-xs'></i>" . strtoupper(htmlspecialchars($log['action'])) . "</span></td>";
                                    echo "<td class='px-5 py-3'><span class='font-mono text-xs text-gray-700 bg-gray-100 px-2 py-1 rounded'>" . htmlspecialchars($log['table_name']) . "</span></td>";
                                    echo "<td class='px-5 py-3 text-gray-700 font-medium'>" . htmlspecialchars($log['username'] ?? 'System') . "</td>";
                                    echo "<td class='px-5 py-3 text-gray-500 text-xs'>" . date('d/m/Y H:i', strtotime($log['created_at'])) . "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='4' class='px-5 py-12 text-center text-gray-400'><i class='fas fa-inbox text-5xl mb-3 text-gray-300'></i><p>Belum ada aktivitas audit</p></td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Recent Users Card Premium -->
            <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden hover:shadow-2xl transition-all duration-300 animate-fade-in" style="animation-delay: 0.15s">
                <div class="bg-gradient-to-r from-emerald-50 to-teal-50 px-6 py-5 border-b border-emerald-100">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center">
                            <div class="w-11 h-11 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-xl flex items-center justify-center shadow-md">
                                <i class="fas fa-user-plus text-white text-lg"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-bold text-gray-800">Pengguna Terbaru</h3>
                                <p class="text-xs text-gray-500">5 user terakhir mendaftar</p>
                            </div>
                        </div>
                        <a href="manage_users" class="text-emerald-600 hover:text-emerald-700 text-sm font-medium flex items-center gap-1">
                            Lihat Semua <i class="fas fa-arrow-right text-xs"></i>
                        </a>
                    </div>
                </div>
                <div class="divide-y divide-gray-100">
                    <?php
                    if (mysqli_num_rows($recent_users) > 0) {
                        while ($user = mysqli_fetch_assoc($recent_users)) {
                            $role_badge = 'bg-gray-100 text-gray-600';
                            $role_icon = 'fa-user';
                            if($user['role'] == 'admin') { $role_badge = 'bg-rose-100 text-rose-700'; $role_icon = 'fa-shield-alt'; }
                            elseif($user['role'] == 'ketua') { $role_badge = 'bg-purple-100 text-purple-700'; $role_icon = 'fa-crown'; }
                            elseif($user['role'] == 'user') { $role_badge = 'bg-emerald-100 text-emerald-700'; $role_icon = 'fa-user'; }
                            
                            echo "<div class='flex items-center justify-between px-5 py-4 hover:bg-gray-50 transition-colors'>";
                            echo "<div class='flex items-center gap-3'>";
                            echo "<div class='w-10 h-10 rounded-full bg-gradient-to-br from-gray-200 to-gray-300 flex items-center justify-center'><i class='fas {$role_icon} text-gray-600'></i></div>";
                            echo "<div><p class='font-semibold text-gray-800'>" . htmlspecialchars($user['username']) . "</p><span class='inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium {$role_badge}'><i class='fas {$role_icon} text-xs'></i>" . htmlspecialchars($user['role']) . "</span></div>";
                            echo "</div>";
                            echo "<div class='text-right'><p class='text-xs text-gray-400'>" . date('d/m/Y', strtotime($user['created_at'])) . "</p></div>";
                            echo "</div>";
                        }
                    } else {
                        echo "<div class='px-5 py-12 text-center text-gray-400'><i class='fas fa-users text-4xl mb-2 text-gray-300'></i><p class='text-sm'>Belum ada pengguna</p></div>";
                    }
                    ?>
                </div>
            </div>
        </div>

        <!-- Quick Actions Section - Premium -->
        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden mb-8 animate-fade-in" style="animation-delay: 0.2s">
            <div class="bg-gradient-to-r from-amber-50 to-yellow-50 px-6 py-5 border-b border-amber-100">
                <div class="flex items-center">
                    <div class="w-11 h-11 bg-gradient-to-br from-amber-500 to-orange-600 rounded-xl flex items-center justify-center shadow-md">
                        <i class="fas fa-bolt text-white text-lg"></i>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-bold text-gray-800">Quick Actions</h3>
                        <p class="text-xs text-gray-500">Akses cepat ke fitur utama</p>
                    </div>
                </div>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
                    <a href="tambah_user" class="quick-action-card group bg-gradient-to-br from-blue-50 to-indigo-50 hover:from-blue-100 hover:to-indigo-100 rounded-xl p-4 text-center transition-all duration-300 border border-blue-100 hover:border-blue-200 hover:shadow-lg">
                        <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center mx-auto mb-3 shadow-md group-hover:scale-110 transition-transform">
                            <i class="fas fa-user-plus text-white text-xl"></i>
                        </div>
                        <h4 class="font-bold text-gray-800 text-sm">Tambah User</h4>
                        <p class="text-xs text-gray-500 mt-1">Buat akun baru</p>
                    </a>
                    <a href="tambah_rt" class="quick-action-card group bg-gradient-to-br from-emerald-50 to-teal-50 hover:from-emerald-100 hover:to-teal-100 rounded-xl p-4 text-center transition-all duration-300 border border-emerald-100 hover:border-emerald-200 hover:shadow-lg">
                        <div class="w-12 h-12 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-xl flex items-center justify-center mx-auto mb-3 shadow-md group-hover:scale-110 transition-transform">
                            <i class="fas fa-plus-circle text-white text-xl"></i>
                        </div>
                        <h4 class="font-bold text-gray-800 text-sm">Tambah RT</h4>
                        <p class="text-xs text-gray-500 mt-1">Buat RT baru</p>
                    </a>
                    <a href="manage_users" class="quick-action-card group bg-gradient-to-br from-purple-50 to-fuchsia-50 hover:from-purple-100 hover:to-fuchsia-100 rounded-xl p-4 text-center transition-all duration-300 border border-purple-100 hover:border-purple-200 hover:shadow-lg">
                        <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-fuchsia-600 rounded-xl flex items-center justify-center mx-auto mb-3 shadow-md group-hover:scale-110 transition-transform">
                            <i class="fas fa-users-cog text-white text-xl"></i>
                        </div>
                        <h4 class="font-bold text-gray-800 text-sm">Kelola User</h4>
                        <p class="text-xs text-gray-500 mt-1">Edit & hapus user</p>
                    </a>
                    <a href="manage_rt_rw" class="quick-action-card group bg-gradient-to-br from-orange-50 to-amber-50 hover:from-orange-100 hover:to-amber-100 rounded-xl p-4 text-center transition-all duration-300 border border-orange-100 hover:border-orange-200 hover:shadow-lg">
                        <div class="w-12 h-12 bg-gradient-to-br from-orange-500 to-amber-600 rounded-xl flex items-center justify-center mx-auto mb-3 shadow-md group-hover:scale-110 transition-transform">
                            <i class="fas fa-map-marked-alt text-white text-xl"></i>
                        </div>
                        <h4 class="font-bold text-gray-800 text-sm">Kelola RT/RW</h4>
                        <p class="text-xs text-gray-500 mt-1">Atur struktur</p>
                    </a>
                    <a href="gallery" class="quick-action-card group bg-gradient-to-br from-pink-50 to-rose-50 hover:from-pink-100 hover:to-rose-100 rounded-xl p-4 text-center transition-all duration-300 border border-pink-100 hover:border-pink-200 hover:shadow-lg">
                        <div class="w-12 h-12 bg-gradient-to-br from-pink-500 to-rose-600 rounded-xl flex items-center justify-center mx-auto mb-3 shadow-md group-hover:scale-110 transition-transform">
                            <i class="fas fa-images text-white text-xl"></i>
                        </div>
                        <h4 class="font-bold text-gray-800 text-sm">Galeri</h4>
                        <p class="text-xs text-gray-500 mt-1">Kelola foto</p>
                    </a>
                    <a href="export_audit_log" class="quick-action-card group bg-gradient-to-br from-slate-50 to-gray-100 hover:from-slate-100 hover:to-gray-200 rounded-xl p-4 text-center transition-all duration-300 border border-gray-200 hover:border-gray-300 hover:shadow-lg">
                        <div class="w-12 h-12 bg-gradient-to-br from-slate-600 to-gray-700 rounded-xl flex items-center justify-center mx-auto mb-3 shadow-md group-hover:scale-110 transition-transform">
                            <i class="fas fa-file-export text-white text-xl"></i>
                        </div>
                        <h4 class="font-bold text-gray-800 text-sm">Export Log</h4>
                        <p class="text-xs text-gray-500 mt-1">Unduh audit log</p>
                    </a>
                </div>
            </div>
        </div>

        <!-- Two columns: RT Distribution + Traffic -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- RT Distribution Chart -->
            <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden hover:shadow-2xl transition-all duration-300 animate-fade-in" style="animation-delay: 0.25s">
                <div class="bg-gradient-to-r from-green-50 to-emerald-50 px-6 py-5 border-b border-green-100">
                    <div class="flex items-center">
                        <div class="w-11 h-11 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center shadow-md">
                            <i class="fas fa-chart-pie text-white text-lg"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-bold text-gray-800">Distribusi RT</h3>
                            <p class="text-xs text-gray-500">Status aktif dan tidak aktif</p>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <div class="flex justify-center">
                        <div class="w-64 h-64">
                            <canvas id="rtDistributionChart"></canvas>
                        </div>
                    </div>
                    <div class="flex justify-center gap-8 mt-4">
                        <div class="flex items-center gap-2">
                            <div class="w-4 h-4 bg-emerald-500 rounded-full shadow-sm"></div>
                            <span class="text-sm font-medium text-gray-700">Aktif: <strong class="text-emerald-600"><?= $data['rt_aktif'] ?></strong></span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-4 h-4 bg-rose-500 rounded-full shadow-sm"></div>
                            <span class="text-sm font-medium text-gray-700">Tidak Aktif: <strong class="text-rose-600"><?= $data['rt_tidak_aktif'] ?></strong></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Traffic Analytics with toggle -->
            <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden hover:shadow-2xl transition-all duration-300 animate-fade-in" style="animation-delay: 0.3s">
                <div class="bg-gradient-to-r from-blue-50 to-cyan-50 px-6 py-5 border-b border-blue-100 cursor-pointer" onclick="toggleTraffic()">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center">
                            <div class="w-11 h-11 bg-gradient-to-br from-blue-500 to-cyan-600 rounded-xl flex items-center justify-center shadow-md">
                                <i class="fas fa-chart-line text-white text-lg"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-bold text-gray-800">Traffic Pengunjung</h3>
                                <p class="text-xs text-gray-500">Analitik kunjungan 7 hari terakhir</p>
                            </div>
                        </div>
                        <i id="trafficIcon" class="fas fa-chevron-up text-gray-400 text-lg"></i>
                    </div>
                </div>
                <div id="trafficBody" class="p-6">
                    <div class="mb-5 flex items-center justify-between flex-wrap gap-3">
                        <div class="flex items-center gap-3">
                            <div class="w-3 h-3 bg-blue-500 rounded-full animate-pulse"></div>
                            <span class="text-sm text-gray-600">Pengunjung Harian</span>
                        </div>
                        <div class="bg-gradient-to-r from-blue-50 to-cyan-50 px-5 py-2 rounded-xl border border-blue-200">
                            <p class="text-2xl font-bold text-blue-600"><?php echo number_format(array_sum($traffic)); ?></p>
                            <p class="text-[10px] text-gray-500 font-medium">Total 7 hari</p>
                        </div>
                    </div>
                    <div class="bg-gray-50 rounded-xl p-3">
                        <canvas id="trafficChart" height="100"></canvas>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="section-divider"></div>
        
        <!-- Footer note -->
        <div class="text-center text-xs text-gray-400 py-4">
            <i class="fas fa-shield-alt mr-1"></i> Sistem aman terenkripsi | Lurahgo.id v2.0
        </div>
    </div>
</div>

<script>
function toggleTraffic(){
    const body = document.getElementById('trafficBody');
    const icon = document.getElementById('trafficIcon');
    if(body.style.display === 'none'){
        body.style.display = 'block';
        icon.classList.remove('fa-chevron-down');
        icon.classList.add('fa-chevron-up');
    } else {
        body.style.display = 'none';
        icon.classList.remove('fa-chevron-up');
        icon.classList.add('fa-chevron-down');
    }
}

// Traffic Chart
new Chart(document.getElementById('trafficChart'), {
    type: 'line',
    data: {
        labels: <?= json_encode($days) ?>,
        datasets: [{
            label: 'Pengunjung',
            data: <?= json_encode($traffic) ?>,
            borderColor: '#3B82F6',
            backgroundColor: 'rgba(59, 130, 246, 0.08)',
            borderWidth: 3,
            fill: true,
            tension: 0.4,
            pointBackgroundColor: '#3B82F6',
            pointBorderColor: '#FFFFFF',
            pointBorderWidth: 2,
            pointRadius: 5,
            pointHoverRadius: 8,
            pointStyle: 'circle'
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
                padding: 10
            }
        },
        scales: {
            y: { beginAtZero: true, grid: { color: 'rgba(0, 0, 0, 0.05)' }, ticks: { color: '#64748B', stepSize: 1 } },
            x: { grid: { display: false }, ticks: { color: '#64748B', font: { size: 11 } } }
        }
    }
});

// RT Distribution Pie Chart
new Chart(document.getElementById('rtDistributionChart'), {
    type: 'doughnut',
    data: {
        labels: ['Aktif', 'Tidak Aktif'],
        datasets: [{ 
            data: [<?= $data['rt_aktif'] ?>, <?= $data['rt_tidak_aktif'] ?>], 
            backgroundColor: ['#10B981', '#F43F5E'],
            borderColor: ['#FFFFFF', '#FFFFFF'],
            borderWidth: 3,
            hoverOffset: 8,
            cutout: '55%'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: { display: false },
            tooltip: { backgroundColor: '#1E293B', titleColor: '#F1F5F9', bodyColor: '#CBD5E1', cornerRadius: 8 }
        }
    }
});
</script>

</body>
</html>