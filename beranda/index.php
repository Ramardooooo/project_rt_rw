<?php if (!session_id()) session_start(); ?>
<?php
include __DIR__ . '/../config/database.php';
$user_id = $_SESSION['user_id'] ?? null;
$user = null;
if ($user_id) {
    $stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);
}

// Get stats for counters
$total_warga = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) total FROM warga"))['total'] ?? 0;
$rt_aktif = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) total FROM rt WHERE status = 'aktif'"))['total'] ?? 0;
$total_users = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) total FROM users"))['total'] ?? 0;
$kegiatan = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) total FROM activities"))['total'] ?? 0;

// Get gallery items
$gallery_query = "SELECT g.*, COUNT(l.id) as like_count FROM gallery g LEFT JOIN gallery_likes l ON g.id = l.gallery_id GROUP BY g.id ORDER BY g.created_at DESC LIMIT 6";
$gallery_result = mysqli_query($conn, $gallery_query);
$gallery_items = $gallery_result ? mysqli_fetch_all($gallery_result, MYSQLI_ASSOC) : [];

// Get testimonials
$test_query = "SELECT * FROM testimonials WHERE name IS NOT NULL AND name != '' AND description IS NOT NULL ORDER BY created_at DESC LIMIT 6";
$test_result = mysqli_query($conn, $test_query);
$testimonials = $test_result ? mysqli_fetch_all($test_result, MYSQLI_ASSOC) : [];

// If table schema uses `name` not `nama`, make page queries consistent

// Get announcements
$ann_query = "SELECT id, title, content, DATE_FORMAT(created_at, '%d %b %Y') as date FROM announcements ORDER BY created_at DESC LIMIT 6";
$ann_result = mysqli_query($conn, $ann_query);
$announcements = $ann_result ? mysqli_fetch_all($ann_result, MYSQLI_ASSOC) : [];
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <script src="https://cdn.tailwindcss.com"></script>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Lurahgo.id — Platform Digital RT/RW</title>
  <meta name="description" content="Platform digital terintegrasi untuk mengelola data warga, komunikasi transparan, dan pelaporan real-time." />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,300&family=Playfair+Display:ital,wght@0,700;1,600&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="beranda/templatemo-622-clearwave.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>


  <style>
    /* --- Testimonials styling fix (form jadi rapi & ga jelek) --- */
    .testimonials-section{padding:40px 0;}
    .testimonials-send-header h3{font-size:1.5rem;font-weight:900;color:var(--accent);margin:0 0 8px;}
    .testimonials-send-header p{color:var(--text-2);margin:0 0 18px;line-height:1.6;}

    #testimonialForm{background:rgba(255,255,255,.75);border:1px solid var(--accent-border);backdrop-filter:blur(10px);border-radius:20px;padding:18px;box-shadow:0 20px 60px rgba(0,0,0,.06);}
    #testimonialForm .form-row{display:grid;grid-template-columns:1fr;gap:14px;}
    @media(min-width:768px){#testimonialForm .form-row{grid-template-columns:1fr 1fr;}}

    #testimonialForm label{display:block;font-weight:800;color:var(--text-1);font-size:.95rem;margin:0 0 8px;}
    #testimonialForm input[type="text"],
    #testimonialForm textarea{
      width:100%;
      background:rgba(255,255,255,.9);
      border:1px solid rgba(26,75,122,.25);
      border-radius:14px;
      padding:12px 14px;
      color:var(--text-1);
      outline:none;
      transition:border-color .2s, box-shadow .2s;
    }
    #testimonialForm input[type="text"]:focus,
    #testimonialForm textarea:focus{border-color:var(--accent-mid);box-shadow:0 0 0 4px rgba(46,127,199,.15);}
    #testimonialForm textarea{resize:none;min-height:120px;}

    .rating-stars{display:flex;gap:8px;align-items:center;margin-bottom:10px;}
    .rating-stars .star{
      cursor:pointer;
      font-size:1.7rem;
      line-height:1;
      color:#d1d5db;
      transition:transform .12s ease, color .12s ease;
      user-select:none;
    }
    .rating-stars .star:hover{transform:translateY(-1px);color:var(--accent-mid);}
    .rating-stars .star.fas{color:#f59e0b;}

    #testimonialForm button[type="submit"]{
      margin-top:14px;
      width:100%;
      border:0;
      border-radius:16px;
      padding:12px 14px;
      background:var(--accent);
      color:#fff;
      font-weight:900;
      cursor:pointer;
      transition:background .2s, transform .12s ease;
    }
    #testimonialForm button[type="submit"]:hover{background:var(--accent-mid);transform:translateY(-1px);}

    .testimonial-message{margin:14px 0;padding:12px 14px;border-radius:14px;font-weight:800;}
    .testimonial-message.success{background:rgba(34,197,94,.10);border:1px solid rgba(34,197,94,.25);color:#16a34a;}
    .testimonial-message.error{background:rgba(239,68,68,.10);border:1px solid rgba(239,68,68,.25);color:#dc2626;}

    /* Override accent colors to match Lurahgo branding */
    :root {
      --accent: #1A4B7A;
      --accent-mid: #2E7FC7;
      --accent-light: #5BA8E8;
      --accent-ghost: rgba(26,75,122,0.08);
      --accent-border: rgba(26,75,122,0.15);
    }
    .pricing-card.featured {
      border-color: var(--accent);
    }
    .pricing-card.featured:hover {
      box-shadow: 0 28px 80px rgba(26,75,122,0.35);
    }
    .pricing-badge {
      background: var(--accent);
    }
    .btn-primary-lg, .btn-primary, .btn-cta-primary, .toggle-switch {
      background: var(--accent) !important;
    }
    .btn-primary-lg:hover, .btn-primary:hover, .btn-cta-primary:hover {
      background: var(--accent-mid) !important;
    }
    .stat-rule {
      background: var(--accent);
    }
    .stat-suffix {
      color: var(--accent);
    }
    .db-stat-change {
      color: var(--accent);
    }
    .db-bar.active {
      background: var(--accent);
      border-color: var(--accent);
    }
    .db-tag {
      color: var(--accent);
      background: var(--accent-ghost);
      border-color: var(--accent-border);
    }
    .fv-pill.green {
      background: rgba(26,75,122,0.12);
      color: var(--accent);
    }
    .fv-pill.blue {
      background: rgba(46,127,199,0.12);
      color: var(--accent-mid);
    }
    .fv-card-bar-fill {
      background: var(--accent);
    }
    .check-icon {
      background: var(--accent-ghost);
      border-color: var(--accent-border);
      color: var(--accent);
    }
    .pricing-check {
      background: var(--accent-ghost);
      border-color: var(--accent-border);
      color: var(--accent);
    }
    .pricing-card.featured .pricing-check {
      background: rgba(91,168,232,0.2);
      border-color: rgba(91,168,232,0.4);
      color: var(--accent-light);
    }
    .pricing-card.featured .pricing-cta {
      background: var(--accent);
      border-color: var(--accent);
      box-shadow: 0 6px 24px rgba(26,75,122,0.35);
    }
    .pricing-card.featured .pricing-cta:hover {
      background: var(--accent-mid);
    }
    .author-avatar {
      border-color: var(--accent-border);
      background: var(--accent-ghost);
      color: var(--accent);
    }
    .integration-tile:hover {
      border-color: var(--accent-border);
      background: var(--accent-ghost);
    }
    .integration-tile:hover .integration-name {
      color: var(--accent);
    }
    .nav-links a:hover {
      color: var(--accent);
      background: var(--accent-ghost);
      box-shadow: inset 0 0 0 1px var(--accent-border);
    }
    .btn-ghost:hover {
      color: var(--accent);
      background: var(--accent-ghost);
    }
    .hero-badge strong {
      color: var(--accent);
    }
    .float-badge-icon {
      background: var(--accent);
    }
    .hero-float-badge-2 {
      background: var(--accent);
      box-shadow: 0 8px 24px rgba(26,75,122,0.35);
    }
    .carousel-btn {
      border-color: var(--accent-border);
      color: var(--accent);
    }
    .carousel-btn:hover {
      background: var(--accent);
      color: #fff;
      border-color: var(--accent);
    }
    .carousel-dot.active {
      background: var(--accent);
    }
    .carousel-dot {
      background: var(--accent-border);
    }
    .zoom-btn {
      border-color: var(--accent-border);
      color: var(--accent);
    }
    .zoom-btn:hover:not(:disabled) {
      background: var(--accent);
      color: #fff;
      border-color: var(--accent);
    }
    .zoom-pip.active {
      background: var(--accent);
    }
    .zoom-pip {
      background: var(--accent-border);
    }
    .faq-toggle-all {
      color: var(--accent);
    }
    .faq-question:hover {
      color: var(--accent);
    }
    .faq-icon {
      border-color: var(--accent-border);
      color: var(--accent);
    }
    .faq-item.open .faq-icon {
      background: var(--accent);
      color: #fff;
      border-color: var(--accent);
    }
    .social-btn:hover {
      border-color: var(--accent-light);
      color: var(--accent-light);
    }
    .footer-links a:hover {
      color: var(--accent-light);
    }
    .footer-copy a:hover {
      color: var(--accent-light);
    }
    .cta-label {
      color: var(--accent-light);
      border-color: rgba(91,168,232,0.3);
    }
    .cta-title em {
      color: var(--accent-light);
    }
    .btn-cta-primary {
      box-shadow: 0 8px 32px rgba(26,75,122,0.40);
    }
    .mobile-menu .mobile-cta {
      background: var(--accent) !important;
    }
    .mobile-menu.open .mobile-cta:hover {
      background: var(--accent-mid) !important;
    }
    .mobile-menu a:hover {
      color: var(--accent);
    }
    .gallery-section-img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
    }
  </style>
</head>
<body>

  <!-- ── MOBILE MENU ── -->
  <div class="mobile-menu" id="mobileMenu" role="dialog" aria-modal="true" aria-label="Navigation">
    <a href="#screens">Gallery</a>
    <a href="#features">Fitur</a>
    <a href="#pricing">Paket</a>
    <a href="#testimonials">Testimoni</a>
    <a href="#faq">FAQ</a>
    <?php if (isset($_SESSION['user_id'])): ?>
      <a href="dashboard_<?php echo $_SESSION['role']; ?>" class="mobile-cta btn-primary">Dashboard</a>
    <?php else: ?>
      <a href="login" class="mobile-cta btn-primary">Masuk</a>
    <?php endif; ?>
  </div>

  <!-- ── NAV ── -->
  <nav class="nav" id="mainNav" role="navigation" aria-label="Main navigation">
    <div class="nav-inner">
      <a href="/" class="nav-logo">Lurahgo<span>.id</span></a>
      <ul class="nav-links" role="list">
        <li><a href="#screens">Gallery</a></li>
        <li><a href="#features">Fitur</a></li>
        <li><a href="#pricing">Paket</a></li>
        <li><a href="#testimonials">Testimoni</a></li>
        <li><a href="#faq">FAQ</a></li>
      </ul>
                <div class="nav-cta">
        <?php if (isset($_SESSION['user_id'])): ?>
          <div class="user-dropdown" style="position:relative;display:inline-block;">
            <?php
              $avatar = $user && !empty($user['profile_photo']) ? $user['profile_photo'] : 'uploads/profiles/default-avatar.png';
              $uname = $user['username'] ?? 'User';
            ?>
            <button type="button" class="btn-primary" style="display:flex;align-items:center;gap:10px;padding:10px 14px;">
              <img src="<?php echo htmlspecialchars($avatar); ?>" alt="Profile" style="width:24px;height:24px;border-radius:50%;object-fit:cover;" />
              <span><?php echo htmlspecialchars($uname); ?></span>
              <span style="opacity:.8;">▾</span>
            </button>
            <div class="user-dropdown-menu" style="display:none;position:absolute;right:0;top:48px;min-width:210px;background:#fff;border:1px solid var(--accent-border);border-radius:14px;box-shadow:0 18px 60px rgba(0,0,0,.12);padding:8px;z-index:50;">
              <a href="dashboard_<?php echo $_SESSION['role']; ?>" style="display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:10px;color:#0f172a;text-decoration:none;">Dashboard</a>
              <a href="account/settings" style="display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:10px;color:#0f172a;text-decoration:none;">Settings</a>
              <a href="account/settings" data-logout="1" style="display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:10px;color:#b91c1c;text-decoration:none;opacity:1;font-weight:800;background:rgba(239,68,68,0.08);border:1px solid rgba(239,68,68,0.25);">Log out</a>
            </div>
          </div>

          <script>
            (function(){
              const root = document.querySelector('.user-dropdown');
              if(!root) return;
              const btn = root.querySelector('button');
              const menu = root.querySelector('.user-dropdown-menu');
              btn.addEventListener('click', function(){
                const open = menu.style.display === 'block';
                menu.style.display = open ? 'none' : 'block';
              });
              document.addEventListener('click', function(e){
                if(!root.contains(e.target)) menu.style.display = 'none';
              });
              const logoutLink = root.querySelector('[data-logout="1"]');
              logoutLink.addEventListener('click', async function(e){
                e.preventDefault();
                // project logout route seems to be plain "logout" (as used in layouts)
                window.location.href = 'logout';
              });
            })();
          </script>
        <?php else: ?>
          <a href="login" class="btn-ghost">Masuk</a>
          <a href="register" class="btn-primary">Daftar Gratis</a>
        <?php endif; ?>
      </div>
      <button class="nav-hamburger" id="hamburger" aria-label="Toggle menu" aria-expanded="false">
        <span></span><span></span><span></span>
      </button>
    </div>
  </nav>

  <!-- ── HERO ── -->
  <section class="hero">
    <div class="container">
      <div class="hero-inner">
        <div class="hero-content">
          <div class="hero-badge reveal">
            <div class="hero-badge-dot">✦</div>
            <span>Platform <strong>#1 RT/RW Digital</strong> Indonesia</span>
          </div>
          <h1 class="hero-title reveal reveal-delay-1">
            Kelola RT/RW,<br><em>lebih mudah & transparan.</em>
          </h1>
          <p class="hero-sub reveal reveal-delay-2">
            Lurahgo.id menyatukan data warga, komunikasi, dan pelaporan dalam satu platform yang tenang dan terfokus. Lebih sedikit kerumitan, lebih banyak selesai.
          </p>
          <div class="hero-actions reveal reveal-delay-3">
            <a href="register" class="btn-primary-lg">
              Daftar Gratis — Tanpa Kartu
              <span class="btn-arrow">→</span>
            </a>
            <a href="#features" class="btn-outline-lg">
              <span>▶</span> Lihat fitur
            </a>
          </div>
          <div class="hero-trust reveal reveal-delay-4">
            <div class="trust-item">
              <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
              Data Terenkripsi
            </div>
            <div class="trust-divider"></div>
            <div class="trust-item">
              <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
              99.9% Uptime
            </div>
            <div class="trust-divider"></div>
            <div class="trust-item">
              <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
              500+ RT/RW
            </div>
          </div>
        </div>
        <div class="hero-visual reveal reveal-delay-2">
          <div class="hero-dashboard">
            <div class="dashboard-bar">
              <div class="db-dot"></div><div class="db-dot"></div><div class="db-dot"></div>
            </div>
            <div class="dashboard-body">
              <div class="db-header">
                <div class="db-title">Statistik Warga</div>
                <div class="db-tag">Live</div>
              </div>
              <div class="db-chart">
                <div class="db-bar" style="height:35%"></div>
                <div class="db-bar" style="height:55%"></div>
                <div class="db-bar" style="height:42%"></div>
                <div class="db-bar active" style="height:75%"></div>
                <div class="db-bar" style="height:60%"></div>
                <div class="db-bar active" style="height:88%"></div>
                <div class="db-bar" style="height:70%"></div>
                <div class="db-bar active" style="height:92%"></div>
              </div>
              <div class="db-stats">
                <div class="db-stat">
                  <div class="db-stat-val"><?php echo number_format($total_warga); ?></div>
                  <div class="db-stat-label">Total Warga</div>
                  <div class="db-stat-change">+12%</div>
                </div>
                <div class="db-stat">
                  <div class="db-stat-val"><?php echo number_format($rt_aktif); ?></div>
                  <div class="db-stat-label">RT Aktif</div>
                  <div class="db-stat-change">+8%</div>
                </div>
                <div class="db-stat">
                  <div class="db-stat-val"><?php echo number_format($kegiatan); ?></div>
                  <div class="db-stat-label">Kegiatan</div>
                  <div class="db-stat-change">+24%</div>
                </div>
              </div>
            </div>
          </div>
          <div class="hero-float-badge">
            <div class="float-badge-icon">✦</div>
            <div class="float-badge-text">
            <strong>Pengumuman Baru</strong>
              <span><?php echo htmlspecialchars($announcements[0]['title'] ?? ''); ?></span>
            </div>
          </div>
          <div class="hero-float-badge-2">
            <div class="float-badge-2-val">↑ 34%</div>
            <div class="float-badge-2-label">Partisipasi warga</div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ── LOGO TICKER ── -->
  <div class="ticker-section">
    <div class="ticker-label">Dipercaya oleh tim-tim di seluruh Indonesia</div>
    <div class="ticker-track-wrap">
      <div class="ticker-track">
        <div class="ticker-item"><div class="ticker-item-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="var(--accent)"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg></div>RT 01/RW 03</div>
        <div class="ticker-dot"></div>
        <div class="ticker-item"><div class="ticker-item-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="var(--accent)"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3" stroke="#fff" stroke-width="2" stroke-linecap="round"/></svg></div>RW 05 Merdeka</div>
        <div class="ticker-dot"></div>
        <div class="ticker-item"><div class="ticker-item-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="var(--accent)"><polygon points="12,2 22,20 2,20"/></svg></div>RT 12 Sukamaju</div>
        <div class="ticker-dot"></div>
        <div class="ticker-item"><div class="ticker-item-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="var(--accent)"><path d="M4 4h16v12H4z M8 20h8"/></svg></div>RW 08 Harmoni</div>
        <div class="ticker-dot"></div>
        <div class="ticker-item"><div class="ticker-item-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="var(--accent)"><circle cx="12" cy="7" r="4"/><path d="M4 21c0-4 3.6-7 8-7s8 3 8 7"/></svg></div>RT 07 Bahagia</div>
        <div class="ticker-dot"></div>
        <div class="ticker-item"><div class="ticker-item-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="var(--accent)"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2"/></svg></div>RW 02 Sejahtera</div>
        <div class="ticker-dot"></div>
        <div class="ticker-item"><div class="ticker-item-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="var(--accent)"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg></div>RT 09 Damai</div>
        <div class="ticker-dot"></div>
        <div class="ticker-item"><div class="ticker-item-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="var(--accent)"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg></div>RW 11 Makmur</div>
        <div class="ticker-dot"></div>
        <!-- Set 2 duplicate -->
        <div class="ticker-item"><div class="ticker-item-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="var(--accent)"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg></div>RT 01/RW 03</div>
        <div class="ticker-dot"></div>
        <div class="ticker-item"><div class="ticker-item-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="var(--accent)"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3" stroke="#fff" stroke-width="2" stroke-linecap="round"/></svg></div>RW 05 Merdeka</div>
        <div class="ticker-dot"></div>
        <div class="ticker-item"><div class="ticker-item-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="var(--accent)"><polygon points="12,2 22,20 2,20"/></svg></div>RT 12 Sukamaju</div>
        <div class="ticker-dot"></div>
        <div class="ticker-item"><div class="ticker-item-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="var(--accent)"><path d="M4 4h16v12H4z M8 20h8"/></svg></div>RW 08 Harmoni</div>
        <div class="ticker-dot"></div>
        <div class="ticker-item"><div class="ticker-item-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="var(--accent)"><circle cx="12" cy="7" r="4"/><path d="M4 21c0-4 3.6-7 8-7s8 3 8 7"/></svg></div>RT 07 Bahagia</div>
        <div class="ticker-dot"></div>
        <div class="ticker-item"><div class="ticker-item-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="var(--accent)"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2"/></svg></div>RW 02 Sejahtera</div>
        <div class="ticker-dot"></div>
        <div class="ticker-item"><div class="ticker-item-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="var(--accent)"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg></div>RT 09 Damai</div>
        <div class="ticker-dot"></div>
        <div class="ticker-item"><div class="ticker-item-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="var(--accent)"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg></div>RW 11 Makmur</div>
        <div class="ticker-dot"></div>
      </div>
    </div>
  </div>

  <!-- ── FEATURES ── -->
  <section class="features-section" id="features">
    <div class="container">
      <div class="features-header">
        <div class="section-label reveal">Fitur Platform</div>
        <h2 class="section-title reveal reveal-delay-1">Dibangun untuk <em>kejelasan</em><br>di setiap level</h2>
        <p class="section-sub reveal reveal-delay-2">Setiap fitur dirancang untuk mengurangi friksi dan menampilkan yang terpenting. Tanpa bloat, tanpa konfigurasi rumit.</p>
      </div>

      <!-- Feature 1 -->
      <div class="feature-row">
        <div class="feature-content reveal">
          <div class="feature-number">01 — Dashboard Terpadu</div>
          <h3 class="feature-title">Semua metrik, satu permukaan tenang</h3>
          <p class="feature-desc">Tarik data dari berbagai sumber ke satu tampilan yang dapat dikomposisi. Bagikan dashboard live dengan stakeholder — tanpa ekspor, tanpa screenshot.</p>
          <div class="feature-checklist">
            <div class="feature-check"><div class="check-icon">✓</div><span>Drag-and-drop widget builder</span></div>
            <div class="feature-check"><div class="check-icon">✓</div><span>Sinkronisasi data real-time dari semua sumber</span></div>
            <div class="feature-check"><div class="check-icon">✓</div><span>Sharing berbasis role dengan izin granular</span></div>
          </div>
        </div>
        <div class="feature-visual reveal reveal-delay-1">
          <div class="feature-visual-inner">
            <div class="fv-row">
              <div class="fv-card">
                <div class="fv-card-label">Total Warga</div>
                <div class="fv-card-val"><?php echo number_format($total_warga); ?></div>
                <div class="fv-card-bar"><div class="fv-card-bar-fill" style="width:72%"></div></div>
              </div>
              <div class="fv-card">
                <div class="fv-card-label">Pengguna Aktif</div>
                <div class="fv-card-val"><?php echo number_format($total_users); ?></div>
                <div class="fv-card-bar"><div class="fv-card-bar-fill" style="width:88%"></div></div>
              </div>
            </div>
            <div class="fv-card">
              <div class="fv-card-label">Aktivitas Terbaru</div>
              <div class="fv-list" style="margin-top:8px">
                <div class="fv-list-item"><span class="fv-list-name">Data warga baru ditambahkan</span><span class="fv-pill green">Selesai</span></div>
                <div class="fv-list-item"><span class="fv-list-name">Review pengumuman pending</span><span class="fv-pill blue">Aktif</span></div>
                <div class="fv-list-item"><span class="fv-list-name">Laporan bulanan</span><span class="fv-pill dim">Antri</span></div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Feature 2 -->
      <div class="feature-row reverse">
        <div class="feature-content reveal">
          <div class="feature-number">02 — Otomatisasi Cerdas</div>
          <h3 class="feature-title">Otomatisasi untuk RT/RW<br><em>yang rapi</em></h3>
          <p class="feature-desc">Otomatisasi aktivitas RT/RW: notifikasi warga, pengingat agenda, dan rekap berjalan tanpa ribet. Semua terhubung ke alur kerja harian.</p>
          <div class="feature-checklist">
            <div class="feature-check"><div class="check-icon">✓</div><span>Notifikasi otomatis ke warga</span></div>
            <div class="feature-check"><div class="check-icon">✓</div><span>500+ template pemicu siap pakai</span></div>
            <div class="feature-check"><div class="check-icon">✓</div><span>Audit trail lengkap untuk setiap aksi</span></div>
          </div>
        </div>
        <div class="feature-visual reveal reveal-delay-1">
          <div class="feature-visual-inner">
            <div class="fv-card">
              <div class="fv-card-label">Otomatisasi Aktif</div>
              <div class="fv-list" style="margin-top:8px">
                <div class="fv-list-item"><span class="fv-list-name">Digest mingguan → WhatsApp</span><span class="fv-pill green">Berjalan</span></div>
                <div class="fv-list-item"><span class="fv-list-name">Iuran jatuh tempo → Alert</span><span class="fv-pill green">Berjalan</span></div>
                <div class="fv-list-item"><span class="fv-list-name">Pengumuman → Notifikasi</span><span class="fv-pill blue">Aktif</span></div>
                <div class="fv-list-item"><span class="fv-list-name">Sinkron laporan → Drive</span><span class="fv-pill dim">Jeda</span></div>
              </div>
            </div>
            <div class="fv-row">
              <div class="fv-card fv-wide">
                <div class="fv-card-label">Jam Hemat Bulan Ini</div>
                <div class="fv-card-val">148h</div>
                <div class="fv-card-bar"><div class="fv-card-bar-fill" style="width:91%"></div></div>
              </div>
              <div class="fv-card">
                <div class="fv-card-label">Alur</div>
                <div class="fv-card-val">24</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Feature 3 -->
      <div class="feature-row">
        <div class="feature-content reveal">
          <div class="feature-number">03 — Kolaborasi Tim</div>
          <h3 class="feature-title">Komentar, konteks,<br>dan <em>kejelasan</em></h3>
          <p class="feature-desc">Annotasi apa saja, tetapkan tugas inline, dan selesaikan percakapan tanpa berpindah tab. Pekerjaan dan diskusi tetap bersama.</p>
          <div class="feature-checklist">
            <div class="feature-check"><div class="check-icon">✓</div><span>Komentar inline pada data apapun</span></div>
            <div class="feature-check"><div class="check-icon">✓</div><span>Penugasan tugas ber-thread</span></div>
            <div class="feature-check"><div class="check-icon">✓</div><span>Presence real-time dan cursor live</span></div>
          </div>
        </div>
        <div class="feature-visual reveal reveal-delay-1">
          <div class="feature-visual-inner">
            <div class="fv-row">
              <div class="fv-card fv-wide">
                <div class="fv-card-label">Online Sekarang</div>
                <div style="display:flex;gap:-4px;margin-top:6px">
                  <div style="width:28px;height:28px;border-radius:50%;background:var(--accent);border:2px solid var(--surface);display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:700;color:#fff">A</div>
                  <div style="width:28px;height:28px;border-radius:50%;background:var(--accent-mid);border:2px solid var(--surface);display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:700;color:#fff;margin-left:-6px">K</div>
                  <div style="width:28px;height:28px;border-radius:50%;background:#5A6B66;border:2px solid var(--surface);display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:700;color:#fff;margin-left:-6px">M</div>
                  <div style="width:28px;height:28px;border-radius:50%;background:var(--bg-alt);border:2px solid var(--surface);display:flex;align-items:center;justify-content:center;font-size:9px;color:var(--text-2);margin-left:-6px">+5</div>
                </div>
              </div>
              <div class="fv-card">
                <div class="fv-card-label">Tugas Terbuka</div>
                <div class="fv-card-val">37</div>
              </div>
            </div>
            <div class="fv-card">
              <div class="fv-card-label">Komentar Terbaru</div>
              <div class="fv-list" style="margin-top:8px">
                <div class="fv-list-item"><span class="fv-list-name">Alex → Grafik Q3 diperbarui</span><span class="fv-pill green">Selesai</span></div>
                <div class="fv-list-item"><span class="fv-list-name">Kim → Perlu review</span><span class="fv-pill blue">Terbuka</span></div>
                <div class="fv-list-item"><span class="fv-list-name">Maya → Disetujui & dikirim</span><span class="fv-pill green">Selesai</span></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ── MOBILE CAROUSEL / GALLERY ── -->
  <section class="carousel-section" id="screens">
    <div class="container">
      <div class="carousel-header">
        <div class="section-label reveal">Galeri Kegiatan</div>
        <h2 class="section-title reveal reveal-delay-1">Momen berharga<br><em>terdokumentasi</em></h2>
        <p class="section-sub reveal reveal-delay-2">Dokumentasi kegiatan dan momen penting di lingkungan RT/RW kami.</p>
      </div>
    </div>
    <div class="carousel-zoom">
      <button class="zoom-btn" id="zoomOut" aria-label="Zoom out">−</button>
      <div class="zoom-pips" id="zoomPips"></div>
      <button class="zoom-btn" id="zoomIn" aria-label="Zoom in">+</button>
    </div>
    <div class="carousel-stage" id="carouselStage">
      <div class="carousel-track" id="carouselTrack">
        <?php if (!empty($gallery_items)): ?>
          <?php foreach ($gallery_items as $idx => $item): ?>
            <div class="phone-card" data-pos="<?php echo $idx === 0 ? 'left2' : ($idx === 1 ? 'left1' : ($idx === 2 ? 'center' : ($idx === 3 ? 'right1' : 'right2'))); ?>" data-index="<?php echo $idx; ?>">
              <div class="phone-shell">
                <div class="phone-screen">
                  <img src="beranda/gallery/<?php echo htmlspecialchars($item['image_path']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>" class="gallery-section-img" loading="lazy" />
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <!-- Placeholder screens -->
          <div class="phone-card" data-pos="left2" data-index="0">
            <div class="phone-shell">
              <div class="phone-screen">
                <img src="beranda/images/tm-622-screen-01.jpg" alt="Placeholder 1" class="gallery-section-img" loading="lazy" />
              </div>
            </div>
          </div>
          <div class="phone-card" data-pos="left1" data-index="1">
            <div class="phone-shell">
              <div class="phone-screen">
                <img src="beranda/images/tm-622-screen-02.jpg" alt="Placeholder 2" class="gallery-section-img" loading="lazy" />
              </div>
            </div>
          </div>
          <div class="phone-card" data-pos="center" data-index="2">
            <div class="phone-shell">
              <div class="phone-screen">
                <img src="beranda/images/tm-622-screen-03.jpg" alt="Placeholder 3" class="gallery-section-img" loading="lazy" />
              </div>
            </div>
          </div>
          <div class="phone-card" data-pos="right1" data-index="3">
            <div class="phone-shell">
              <div class="phone-screen">
                <img src="beranda/images/tm-622-screen-04.jpg" alt="Placeholder 4" class="gallery-section-img" loading="lazy" />
              </div>
            </div>
          </div>
          <div class="phone-card" data-pos="right2" data-index="4">
            <div class="phone-shell">
              <div class="phone-screen">
                <img src="beranda/images/tm-622-screen-05.jpg" alt="Placeholder 5" class="gallery-section-img" loading="lazy" />
              </div>
            </div>
          </div>
        <?php endif; ?>
      </div>
    </div>
    <div style="display:flex;align-items:center;justify-content:center;gap:24px;width:100%;margin-top:48px;">
      <button class="carousel-btn" id="carouselPrev" aria-label="Previous screen">
        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
      </button>
      <div class="carousel-dots" id="carouselDots"></div>
      <button class="carousel-btn" id="carouselNext" aria-label="Next screen">
        <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
      </button>
    </div>
  </section>

  <!-- ── STATS ── -->
  <section class="stats-section">
    <div class="container">
      <div class="stats-grid">
        <div class="stat-card reveal">
          <div class="stat-rule"></div>
          <div class="stat-value"><span class="stat-num" data-target="<?php echo $total_warga; ?>">0</span><span class="stat-suffix">+</span></div>
          <div class="stat-label">Total Warga</div>
          <div class="stat-sublabel">Terdata di seluruh RT/RW</div>
        </div>
        <div class="stat-card reveal reveal-delay-1">
          <div class="stat-rule"></div>
          <div class="stat-value"><span class="stat-num" data-target="<?php echo $rt_aktif; ?>">0</span><span class="stat-suffix"></span></div>
          <div class="stat-label">RT Aktif</div>
          <div class="stat-sublabel">Menggunakan platform</div>
        </div>
        <div class="stat-card reveal reveal-delay-2">
          <div class="stat-rule"></div>
          <div class="stat-value"><span class="stat-num" data-target="<?php echo $total_users; ?>">0</span><span class="stat-suffix">+</span></div>
          <div class="stat-label">Pengguna Terdaftar</div>
          <div class="stat-sublabel">Admin, ketua, dan warga</div>
        </div>
        <div class="stat-card reveal reveal-delay-3">
          <div class="stat-rule"></div>
          <div class="stat-value"><span class="stat-num" data-target="<?php echo $kegiatan; ?>">0</span><span class="stat-suffix">+</span></div>
          <div class="stat-label">Kegiatan Tercatat</div>
          <div class="stat-sublabel">Dari berbagai RT/RW</div>
        </div>
      </div>
    </div>
  </section>

  <!-- ── PRICING ── -->
  <section class="pricing-section" id="pricing">
    <div class="container">
      <div class="pricing-header">
        <div class="section-label reveal">Paket</div>
        <h2 class="section-title reveal reveal-delay-1">Harga <em>transparan</em></h2>
        <p class="section-sub reveal reveal-delay-2">Tanpa biaya tersembunyi. Tanpa kejutan. Batal kapan saja.</p>
      </div>
      <div class="pricing-toggle reveal">
        <span class="toggle-label active" id="monthlyLabel">Bulanan</span>
        <div class="toggle-switch" id="pricingToggle" role="switch" aria-checked="false" tabindex="0" aria-label="Toggle annual billing"></div>
        <span class="toggle-label" id="annualLabel">Tahunan</span>
        <div class="toggle-badge">Hemat 35%</div>
      </div>
      <div class="pricing-grid">
        <div class="pricing-card reveal">
          <div class="pricing-tier">Starter</div>
          <div class="pricing-price">
            <span class="price-currency">Rp</span>
            <span class="price-amount" id="price-starter">50<span style="font-size:1rem">k</span></span>
            <span class="price-period">&nbsp;/ bln</span>
          </div>
          <div class="price-annual-note" id="annual-note-starter">&nbsp;</div>
          <p class="pricing-desc">Untuk RT/RW kecil yang baru memulai digitalisasi.</p>
          <div class="pricing-divider"></div>
          <div class="pricing-features">
            <div class="pricing-feature"><div class="pricing-check">✓</div><span>Hingga 100 warga</span></div>
            <div class="pricing-feature"><div class="pricing-check">✓</div><span>Dashboard dasar</span></div>
            <div class="pricing-feature"><div class="pricing-check">✓</div><span>Pengumuman & notifikasi</span></div>
            <div class="pricing-feature"><div class="pricing-check">✓</div><span>Data warga dasar</span></div>
            <div class="pricing-feature"><div class="pricing-check">✓</div><span>Support email</span></div>
          </div>
          <a href="register" class="pricing-cta">Mulai Gratis</a>
        </div>
        <div class="pricing-card featured reveal reveal-delay-1">
          <div class="pricing-badge">Paling Populer</div>
          <div class="pricing-tier">Professional</div>
          <div class="pricing-price">
            <span class="price-currency">Rp</span>
            <span class="price-amount" id="price-pro">150<span style="font-size:1rem">k</span></span>
            <span class="price-period">&nbsp;/ bln</span>
          </div>
          <div class="price-annual-note" id="annual-note-pro">&nbsp;</div>
          <p class="pricing-desc">Untuk RW yang membutuhkan fitur lengkap dan otomasi.</p>
          <div class="pricing-divider"></div>
          <div class="pricing-features">
            <div class="pricing-feature"><div class="pricing-check">✓</div><span>Hingga 500 warga</span></div>
            <div class="pricing-feature"><div class="pricing-check">✓</div><span>Dashboard tanpa batas</span></div>
            <div class="pricing-feature"><div class="pricing-check">✓</div><span>Otomasi lanjutan</span></div>
            <div class="pricing-feature"><div class="pricing-check">✓</div><span>Manajemen iuran</span></div>
            <div class="pricing-feature"><div class="pricing-check">✓</div><span>Priority chat support</span></div>
            <div class="pricing-feature"><div class="pricing-check">✓</div><span>Akses aplikasi mobile</span></div>
          </div>
          <a href="register" class="pricing-cta">Mulai Gratis</a>
        </div>
        <div class="pricing-card reveal reveal-delay-2">
          <div class="pricing-tier">Enterprise</div>
          <div class="pricing-price">
            <span class="price-currency">Rp</span>
            <span class="price-amount" id="price-ent">500<span style="font-size:1rem">k</span></span>
            <span class="price-period">&nbsp;/ bln</span>
          </div>
          <div class="price-annual-note" id="annual-note-ent">&nbsp;</div>
          <p class="pricing-desc">Untuk kelurahan/desa dengan kebutuhan kustom.</p>
          <div class="pricing-divider"></div>
          <div class="pricing-features">
            <div class="pricing-feature"><div class="pricing-check">✓</div><span>Warga tanpa batas</span></div>
            <div class="pricing-feature"><div class="pricing-check">✓</div><span>Integrasi kustom & API</span></div>
            <div class="pricing-feature"><div class="pricing-check">✓</div><span>SSO & izin lanjutan</span></div>
            <div class="pricing-feature"><div class="pricing-check">✓</div><span>Riwayat data tanpa batas</span></div>
            <div class="pricing-feature"><div class="pricing-check">✓</div><span>Manager sukses dedicated</span></div>
            <div class="pricing-feature"><div class="pricing-check">✓</div><span>Laporan kepatuhan</span></div>
          </div>
          <a href="contact" class="pricing-cta">Hubungi Sales</a>
        </div>
      </div>
    </div>
  </section>

  <!-- ── TESTIMONIALS (inlined from beranda/testimonials.php) ── -->
<?php
$has_testimoni = false;

$user_id = $_SESSION['user_id'] ?? null;
if ($user_id) {
    $stmt = mysqli_prepare($conn, 'SELECT COUNT(*) as count FROM testimonials WHERE user_id = ?');
    mysqli_stmt_bind_param($stmt, 'i', $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    $has_testimoni = ($row['count'] ?? 0) > 0;
    mysqli_stmt_close($stmt);
}
?>

<section class="testimonials-section" id="testimonials">
    <div class="max-w-7xl mx-auto px-6">
        <div class="testimonials-header text-center mb-12">
            <div class="testimonials-label inline-flex items-center gap-2 text-xs font-semibold tracking-widest uppercase text-[var(--accent)] bg-[var(--accent-ghost)] border border-[var(--accent-border)] px-4 py-2 rounded-full">
                Rating & Testimoni
            </div>
            <h2 class="mt-4 text-3xl md:text-4xl font-bold tracking-tight text-[var(--text-1)]">
                Rating & Testimoni Pengguna
            </h2>
            <p class="testimonials-sub mt-4 text-[var(--text-2)] max-w-xl mx-auto leading-relaxed">
                Dengar pengalaman warga—langsung dari RT/RW yang sudah pakai Lurahgo.id.
            </p>
        </div>

        <div id="testimonials-grid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
<?php
$stmt = mysqli_prepare($conn, 'SELECT * FROM testimonials WHERE name IS NOT NULL AND name != "" AND description IS NOT NULL AND description != "" ORDER BY created_at DESC LIMIT 6');
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

while ($row = mysqli_fetch_assoc($result)) {
    $nama = $row['name'] ?? 'Anonymous';
    $pesan = $row['description'] ?? 'No description provided.';
    $rating = (int)($row['rating'] ?? 0);

    $avatar = 'https://randomuser.me/api/portraits/' . ($rating % 2 ? 'men' : 'women') . '/' . rand(1, 99) . '.jpg';

    // Optional: avatar berdasarkan username (kalau nama disimpan sama seperti username)
    $safe_name = mysqli_real_escape_string($conn, $nama);
    $user_result = mysqli_query($conn, "SELECT profile_photo FROM users WHERE username = '" . $safe_name . "' LIMIT 1");
    if ($user_row = mysqli_fetch_assoc($user_result)) {
        $user_photo = $user_row['profile_photo'] ?? '';
        if (!empty($user_photo)) {
            $localAvatar = '../account/uploads/profiles/' . $user_photo;
            if (file_exists($localAvatar)) {
                $avatar = $localAvatar;
            } else {
                $avatar = $user_photo;
            }
        }
    }

    $rating_stars = '';
    for ($i = 1; $i <= 5; $i++) {
        $rating_stars .= ($i <= $rating)
            ? '<i class="fas fa-star"></i>'
            : '<i class="far fa-star"></i>';
    }

    $date = date('d M Y', strtotime($row['created_at'] ?? time()));

    echo '<div class="testimonials-card bg-white/80 backdrop-blur border border-[var(--accent-border)] rounded-2xl p-6 shadow-[0_10px_40px_rgba(0,0,0,0.06)] hover:shadow-[0_18px_60px_rgba(0,0,0,0.10)] transition-all duration-300">
            <div class="testimonials-author flex items-center gap-4">
                <img class="testimonials-avatar w-12 h-12 rounded-full border-2 border-[var(--accent-border)] object-cover" src="' . htmlspecialchars($avatar) . '" alt="' . htmlspecialchars($nama) . '">
                <div class="testimonials-info">
                    <h4 class="text-[var(--text-1)] font-bold text-lg leading-snug">' . htmlspecialchars($nama) . '</h4>
                    <p class="text-[var(--text-3)] text-sm mt-1">' . $date . '</p>
                </div>
            </div>
            <p class="testimonials-quote mt-4 text-[var(--text-2)] leading-relaxed italic">" ' . htmlspecialchars($pesan) . ' "</p>
            <div class="testimonials-stars mt-4 flex gap-1 text-yellow-400">' . $rating_stars . '</div>
          </div>';
}
mysqli_stmt_close($stmt);
?>
        </div>

<div class="testimonials-send mt-14">
            <div class="testimonials-send-inner">
                <div class="max-w-2xl mx-auto">
                <?php if (!$has_testimoni): ?>
                    <div class="testimonials-send-header">
                        <h3>Kirim Testimoni Anda</h3>
                        <p>Bantu kami improve layanan dengan rating dan feedback Anda!</p>
                    </div>

                    <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['testimonial_submit'])) {
                        $nama = trim($_POST['name'] ?? ($_SESSION['username'] ?? 'Anonymous'));
                        $rating = intval($_POST['rating'] ?? 0);
                        $pesan = trim($_POST['description'] ?? '');
                        $errors = [];

                        if (empty($nama)) $errors[] = 'Nama wajib diisi.';
                        if ($rating < 1 || $rating > 5) $errors[] = 'Rating harus 1-5.';
                        if (empty($pesan)) $errors[] = 'Testimoni wajib diisi.';
                        if (strlen($pesan) < 10) $errors[] = 'Testimoni minimal 10 karakter.';

                        if (empty($errors)) {
$stmt = mysqli_prepare($conn, 'INSERT INTO testimonials (name, rating, description, user_id, created_at) VALUES (?, ?, ?, ?, NOW())');
                            mysqli_stmt_bind_param($stmt, 'sisi', $nama, $rating, $pesan, $user_id);

                            if (mysqli_stmt_execute($stmt)) {
                                $has_testimoni = true;
                                echo '<div class="testimonial-message success"><i class="fas fa-check-circle mr-3"></i> Terima kasih atas testimoni Anda!</div>';
                            } else {
                                echo '<div class="testimonial-message error"><i class="fas fa-exclamation-triangle mr-3"></i> Gagal: ' . htmlspecialchars(mysqli_error($conn)) . '</div>';
                            }

                            mysqli_stmt_close($stmt);
                        } else {
                            echo '<div class="testimonial-message error"><i class="fas fa-exclamation-triangle mr-3"></i> ' . implode('<br>', $errors) . '</div>';
                        }
                    } ?>

                    <form method="POST" id="testimonialForm">
                        <input type="hidden" name="testimonial_submit" value="1">

                        <div class="form-row">
                            <div>
                                <label>Nama</label>
                                <input type="text" name="name" value="<?php echo htmlspecialchars($_SESSION['username'] ?? ''); ?>" readonly>
                            </div>

                            <div>
                                <label>Rating</label>
                                <div class="rating-stars">
                                    <i class="far fa-star star" onclick="setRating(1)"></i>
                                    <i class="far fa-star star" onclick="setRating(2)"></i>
                                    <i class="far fa-star star" onclick="setRating(3)"></i>
                                    <i class="far fa-star star" onclick="setRating(4)"></i>
                                    <i class="far fa-star star" onclick="setRating(5)"></i>
                                </div>
                                <input type="hidden" id="rating" name="rating" value="0" required>
                            </div>
                        </div>

                        <div>
                            <label>Testimoni Anda</label>
                            <textarea name="description" rows="4" required placeholder="Ceritakan pengalaman Anda..."></textarea>
                        </div>

                        <button type="submit">Kirim Testimoni</button>
                    </form>

                <?php else: ?>
                    <div class="text-center" style="padding: 36px 10px;">
                        <i class="fas fa-star" style="color:#22c55e;font-size:3rem;display:block;margin-bottom:18px;"></i>
                        <h3 style="font-size:1.6rem;font-weight:900;color:#16a34a;margin-bottom:10px;">Terima kasih!</h3>
                        <p style="color:#64748b;max-width:520px;margin:0 auto;">Testimoni Anda tercatat dan akan ditampilkan setelah diverifikasi.</p>
                    </div>
                <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
function setRating(stars) {
    document.getElementById('rating').value = stars;
    const starsIcons = document.querySelectorAll('.star');
    starsIcons.forEach((icon, index) => {
        if (index < stars) {
            icon.classList.add('fas');
            icon.classList.remove('far');
            icon.classList.add('text-yellow-400');
        } else {
            icon.classList.add('far');
            icon.classList.remove('fas');
            icon.classList.remove('text-yellow-400');
        }
    });
}
</script>



  <!-- ── INTEGRATIONS ── -->
  <section class="integrations-section" id="integrations">
      <div class="container">
      <div class="integrations-header">
        <div class="section-label reveal">Integrasi</div>
        <h2 class="section-title reveal reveal-delay-1">Terhubung untuk<br><em>RT/RW Anda</em></h2>
        <p class="section-sub reveal reveal-delay-2">Integrasi satu klik untuk urusan RT/RW: pengumuman, notifikasi, rekap, dan dokumentasi. Tanpa coding.</p>
      </div>
      <div class="integrations-grid">
        <div class="integration-tile reveal"><div class="integration-name">WhatsApp</div></div>
        <div class="integration-tile reveal reveal-delay-1"><div class="integration-name">Google Sheets</div></div>
        <div class="integration-tile reveal reveal-delay-2"><div class="integration-name">Google Drive</div></div>
        <div class="integration-tile reveal reveal-delay-3"><div class="integration-name">Zapier</div></div>
        <div class="integration-tile reveal"><div class="integration-name">Telegram</div></div>
        <div class="integration-tile reveal reveal-delay-1"><div class="integration-name">Midtrans</div></div>
        <div class="integration-tile reveal reveal-delay-2"><div class="integration-name">Notion</div></div>
        <div class="integration-tile reveal reveal-delay-3"><div class="integration-name">Mailchimp</div></div>
        <div class="integration-tile reveal"><div class="integration-name">Dukcapil API</div></div>
        <div class="integration-tile reveal reveal-delay-1"><div class="integration-name">Airtable</div></div>
        <div class="integration-tile reveal reveal-delay-2"><div class="integration-name">Slack</div></div>
        <div class="integration-tile reveal reveal-delay-3"><div class="integration-name">Custom API</div></div>
      </div>
    </div>
  </section>

  <!-- ── FAQ ── -->
  <section class="faq-section" id="faq">
    <div class="container">
      <div class="faq-inner">
        <div class="faq-sidebar reveal">
          <div class="section-label">FAQ</div>
          <h2 class="section-title">Pertanyaan,<br><em>dijawab</em></h2>
          <p class="section-sub">Tidak menemukan yang Anda cari? Hubungi tim kami di admin@lurahgo.id — kami balas dalam 2 jam.</p>
          <button class="faq-toggle-all" id="faqToggleAll">
            <span id="faqToggleIcon">+</span> Buka semua</button>
        </div>
        <div class="faq-list reveal reveal-delay-1" id="faqList">
          <div class="faq-item">
            <div class="faq-question" tabindex="0" role="button" aria-expanded="false">
              Apakah ada uji coba gratis?
              <div class="faq-icon">+</div>
            </div>
            <div class="faq-answer"><div class="faq-answer-inner">Ya — setiap paket dimulai dengan uji coba 14 hari gratis, tanpa perlu kartu kredit. Anda mendapat akses penuh ke semua fitur di tier yang dipilih sehingga bisa melakukan evaluasi nyata sebelum berkomitmen.</div></div>
          </div>
          <div class="faq-item">
            <div class="faq-question" tabindex="0" role="button" aria-expanded="false">
              Bagaimana harga untuk RT/RW yang lebih besar?
              <div class="faq-icon">+</div>
            </div>
            <div class="faq-answer"><div class="faq-answer-inner">Paket Starter dan Professional berdasarkan workspace, bukan per-seat — jadi tidak ada tagihan mengejutkan saat tim Anda bertambah. Paket Enterprise dikutip secara custom berdasarkan kebutuhan spesifik dan panjang kontrak Anda.</div></div>
          </div>
          <div class="faq-item">
            <div class="faq-question" tabindex="0" role="button" aria-expanded="false">
              Bisa migrasi data dari sistem lain?
              <div class="faq-icon">+</div>
            </div>
            <div class="faq-answer"><div class="faq-answer-inner">Kami mendukung impor CSV dan migrasi langsung dari Excel, Google Sheets, dan sistem pencatatan manual. Pelanggan Enterprise mendapat specialist migrasi dedicated yang menangani seluruh proses untuk Anda.</div></div>
          </div>
          <div class="faq-item">
            <div class="faq-question" tabindex="0" role="button" aria-expanded="false">
              Apakah data saya aman?
              <div class="faq-icon">+</div>
            </div>
            <div class="faq-answer"><div class="faq-answer-inner">Lurahgo.id menggunakan enkripsi end-to-end, backup otomatis, dan mengikuti standar keamanan data Indonesia. Semua data dienkripsi saat transit dan saat disimpan. Anda bisa meminta laporan keamanan lengkap dari tim kami kapan saja.</div></div>
          </div>
          <div class="faq-item">
            <div class="faq-question" tabindex="0" role="button" aria-expanded="false">
              Bisa batal kapan saja?
              <div class="faq-icon">+</div>
            </div>
            <div class="faq-answer"><div class="faq-answer-inner">Ya. Tidak ada kontrak penguncian pada paket bulanan. Batalkan dari pengaturan akun Anda kapan saja dan Anda tidak akan ditagih lagi. Paket tahunan tidak bisa di-refund tapi bisa dibatalkan untuk menghentikan perpanjangan.</div></div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- ── CTA BANNER ── -->
  <section class="cta-section">
    <div class="container">
      <div class="cta-inner reveal">
        <div class="cta-content">
          <div class="cta-label">✦ Mulai Hari Ini</div>
          <h2 class="cta-title">Siap untuk<br><em>RT/RW yang lebih jelas?</em></h2>
          <p class="cta-sub">Bergabung dengan 500+ RT/RW yang mengganti kerumitan dengan kejelasan. Setup dalam 10 menit.</p>
        </div>
        <div class="cta-actions">
          <a href="register" class="btn-cta-primary">
            Mulai Uji Coba Gratis
            <span>→</span>
          </a>
          <a href="contact" class="btn-cta-ghost">Jadwalkan demo</a>
        </div>
      </div>
    </div>
  </section>

  <!-- ── FOOTER ── -->
  <footer class="footer">
    <div class="container">
      <div class="footer-grid">
        <div class="footer-brand">
          <a href="/" class="nav-logo">Lurahgo<span style="color:var(--accent-light)">.id</span></a>
          <p class="footer-brand-desc">Platform digital RT/RW yang tenang dan powerful untuk tim yang ingin fokus pada pekerjaan — bukan mengelolanya.</p>
          <div class="footer-socials">
            <a href="#" class="social-btn" aria-label="Twitter / X">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.744l7.73-8.835L1.254 2.25H8.08l4.253 5.622zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
            </a>
            <a href="#" class="social-btn" aria-label="Instagram">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 100 12.324 6.162 6.162 0 000-12.324zM12 16a4 4 0 110-8 4 4 0 010 8zm6.406-11.845a1.44 1.44 0 100 2.881 1.44 1.44 0 000-2.881z"/></svg>
            </a>
            <a href="#" class="social-btn" aria-label="YouTube">
              <svg width="15" height="15" viewBox="0 0 24 24" fill="currentColor"><path d="M23.498 6.186a3.016 3.016 0 00-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 00.502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 002.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 002.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
            </a>
          </div>
        </div>
        <div>
          <div class="footer-col-label">Produk</div>
          <div class="footer-links">
            <a href="#features">Fitur</a>
            <a href="#screens">Galeri</a>
            <a href="#pricing">Paket</a>
            <a href="#integrations">Integrasi</a>
          </div>
        </div>
        <div>
          <div class="footer-col-label">Perusahaan</div>
          <div class="footer-links">
            <a href="#">Tentang</a>
            <a href="#">Blog</a>
            <a href="#">Karir</a>
            <a href="#">Status</a>
          </div>
        </div>
        <div>
          <div class="footer-col-label">Dukungan</div>
          <div class="footer-links">
            <a href="#">Pusat Bantuan</a>
            <a href="#">Dokumentasi</a>
            <a href="#">Keamanan</a>
            <a href="#">Kontak</a>
          </div>
        </div>
      </div>
      <div class="footer-bottom">
        <div class="footer-copy">
          © <span id="year"></span> Lurahgo.id — Dibuat Oleh Ramardo
        </div>
        <div class="footer-legal">
          <a href="#">Kebijakan Privasi</a>
          <a href="#">Syarat Layanan</a>
        </div>
      </div>
    </div>
  </footer>

  <script src="beranda/templatemo-622-clearwave.js"></script>
  <script>
    // Init year
    document.addEventListener('DOMContentLoaded', () => {
      const yearEl = document.getElementById('year');
      if (yearEl) yearEl.textContent = new Date().getFullYear();
    });
  </script>
</body>
</html>
