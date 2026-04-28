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

if (isset($_GET['id'])) {
    $rt_id = (int)$_GET['id'];
    $rt = mysqli_fetch_assoc(mysqli_query($conn, "SELECT rt.*, rw.name as rw_name FROM rt LEFT JOIN rw ON rt.id_rw = rw.id WHERE rt.id = $rt_id"));
    if (!$rt) {
        header("Location: /PROJECT/manage_rt_rw");
        exit();
    }
}

if (isset($_POST['update_rt'])) {
    $nama_rt = $_POST['nama_rt'];
    $id_rw = isset($_POST['rw_id']) ? (int)$_POST['rw_id'] : null;
    $ketua_rt = $_POST['ketua_rt'];
    $status = $_POST['status'];

    $old_rt = mysqli_fetch_assoc(mysqli_query($conn, "SELECT nama_rt, ketua_rt, status FROM rt WHERE id = $rt_id"));

    $stmt = mysqli_prepare($conn, "UPDATE rt SET nama_rt=?, id_rw=?, ketua_rt=?, status=? WHERE id=?");
    mysqli_stmt_bind_param($stmt, "sissi", $nama_rt, $id_rw, $ketua_rt, $status, $rt_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    $action = "Update RT";
    $table_name = "rt";
    $record_id = $rt_id;
    $old_value = json_encode($old_rt);
    $new_value = json_encode(['nama_rt' => $nama_rt, 'ketua_rt' => $ketua_rt, 'status' => $status]);
    $user_id = $_SESSION['user_id'] ?? null;
    $username = $_SESSION['username'] ?? 'Unknown';
    $audit_stmt = mysqli_prepare($conn, "INSERT INTO audit_log (action, table_name, record_id, old_value, new_value, user_id, username) VALUES (?, ?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($audit_stmt, "ssissis", $action, $table_name, $record_id, $old_value, $new_value, $user_id, $username);
    mysqli_stmt_execute($audit_stmt);
    mysqli_stmt_close($audit_stmt);

    header("Location: manage_rt_rw");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit RT - Lurahgo.id</title>
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
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            border-color: #3b82f6;
        }
        
        .card-shadow {
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
        }
        
        input:focus, select:focus {
            outline: none;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-blue-50 via-indigo-50 to-slate-100">
<div class="ml-64 min-h-screen flex items-center justify-center p-6">
    <div class="max-w-lg w-full animate-in">
        
        <!-- Card Header Premium - Tema Biru -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden card-shadow">
            <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-5">
                <div>
                    <h2 class="text-xl font-bold text-white tracking-tight">Edit RT</h2>
                    <p class="text-xs text-blue-100 mt-0.5">Perbarui data Rukun Tetangga</p>
                </div>
            </div>
            
            <div class="p-6">
                <?php if (isset($error)): ?>
                    <div class="mb-5 p-3 rounded-xl bg-red-50 border border-red-200 text-red-600 text-sm flex items-center gap-2">
                        <i class="fas fa-exclamation-circle text-red-500"></i>
                        <span><?php echo $error; ?></span>
                    </div>
                <?php endif; ?>

                <form method="POST" class="space-y-4">
                    <!-- Nama RT Field -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                            <i class="fas fa-tag text-gray-400 mr-1 text-xs"></i> Nama RT
                        </label>
                        <input type="text" name="nama_rt" value="<?php echo htmlspecialchars($rt['nama_rt']); ?>"
                            placeholder="Contoh: RT 01" required
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:outline-none focus:border-blue-400 form-input-focus transition-all bg-gray-50/50">
                    </div>

                    <!-- RW Parent Field -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                            <i class="fas fa-layer-group text-gray-400 mr-1 text-xs"></i> RW Parent
                        </label>
                        <select name="rw_id" required class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:outline-none focus:border-blue-400 form-input-focus transition-all bg-gray-50/50 cursor-pointer">
                            <option value="">Pilih RW</option>
                            <?php 
                            $rw_list_query = mysqli_query($conn, "SELECT id, name FROM rw WHERE status = 'aktif' ORDER BY name ASC");
                            while ($rw_option = mysqli_fetch_assoc($rw_list_query)): 
                                $selected = ($rt['id_rw'] == $rw_option['id']) ? 'selected' : '';
                            ?>
                                <option value="<?php echo $rw_option['id']; ?>" <?php echo $selected; ?>><?php echo htmlspecialchars($rw_option['name']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <!-- Ketua RT Field -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                            <i class="fas fa-user-tie text-gray-400 mr-1 text-xs"></i> Ketua RT
                        </label>
                        <input type="text" name="ketua_rt" value="<?php echo htmlspecialchars($rt['ketua_rt']); ?>"
                            placeholder="Nama Ketua RT" required
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:outline-none focus:border-blue-400 form-input-focus transition-all bg-gray-50/50">
                    </div>

                    <!-- Status Field -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                            <i class="fas fa-circle text-gray-400 mr-1 text-xs"></i> Status
                        </label>
                        <select name="status" required
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:outline-none focus:border-blue-400 form-input-focus transition-all bg-gray-50/50 cursor-pointer">
                            <option value="aktif" <?php echo ($rt['status'] == 'aktif') ? 'selected' : ''; ?>>🟢 Aktif</option>
                            <option value="tidak_aktif" <?php echo ($rt['status'] == 'tidak_aktif') ? 'selected' : ''; ?>>🔴 Tidak Aktif</option>
                        </select>
                    </div>

                    <!-- Buttons -->
                    <div class="flex gap-3 pt-4">
                        <button type="submit" name="update_rt"
                            class="flex-1 py-3 rounded-xl font-bold text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 transition-all duration-300 shadow-md hover:shadow-lg flex items-center justify-center gap-2">
                            <i class="fas fa-save"></i>
                            <span>Update RT</span>
                        </button>

                        <a href="manage_rt_rw"
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
            <i class="fas fa-shield-alt mr-1"></i> Perubahan akan tercatat dalam log aktivitas
        </p>
    </div>
</div>
</body>
</html>