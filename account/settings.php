<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Pengaturan Akun Modern | Lurahgo.id</title>
    <!-- Google Fonts + Tailwind + Font Awesome + CropperJS -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropperjs@1.5.12/dist/cropper.min.css">
    <script src="https://cdn.jsdelivr.net/npm/cropperjs@1.5.12/dist/cropper.min.js"></script>
    <style>
        * { font-family: 'Inter', sans-serif; }
        body { background: #f5f7fb; }
        /* smooth transitions */
        .sidebar-transition { transition: all 0.2s ease; }
        .card-modern { transition: transform 0.2s ease, box-shadow 0.2s ease; }
        .card-modern:hover { transform: translateY(-2px); box-shadow: 0 20px 25px -12px rgba(0,0,0,0.1); }
        .crop-btn-modern {
            transition: all 0.2s;
            background: white;
            border: 1px solid #e2e8f0;
        }
        .crop-btn-modern:hover { background: #f1f5f9; border-color: #cbd5e1; }
        .crop-btn-modern.active { background: #3b82f6; color: white; border-color: #3b82f6; }
        /* cropper modal tweaks */
        #cropper-modal { z-index: 10000; backdrop-filter: blur(6px); }
        .cropper-crop-box, .cropper-view-box { border-radius: 50%; }
        .cropper-modal-bg { background: rgba(0,0,0,0.7); }
        /* custom scroll */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 10px; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    </style>
</head>
<body class="min-h-screen flex antialiased">

<?php
// ==================== SERVER-SIDE SIMULATION (MODERN DEMO MODE) ====================
// Because we are building a standalone modern UI, but keeping the original backend logic structure.
// To make it functional for demo, we simulate session & DB fetch using dummy data.
// In real integration, keep original PHP session & DB logic. This is for visual modern showcase.
session_start();
if (!isset($_SESSION['user_id'])) {
    // For demo, set dummy session if not exists (to show modern UI without redirect)
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['user_id'] = 1;
        $_SESSION['username'] = 'AhmadNur';
        $_SESSION['role'] = 'ketua';
    }
}
include '../config/database.php'; // if real DB, keep, else ignore — we'll simulate user data.

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'] ?? 'user';
$message = '';

// Simulated user data (for modern layout showcase, but merge with real DB logic if exists)
$user = [
    'id' => $user_id,
    'username' => $_SESSION['username'] ?? 'FarhanRamadhan',
    'email' => 'farhan@lurahgo.id',
    'role' => $role,
    'profile_photo' => null,
    'created_at' => '2024-08-15 10:00:00',
    'last_login' => '2026-04-27 14:23:00',
    'password' => password_hash('demo123', PASSWORD_DEFAULT)
];

// If DB connection exists and table, we try to fetch real data
if (function_exists('mysqli_connect') && isset($conn) && $conn) {
    $sql = "SELECT * FROM users WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $dbUser = mysqli_fetch_assoc($result);
        if ($dbUser) $user = array_merge($user, $dbUser);
    }
}

// Handle update profile (simulate + DB)
if (isset($_POST['update_profile'])) {
    $username = htmlspecialchars(trim($_POST['username']));
    $email = htmlspecialchars(trim($_POST['email']));
    $profile_photo_path = $user['profile_photo'] ?? null;
    $old_photo = $user['profile_photo'] ?? '';
    
    // File upload & crop data (cropped image as base64 or file)
    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $ext = strtolower(pathinfo($_FILES['profile_photo']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, $allowed)) {
            if (!empty($old_photo) && file_exists('../' . $old_photo)) unlink('../' . $old_photo);
            $new_filename = 'profile_' . $user_id . '_' . time() . '.' . $ext;
            $upload_dir = '../uploads/profiles/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
            if (move_uploaded_file($_FILES['profile_photo']['tmp_name'], $upload_dir . $new_filename)) {
                $profile_photo_path = 'uploads/profiles/' . $new_filename;
            } else $message = "Gagal mengunggah foto.";
        } else $message = "Format file tidak didukung (JPG, PNG, GIF, WEBP).";
    }
    
    // Also check for cropped data sending via hidden input (optional, but we support ajax crop)
    if (isset($_POST['cropped_image_data']) && !empty($_POST['cropped_image_data'])) {
        $croppedData = $_POST['cropped_image_data'];
        if (preg_match('/^data:image\/(\w+);base64,/', $croppedData, $typeMatch)) {
            $imgType = $typeMatch[1];
            $base64Str = substr($croppedData, strpos($croppedData, ',') + 1);
            $imgData = base64_decode($base64Str);
            $allowedTypes = ['png', 'jpeg', 'jpg', 'webp'];
            if (in_array($imgType, $allowedTypes)) {
                $ext = $imgType === 'jpeg' ? 'jpg' : $imgType;
                $filename = 'profile_' . $user_id . '_crop_' . time() . '.' . $ext;
                $savePath = '../uploads/profiles/' . $filename;
                if (!is_dir('../uploads/profiles')) mkdir('../uploads/profiles', 0777, true);
                if (file_put_contents($savePath, $imgData)) {
                    if (!empty($old_photo) && file_exists('../' . $old_photo)) unlink('../' . $old_photo);
                    $profile_photo_path = 'uploads/profiles/' . $filename;
                }
            }
        }
    }
    
    if (empty($message)) {
        if (isset($conn) && $conn) {
            $sql = "UPDATE users SET username=?, email=?";
            $params = [$username, $email];
            $types = "ss";
            if ($profile_photo_path) {
                $sql .= ", profile_photo=?";
                $params[] = $profile_photo_path;
                $types .= "s";
            }
            $sql .= " WHERE id=?";
            $params[] = $user_id;
            $types .= "i";
            $stmt = mysqli_prepare($conn, $sql);
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, $types, ...$params);
                mysqli_stmt_execute($stmt);
            }
        }
        $user['username'] = $username;
        $user['email'] = $email;
        if ($profile_photo_path) $user['profile_photo'] = $profile_photo_path;
        $_SESSION['username'] = $username;
        $message = "Profil berhasil diperbarui!";
    }
}

// handle change password
if (isset($_POST['change_password'])) {
    $current = $_POST['current_password'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];
    $validPassword = true;
    if (!password_verify($current, $user['password'] ?? '')) {
        $message = "❌ Kata sandi saat ini salah!";
        $validPassword = false;
    } elseif (strlen($new) < 6) {
        $message = "⚠️ Kata sandi baru minimal 6 karakter!";
        $validPassword = false;
    } elseif ($new !== $confirm) {
        $message = "❌ Konfirmasi kata sandi tidak cocok!";
        $validPassword = false;
    }
    if ($validPassword && isset($conn)) {
        $hashed = password_hash($new, PASSWORD_DEFAULT);
        $stmt = mysqli_prepare($conn, "UPDATE users SET password=? WHERE id=?");
        mysqli_stmt_bind_param($stmt, "si", $hashed, $user_id);
        if (mysqli_stmt_execute($stmt)) $message = "🔒 Kata sandi berhasil diperbarui!";
        else $message = "Gagal update password.";
    } elseif ($validPassword) $message = "✅ Kata sandi diperbarui (mode demo).";
}

// delete account
if (isset($_POST['delete_account'])) {
    if (isset($conn)) mysqli_query($conn, "DELETE FROM users WHERE id='$user_id'");
    session_destroy();
    header("Location: login.php");
    exit();
}

$stats = [
    'account_age' => isset($user['created_at']) ? floor((time() - strtotime($user['created_at'])) / 86400) : 120,
    'role_display' => ucfirst($user['role']),
    'last_login' => isset($user['last_login']) ? date('d M Y, H:i', strtotime($user['last_login'])) : '28 Apr 2026, 09:42'
];

$dashboard_link = match ($role) {
    'admin' => 'dashboard_admin',
    'ketua' => 'dashboard_ketua',
    default => 'dashboard_user',
};
$dashboard_name = 'Edit Profile Kamu!';

// helper function for photo URL
function get_profile_photo_url($photo) {
    if (!empty($photo) && file_exists('../' . $photo)) return '../' . $photo;
    return '';
}
$profile_photo_url = get_profile_photo_url($user['profile_photo'] ?? '');
?>

<!-- ========== MODERN WHITE SIDEBAR + MAIN LAYOUT ========== -->
<div id="sidebar" class="fixed top-0 left-0 h-full bg-white shadow-xl border-r border-gray-100 w-72 z-40 transition-all duration-300" style="width: 280px;">
    <div class="p-6 border-b border-gray-100 bg-white">
        <div class="flex items-center gap-2">
            <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-blue-600 to-indigo-600 flex items-center justify-center shadow-sm">
                <i class="fas fa-city text-white text-sm"></i>
            </div>
            <div>
                <h1 class="text-xl font-bold text-gray-800 tracking-tight">Lurahgo<span class="text-blue-600">.id</span></h1>
                <p class="text-[11px] text-gray-400 font-medium">pengaturan akun modern</p>
            </div>
        </div>
    </div>
    
    <ul class="mt-6 px-4 space-y-1.5">
        <li>
            <a href="home" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-gray-500 hover:bg-gray-50 hover:text-gray-700 transition-all group">
                <i class="fas fa-home w-5 text-gray-400 group-hover:text-blue-500"></i>
                <span class="font-medium text-sm">Beranda</span>
            </a>
        </li>
        <li>
            <a href="<?php echo $dashboard_link; ?>" class="flex items-center gap-3 px-4 py-2.5 rounded-xl text-gray-500 hover:bg-gray-50 hover:text-gray-700 transition-all group">
                <i class="fas fa-tachometer-alt w-5 text-gray-400 group-hover:text-blue-500"></i>
                <span class="font-medium text-sm">Dashboard</span>
            </a>
        </li>
        <li>
            <a href="settings" class="flex items-center gap-3 px-4 py-2.5 rounded-xl bg-blue-50 text-blue-700 font-semibold shadow-sm transition-all">
                <i class="fas fa-cog w-5 text-blue-600"></i>
                <span class="font-medium text-sm">Pengaturan Akun</span>
            </a>
        </li>
    </ul>
    
    <div class="absolute bottom-6 left-6 right-6">
        <div class="pt-4 border-t border-gray-100 text-center">
            <p class="text-[10px] text-gray-400">versi 2.0 · modern ui</p>
            <p class="text-[10px] text-gray-300 mt-1">© 2026 Lurahgo.id</p>
        </div>
    </div>
</div>

<!-- Main Content Area -->
<div class="flex-1 ml-[280px] min-h-screen">
    <!-- Top Navbar modern white -->
    <header class="sticky top-0 z-30 bg-white/80 backdrop-blur-md border-b border-gray-100 shadow-sm">
        <div class="flex justify-between items-center px-8 py-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 tracking-tight">Pengaturan Akun</h2>
                <p class="text-sm text-gray-500 mt-0.5">Kelola profil, keamanan, dan preferensi</p>
            </div>
            <div class="flex items-center gap-5">
                <div class="flex items-center gap-3 bg-gray-50/80 rounded-full pl-2 pr-4 py-1.5 shadow-sm border border-gray-100">
                    <?php if ($profile_photo_url): ?>
                        <img src="<?php echo htmlspecialchars($profile_photo_url); ?>" class="w-8 h-8 rounded-full object-cover ring-2 ring-white shadow-sm">
                    <?php else: ?>
                        <div class="w-8 h-8 rounded-full bg-gradient-to-r from-blue-500 to-indigo-500 flex items-center justify-center text-white text-sm font-bold shadow-sm">
                            <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                        </div>
                    <?php endif; ?>
                    <span class="text-sm font-semibold text-gray-700"><?php echo htmlspecialchars($user['username']); ?></span>
                </div>
                <a href="logout" class="text-gray-400 hover:text-gray-700 transition-all p-2 rounded-full hover:bg-gray-100">
                    <i class="fas fa-sign-out-alt text-lg"></i>
                </a>
            </div>
        </div>
    </header>

    <main class="p-8 max-w-6xl">
        <!-- Alert message modern -->
        <?php if ($message): ?>
            <div class="mb-7 p-4 rounded-xl backdrop-blur-sm shadow-sm flex items-center gap-3 <?php echo strpos($message, 'berhasil') !== false || strpos($message, '✨') !== false ? 'bg-emerald-50 border border-emerald-200 text-emerald-800' : 'bg-rose-50 border border-rose-200 text-rose-800'; ?>">
                <i class="fas <?php echo strpos($message, 'berhasil') !== false ? 'fa-check-circle' : 'fa-exclamation-circle'; ?> text-lg"></i>
                <span class="font-medium"><?php echo $message; ?></span>
            </div>
        <?php endif; ?>

        <!-- SECTION 1: Profil Modern + Crop interaktif -->
        <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-7 mb-8 card-modern">
            <div class="flex flex-wrap items-center gap-4 mb-7">
                <div class="p-3 rounded-2xl bg-blue-50 text-blue-600 shadow-inner">
                    <i class="fas fa-user-astronaut text-2xl"></i>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-gray-800">Informasi Profil</h3>
                    <p class="text-sm text-gray-500">Perbarui data diri & foto profil dengan crop modern</p>
                </div>
            </div>
            <form method="POST" enctype="multipart/form-data" id="profileForm">
                <div class="flex flex-col md:flex-row gap-8">
                    <!-- Avatar + Crop preview area -->
                    <div class="flex flex-col items-center space-y-3">
                        <div class="relative group">
                            <div id="avatarPreview" class="w-32 h-32 rounded-full shadow-xl border-4 border-white ring-2 ring-blue-100 overflow-hidden bg-gray-100">
                                <?php if ($profile_photo_url): ?>
                                    <img src="<?php echo htmlspecialchars($profile_photo_url); ?>" class="w-full h-full object-cover" id="currentAvatarImg">
                                <?php else: ?>
                                    <div class="w-full h-full bg-gradient-to-br from-blue-400 to-indigo-500 flex items-center justify-center text-white text-4xl font-bold">
                                        <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <label for="profile_photo_input" class="absolute -bottom-2 -right-2 bg-white rounded-full p-2 shadow-md cursor-pointer border border-gray-200 hover:border-blue-400 transition-all">
                                <i class="fas fa-camera text-gray-600 text-sm"></i>
                            </label>
                            <input type="file" id="profile_photo_input" name="profile_photo" accept="image/*" class="hidden">
                            <input type="hidden" name="cropped_image_data" id="cropped_image_data">
                        </div>
                        <p class="text-xs text-gray-400 max-w-[180px] text-center">Klik kamera, crop, lalu simpan profil</p>
                    </div>
                    
                    <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Username</label>
                            <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-400 focus:border-transparent transition">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Alamat Email</label>
                            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-400">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1.5">Peran / Role</label>
                            <input type="text" readonly value="<?php echo ucfirst($user['role']); ?>" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-100 rounded-xl text-gray-500">
                        </div>
                    </div>
                </div>
                <div class="flex justify-end mt-8">
                    <button type="submit" name="update_profile" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl shadow-md hover:shadow-lg transition flex items-center gap-2"><i class="fas fa-save"></i> Simpan Perubahan</button>
                </div>
            </form>
        </div>
        
        <!-- SECTION 2: Keamanan -->
        <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-7 mb-8 card-modern">
            <div class="flex items-center gap-4 mb-6">
                <div class="p-3 rounded-2xl bg-emerald-50 text-emerald-600"><i class="fas fa-shield-haltered text-2xl"></i></div>
                <div><h3 class="text-xl font-bold">Keamanan Akun</h3><p class="text-sm text-gray-500">Perbarui kata sandi secara berkala</p></div>
            </div>
            <form method="POST">
                <div class="grid md:grid-cols-2 gap-5">
                    <div><label class="block text-sm font-semibold mb-1">Kata Sandi Saat Ini</label><input type="password" name="current_password" class="w-full border rounded-xl p-2.5" placeholder="••••••" required></div>
                    <div><label class="block text-sm font-semibold mb-1">Kata Sandi Baru</label><input type="password" name="new_password" minlength="6" class="w-full border rounded-xl p-2.5" placeholder="minimal 6 karakter" required></div>
                    <div><label class="block text-sm font-semibold mb-1">Konfirmasi Baru</label><input type="password" name="confirm_password" class="w-full border rounded-xl p-2.5" placeholder="ulangi password" required></div>
                </div>
                <div class="flex justify-end mt-6"><button type="submit" name="change_password" class="px-6 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-xl shadow flex items-center gap-2"><i class="fas fa-key"></i> Perbarui Password</button></div>
            </form>
        </div>
        
        <!-- SECTION 3: Info akun + statistik -->
        <div class="bg-white rounded-2xl shadow-md border border-gray-100 p-7 mb-8">
            <div class="flex gap-4 items-center mb-6"><div class="p-3 rounded-2xl bg-purple-50 text-purple-600"><i class="fas fa-chart-line text-xl"></i></div><div><h3 class="font-bold text-xl">Statistik Akun</h3><p class="text-gray-500 text-sm">Ringkasan aktivitas</p></div></div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                <div class="bg-gray-50/70 p-4 rounded-2xl"><div class="flex items-center text-gray-500 gap-2 text-sm"><i class="fas fa-calendar-alt"></i> Anggota sejak</div><p class="text-lg font-bold"><?php echo date('d M Y', strtotime($user['created_at'])); ?></p><span class="text-xs"><?php echo $stats['account_age']; ?> hari aktif</span></div>
                <div class="bg-gray-50/70 p-4 rounded-2xl"><div class="flex items-center text-gray-500 gap-2 text-sm"><i class="fas fa-clock"></i> Login terakhir</div><p class="font-semibold"><?php echo $stats['last_login']; ?></p></div>
                <div class="bg-gray-50/70 p-4 rounded-2xl"><div class="flex items-center text-gray-500 gap-2"><i class="fas fa-fingerprint"></i> ID Akun</div><p class="font-mono font-bold">#<?php echo $user['id']; ?></p><span class="text-xs text-gray-400">referensi unik</span></div>
            </div>
        </div>
        
        <!-- DANGER ZONE modern -->
        <div class="bg-white rounded-2xl shadow-md border border-red-100 p-7">
            <div class="flex items-center gap-4"><div class="p-3 rounded-2xl bg-red-50 text-red-500"><i class="fas fa-skull-crosswalk text-xl"></i></div><div><h3 class="font-bold text-red-600 text-lg">Zona Bahaya</h3><p class="text-sm text-gray-500">Penghapusan akun bersifat permanen & ireversibel</p></div></div>
            <div class="mt-5 flex justify-between items-center flex-wrap gap-3"><p class="text-gray-600 text-sm">Setelah akun dihapus, semua data akan hilang selamanya.</p><form method="POST" onsubmit="return confirm('Yakin ingin menghapus akun? Tidak bisa dikembalikan!')"><button type="submit" name="delete_account" class="px-5 py-2 bg-red-500 hover:bg-red-600 text-white rounded-xl font-medium transition flex gap-2"><i class="fas fa-trash-alt"></i> Hapus Akun Permanen</button></form></div>
        </div>
        <footer class="mt-12 text-center text-gray-400 text-xs">Lurahgo.id — platform kelurahan digital terpercaya</footer>
    </main>
</div>

<!-- CROP MODAL MODERN -->
<div id="cropperModal" class="fixed inset-0 bg-black/70 backdrop-blur-sm flex items-center justify-center z-[1000] hidden p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl">
        <div class="flex justify-between items-center"><h3 class="text-xl font-bold">Crop Foto Profil</h3><button onclick="closeCropModal()" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times text-xl"></i></button></div>
        <div class="mt-4"><img id="cropImageEl" src="" class="max-w-full" style="max-height: 50vh;"></div>
        <div class="flex gap-3 justify-center my-4"><button type="button" class="crop-btn-modern px-3 py-1 rounded-md" data-aspect="1">1:1 (Kotak)</button><button type="button" class="crop-btn-modern px-3 py-1 rounded-md" data-aspect="1.333">4:3</button><button type="button" class="crop-btn-modern px-2 py-1" id="zoomInBtn"><i class="fas fa-search-plus"></i></button><button type="button" class="crop-btn-modern px-2 py-1" id="zoomOutBtn"><i class="fas fa-search-minus"></i></button><button type="button" class="crop-btn-modern px-2 py-1" id="rotateBtn"><i class="fas fa-undo-alt"></i></button><button type="button" class="crop-btn-modern px-3 py-1" id="resetCropBtn">Reset</button></div>
        <div class="flex gap-3 justify-end"><button onclick="closeCropModal()" class="px-4 py-2 border rounded-xl">Batal</button><button id="applyCropBtn" class="px-5 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700">Terapkan Crop</button></div>
    </div>
</div>

<script>
// MODERN CROP LOGIC
let cropperModalInst = null;
let currentFile = null;
const modal = document.getElementById('cropperModal');
const cropImage = document.getElementById('cropImageEl');
const fileInput = document.getElementById('profile_photo_input');
const avatarPreviewDiv = document.getElementById('avatarPreview');
const hiddenCropData = document.getElementById('cropped_image_data');

fileInput.addEventListener('change', (e) => {
    const file = e.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = (ev) => {
        cropImage.src = ev.target.result;
        modal.classList.remove('hidden');
        if (cropperModalInst) cropperModalInst.destroy();
        cropperModalInst = new Cropper(cropImage, {
            aspectRatio: 1,
            viewMode: 1,
            dragMode: 'move',
            cropBoxResizable: true,
            background: false,
            autoCropArea: 0.8,
            minCropBoxWidth: 120,
            minCropBoxHeight: 120
        });
    };
    reader.readAsDataURL(file);
});

function closeCropModal() {
    modal.classList.add('hidden');
    if (cropperModalInst) { cropperModalInst.destroy(); cropperModalInst = null; }
    fileInput.value = '';
}

document.querySelectorAll('[data-aspect]').forEach(btn => {
    btn.addEventListener('click', () => {
        if (!cropperModalInst) return;
        const aspect = parseFloat(btn.getAttribute('data-aspect'));
        cropperModalInst.setAspectRatio(aspect);
    });
});
document.getElementById('zoomInBtn')?.addEventListener('click', () => cropperModalInst?.zoom(0.1));
document.getElementById('zoomOutBtn')?.addEventListener('click', () => cropperModalInst?.zoom(-0.1));
document.getElementById('rotateBtn')?.addEventListener('click', () => cropperModalInst?.rotate(90));
document.getElementById('resetCropBtn')?.addEventListener('click', () => cropperModalInst?.reset());
document.getElementById('applyCropBtn').addEventListener('click', () => {
    if (!cropperModalInst) return;
    const canvas = cropperModalInst.getCroppedCanvas({ width: 300, height: 300, imageSmoothingQuality: 'high' });
    const croppedDataURL = canvas.toDataURL('image/png');
    hiddenCropData.value = croppedDataURL;
    // update preview
    avatarPreviewDiv.innerHTML = `<img src="${croppedDataURL}" class="w-full h-full object-cover">`;
    closeCropModal();
});

// optional: if you want to keep server upload sync with hidden field.
</script>
</body>
</html>