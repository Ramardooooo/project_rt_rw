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
    $user_id = (int)$_GET['id'];
    $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    if (!$user) {
        header("Location: manage_users");
        exit();
    }
}

if (isset($_POST['update_user'])) {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $role = $_POST['role'];
    $password = $_POST['password'];

    $check_username = mysqli_prepare($conn, "SELECT id FROM users WHERE username = ? AND id != ?");
    mysqli_stmt_bind_param($check_username, "si", $username, $user_id);
    mysqli_stmt_execute($check_username);
    mysqli_stmt_store_result($check_username);
    if (mysqli_stmt_num_rows($check_username) > 0) {
        $error = "Username sudah digunakan oleh user lain.";
    } else {
        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = mysqli_prepare($conn, "UPDATE users SET username=?, email=?, password=?, role=? WHERE id=?");
            mysqli_stmt_bind_param($stmt, "ssssi", $username, $email, $hashed_password, $role, $user_id);
        } else {
            $stmt = mysqli_prepare($conn, "UPDATE users SET username=?, email=?, role=? WHERE id=?");
            mysqli_stmt_bind_param($stmt, "sssi", $username, $email, $role, $user_id);
        }
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        $action = "User diedit oleh " . ($_SESSION['username'] ?? 'Unknown');
        $table_name = "users";
        $record_id = $user_id;
        $old_value = json_encode($user);
        $new_value = json_encode(['username' => $username, 'email' => $email, 'role' => $role]);
        $user_id_session = $_SESSION['user_id'] ?? null;
        $username_session = $_SESSION['username'] ?? 'Unknown';
        $audit_stmt = mysqli_prepare($conn, "INSERT INTO audit_log (action, table_name, record_id, old_value, new_value, user_id, username) VALUES (?, ?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($audit_stmt, "ssissis", $action, $table_name, $record_id, $old_value, $new_value, $user_id_session, $username_session);
        mysqli_stmt_execute($audit_stmt);
        mysqli_stmt_close($audit_stmt);

        $success = "User berhasil diupdate.";
        header("Location: /PROJECT/manage_users");
        exit();
    }
    mysqli_stmt_close($check_username);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit User - Lurahgo.id</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
    <script>
        // Live password strength indicator
        function checkPasswordStrength() {
            const password = document.getElementById('passwordInput')?.value || '';
            const strengthBar = document.getElementById('strengthBar');
            const strengthText = document.getElementById('strengthText');
            
            if (!strengthBar) return;
            
            let strength = 0;
            if (password.length >= 6) strength++;
            if (password.length >= 10) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^A-Za-z0-9]/.test(password)) strength++;
            
            const percent = Math.min(100, strength * 20);
            strengthBar.style.width = percent + '%';
            
            if (percent < 20) {
                strengthBar.className = 'h-1 rounded-full bg-red-400 transition-all duration-300';
                if (strengthText) strengthText.innerText = 'Sangat Lemah';
            } else if (percent < 40) {
                strengthBar.className = 'h-1 rounded-full bg-orange-400 transition-all duration-300';
                if (strengthText) strengthText.innerText = 'Lemah';
            } else if (percent < 60) {
                strengthBar.className = 'h-1 rounded-full bg-yellow-400 transition-all duration-300';
                if (strengthText) strengthText.innerText = 'Sedang';
            } else if (percent < 80) {
                strengthBar.className = 'h-1 rounded-full bg-blue-400 transition-all duration-300';
                if (strengthText) strengthText.innerText = 'Kuat';
            } else {
                strengthBar.className = 'h-1 rounded-full bg-emerald-400 transition-all duration-300';
                if (strengthText) strengthText.innerText = 'Sangat Kuat';
            }
        }
        
        function resetStrength() {
            const password = document.getElementById('passwordInput')?.value || '';
            const strengthBar = document.getElementById('strengthBar');
            const strengthText = document.getElementById('strengthText');
            if (password === '' && strengthBar) {
                strengthBar.style.width = '0%';
                if (strengthText) strengthText.innerText = '';
            }
        }
    </script>
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
        
        input:-webkit-autofill,
        input:-webkit-autofill:hover,
        input:-webkit-autofill:focus,
        input:-webkit-autofill:active {
            -webkit-box-shadow: 0 0 0 30px #f8fafc inset !important;
            box-shadow: 0 0 0 30px #f8fafc inset !important;
            -webkit-text-fill-color: #1e293b !important;
            background-color: #f8fafc !important;
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
                    <h2 class="text-xl font-bold text-white tracking-tight">Edit User</h2>
                    <p class="text-xs text-blue-100 mt-0.5">Perbarui informasi akun pengguna</p>
                </div>
            </div>
            
            <div class="p-6">
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
                    <!-- Username Field -->
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                            <i class="fas fa-user text-gray-400 mr-1 text-xs"></i> Username
                        </label>
                        <input type="text" name="username" value="<?php echo htmlspecialchars($user['username']); ?>"
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:outline-none focus:border-blue-400 form-input-focus transition-all bg-gray-50/50"
                            placeholder="Masukkan username" required>
                    </div>

                    <!-- Email Field -->
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                            <i class="fas fa-envelope text-gray-400 mr-1 text-xs"></i> Email
                        </label>
                        <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>"
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:outline-none focus:border-blue-400 form-input-focus transition-all bg-gray-50/50"
                            placeholder="contoh@email.com" required>
                    </div>

                    <!-- Password Field with Strength Indicator -->
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                            <i class="fas fa-lock text-gray-400 mr-1 text-xs"></i> Password Baru
                        </label>
                        <div class="relative">
                            <input type="password" id="passwordInput" name="password" autocomplete="new-password"
                                class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:outline-none focus:border-blue-400 form-input-focus transition-all bg-gray-50/50 pr-10"
                                placeholder="Kosongkan jika tidak ingin mengubah" onkeyup="checkPasswordStrength(); resetStrength()">
                            <div class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400">
                                <i class="fas fa-key text-xs"></i>
                            </div>
                        </div>
                        <!-- Strength Bar -->
                        <div class="mt-2">
                            <div class="h-1 bg-gray-100 rounded-full overflow-hidden">
                                <div id="strengthBar" class="h-1 rounded-full transition-all duration-300" style="width: 0%"></div>
                            </div>
                            <p id="strengthText" class="text-[10px] text-gray-400 mt-1"></p>
                        </div>
                        <small class="text-gray-400 text-xs mt-1 inline-block">
                            <i class="fas fa-info-circle mr-1"></i>Kosongkan jika tidak ingin mengubah password
                        </small>
                    </div>

                    <!-- Role Selection -->
                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                            <i class="fas fa-tag text-gray-400 mr-1 text-xs"></i> Role / Hak Akses
                        </label>
                        <select name="role"
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:outline-none focus:border-blue-400 form-input-focus transition-all bg-gray-50/50 cursor-pointer">
                            <option value="user" <?php if ($user['role'] == 'user') echo 'selected'; ?>>👤 User (Warga Biasa)</option>
                            <option value="ketua" <?php if ($user['role'] == 'ketua') echo 'selected'; ?>>👑 Ketua RT</option>
                            <option value="admin" <?php if ($user['role'] == 'admin') echo 'selected'; ?>>⚙️ Administrator</option>
                        </select>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" name="update_user"
                        class="w-full py-3 rounded-xl font-bold text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 transition-all duration-300 shadow-md hover:shadow-lg flex items-center justify-center gap-2">
                        <i class="fas fa-save"></i>
                        <span>Update User</span>
                    </button>
                </form>

                <!-- Back Button -->
                <a href="manage_users"
                    class="block text-center mt-4 py-3 rounded-xl font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 hover:text-gray-800 transition-all duration-300 flex items-center justify-center gap-2">
                    <i class="fas fa-arrow-left text-sm"></i>
                    <span>Kembali ke Kelola User</span>
                </a>
            </div>
        </div>
        
        <!-- Footer note -->
        <p class="text-center text-xs text-gray-400 mt-6">
            <i class="fas fa-shield-alt mr-1"></i> Perubahan akan tercatat dalam log aktivitas
        </p>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const strengthBar = document.getElementById('strengthBar');
        const strengthText = document.getElementById('strengthText');
        if (strengthBar) {
            strengthBar.style.width = '0%';
        }
        if (strengthText) {
            strengthText.innerText = '';
        }
    });
</script>
</body>
</html>