<?php
session_start();
include '../config/database.php';

$error = '';
$error_type = 'general';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $login_input = trim($_POST['login_input'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($login_input)) {
        $error = "Masukkan username atau email.";
        $error_type = 'login_input';
    } elseif (empty($password)) {
        $error = "Masukkan password.";
        $error_type = 'password';
    } else {
        if (filter_var($login_input, FILTER_VALIDATE_EMAIL)) {
            $sql = "SELECT * FROM users WHERE email = ?";
        } else {
            $sql = "SELECT * FROM users WHERE username = ?";
        }
        
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $login_input);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($user = mysqli_fetch_assoc($result)) {
            if (password_verify($password, $user['password']) && $user['status'] === 'aktif') {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];

                mysqli_close($conn);
                $dashboard_path = '';
                if ($user['role'] === 'admin') {
                    $dashboard_path = '../dashboard_admin';
                } elseif ($user['role'] === 'ketua') {
                    $dashboard_path = '../dashboard_ketua';
                } else {
                    $dashboard_path = '../dashboard_user';
                }
                header("Location: $dashboard_path");
                exit();
            } else {
                $error = 'Password salah atau akun tidak aktif.';
                $error_type = 'password';
            }
        } else {
            $error = 'Username atau email tidak ditemukan.';
            $error_type = 'login_input';
        }
        mysqli_stmt_close($stmt);
    }
    if (isset($conn)) mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lurahgo.id - Masuk ke Akun Anda</title>
    <link href='https://cdn.jsdelivr.net/npm/boxicons@2.0.5/css/boxicons.min.css' rel='stylesheet'>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary-red: #dc2626;
            --primary-red-dark: #b91c1c;
            --primary-red-light: #ef4444;
            --primary-navy: #1e3a8a;
            --primary-navy-dark: #172554;
            --primary-navy-light: #2563eb;
            --accent: #f97316;
            --success: #10b981;
            --warning: #f59e0b;
            --error: #ef4444;
            --dark: #0f172a;
            --darker: #020617;
            --gray-900: #1e293b;
            --gray-800: #334155;
            --gray-700: #475569;
            --gray-600: #64748b;
            --gray-500: #94a3b8;
            --gray-400: #cbd5e1;
            --white: #ffffff;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            min-height: 100vh;
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }

        /* Background gradient merah & biru tua */
        .bg-gradient {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(ellipse 80% 50% at 50% -20%, rgba(220, 38, 38, 0.25) 0%, transparent 50%),
                radial-gradient(ellipse 60% 40% at 90% 10%, rgba(30, 58, 138, 0.3) 0%, transparent 50%),
                radial-gradient(ellipse 50% 30% at 10% 90%, rgba(220, 38, 38, 0.15) 0%, transparent 50%);
            z-index: 0;
        }

        /* Partikel animasi */
        .particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
            overflow: hidden;
        }

        .particle {
            position: absolute;
            width: 3px;
            height: 3px;
            background: rgba(239, 68, 68, 0.4);
            border-radius: 50%;
            animation: particleFloat 15s linear infinite;
        }

        .particle:nth-child(1) { left: 10%; animation-duration: 20s; }
        .particle:nth-child(2) { left: 20%; animation-duration: 25s; animation-delay: 1s; }
        .particle:nth-child(3) { left: 30%; animation-duration: 18s; animation-delay: 2s; }
        .particle:nth-child(4) { left: 40%; animation-duration: 22s; animation-delay: 0.5s; }
        .particle:nth-child(5) { left: 50%; animation-duration: 19s; animation-delay: 1.5s; }
        .particle:nth-child(6) { left: 60%; animation-duration: 24s; animation-delay: 2.5s; }
        .particle:nth-child(7) { left: 70%; animation-duration: 21s; animation-delay: 0.8s; }
        .particle:nth-child(8) { left: 80%; animation-duration: 17s; animation-delay: 1.8s; }
        .particle:nth-child(9) { left: 90%; animation-duration: 23s; animation-delay: 3s; }
        .particle:nth-child(10) { left: 15%; animation-duration: 26s; animation-delay: 1.2s; }
        .particle:nth-child(11) { left: 25%; animation-duration: 20s; animation-delay: 2.2s; }
        .particle:nth-child(12) { left: 35%; animation-duration: 18s; animation-delay: 0.3s; }
        .particle:nth-child(13) { left: 45%; animation-duration: 22s; animation-delay: 1.3s; }
        .particle:nth-child(14) { left: 55%; animation-duration: 25s; animation-delay: 2.3s; }
        .particle:nth-child(15) { left: 65%; animation-duration: 19s; animation-delay: 0.6s; }

        @keyframes particleFloat {
            0% { transform: translateY(100vh) scale(0); opacity: 0; }
            10% { opacity: 1; }
            90% { opacity: 1; }
            100% { transform: translateY(-100vh) scale(1); opacity: 0; }
        }

        /* Container utama */
        .container {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 880px;
            display: flex;
            background: rgba(15, 23, 42, 0.85);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            overflow: hidden;
            animation: containerFadeIn 0.6s ease-out;
        }

        @keyframes containerFadeIn {
            from { opacity: 0; transform: scale(0.98) translateY(20px); }
            to { opacity: 1; transform: scale(1) translateY(0); }
        }

        /* Sisi kiri - Branding (Gradasi Merah ke Biru Tua) */
        .brand-side {
            flex: 1;
            background: linear-gradient(135deg, var(--primary-red) 0%, var(--primary-navy) 50%, var(--primary-navy-dark) 100%);
            padding: 45px 35px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .brand-side::before {
            content: "";
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle at 30% 70%, rgba(255, 255, 255, 0.08) 0%, transparent 40%),
                        radial-gradient(circle at 70% 30%, rgba(249, 115, 22, 0.15) 0%, transparent 40%);
            animation: brandPulse 8s ease-in-out infinite;
        }

        @keyframes brandPulse {
            0%, 100% { transform: translate(0, 0) rotate(0deg); }
            50% { transform: translate(2%, 2%) rotate(3deg); }
        }

        .brand-content { position: relative; z-index: 2; }
        
        .brand-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 35px;
        }
        
        .logo-icon {
            width: 48px;
            height: 48px;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .logo-icon i { font-size: 28px; color: white; }
        .logo-text { font-size: 28px; font-weight: 700; color: white; letter-spacing: -0.5px; }
        .logo-text span { color: #fca5a5; font-weight: 800; }
        
        .brand-text h2 { 
            font-size: 32px; 
            font-weight: 700; 
            color: white; 
            margin-bottom: 16px;
            line-height: 1.2;
        }
        .brand-text p { 
            font-size: 14px; 
            color: rgba(255, 255, 255, 0.85); 
            line-height: 1.6; 
            max-width: 280px;
        }

        .brand-features {
            margin-top: 32px;
            display: flex;
            flex-direction: column;
            gap: 14px;
        }
        
        .feature-item { display: flex; align-items: center; gap: 12px; }
        .feature-icon {
            width: 34px;
            height: 34px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .feature-icon i { font-size: 18px; color: #fca5a5; }
        .feature-text { font-size: 13px; font-weight: 500; color: rgba(255, 255, 255, 0.9); }

        .brand-stats {
            margin-top: 35px;
            padding-top: 28px;
            border-top: 1px solid rgba(255, 255, 255, 0.15);
            display: flex;
            gap: 30px;
        }
        .stat-value { font-size: 22px; font-weight: 700; color: white; }
        .stat-label { font-size: 10px; color: rgba(255, 255, 255, 0.6); text-transform: uppercase; letter-spacing: 1px; margin-top: 4px; }

        /* Sisi kanan - Form */
        .form-side {
            flex: 1;
            padding: 45px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .form-header { text-align: center; margin-bottom: 28px; }
        .form-header h1 { font-size: 26px; font-weight: 700; color: var(--white); margin-bottom: 8px; }
        .form-header p { font-size: 13px; color: var(--gray-500); }

        .form-group { margin-bottom: 22px; }
        .form-label { display: block; font-size: 13px; font-weight: 600; color: var(--gray-400); margin-bottom: 8px; }
        
        .input-wrapper { position: relative; }
        .input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray-600);
        }
        .input-icon i { font-size: 18px; }
        
        .form-input {
            width: 100%;
            padding: 13px 44px;
            font-size: 14px;
            font-family: inherit;
            color: var(--white);
            background: var(--gray-900);
            border: 2px solid var(--gray-800);
            border-radius: 12px;
            outline: none;
            transition: all 0.2s ease;
        }
        .form-input:focus { border-color: var(--primary-red); box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.2); }
        
        .password-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--gray-600);
            cursor: pointer;
            padding: 4px;
        }
        .password-toggle i { font-size: 20px; }
        .password-toggle:hover { color: var(--gray-400); }

        .field-error {
            color: #ef4444;
            font-size: 12px;
            margin-top: 6px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }
        
        .checkbox-wrapper { display: flex; align-items: center; gap: 10px; cursor: pointer; }
        .custom-checkbox { position: relative; width: 18px; height: 18px; }
        .custom-checkbox input { position: absolute; opacity: 0; cursor: pointer; }
        .checkmark {
            position: absolute;
            top: 0;
            left: 0;
            width: 18px;
            height: 18px;
            background: var(--gray-900);
            border: 2px solid var(--gray-700);
            border-radius: 5px;
        }
        .custom-checkbox input:checked ~ .checkmark { background: var(--primary-red); border-color: var(--primary-red); }
        .checkmark::after {
            content: "";
            position: absolute;
            left: 5px;
            top: 1px;
            width: 4px;
            height: 8px;
            border: solid white;
            border-width: 0 2px 2px 0;
            transform: rotate(45deg) scale(0);
            transition: transform 0.1s ease;
        }
        .custom-checkbox input:checked ~ .checkmark::after { transform: rotate(45deg) scale(1); }
        
        .checkbox-label { font-size: 13px; color: var(--gray-400); }
        .forgot-link { font-size: 13px; color: #fca5a5; text-decoration: none; font-weight: 500; }
        .forgot-link:hover { color: var(--primary-red); }

        .submit-btn {
            width: 100%;
            padding: 14px;
            font-size: 15px;
            font-weight: 600;
            color: white;
            background: linear-gradient(135deg, var(--primary-red) 0%, var(--primary-navy) 100%);
            border: none;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .submit-btn:hover { transform: translateY(-2px); box-shadow: 0 10px 25px -5px rgba(220, 38, 38, 0.4); }
        .submit-btn.loading { opacity: 0.8; cursor: not-allowed; }
        
        .btn-content { display: flex; align-items: center; justify-content: center; gap: 10px; }
        .spinner {
            width: 18px;
            height: 18px;
            border: 2px solid rgba(255,255,255,0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        .divider {
            display: flex;
            align-items: center;
            margin: 24px 0;
            gap: 12px;
        }
        .divider-line { flex: 1; height: 1px; background: linear-gradient(90deg, transparent, var(--gray-700), transparent); }
        .divider-text { font-size: 11px; color: var(--gray-600); }

        .social-buttons { display: flex; gap: 12px; }
        .social-btn {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 11px;
            font-size: 13px;
            font-weight: 500;
            background: var(--gray-900);
            border: 2px solid var(--gray-800);
            border-radius: 10px;
            color: var(--white);
            cursor: pointer;
            transition: all 0.2s;
        }
        .social-btn:hover { background: var(--gray-800); border-color: var(--primary-red); transform: translateY(-1px); }
        
        .register-link {
            text-align: center;
            margin-top: 24px;
            font-size: 13px;
            color: var(--gray-500);
        }
        .register-link a { color: #fca5a5; font-weight: 600; text-decoration: none; }
        .register-link a:hover { color: var(--primary-red); }

        /* Toast Notification */
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1100;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .toast {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 20px;
            background: var(--gray-900);
            border-radius: 12px;
            border-left: 4px solid;
            box-shadow: 0 10px 25px -5px rgba(0,0,0,0.3);
            animation: slideIn 0.3s ease;
            min-width: 300px;
        }
        .toast.error { border-left-color: #dc2626; }
        .toast.success { border-left-color: #10b981; }
        .toast i:first-child { font-size: 22px; }
        .toast.error i:first-child { color: #dc2626; }
        .toast-content { flex: 1; }
        .toast-title { font-size: 14px; font-weight: 700; color: white; }
        .toast-message { font-size: 12px; color: var(--gray-400); margin-top: 2px; }
        .toast-close { background: none; border: none; color: var(--gray-600); cursor: pointer; font-size: 18px; }
        @keyframes slideIn {
            from { opacity: 0; transform: translateX(100px); }
            to { opacity: 1; transform: translateX(0); }
        }
        .toast-removing { animation: slideOut 0.3s forwards; }
        @keyframes slideOut { to { opacity: 0; transform: translateX(100px); } }

        @media (max-width: 768px) {
            .container { flex-direction: column; max-width: 100%; margin: 0 10px; }
            .brand-side { padding: 30px 25px; }
            .form-side { padding: 30px 25px; }
            .social-buttons { flex-direction: column; }
            .toast-container { right: 10px; left: 10px; }
            .toast { min-width: auto; }
        }
    </style>
</head>
<body>
    <div class="bg-gradient"></div>
    <div class="particles">
        <?php for($i = 1; $i <= 15; $i++): ?>
        <div class="particle"></div>
        <?php endfor; ?>
    </div>

    <div class="toast-container" id="toastContainer">
        <?php if(!empty($error)): ?>
        <div class="toast error">
            <i class='bx bx-error-circle'></i>
            <div class="toast-content">
                <div class="toast-title">Login Gagal</div>
                <div class="toast-message"><?= htmlspecialchars($error) ?></div>
            </div>
            <button class="toast-close" onclick="this.parentElement.remove()"><i class='bx bx-x'></i></button>
        </div>
        <?php endif; ?>
    </div>

    <div class="container">
        <!-- Sisi kiri - Branding Merah & Biru Tua -->
        <div class="brand-side">
            <div class="brand-content">
                <div class="brand-logo">
                    <div class="logo-icon"><i class='bx bx-building-house'></i></div>
                    <span class="logo-text">Lurahgo<span>.id</span></span>
                </div>
                <div class="brand-text">
                    <h2>Kelola Desa Modern</h2>
                    <p>Sistem informasi desa terintegrasi untuk pelayanan publik yang cepat, transparan, dan akuntabel.</p>
                </div>
                <div class="brand-features">
                    <div class="feature-item">
                        <div class="feature-icon"><i class='bx bx-shield-alt-2'></i></div>
                        <span class="feature-text">Keamanan data terenkripsi</span>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon"><i class='bx bx-line-chart'></i></div>
                        <span class="feature-text">Laporan real-time & akurat</span>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon"><i class='bx bx-headphone'></i></div>
                        <span class="feature-text">Dukungan prioritas 24/7</span>
                    </div>
                </div>
                <div class="brand-stats">
                    <div class="stat-item"><div class="stat-value">200+</div><div class="stat-label">Desa Bermitra</div></div>
                    <div class="stat-item"><div class="stat-value">25K+</div><div class="stat-label">Pengguna Aktif</div></div>
                    <div class="stat-item"><div class="stat-value">99%</div><div class="stat-label">Kepuasan</div></div>
                </div>
            </div>
        </div>

        <!-- Sisi kanan - Form Login -->
        <div class="form-side">
            <div class="form-header">
                <h1>Selamat Datang Kembali</h1>
                <p>Masuk untuk mengakses dashboard Anda</p>
            </div>

            <form action="" method="POST" id="loginForm">
                <div class="form-group">
                    <label class="form-label">Username atau Email</label>
                    <div class="input-wrapper">
                        <span class="input-icon"><i class='bx bx-user-circle'></i></span>
                        <input type="text" name="login_input" class="form-input" id="loginInput" 
                               value="<?= htmlspecialchars($_POST['login_input'] ?? '') ?>" 
                               placeholder="Masukkan username atau email" required>
                    </div>
                    <?php if($error_type === 'login_input' && !empty($error)): ?>
                    <div class="field-error"><i class='bx bx-error-circle'></i> <?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label class="form-label">Password</label>
                    <div class="input-wrapper">
                        <span class="input-icon"><i class='bx bx-lock-alt'></i></span>
                        <input type="password" name="password" class="form-input" id="passwordField" placeholder="Masukkan password" required>
                        <button type="button" class="password-toggle" id="togglePasswordBtn">
                            <i class='bx bx-show-alt'></i>
                        </button>
                    </div>
                    <?php if($error_type === 'password' && !empty($error)): ?>
                    <div class="field-error"><i class='bx bx-error-circle'></i> <?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>
                </div>

                <div class="form-options">
                    <label class="checkbox-wrapper">
                        <div class="custom-checkbox">
                            <input type="checkbox" name="remember" id="rememberCheck">
                            <span class="checkmark"></span>
                        </div>
                        <span class="checkbox-label">Ingat saya</span>
                    </label>
                    <a href="#" class="forgot-link">Lupa password?</a>
                </div>

                <button type="submit" class="submit-btn" id="loginBtn">
                    <span class="btn-content">Masuk</span>
                </button>

                <div class="divider">
                    <span class="divider-line"></span>
                    <span class="divider-text">atau</span>
                    <span class="divider-line"></span>
                </div>

                <div class="social-buttons">
                    <button type="button" class="social-btn" onclick="socialAlert('Google')">
                        <i class='bx bxl-google'></i> Google
                    </button>
                    <button type="button" class="social-btn" onclick="socialAlert('Apple')">
                        <i class='bx bxl-apple'></i> Apple
                    </button>
                </div>
            </form>

            <p class="register-link">
                Belum punya akun? <a href="register.php">Daftar sekarang</a>
            </p>
        </div>
    </div>

    <script>
        // Toggle password visibility
        const toggleBtn = document.getElementById('togglePasswordBtn');
        const passwordInput = document.getElementById('passwordField');
        const eyeIcon = toggleBtn.querySelector('i');
        
        toggleBtn.addEventListener('click', function() {
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeIcon.classList.remove('bx-show-alt');
                eyeIcon.classList.add('bx-hide');
            } else {
                passwordInput.type = 'password';
                eyeIcon.classList.remove('bx-hide');
                eyeIcon.classList.add('bx-show-alt');
            }
        });

        // Submit loading state
        const loginForm = document.getElementById('loginForm');
        const loginBtn = document.getElementById('loginBtn');
        
        loginForm.addEventListener('submit', function() {
            loginBtn.classList.add('loading');
            loginBtn.querySelector('.btn-content').innerHTML = '<div class="spinner"></div> Memproses...';
        });

        // Social login demo
        function socialAlert(provider) {
            const container = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            toast.className = 'toast';
            toast.style.borderLeftColor = '#f59e0b';
            toast.innerHTML = `
                <i class='bx bx-info-circle' style="color: #f59e0b; font-size: 22px;"></i>
                <div class="toast-content">
                    <div class="toast-title">Fitur Segera Hadir</div>
                    <div class="toast-message">Login dengan ${provider} akan tersedia dalam waktu dekat.</div>
                </div>
                <button class="toast-close" onclick="this.parentElement.remove()"><i class='bx bx-x'></i></button>
            `;
            container.appendChild(toast);
            
            setTimeout(() => {
                if(toast.parentElement) {
                    toast.classList.add('toast-removing');
                    setTimeout(() => toast.remove(), 300);
                }
            }, 4000);
        }

        // Auto remove existing toast after 5 seconds
        document.querySelectorAll('.toast').forEach(toast => {
            setTimeout(() => {
                if(toast.parentElement) {
                    toast.classList.add('toast-removing');
                    setTimeout(() => toast.remove(), 300);
                }
            }, 5000);
        });
    </script>
</body>
</html>