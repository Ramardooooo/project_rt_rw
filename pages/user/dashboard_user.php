<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header("Location: home");
    exit();
}

include '../../config/database.php';
include '../../layouts/user/header.php';
include '../../layouts/user/sidebar.php';

$user_id = $_SESSION['user_id'];
$user_id_esc = mysqli_real_escape_string($conn, $user_id);
$user_query = "SELECT * FROM users WHERE id = '$user_id_esc'"; 
$user_result = mysqli_query($conn, $user_query);
$user = mysqli_fetch_assoc($user_result);

$nama = $user['username'] ?? '';

// Get KK based on warga's kk_id
$kk = null;
$kk_id_from_warga = null;
$nama_esc = mysqli_real_escape_string($conn, $nama);
$warga_query = "SELECT rt, rw, kk_id FROM warga WHERE nama = '$nama_esc'";
$warga_result = mysqli_query($conn, $warga_query);
if (!$warga_result) {
    die('Warga query error: ' . mysqli_error($conn));
}
$warga = mysqli_fetch_assoc($warga_result);

if ($warga && isset($warga['kk_id']) && $warga['kk_id']) {
    $kk_id_from_warga = $warga['kk_id'];
    $kk_query = "SELECT * FROM kk WHERE id = '$kk_id_from_warga'";
    $kk_result = mysqli_query($conn, $kk_query);
    $kk = mysqli_fetch_assoc($kk_result);
}

$rt_name = 'Belum ada';
$rw_name = 'Belum ada';
$rt_id = null;
$rw_id = null;

if ($warga && isset($warga['rt']) && isset($warga['rw'])) {
    $rt_id = $warga['rt'];
    $rw_id = $warga['rw'];
    
    $rt_id_esc = mysqli_real_escape_string($conn, $rt_id ?? '');
    $rw_id_esc = mysqli_real_escape_string($conn, $rw_id ?? '');
    $rt_rw_names_query = "SELECT rt.nama_rt, rw.name as nama_rw FROM rt JOIN rw ON rt.id_rw = rw.id WHERE rt.id = '$rt_id_esc' AND rw.id = '$rw_id_esc'";
    $rt_rw_names_result = mysqli_query($conn, $rt_rw_names_query);
    $rt_rw_names = mysqli_fetch_assoc($rt_rw_names_result);
    if ($rt_rw_names) {
        $rt_name = $rt_rw_names['nama_rt'];
        $rw_name = $rt_rw_names['nama_rw'];
    }
}

// Get personal data
$personal_query = "SELECT nik, alamat, tanggal_lahir, jk, kk_id, status_approval, pekerjaan FROM warga WHERE nama = '$nama_esc'"; 
$personal_result = mysqli_query($conn, $personal_query);
if (!$personal_result) {
    die('Personal query error: ' . mysqli_error($conn));
}
$personal = mysqli_fetch_assoc($personal_result);

// Get status approval
$status_approval = $personal ? ($personal['status_approval'] ?? 'diterima') : 'menunggu';
$kk_id = $personal['kk_id'] ?? null;

$warga_list = [];
if ($rt_id && $rw_id) {
    $warga_list_query = "SELECT nama, nik, alamat, jk, tanggal_lahir FROM warga WHERE rt = '$rt_id_esc' AND rw = '$rw_id_esc' ORDER BY nama ASC";
    $warga_list_result = mysqli_query($conn, $warga_list_query);
    $warga_list = mysqli_fetch_all($warga_list_result, MYSQLI_ASSOC);
}

$total_warga = count($warga_list);

$laki_laki = 0;
$perempuan = 0;
foreach ($warga_list as $w) {
    if ($w['jk'] === 'L') $laki_laki++;
    if ($w['jk'] === 'P') $perempuan++;
}

$announcements_query = "SELECT title, content, created_at FROM announcements ORDER BY created_at DESC LIMIT 5";
$announcements_result = mysqli_query($conn, $announcements_query);
$announcements = mysqli_fetch_all($announcements_result, MYSQLI_ASSOC);

$message = '';
$show_form = false;

$rt_list = [];
$rw_list = [];
$rt_result = mysqli_query($conn, "SELECT * FROM rt ORDER BY nama_rt");
if ($rt_result) $rt_list = mysqli_fetch_all($rt_result, MYSQLI_ASSOC);
$rw_result = mysqli_query($conn, "SELECT * FROM rw ORDER BY name");
if ($rw_result) $rw_list = mysqli_fetch_all($rw_result, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard User - Lurahgo</title>
    <style>
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(25px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes slideInLeft {
            from { opacity: 0; transform: translateX(-30px); }
            to { opacity: 1; transform: translateX(0); }
        }
        
        .animate-fade-in { animation: fadeInUp 0.6s ease-out forwards; }
        .animate-slide-left { animation: slideInLeft 0.6s ease-out forwards; }
        
        .stat-card {
            transition: all 0.35s cubic-bezier(0.2, 0.9, 0.4, 1.1);
        }
        .stat-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 25px 40px -12px rgba(0, 0, 0, 0.15);
        }
        
        .card-hover {
            transition: all 0.3s ease;
        }
        .card-hover:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 35px -10px rgba(0, 0, 0, 0.1);
        }
        
        .table-row-hover:hover {
            background-color: #F8FAFC;
            transition: background 0.2s;
        }
        
        .gradient-blue { background: linear-gradient(135deg, #2563EB 0%, #7C3AED 100%); }
        .gradient-green { background: linear-gradient(135deg, #059669 0%, #10B981 100%); }
        .gradient-purple { background: linear-gradient(135deg, #7C3AED 0%, #A855F7 100%); }
        .gradient-orange { background: linear-gradient(135deg, #EA580C 0%, #F97316 100%); }
        .gradient-teal { background: linear-gradient(135deg, #0D9488 0%, #14B8A6 100%); }
        .gradient-pink { background: linear-gradient(135deg, #DB2777 0%, #F43F5E 100%); }
        .gradient-indigo { background: linear-gradient(135deg, #4F46E5 0%, #6366F1 100%); }
        
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: #E2E8F0; border-radius: 10px; }
        ::-webkit-scrollbar-thumb { background: #94A3B8; border-radius: 10px; }
    </style>
</head>
<body>
<div id="mainContent" class="ml-64 min-h-screen bg-gradient-to-br from-gray-50 via-gray-50 to-slate-100">
    <div class="p-7 lg:p-8">
        
        <!-- Header Welcome -->
        <div class="mb-8 animate-fade-in">
            <div class="flex items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800 tracking-tight">Dashboard Warga</h1>
                    <p class="text-gray-500 text-sm mt-1">Informasi data kependudukan Anda</p>
                </div>
            </div>
        </div>
        
        <!-- Status Info Banner -->
        <?php if ($status_approval !== 'diterima'): ?>
            <?php 
            if ($status_approval === 'diterima') {
                $status_class = 'bg-green-100 border-green-400 text-green-700';
                $status_icon = 'fa-check-circle';
                $status_text = 'Data Anda DITERIMA';
            } elseif ($status_approval === 'ditolak') {
                $status_class = 'bg-red-100 border-red-400 text-red-700';
                $status_icon = 'fa-times-circle';
                $status_text = 'Data Anda DITOLAK - Silakan perbaiki data Anda';
            } else {
                $status_class = 'bg-yellow-100 border-yellow-400 text-yellow-700';
                $status_icon = 'fa-clock';
                $status_text = 'Data Anda MENUNGGU persetujuan';
            }
            ?>
            <div id="status-banner" class="<?php echo $status_class; ?> border-l-4 px-5 py-3 rounded-xl mb-7 animate-fade-in">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <i class="fas <?php echo $status_icon; ?> text-xl"></i>
                        <span class="font-medium"><?php echo $status_text; ?></span>
                    </div>
                    <button onclick="document.getElementById('status-banner').style.display='none'" class="text-gray-500 hover:text-gray-700">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        <?php endif; ?>

        <!-- Data Diri Form (if needed) -->
        <?php if ($show_form): ?>
        <div class="bg-white rounded-2xl shadow-xl mb-8 p-6 animate-fade-in">
            <div class="flex items-center gap-3 mb-5">
                <div class="w-11 h-11 bg-blue-500 rounded-xl flex items-center justify-center shadow-md">
                    <i class="fas fa-user-plus text-white text-lg"></i>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-gray-800">Lengkapi Data Diri</h3>
                    <p class="text-xs text-gray-500">Silakan lengkapi data diri Anda untuk melanjutkan</p>
                </div>
            </div>
            <form method="POST" class="space-y-5">
                <div class="grid md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">NIK</label>
                        <input type="text" name="nik" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Tanggal Lahir</label>
                        <input type="date" name="tanggal_lahir" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                    </div>
                </div>
                <div class="grid md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Jenis Kelamin</label>
                        <select name="jk" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                            <option value="">Pilih</option>
                            <option value="L">Laki-laki</option>
                            <option value="P">Perempuan</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Pekerjaan</label>
                        <input type="text" name="pekerjaan" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                    </div>
                </div>
                <div class="grid md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">RT</label>
                        <select name="rt_id" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                            <option value="">Pilih RT</option>
                            <?php foreach ($rt_list as $rt): ?>
                                <option value="<?php echo $rt['id']; ?>"><?php echo htmlspecialchars($rt['nama_rt']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">RW</label>
                        <select name="rw_id" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all">
                            <option value="">Pilih RW</option>
                            <?php foreach ($rw_list as $rw): ?>
                                <option value="<?php echo $rw['id']; ?>"><?php echo htmlspecialchars($rw['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Alamat Lengkap</label>
                    <textarea name="alamat" rows="2" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"></textarea>
                </div>
                <button type="submit" name="submit_data_diri" class="px-6 py-3 bg-gradient-to-r from-blue-500 to-indigo-600 text-white rounded-xl hover:shadow-lg transition-all font-semibold">
                    <i class="fas fa-save mr-2"></i>Simpan Data Diri
                </button>
            </form>
        </div>
        <?php endif; ?>

        <!-- Stats Cards Premium (Mirip Admin & Ketua) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-9">
            <div class="stat-card bg-white rounded-2xl shadow-lg hover:shadow-2xl p-5 border border-gray-100 animate-fade-in" style="animation-delay: 0.05s">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Nama Lengkap</p>
                        <p class="text-xl font-extrabold text-gray-800"><?php echo htmlspecialchars($nama); ?></p>
                    </div>
                    <div class="w-14 h-14 rounded-2xl gradient-blue flex items-center justify-center shadow-lg">
                        <i class="fas fa-user text-white text-2xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="stat-card bg-white rounded-2xl shadow-lg hover:shadow-2xl p-5 border border-gray-100 animate-fade-in" style="animation-delay: 0.1s">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">No. KK</p>
                        <p class="text-xl font-extrabold text-gray-800"><?php echo $kk ? htmlspecialchars($kk['no_kk']) : '-'; ?></p>
                    </div>
                    <div class="w-14 h-14 rounded-2xl gradient-green flex items-center justify-center shadow-lg">
                        <i class="fas fa-id-card text-white text-2xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="stat-card bg-white rounded-2xl shadow-lg hover:shadow-2xl p-5 border border-gray-100 animate-fade-in" style="animation-delay: 0.15s">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">RT / RW</p>
                        <p class="text-xl font-extrabold text-gray-800"><?php echo $rt_name; ?> / <?php echo $rw_name; ?></p>
                    </div>
                    <div class="w-14 h-14 rounded-2xl gradient-purple flex items-center justify-center shadow-lg">
                        <i class="fas fa-map-marker-alt text-white text-2xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="stat-card bg-white rounded-2xl shadow-lg hover:shadow-2xl p-5 border border-gray-100 animate-fade-in" style="animation-delay: 0.2s">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Total Warga</p>
                        <p class="text-xl font-extrabold text-gray-800"><?php echo $total_warga; ?> Orang</p>
                    </div>
                    <div class="w-14 h-14 rounded-2xl gradient-orange flex items-center justify-center shadow-lg">
                        <i class="fas fa-users text-white text-2xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistik JK + Data Diri -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-9">
            <!-- Statistik Jenis Kelamin -->
            <div class="card-hover bg-white rounded-2xl shadow-xl overflow-hidden animate-slide-left" style="animation-delay: 0.1s">
                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 px-6 py-4 border-b border-blue-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-blue-500 rounded-xl flex items-center justify-center shadow-md">
                            <i class="fas fa-chart-pie text-white text-sm"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-800">Statistik Jenis Kelamin</h3>
                            <p class="text-xs text-gray-500">Distribusi warga RT / RW</p>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <div class="space-y-5">
                        <div>
                            <div class="flex justify-between mb-2">
                                <span class="text-sm font-semibold text-blue-700"><i class="fas fa-mars mr-1"></i> Laki-laki</span>
                                <span class="text-sm font-semibold text-blue-700"><?php echo $laki_laki; ?> orang</span>
                            </div>
                            <div class="w-full bg-blue-100 rounded-full h-3">
                                <div class="bg-blue-600 h-3 rounded-full transition-all duration-700" style="width: <?php echo $total_warga > 0 ? ($laki_laki / $total_warga * 100) : 0; ?>%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between mb-2">
                                <span class="text-sm font-semibold text-pink-700"><i class="fas fa-venus mr-1"></i> Perempuan</span>
                                <span class="text-sm font-semibold text-pink-700"><?php echo $perempuan; ?> orang</span>
                            </div>
                            <div class="w-full bg-pink-100 rounded-full h-3">
                                <div class="bg-pink-500 h-3 rounded-full transition-all duration-700" style="width: <?php echo $total_warga > 0 ? ($perempuan / $total_warga * 100) : 0; ?>%"></div>
                            </div>
                        </div>
                        <div class="pt-4 mt-2 border-t border-gray-100">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Total Terdata:</span>
                                <span class="font-bold text-gray-800"><?php echo $total_warga; ?> jiwa</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Data Diri User -->
            <div class="lg:col-span-2 card-hover bg-white rounded-2xl shadow-xl overflow-hidden animate-slide-left" style="animation-delay: 0.15s">
                <div class="bg-gradient-to-r from-emerald-50 to-teal-50 px-6 py-4 border-b border-emerald-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-emerald-500 rounded-xl flex items-center justify-center shadow-md">
                            <i class="fas fa-user-circle text-white text-sm"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-800">Data Diri Anda</h3>
                            <p class="text-xs text-gray-500">Informasi pribadi terdaftar</p>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                            <p class="text-xs text-gray-500 mb-1">NIK</p>
                            <p class="text-sm font-bold text-gray-800"><?php echo $personal && isset($personal['nik']) ? htmlspecialchars($personal['nik']) : '-'; ?></p>
                        </div>
                        <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                            <p class="text-xs text-gray-500 mb-1">Tanggal Lahir</p>
                            <p class="text-sm font-bold text-gray-800"><?php echo $personal && isset($personal['tanggal_lahir']) ? date('d-m-Y', strtotime($personal['tanggal_lahir'])) : '-'; ?></p>
                        </div>
                        <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                            <p class="text-xs text-gray-500 mb-1">Jenis Kelamin</p>
                            <p class="text-sm font-bold text-gray-800"><?php echo $personal && isset($personal['jk']) && $personal['jk'] === 'L' ? 'Laki-laki' : ($personal && isset($personal['jk']) && $personal['jk'] === 'P' ? 'Perempuan' : '-'); ?></p>
                        </div>
                        <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                            <p class="text-xs text-gray-500 mb-1">Pekerjaan</p>
                            <p class="text-sm font-bold text-gray-800"><?php echo $personal && isset($personal['pekerjaan']) && $personal['pekerjaan'] ? htmlspecialchars($personal['pekerjaan']) : '-'; ?></p>
                        </div>
                    </div>
                    <div class="mt-4 bg-gray-50 rounded-xl p-4 border border-gray-100">
                        <p class="text-xs text-gray-500 mb-1">Alamat Lengkap</p>
                        <p class="text-sm font-bold text-gray-800"><?php echo $personal && isset($personal['alamat']) && $personal['alamat'] ? htmlspecialchars($personal['alamat']) : '-'; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Daftar Warga Table -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden mb-9 animate-fade-in" style="animation-delay: 0.2s">
            <div class="bg-gradient-to-r from-indigo-50 to-purple-50 px-6 py-4 border-b border-indigo-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-indigo-500 rounded-xl flex items-center justify-center shadow-md">
                        <i class="fas fa-users text-white text-sm"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-800">Daftar Warga</h3>
                        <p class="text-xs text-gray-500">RT <?php echo $rt_name; ?> / RW <?php echo $rw_name; ?></p>
                    </div>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">No</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Nama</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">JK</th>
                            <th class="px-6 py-3 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Alamat</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if ($warga_list && count($warga_list) > 0): $no = 1; ?>
                            <?php foreach ($warga_list as $warga_item): ?>
                                <tr class="table-row-hover">
                                    <td class="px-6 py-3 text-sm text-gray-500"><?php echo $no++; ?></td>
                                    <td class="px-6 py-3 text-sm font-medium text-gray-800"><?php echo htmlspecialchars($warga_item['nama']); ?></td>
                                    <td class="px-6 py-3">
                                        <?php if ($warga_item['jk'] === 'L'): ?>
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">
                                                <i class="fas fa-mars text-xs"></i> Laki-laki
                                            </span>
                                        <?php elseif ($warga_item['jk'] === 'P'): ?>
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-pink-100 text-pink-700">
                                                <i class="fas fa-venus text-xs"></i> Perempuan
                                            </span>
                                        <?php else: ?>
                                            <span class="text-gray-400">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-3 text-sm text-gray-500"><?php echo htmlspecialchars($warga_item['alamat'] ?? '-'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center text-gray-400">
                                    <i class="fas fa-inbox text-5xl mb-2 text-gray-300"></i>
                                    <p>Belum ada data warga</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pengumuman Terbaru & Quick Access -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Announcements -->
            <div class="card-hover bg-white rounded-2xl shadow-xl overflow-hidden animate-fade-in" style="animation-delay: 0.25s">
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
                <div class="p-5 space-y-3 max-h-80 overflow-y-auto">
                    <?php if ($announcements && count($announcements) > 0): ?>
                        <?php foreach ($announcements as $announcement): ?>
                            <div class="p-4 rounded-xl bg-gradient-to-r from-rose-50 to-orange-50 border border-rose-200 hover:shadow-md transition-all">
                                <h4 class="font-bold text-gray-800 mb-1"><?php echo htmlspecialchars($announcement['title']); ?></h4>
                                <p class="text-sm text-gray-600 line-clamp-2"><?php echo htmlspecialchars(substr($announcement['content'], 0, 100)) . (strlen($announcement['content']) > 100 ? '...' : ''); ?></p>
                                <p class="text-xs text-gray-400 mt-2"><i class="far fa-calendar-alt mr-1"></i><?php echo date('d F Y', strtotime($announcement['created_at'])); ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="text-center py-10 text-gray-400">
                            <i class="fas fa-bullhorn text-5xl mb-3 text-gray-300"></i>
                            <p>Belum ada pengumuman</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Access -->
            <div class="card-hover bg-white rounded-2xl shadow-xl overflow-hidden animate-fade-in" style="animation-delay: 0.3s">
                <div class="bg-gradient-to-r from-amber-50 to-yellow-50 px-6 py-4 border-b border-amber-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-amber-500 rounded-xl flex items-center justify-center shadow-md">
                            <i class="fas fa-bolt text-white text-sm"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-800">Akses Cepat</h3>
                            <p class="text-xs text-gray-500">Menu favorit Anda</p>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <a href="../../account/settings" class="group flex items-center p-4 bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl hover:shadow-lg transition-all duration-300 border border-blue-100 hover:-translate-y-1">
                            <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center mr-4 shadow-md group-hover:scale-110 transition-transform">
                                <i class="fas fa-cog text-white text-xl"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-800">Pengaturan</h4>
                                <p class="text-xs text-gray-500">Kelola akun Anda</p>
                            </div>
                        </a>
                        <a href="../../beranda/gallery" class="group flex items-center p-4 bg-gradient-to-br from-green-50 to-emerald-50 rounded-xl hover:shadow-lg transition-all duration-300 border border-green-100 hover:-translate-y-1">
                            <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center mr-4 shadow-md group-hover:scale-110 transition-transform">
                                <i class="fas fa-images text-white text-xl"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-800">Galeri</h4>
                                <p class="text-xs text-gray-500">Lihat foto desa</p>
                            </div>
                        </a>
                        <a href="../../beranda/announcements" class="group flex items-center p-4 bg-gradient-to-br from-purple-50 to-pink-50 rounded-xl hover:shadow-lg transition-all duration-300 border border-purple-100 hover:-translate-y-1">
                            <div class="w-12 h-12 bg-gradient-to-br from-purple-500 to-pink-600 rounded-xl flex items-center justify-center mr-4 shadow-md group-hover:scale-110 transition-transform">
                                <i class="fas fa-bullhorn text-white text-xl"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-800">Pengumuman</h4>
                                <p class="text-xs text-gray-500">Info terbaru</p>
                            </div>
                        </a>
                        <div class="group flex items-center p-4 bg-gradient-to-br from-teal-50 to-cyan-50 rounded-xl border border-teal-100 opacity-75">
                            <div class="w-12 h-12 bg-gradient-to-br from-teal-500 to-cyan-600 rounded-xl flex items-center justify-center mr-4 shadow-md">
                                <i class="fas fa-phone-alt text-white text-xl"></i>
                            </div>
                            <div>
                                <h4 class="font-bold text-gray-800">Kontak</h4>
                                <p class="text-xs text-gray-500">Hubungi pengurus</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="text-center text-xs text-gray-400 py-6 mt-2">
            <i class="fas fa-shield-alt mr-1"></i> Sistem Informasi Terintegrasi | Lurahgo.id
        </div>
    </div>
</div>

</body>
</html>