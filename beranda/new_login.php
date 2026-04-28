<?php
session_start();

if (isset($_SESSION['user']) && $_SESSION['user']['logged_in'] === true) {
    if (strtolower($_SESSION['user']['role']) === 'admin') {
        header("Location: /admin.php?page=dashboard");
    } else {
        header("Location: /index.php");
    }
    exit;
}

$error = $_SESSION['error'] ?? null;
$success = $_SESSION['success'] ?? null;
unset($_SESSION['error']);
unset($_SESSION['success']);
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Kigi.id - Sign In</title>
    <style>
      @import url("https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap");

      * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
      }

      :root {
        --primary: #6366f1;
        --primary-dark: #4f46e5;
        --primary-light: #818cf8;
        --accent: #06b6d4;
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
        --gray-300: #e2e8f0;
        --gray-200: #f1f5f9;
        --white: #ffffff;
      }

      body {
        font-family: "Plus Jakarta Sans", -apple-system, BlinkMacSystemFont, sans-serif;
        min-height: 100vh;
        background: var(--darker);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
        position: relative;
        overflow: hidden;
      }

      .bg-gradient {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background:
          radial-gradient(ellipse 80% 50% at 50% -20%, rgba(99, 102, 241, 0.3) 0%, transparent 50%),
          radial-gradient(ellipse 60% 40% at 90% 10%, rgba(6, 182, 212, 0.2) 0%, transparent 50%),
          radial-gradient(ellipse 50% 30% at 10% 90%, rgba(99, 102, 241, 0.15) 0%, transparent 50%);
        z-index: 0;
      }

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
        width: 4px;
        height: 4px;
        background: rgba(255, 255, 255, 0.3);
        border-radius: 50%;
        animation: particleFloat 15s linear infinite;
      }

      .particle:nth-child(1) { left: 10%; animation-duration: 20s; animation-delay: 0s; }
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

      .container {
        position: relative;
        z-index: 10;
        width: 100%;
        max-width: 850px;
        display: flex;
        background: rgba(15, 23, 42, 0.85);
        backdrop-filter: blur(20px);
        border-radius: 20px;
        border: 1px solid rgba(255, 255, 255, 0.08);
        box-shadow: 0 0 0 1px rgba(255, 255, 255, 0.05), 0 20px 40px -12px rgba(0, 0, 0, 0.5);
        overflow: hidden;
        animation: containerFadeIn 0.8s cubic-bezier(0.16, 1, 0.3, 1);
      }

      @keyframes containerFadeIn {
        from { opacity: 0; transform: scale(0.95) translateY(20px); }
        to { opacity: 1; transform: scale(1) translateY(0); }
      }

      .brand-side {
        flex: 1;
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 50%, #3730a3 100%);
        padding: 40px 40px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        position: relative;
        overflow: hidden;
        transition: all 0.5s ease;
      }

      .brand-side::before {
        content: "";
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle at 30% 70%, rgba(255, 255, 255, 0.1) 0%, transparent 40%),
                    radial-gradient(circle at 70% 30%, rgba(6, 182, 212, 0.2) 0%, transparent 40%);
        animation: brandPulse 8s ease-in-out infinite;
      }

      @keyframes brandPulse {
        0%, 100% { transform: translate(0, 0) rotate(0deg); }
        50% { transform: translate(3%, 3%) rotate(5deg); }
      }

      .brand-content { position: relative; z-index: 2; }

      .brand-logo {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 40px;
        animation: logoSlideIn 0.6s ease-out 0.3s both;
      }

      @keyframes logoSlideIn {
        from { opacity: 0; transform: translateX(-20px); }
        to { opacity: 1; transform: translateX(0); }
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
        animation: logoFloat 4s ease-in-out infinite;
      }

      @keyframes logoFloat {
        0%, 100% { transform: translateY(0) rotate(0deg); }
        50% { transform: translateY(-5px) rotate(3deg); }
      }

      .logo-icon svg { width: 28px; height: 28px; color: white; }

      .logo-text { font-size: 28px; font-weight: 800; color: white; letter-spacing: -1px; }
      .logo-text span { color: var(--accent); }

      .brand-text { animation: fadeInUp 0.6s ease-out 0.5s both; }

      @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(30px); }
        to { opacity: 1; transform: translateY(0); }
      }

      .brand-text h2 { font-size: 32px; font-weight: 800; color: white; line-height: 1.2; margin-bottom: 16px; }
      .brand-text p { font-size: 15px; color: rgba(255, 255, 255, 0.8); line-height: 1.6; max-width: 300px; }

      .brand-features {
        margin-top: 32px;
        display: flex;
        flex-direction: column;
        gap: 14px;
        animation: fadeInUp 0.6s ease-out 0.7s both;
      }

      .feature-item { display: flex; align-items: center; gap: 12px; }

      .feature-icon {
        width: 36px;
        height: 36px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
      }

      .feature-icon svg { width: 18px; height: 18px; color: var(--accent); }
      .feature-text { font-size: 13px; font-weight: 500; color: rgba(255, 255, 255, 0.9); }

      .brand-stats {
        margin-top: 40px;
        padding-top: 32px;
        border-top: 1px solid rgba(255, 255, 255, 0.15);
        display: flex;
        gap: 32px;
        animation: fadeInUp 0.6s ease-out 0.9s both;
      }

      .stat-item { text-align: left; }
      .stat-value { font-size: 24px; font-weight: 800; color: white; }
      .stat-label { font-size: 11px; color: rgba(255, 255, 255, 0.6); text-transform: uppercase; letter-spacing: 1px; margin-top: 4px; }

      .form-side {
        flex: 1;
        padding: 40px 45px;
        display: flex;
        flex-direction: column;
        justify-content: center;
      }

      .form-header { text-align: center; margin-bottom: 32px; }
      .form-header h1 { font-size: 26px; font-weight: 700; color: var(--white); margin-bottom: 8px; }
      .form-header p { font-size: 14px; color: var(--gray-500); }

      .form-group { margin-bottom: 20px; }

      .form-label { display: block; font-size: 13px; font-weight: 600; color: var(--gray-400); margin-bottom: 8px; }

      .input-wrapper { position: relative; }

      .input-icon {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--gray-600);
        pointer-events: none;
        transition: color 0.2s;
      }

      .input-icon svg { width: 18px; height: 18px; }
      .form-group:focus-within .input-icon { color: var(--primary); }

      .form-input {
        width: 100%;
        padding: 14px 44px;
        font-size: 14px;
        font-family: inherit;
        color: var(--white);
        background: var(--gray-900);
        border: 2px solid var(--gray-800);
        border-radius: 10px;
        outline: none;
        transition: all 0.2s ease;
      }

      .form-input::placeholder { color: var(--gray-600); }
      .form-input:hover { border-color: var(--gray-700); }
      .form-input:focus { border-color: var(--primary); background: var(--gray-900); box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.15); }

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
        display: flex;
        align-items: center;
        justify-content: center;
        transition: color 0.2s;
        border-radius: 4px;
      }

      .password-toggle:hover { color: var(--gray-400); background: rgba(255, 255, 255, 0.05); }
      .password-toggle svg { width: 18px; height: 18px; }

      .form-options {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
      }

      .checkbox-wrapper { display: flex; align-items: center; gap: 10px; cursor: pointer; }

      .custom-checkbox { position: relative; width: 18px; height: 18px; }
      .custom-checkbox input { position: absolute; opacity: 0; width: 100%; height: 100%; cursor: pointer; }

      .checkmark {
        position: absolute;
        top: 0;
        left: 0;
        width: 18px;
        height: 18px;
        background: var(--gray-900);
        border: 2px solid var(--gray-700);
        border-radius: 5px;
        transition: all 0.2s;
      }

      .custom-checkbox input:checked ~ .checkmark { background: var(--primary); border-color: var(--primary); }

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
        transition: transform 0.15s cubic-bezier(0.4, 0, 0.2, 1);
      }

      .custom-checkbox input:checked ~ .checkmark::after { transform: rotate(45deg) scale(1); }
      .checkbox-label { font-size: 13px; color: var(--gray-400); }

      .forgot-link { font-size: 13px; color: var(--primary-light); text-decoration: none; font-weight: 500; transition: color 0.2s; }
      .forgot-link:hover { color: var(--primary); }

      .submit-btn {
        width: 100%;
        padding: 14px;
        font-size: 15px;
        font-weight: 600;
        font-family: inherit;
        color: white;
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        border: none;
        border-radius: 12px;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        position: relative;
        overflow: hidden;
      }

      .submit-btn::before {
        content: "";
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.25), transparent);
        transition: left 0.6s ease;
      }

      .submit-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 35px rgba(99, 102, 241, 0.35);
      }

      .submit-btn:hover::before { left: 100%; }
      .submit-btn:active { transform: translateY(-1px); }
      .submit-btn.loading { pointer-events: none; }

      .btn-content { display: flex; align-items: center; justify-content: center; gap: 10px; }

      .spinner {
        width: 20px;
        height: 20px;
        border: 2.5px solid rgba(255, 255, 255, 0.3);
        border-top-color: white;
        border-radius: 50%;
        animation: spin 0.7s linear infinite;
      }

      @keyframes spin { to { transform: rotate(360deg); } }

      .divider {
        display: flex;
        align-items: center;
        margin: 24px 0;
        gap: 14px;
      }

      .divider-line { flex: 1; height: 1px; background: linear-gradient(90deg, transparent, var(--gray-700), transparent); }
      .divider-text { font-size: 12px; font-weight: 500; color: var(--gray-600); }

      .social-buttons { display: flex; gap: 12px; }

      .social-btn {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 12px;
        font-size: 13px;
        font-weight: 600;
        font-family: inherit;
        color: var(--white);
        background: var(--gray-900);
        border: 2px solid var(--gray-800);
        border-radius: 10px;
        cursor: pointer;
        transition: all 0.2s ease;
      }

      .social-btn:hover { background: var(--gray-800); border-color: var(--gray-700); transform: translateY(-2px); }
      .social-btn:active { transform: translateY(0); }
      .social-btn svg { width: 22px; height: 22px; }

      .toast-container {
        position: fixed;
        top: 24px;
        right: 24px;
        z-index: 1000;
        display: flex;
        flex-direction: column;
        gap: 14px;
      }

      .toast {
        display: flex;
        align-items: flex-start;
        gap: 14px;
        padding: 18px 22px;
        background: var(--gray-900);
        border-radius: 14px;
        border: 1px solid var(--gray-800);
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.4);
        animation: toastIn 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        min-width: 340px;
        position: relative;
        overflow: hidden;
      }

      .toast::before {
        content: "";
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 4px;
      }

      .toast.success::before { background: var(--success); }
      .toast.error::before { background: var(--error); }
      .toast.warning::before { background: var(--warning); }

      .toast.removing { animation: toastOut 0.3s ease-in forwards; }

      @keyframes toastIn {
        from { opacity: 0; transform: translateX(100px) scale(0.9); }
        to { opacity: 1; transform: translateX(0) scale(1); }
      }

      @keyframes toastOut {
        to { opacity: 0; transform: translateX(100px); }
      }

      .toast-icon { width: 26px; height: 26px; flex-shrink: 0; }
      .toast.success .toast-icon { color: var(--success); }
      .toast.error .toast-icon { color: var(--error); }
      .toast.warning .toast-icon { color: var(--warning); }

      .toast-content { flex: 1; }
      .toast-title { font-size: 15px; font-weight: 700; color: var(--white); }
      .toast-message { font-size: 13px; color: var(--gray-400); margin-top: 4px; line-height: 1.5; }

      .toast-close {
        background: none;
        border: none;
        color: var(--gray-600);
        cursor: pointer;
        padding: 4px;
        display: flex;
        transition: color 0.2s;
      }

      .toast-close:hover { color: var(--white); }

      .register-link {
        text-align: center;
        margin-top: 24px;
        font-size: 14px;
        color: var(--gray-500);
      }

      .register-link a {
        color: var(--primary-light);
        font-weight: 600;
        text-decoration: none;
      }

      .register-link a:hover { color: var(--primary); text-decoration: underline; }

      @media (max-width: 768px) {
        body { padding: 12px; }
        .container { flex-direction: column; max-width: 100%; border-radius: 16px; }
        .brand-side { padding: 30px 24px; min-height: auto; }
        .brand-logo { margin-bottom: 24px; }
        .logo-icon { width: 44px; height: 44px; }
        .logo-icon svg { width: 24px; height: 24px; }
        .logo-text { font-size: 24px; }
        .brand-text h2 { font-size: 22px; }
        .brand-text p { font-size: 13px; max-width: 100%; }
        .brand-features { margin-top: 24px; gap: 10px; }
        .brand-stats { margin-top: 28px; padding-top: 24px; gap: 20px; }
        .form-side { padding: 28px 24px; }
        .form-header { margin-bottom: 24px; }
        .form-header h1 { font-size: 22px; margin-bottom: 6px; }
        .form-header p { font-size: 13px; }
        .form-group { margin-bottom: 16px; }
        .form-input { padding: 12px 40px; font-size: 14px; }
        .form-options { margin-bottom: 20px; flex-wrap: wrap; gap: 10px; }
        .submit-btn { padding: 12px; font-size: 14px; }
        .social-buttons { flex-direction: column; }
        .social-btn { padding: 10px; font-size: 13px; }
      }
    </style>
</head>
<body>
    <div class="bg-gradient"></div>

    <div class="particles">
      <div class="particle"></div>
      <div class="particle"></div>
      <div class="particle"></div>
      <div class="particle"></div>
      <div class="particle"></div>
      <div class="particle"></div>
      <div class="particle"></div>
      <div class="particle"></div>
      <div class="particle"></div>
      <div class="particle"></div>
      <div class="particle"></div>
      <div class="particle"></div>
      <div class="particle"></div>
      <div class="particle"></div>
      <div class="particle"></div>
    </div>

    <div class="toast-container" id="toastContainer">
      <?php if($error): ?>
      <div class="toast error">
        <svg class="toast-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="12" cy="12" r="10"/>
          <line x1="15" y1="9" x2="9" y2="15"/>
          <line x1="9" y1="9" x2="15" y2="15"/>
        </svg>
        <div class="toast-content">
          <div class="toast-title">Login Gagal</div>
          <div class="toast-message"><?= htmlspecialchars($error) ?></div>
        </div>
        <button class="toast-close" onclick="this.parentElement.remove()">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="18" y1="6" x2="6" y2="18"/>
            <line x1="6" y1="6" x2="18" y2="18"/>
          </svg>
        </button>
      </div>
      <?php endif; ?>
    </div>

    <div class="container">
      <div class="brand-side">
        <div class="brand-content">
          <div class="brand-logo">
            <div class="logo-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 2L2 7l10 5 10-5-10-5z" />
                <path d="M2 17l10 5 10-5" />
                <path d="M2 12l10 5 10-5" />
              </svg>
            </div>
            <span class="logo-text">kigi<span>.id</span></span>
          </div>

          <div class="brand-text">
            <h2>Build Something Amazing With Us</h2>
            <p>Join thousands of creators building the future of digital innovation. Your journey starts here.</p>
          </div>

          <div class="brand-features">
            <div class="feature-item">
              <div class="feature-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                  <path d="M9 12l2 2 4-4" />
                </svg>
              </div>
              <span class="feature-text">Enterprise-grade security</span>
            </div>
            <div class="feature-item">
              <div class="feature-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <circle cx="12" cy="12" r="10" />
                  <path d="M12 6v6l4 2" />
                </svg>
              </div>
              <span class="feature-text">Lightning fast performance</span>
            </div>
            <div class="feature-item">
              <div class="feature-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                  <circle cx="9" cy="7" r="4" />
                  <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                  <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                </svg>
              </div>
              <span class="feature-text">Join 50,000+ active users</span>
            </div>
          </div>

          <div class="brand-stats">
            <div class="stat-item">
              <div class="stat-value">50K+</div>
              <div class="stat-label">Active Users</div>
            </div>
            <div class="stat-item">
              <div class="stat-value">99.9%</div>
              <div class="stat-label">Uptime</div>
            </div>
            <div class="stat-item">
              <div class="stat-value">24/7</div>
              <div class="stat-label">Support</div>
            </div>
          </div>
        </div>
      </div>

      <div class="form-side">
        <div class="form-header">
          <h1>Welcome Back</h1>
          <p>Enter your details to continue</p>
        </div>

        <div class="form-wrapper">
          <form action="login_proses.php" method="POST" id="loginForm">
            <div class="form-group">
              <label class="form-label">Email Address</label>
              <div class="input-wrapper">
                <span class="input-icon">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                    <polyline points="22,6 12,13 2,6"/>
                  </svg>
                </span>
                <input type="email" name="email" class="form-input" id="loginEmail" placeholder="Enter your email" required />
              </div>
            </div>

            <div class="form-group">
              <label class="form-label">Password</label>
              <div class="input-wrapper">
                <span class="input-icon">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                  </svg>
                </span>
                <input type="password" name="password" class="form-input" id="loginPassword" placeholder="Enter your password" required />
                <button type="button" class="password-toggle" onclick="togglePassword()">
                  <svg class="eye-open" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                    <circle cx="12" cy="12" r="3"/>
                  </svg>
                  <svg class="eye-closed" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none">
                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/>
                    <line x1="1" y1="1" x2="23" y2="23"/>
                  </svg>
                </button>
              </div>
            </div>

            <div class="form-options">
              <label class="checkbox-wrapper">
                <div class="custom-checkbox">
                  <input type="checkbox" name="remember" />
                  <span class="checkmark"></span>
                </div>
                <span class="checkbox-label">Remember me</span>
              </label>
              <a href="#" class="forgot-link">Forgot password?</a>
            </div>

            <button type="submit" class="submit-btn" id="loginBtn">
              <span class="btn-content">Sign In</span>
            </button>

            <div class="divider">
              <span class="divider-line"></span>
              <span class="divider-text">or continue with</span>
              <span class="divider-line"></span>
            </div>

            <div class="social-buttons">
              <button type="button" class="social-btn" onclick="socialLogin('google')">
                <svg viewBox="0 0 24 24">
                  <path fill="#EA4335" d="M5.26620003,9.76452941 C6.19878754,6.93863203 8.85444915,4.90909091 12,4.90909091 C13.6909091,4.90909091 15.2181818,5.50909091 16.4181818,6.49090909 L19.9090909,3 C17.7818182,1.14545455 15.0545455,0 12,0 C7.27006974,0 3.1977497,2.69829785 1.23999023,6.65002441 L5.26620003,9.76452941 Z"/>
                  <path fill="#34A853" d="M16.0407269,18.0125889 C14.9509167,18.7163016 13.5660892,19.0909091 12,19.0909091 C8.86648613,19.0909091 6.21911939,17.076871 5.27698177,14.2678769 L1.23746264,17.3349879 C3.19279051,21.2936293 7.26500293,24 12,24 C14.9328362,24 17.7353462,22.9573905 19.834192,20.9995801 L16.0407269,18.0125889 Z"/>
                  <path fill="#4A90E2" d="M19.834192,20.9995801 C22.0291676,18.9520994 23.4545455,15.903663 23.4545455,12 C23.4545455,11.2909091 23.3454545,10.5272727 23.1818182,9.81818182 L12,9.81818182 L12,14.4545455 L18.4363636,14.4545455 C18.1187732,16.013626 17.2662994,17.2212117 16.0407269,18.0125889 L19.834192,20.9995801 Z"/>
                  <path fill="#FBBC05" d="M5.27698177,14.2678769 C5.03832634,13.556323 4.90909091,12.7937589 4.90909091,12 C4.90909091,11.2182781 5.03443647,10.4668121 5.26620003,9.76452941 L1.23999023,6.65002441 C0.43658717,8.26043162 0,10.0753848 0,12 C0,13.9195484 0.444780743,15.7.992080066,21 L5.27698177,14.2678769 Z"/>
                </svg>
                Google
              </button>
              <button type="button" class="social-btn" onclick="socialLogin('apple')">
                <svg viewBox="0 0 24 24" fill="currentColor">
                  <path d="M17.05 20.28c-.98.95-2.05.8-3.08.35-1.09-.46-2.09-.48-3.24 0-1.44.62-2.2.44-3.06-.35C2.79 15.25 3.51 7.59 9.05 7.31c1.35.07 2.29.74 3.08.8 1.18-.24 2.31-.93 3.57-.84 1.51.12 2.65.72 3.4 1.8-3.12 1.87-2.38 5.98.48 7.13-.57 1.5-1.31 2.99-2.54 4.09l.01-.01zM12.03 7.25c-.15-2.23 1.66-4.07 3.74-4.25.29 2.58-2.34 4.5-3.74 4.25z"/>
                </svg>
                Apple
              </button>
            </div>
          </form>

          <p class="register-link">
            Don't have an account? <a href="register.php">Create one</a>
          </p>
        </div>
      </div>
    </div>

    <script>
      function togglePassword() {
        const input = document.getElementById("loginPassword");
        const eyeOpen = document.querySelector(".eye-open");
        const eyeClosed = document.querySelector(".eye-closed");

        if (input.type === "password") {
          input.type = "text";
          eyeOpen.style.display = "none";
          eyeClosed.style.display = "block";
        } else {
          input.type = "password";
          eyeOpen.style.display = "block";
          eyeClosed.style.display = "none";
        }
      }

      function showToast(type, title, message) {
        const container = document.getElementById("toastContainer");
        const toast = document.createElement("div");
        toast.className = `toast ${type}`;

        const icons = {
          success: '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>',
          error: '<circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>',
          warning: '<path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/>',
        };

        toast.innerHTML = `
          <svg class="toast-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            ${icons[type]}
          </svg>
          <div class="toast-content">
            <div class="toast-title">${title}</div>
            <div class="toast-message">${message}</div>
          </div>
          <button class="toast-close" onclick="this.parentElement.remove()">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <line x1="18" y1="6" x2="6" y2="18"/>
              <line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
          </button>
        `;

        container.appendChild(toast);

        setTimeout(() => {
          toast.classList.add("removing");
          setTimeout(() => toast.remove(), 300);
        }, 4000);
      }

      document.getElementById("loginForm").addEventListener("submit", function() {
        const btn = document.getElementById("loginBtn");
        btn.classList.add("loading");
        btn.querySelector(".btn-content").innerHTML = '<div class="spinner"></div> Signing in...';
      });

      function socialLogin(provider) {
        showToast("warning", "Coming Soon", `Sign in with ${provider.charAt(0).toUpperCase() + provider.slice(1)} will be available soon.`);
      }

      setTimeout(() => {
        const toasts = document.querySelectorAll('.toast');
        toasts.forEach(toast => {
          setTimeout(() => {
            toast.classList.add('removing');
            setTimeout(() => toast.remove(), 300);
          }, 5000);
        });
      }, 100);
    </script>
</body>
</html>