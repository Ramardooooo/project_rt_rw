<?php
session_start();
include '../../../config/database.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: /PROJECT/home");
    exit();
}

if (isset($_POST['tambah_user'])) {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? '';

    $check_username = mysqli_prepare($conn, "SELECT id FROM users WHERE username = ?");
    mysqli_stmt_bind_param($check_username, "s", $username);
    mysqli_stmt_execute($check_username);
    mysqli_stmt_store_result($check_username);
    if (mysqli_stmt_num_rows($check_username) > 0) {
        $error = "Username sudah ada.";
    } else {
        $check_email = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ?");
        mysqli_stmt_bind_param($check_email, "s", $email);
        mysqli_stmt_execute($check_email);
        mysqli_stmt_store_result($check_email);
        if (mysqli_stmt_num_rows($check_email) > 0) {
            $error = "Email sudah ada.";
        } else {
            if ($password !== $confirm_password) {
                $error = "Password dan konfirmasi password tidak cocok.";
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = mysqli_prepare($conn, "INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
                mysqli_stmt_bind_param($stmt, "ssss", $username, $email, $hashed_password, $role);
                if (mysqli_stmt_execute($stmt)) {
                    $user_id = mysqli_insert_id($conn);
                    $user_id_session = $_SESSION['user_id'] ?? null;
                    $username_session = $_SESSION['username'] ?? 'Unknown';
                    $action = "User baru ditambahkan oleh " . $username_session;
                    $table_name = "users";
                    $record_id = $user_id;
                    $old_value = null;
                    $new_value = json_encode(['username' => $username, 'email' => $email, 'role' => $role]);
                    $audit_stmt = mysqli_prepare($conn, "INSERT INTO audit_log (action, table_name, record_id, old_value, new_value, user_id, username) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    mysqli_stmt_bind_param($audit_stmt, "ssissis", $action, $table_name, $record_id, $old_value, $new_value, $user_id_session, $username_session);
                    mysqli_stmt_execute($audit_stmt);
                    mysqli_stmt_close($audit_stmt);
                    header("Location: /PROJECT/manage_users");
                    exit();
                } else {
                    $error = "Gagal menambahkan user: " . mysqli_error($conn);
                }
                mysqli_stmt_close($stmt);
            }
        }
        mysqli_stmt_close($check_email);
    }
    mysqli_stmt_close($check_username);
}

include '../../../layouts/admin/header.php';
include '../../../layouts/admin/sidebar.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah User - Lurahgo.id</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
    <script>
        window.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form[method="POST"]');
            if (form) {
                const inputs = form.querySelectorAll('input[name="username"], input[name="email"], input[name="password"], input[name="confirm_password"]');
                inputs.forEach(input => {
                    if (input.value === '') return;
                    input.value = '';
                });
            }
        });
        
        document.addEventListener('input', function(e) {
            if (e.target.matches('input[name="username"], input[name="email"], input[name="password"], input[name="confirm_password"]')) {
                e.target.setAttribute('autocomplete', 'off');
            }
        });
        
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
        
        // Confirm password match
        function checkPasswordMatch() {
            const password = document.getElementById('passwordInput')?.value || '';
            const confirm = document.getElementById('confirmPasswordInput')?.value || '';
            const matchIcon = document.getElementById('matchIcon');
            
            if (matchIcon && confirm.length > 0) {
                if (password === confirm) {
                    matchIcon.innerHTML = '<i class="fas fa-check-circle text-emerald-500 text-sm"></i>';
                    matchIcon.className = 'absolute right-3 top-1/2 -translate-y-1/2';
                } else {
                    matchIcon.innerHTML = '<i class="fas fa-times-circle text-red-400 text-sm"></i>';
                    matchIcon.className = 'absolute right-3 top-1/2 -translate-y-1/2';
                }
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
        
        input:-webkit-autofill,
        input:-webkit-autofill:hover,
        input:-webkit-autofill:focus,
        input:-webkit-autofill:active {
            -webkit-box-shadow: 0 0 0 30px #f8fafc inset !important;
            box-shadow: 0 0 0 30px #f8fafc inset !important;
            -webkit-text-fill-color: #1e293b !important;
            background-color: #f8fafc !important;
        }
        
        .form-input-focus:focus {
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
            border-color: #10b981;
        }
        
        .card-shadow {
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
        }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-100 via-gray-50 to-zinc-100">
<div class="ml-64 min-h-screen flex items-center justify-center p-6">
    <div class="max-w-lg w-full animate-in">
        
        <!-- Card Header dengan icon premium -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden card-shadow">
            <div class="bg-gradient-to-r from-slate-800 to-slate-900 px-6 py-5">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-xl flex items-center justify-center shadow-lg">
                        <i class="fas fa-user-plus text-white text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-white tracking-tight">Tambah User Baru</h2>
                        <p class="text-xs text-slate-400 mt-0.5">Isi formulir untuk menambahkan akun pengguna</p>
                    </div>
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

                <form method="POST" autocomplete="off">
                    <!-- Honeypot fields -->
                    <div style="position: absolute; opacity: 0; height: 0; overflow: hidden;">
                        <input type="text" name="fake_username" tabindex="-1" autocomplete="username">
                        <input type="email" name="fake_email" tabindex="-1" autocomplete="email">
                        <input type="password" name="fake_password" tabindex="-1" autocomplete="current-password">
                    </div>
                    
                    <!-- Username Field -->
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                            <i class="fas fa-user text-gray-400 mr-1 text-xs"></i> Username
                        </label>
                        <input type="text" name="username" autocomplete="new-username"
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:outline-none focus:border-emerald-400 form-input-focus transition-all bg-gray-50/50"
                            placeholder="Masukkan username" required>
                    </div>

                    <!-- Email Field -->
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                            <i class="fas fa-envelope text-gray-400 mr-1 text-xs"></i> Email
                        </label>
                        <input type="email" name="email" autocomplete="new-email"
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:outline-none focus:border-emerald-400 form-input-focus transition-all bg-gray-50/50"
                            placeholder="contoh@email.com" required>
                    </div>

                    <!-- Password Field with Strength Indicator -->
                    <div class="mb-4">
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                            <i class="fas fa-lock text-gray-400 mr-1 text-xs"></i> Password
                        </label>
                        <div class="relative">
                            <input type="password" id="passwordInput" name="password" autocomplete="new-password"
                                class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:outline-none focus:border-emerald-400 form-input-focus transition-all bg-gray-50/50 pr-10"
                                placeholder="Minimal 6 karakter" required onkeyup="checkPasswordStrength()">
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
                    </div>

                    <!-- Confirm Password Field -->
                    <div class="mb-5">
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                            <i class="fas fa-check-circle text-gray-400 mr-1 text-xs"></i> Konfirmasi Password
                        </label>
                        <div class="relative">
                            <input type="password" id="confirmPasswordInput" name="confirm_password" autocomplete="new-password"
                                class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:outline-none focus:border-emerald-400 form-input-focus transition-all bg-gray-50/50 pr-10"
                                placeholder="Ulangi password" required onkeyup="checkPasswordMatch()">
                            <span id="matchIcon" class="absolute right-3 top-1/2 -translate-y-1/2"></span>
                        </div>
                    </div>

                    <!-- Role Selection -->
                    <div class="mb-6">
                        <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                            <i class="fas fa-tag text-gray-400 mr-1 text-xs"></i> Role / Hak Akses
                        </label>
                        <select name="role" autocomplete="off"
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:outline-none focus:border-emerald-400 form-input-focus transition-all bg-gray-50/50 cursor-pointer">
                            <option value="user">👤 User (Warga Biasa)</option>
                            <option value="ketua">👑 Ketua RT</option>
                            <option value="admin">⚙️ Administrator</option>
                        </select>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" name="tambah_user"
                        class="w-full py-3 rounded-xl font-bold text-white bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 transition-all duration-300 shadow-md hover:shadow-lg flex items-center justify-center gap-2">
                        <i class="fas fa-save"></i>
                        <span>Tambah User</span>
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
            <i class="fas fa-shield-alt mr-1"></i> Data pengguna akan tersimpan dengan aman
        </p>
    </div>
</div>

<script>
    // Initialize strength check on page load if any value
    document.addEventListener('DOMContentLoaded', function() {
        checkPasswordStrength();
        checkPasswordMatch();
    });
</script>
</body>
</html>