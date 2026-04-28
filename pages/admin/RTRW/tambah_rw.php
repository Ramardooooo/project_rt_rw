<?php
ob_start();
session_start();
include '../../../config/database.php';
include '../../../layouts/admin/header.php';
include '../../../layouts/admin/sidebar.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: home");
    exit();
}

if (isset($_POST['add_rw'])) {
    $nama_rw = $_POST['nama_rw'];
    $ketua_rw_id = (int)$_POST['ketua_rw_id'];

    $check_nama_rw = mysqli_prepare($conn, "SELECT id FROM rw WHERE name = ?");
    mysqli_stmt_bind_param($check_nama_rw, "s", $nama_rw);
    mysqli_stmt_execute($check_nama_rw);
    mysqli_stmt_store_result($check_nama_rw);
    if (mysqli_stmt_num_rows($check_nama_rw) > 0) {
        $error = "Nama RW sudah ada.";
    } else {
        $stmt = mysqli_prepare($conn, "INSERT INTO rw (name, ketua_rw_id) VALUES (?, ?)");
        mysqli_stmt_bind_param($stmt, "si", $nama_rw, $ketua_rw_id);
        if (mysqli_stmt_execute($stmt)) {
            $success = "Data RW berhasil ditambahkan.";
            header("Location: manage_rw");
            exit();
        } else {
            $error = "Gagal menambahkan data RW: " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
    }
    mysqli_stmt_close($check_nama_rw);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah RW - Lurahgo.id</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: 'Inter', sans-serif;
        }
        
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .animate-in {
            animation: fadeInUp 0.5s ease-out forwards;
        }
        
        .form-input-focus:focus {
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
            border-color: #10b981;
        }
        
        .card-shadow {
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
        }
        
        input:focus, select:focus {
            outline: none;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-emerald-50 via-green-50 to-teal-100">
<div class="ml-64 min-h-screen flex items-center justify-center p-6">
    <div class="max-w-lg w-full animate-in">
        
        <!-- Card Header Premium - Tema Hijau -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden card-shadow">
            <div class="bg-gradient-to-r from-emerald-600 to-teal-600 px-6 py-5">
                <div>
                    <h2 class="text-xl font-bold text-white tracking-tight">Tambah RW</h2>
                    <p class="text-xs text-emerald-100 mt-0.5">Tambahkan data Rukun Warga baru</p>
                </div>
            </div>
            
            <div class="p-6">
                <!-- Alert Messages -->
                <?php if (isset($success)): ?>
                    <div class="mb-5 p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm flex items-center gap-2">
                        <i class="fas fa-check-circle text-emerald-500"></i>
                        <span><?php echo $success; ?></span>
                    </div>
                <?php endif; ?>
                <?php if (isset($error)): ?>
                    <div class="mb-5 p-3 rounded-xl bg-red-50 border border-red-200 text-red-600 text-sm flex items-center gap-2">
                        <i class="fas fa-exclamation-circle text-red-500"></i>
                        <span><?php echo $error; ?></span>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <!-- Nama RW Field -->
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                            <i class="fas fa-tag text-gray-400 mr-1 text-xs"></i> Nama RW
                        </label>
                        <input type="text" name="nama_rw" placeholder="Contoh: RW 01"
                            required
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:outline-none focus:border-emerald-400 form-input-focus transition-all bg-gray-50/50">
                        <p class="text-xs text-gray-400 mt-1">* Nama RW akan digunakan untuk identifikasi wilayah</p>
                    </div>

                    <!-- Ketua RW Field -->
                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                            <i class="fas fa-user-tie text-gray-400 mr-1 text-xs"></i> Ketua RW
                        </label>
                        <select name="ketua_rw_id" required class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:outline-none focus:border-emerald-400 form-input-focus transition-all bg-gray-50/50 cursor-pointer">
                            <option value="">Pilih Ketua RW dari Warga</option>
                            <?php 
                            $warga_list = mysqli_query($conn, "SELECT id, nama, nik FROM warga WHERE status = 'aktif' ORDER BY nama ASC");
                            while ($w = mysqli_fetch_assoc($warga_list)): 
                            ?>
                                <option value="<?php echo $w['id']; ?>"><?php echo htmlspecialchars($w['nama']) . ' (' . $w['nik'] . ')'; ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <!-- Buttons -->
                    <div class="flex gap-3 pt-2">
                        <button type="submit" name="add_rw"
                            class="flex-1 py-3 rounded-xl font-bold text-white bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 transition-all duration-300 shadow-md hover:shadow-lg flex items-center justify-center gap-2">
                            <i class="fas fa-save"></i>
                            <span>Simpan RW</span>
                        </button>

                        <a href="manage_rw"
                            class="flex-1 text-center py-3 rounded-xl font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 hover:text-gray-800 transition-all duration-300 flex items-center justify-center gap-2">
                            <i class="fas fa-arrow-left text-sm"></i>
                            <span>Kembali</span>
                        </a>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Footer note -->
        <p class="text-center text-xs text-gray-400 mt-6">
            <i class="fas fa-shield-alt mr-1"></i> Data RW akan tercatat dalam sistem
        </p>
    </div>
</div>
</body>
</html>