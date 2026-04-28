<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Lurahgo — Revolutionize Digital Governance | Hero Pro Max</title>
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Google Fonts: Inter + Space Grotesk (super modern) -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700;14..32,800;14..32,900&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', 'Space Grotesk', sans-serif;
            background: #010101;
            overflow-x: hidden;
        }

        .hero-banget {
            position: relative;
            min-height: 100vh;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            isolation: isolate;
            overflow: hidden;
            background: #000000;
        }

        .hero-mesh {
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at 20% 30%, #0a0f1f, #000000 90%);
        }

        .hero-mesh::before {
            content: "";
            position: absolute;
            width: 200%;
            height: 200%;
            top: -50%;
            left: -50%;
            background: radial-gradient(circle at 30% 40%, rgba(56,189,248,0.12), transparent 60%),
                        radial-gradient(circle at 70% 80%, rgba(168,85,247,0.12), transparent 70%);
            animation: slowDrift 26s infinite alternate;
        }

        @keyframes slowDrift {
            0% { transform: translate(0%, 0%) rotate(0deg); opacity: 0.7; }
            100% { transform: translate(4%, 3%) rotate(2deg); opacity: 1; }
        }

        .noise-overlay {
            position: absolute;
            inset: 0;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 512 512' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='f'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.85' numOctaves='3'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23f)' opacity='0.035'/%3E%3C/svg%3E");
            pointer-events: none;
            opacity: 0.4;
            mix-blend-mode: overlay;
        }

        .orb-ultra {
            position: absolute;
            border-radius: 50%;
            filter: blur(100px);
            pointer-events: none;
            animation: floatFantastic 15s infinite alternate ease-in-out;
        }

        .orb-pink {
            width: 55vmax;
            height: 55vmax;
            background: radial-gradient(circle, rgba(236,72,153,0.25), rgba(0,0,0,0));
            top: -10%;
            right: -15%;
        }

        .orb-cyan {
            width: 60vmax;
            height: 60vmax;
            background: radial-gradient(circle, rgba(34,211,238,0.2), rgba(2,132,199,0));
            bottom: -20%;
            left: -10%;
        }

        .orb-purple-mid {
            width: 45vmax;
            height: 45vmax;
            background: radial-gradient(circle, rgba(139,92,246,0.2), rgba(88,28,135,0));
            top: 45%;
            left: 30%;
            filter: blur(110px);
        }

        @keyframes floatFantastic {
            0% { transform: translate(0, 0) scale(1); }
            100% { transform: translate(5%, 7%) scale(1.1); }
        }

        .container-mantap {
            position: relative;
            z-index: 30;
            max-width: 1320px;
            margin: 0 auto;
            padding: 3rem 2rem 5rem;
            width: 100%;
        }

        .badge-elite {
            display: inline-flex;
            align-items: center;
            gap: 0.8rem;
            background: rgba(10, 20, 40, 0.55);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(56, 189, 248, 0.5);
            border-radius: 100px;
            padding: 0.55rem 1.6rem 0.55rem 1.3rem;
            font-size: 0.85rem;
            font-weight: 700;
            letter-spacing: 0.3px;
            color: #E2E8F0;
            transition: all 0.35s;
            margin-bottom: 2rem;
            box-shadow: 0 0 12px rgba(56,189,248,0.2);
        }

        .badge-elite i {
            font-size: 0.9rem;
            color: #FBBF24;
            filter: drop-shadow(0 0 4px gold);
        }

        .badge-elite:hover {
            border-color: #2DD4BF;
            background: rgba(45,212,191,0.15);
            transform: translateY(-2px);
            box-shadow: 0 0 20px rgba(45,212,191,0.3);
        }

        .headline-mega {
            font-size: clamp(3.5rem, 10vw, 6rem);
            font-weight: 900;
            line-height: 1.08;
            letter-spacing: -0.03em;
            color: white;
            margin-bottom: 1.5rem;
            text-shadow: 0 4px 20px rgba(0,0,0,0.5);
        }

        .gradient-king {
            background: linear-gradient(125deg, #FFFFFF, #A5F3FC, #C084FC, #FBBF24);
            background-clip: text;
            -webkit-background-clip: text;
            color: transparent;
            background-size: 250% auto;
            animation: gradientFlow 5s infinite alternate;
        }

        @keyframes gradientFlow {
            0% { background-position: 0% 30%;}
            100% { background-position: 100% 70%;}
        }

        .desc-killer {
            font-size: clamp(1rem, 2.2vw, 1.35rem);
            line-height: 1.55;
            color: #B9C7D9;
            max-width: 650px;
            margin: 0 auto 2.5rem auto;
            font-weight: 450;
            backdrop-filter: blur(3px);
        }

        .cta-super {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: center;
            gap: 1.5rem;
            margin-bottom: 3rem;
        }

        .btn-ultimate {
            background: linear-gradient(95deg, #FFFFFF, #EFF6FF);
            color: #0B1120;
            font-weight: 800;
            padding: 1rem 2.4rem;
            border-radius: 3rem;
            font-size: 1rem;
            transition: all 0.3s cubic-bezier(0.2, 1.2, 0.4, 1);
            display: inline-flex;
            align-items: center;
            gap: 0.8rem;
            border: none;
            cursor: pointer;
            box-shadow: 0 18px 30px -12px rgba(0, 0, 0, 0.5);
        }

        .btn-ultimate i {
            transition: transform 0.25s;
        }

        .btn-ultimate:hover {
            transform: translateY(-5px) scale(1.02);
            background: white;
            box-shadow: 0 28px 40px -14px rgba(0, 0, 0, 0.6);
        }

        .btn-ultimate:hover i {
            transform: translateX(7px);
        }

        .btn-glass-premium {
            background: rgba(20, 30, 55, 0.7);
            backdrop-filter: blur(16px);
            border: 1.5px solid rgba(45, 212, 191, 0.6);
            padding: 1rem 2.2rem;
            border-radius: 3rem;
            font-weight: 700;
            font-size: 1rem;
            color: #E0F2FE;
            display: inline-flex;
            align-items: center;
            gap: 0.7rem;
            transition: all 0.3s;
            letter-spacing: -0.2px;
        }

        .btn-glass-premium:hover {
            background: rgba(45, 212, 191, 0.2);
            border-color: #2DD4BF;
            transform: translateY(-4px);
            box-shadow: 0 12px 28px -8px rgba(45,212,191,0.4);
            color: white;
        }

        .stats-hologram {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 2rem;
            margin-bottom: 3.5rem;
        }

        .stat-card-glow {
            background: rgba(5, 12, 25, 0.65);
            backdrop-filter: blur(12px);
            border-radius: 2rem;
            padding: 0.8rem 1.8rem;
            min-width: 130px;
            text-align: center;
            border: 1px solid rgba(56, 189, 248, 0.25);
            transition: all 0.3s;
            box-shadow: 0 8px 20px -10px rgba(0,0,0,0.4);
        }

        .stat-card-glow:hover {
            border-color: #38BDF8;
            background: rgba(20, 40, 65, 0.8);
            transform: translateY(-5px);
        }

        .stat-number-mega {
            font-size: 2.2rem;
            font-weight: 800;
            background: linear-gradient(145deg, #FEF08A, #2DD4BF);
            background-clip: text;
            -webkit-background-clip: text;
            color: transparent;
            line-height: 1.2;
        }

        .stat-label-cool {
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #8CABD6;
            margin-top: 6px;
        }

        /* FEATURE CARDS - PERFECTLY ALIGNED & BALANCED */
        .feature-showcase {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            align-items: stretch;
            gap: 1.8rem;
            margin-top: 0.5rem;
        }

        .card-3d {
            flex: 1;
            min-width: 260px;
            max-width: 320px;
            background: rgba(8, 16, 30, 0.7);
            backdrop-filter: blur(16px);
            border-radius: 2rem;
            padding: 2rem 1.6rem;
            border: 1px solid rgba(255,255,255,0.08);
            position: relative;
            transition: all 0.4s cubic-bezier(0.2, 0.9, 0.4, 1.1);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .card-3d::before {
            content: "";
            position: absolute;
            inset: 0;
            border-radius: 2rem;
            padding: 1px;
            background: linear-gradient(125deg, rgba(56,189,248,0.5), rgba(168,85,247,0.5), rgba(251,191,36,0.4));
            -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
            opacity: 0;
            transition: opacity 0.4s;
        }

        .card-3d:hover::before {
            opacity: 0.9;
        }

        .card-3d:hover {
            transform: translateY(-8px);
            background: rgba(20, 35, 55, 0.88);
            border-color: transparent;
        }

        .icon-3d {
            width: 58px;
            height: 58px;
            background: linear-gradient(135deg, #1E293B, #0F172A);
            border-radius: 1.3rem;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.4rem;
            border: 0.5px solid rgba(56,189,248,0.5);
            box-shadow: 0 10px 20px -8px rgba(0,0,0,0.4);
            transition: transform 0.3s;
        }

        .card-3d:hover .icon-3d {
            transform: scale(1.05);
        }

        .icon-3d i {
            font-size: 1.8rem;
            color: #2DD4BF;
        }

        .card-title-x {
            font-weight: 800;
            font-size: 1.25rem;
            color: #F8FAFC;
            margin-bottom: 0.7rem;
            letter-spacing: -0.2px;
        }

        .card-desc-premium {
            font-size: 0.85rem;
            color: #B3C5E0;
            line-height: 1.55;
            flex: 1;
        }

        .fade-up-mantap {
            opacity: 0;
            transform: translateY(35px);
            transition: opacity 0.9s cubic-bezier(0.2, 0.9, 0.3, 1), transform 0.8s ease;
        }

        .fade-up-mantap.visible {
            opacity: 1;
            transform: translateY(0);
        }

        @media (max-width: 768px) {
            .container-mantap {
                padding: 2rem 1.3rem 3rem;
            }
            .card-3d {
                min-width: 100%;
                max-width: 100%;
            }
            .stat-card-glow {
                min-width: 100px;
                padding: 0.6rem 1rem;
            }
            .stat-number-mega {
                font-size: 1.6rem;
            }
            .btn-ultimate, .btn-glass-premium {
                padding: 0.8rem 1.6rem;
                font-size: 0.9rem;
            }
            .feature-showcase {
                gap: 1.2rem;
            }
        }

        /* tablet landscape */
        @media (min-width: 769px) and (max-width: 1024px) {
            .card-3d {
                min-width: 280px;
            }
            .feature-showcase {
                gap: 1.2rem;
            }
        }
    </style>
</head>
<body>
    <section class="hero-banget">
        <div class="hero-mesh"></div>
        <div class="noise-overlay"></div>
        <div class="orb-ultra orb-pink"></div>
        <div class="orb-ultra orb-cyan"></div>
        <div class="orb-ultra orb-purple-mid"></div>
        
        <div class="container-mantap">
            <!-- badge raja digital -->
            <div class="fade-up-mantap" style="text-align: center;">
                <div class="badge-elite">
                    <span>⚡ Lurahgo Semakin Canggih!</span>
                </div>
            </div>

            <!-- headline power -->
            <div class="fade-up-mantap" style="text-align: center; transition-delay: 0.05s;">
                <h1 class="headline-mega">
                    Lurahgo.id<br>
                    <span class="gradient-king"> #WebsiteRajaCanggih </span>
                </h1>
            </div>

            <!-- deskripsi -->
            <div class="fade-up-mantap" style="text-align: center; transition-delay: 0.1s;">
                <p class="desc-killer">
                    Wujudkan RT/RW impian dengan platform all-in-one: data warga, administrasi KK, 
                    mutasi real-time & dashboard interaktif. Satu sistem, ribuan kemudahan.
                </p>
            </div>

            <!-- tombol keren maksimal -->
            <div class="fade-up-mantap" style="transition-delay: 0.2s;">
                <div class="cta-super">
                    <a href="#services" class="btn-ultimate">
                        <i class="fas fa-chess-queen"></i> Jelajahi Fitur <i class="fas fa-arrow-right"></i>
                    </a>
                    <a href="#about" class="btn-glass-premium">
                        <i class="fas fa-vr-cardboard"></i> Kenali Kami
                    </a>
                </div>
            </div>

            <!-- stats hologram -->
            <div class="fade-up-mantap" style="transition-delay: 0.3s;">
                <div class="stats-hologram">
                    <div class="stat-card-glow">
                        <div class="stat-number-mega">1.2K+</div>
                        <div class="stat-label-cool">Wilayah Terhubung</div>
                    </div>
                    <div class="stat-card-glow">
                        <div class="stat-number-mega">99.98%</div>
                        <div class="stat-label-cool">Stabilitas Server</div>
                    </div>
                    <div class="stat-card-glow">
                        <div class="stat-number-mega">4.98</div>
                        <div class="stat-label-cool">Rating Pengguna</div>
                    </div>
                </div>
            </div>

            <!-- FITUR CARD - 3 KOLOM RAPI, SEIMBANG, PROPORSIONAL -->
            <div class="fade-up-mantap" style="transition-delay: 0.4s;">
                <div class="feature-showcase">
                    <!-- Card 1: Manajemen RT/RW Pintar -->
                    <div class="card-3d">
                        <div class="icon-3d">
                            <i class="fas fa-map-marked-alt"></i>
                        </div>
                        <div class="card-title-x">Manajemen RT/RW Pintar</div>
                        <div class="card-desc-premium">
                            Pantau & kelola data warga, KK, mutasi, hingga struktur RT/RW secara terintegrasi dalam satu genggaman.
                        </div>
                    </div>
                    
                    <!-- Card 2: Blockchain Vault -->
                    <div class="card-3d">
                        <div class="icon-3d">
                            <i class="fas fa-lock"></i>
                        </div>
                        <div class="card-title-x">Blockchain Vault</div>
                        <div class="card-desc-premium">
                            Keamanan data terenkripsi & sistem voting digital tak tertandingi. Perlindungan maksimal untuk setiap transaksi data warga.
                        </div>
                    </div>
                    
                    <!-- Card 3: Komando Pusat -->
                    <div class="card-3d">
                        <div class="icon-3d">
                            <i class="fas fa-satellite-dish"></i>
                        </div>
                        <div class="card-title-x">Komando Pusat</div>
                        <div class="card-desc-premium">
                            Integrasi lintas desa, kelurahan, hingga kecamatan dalam 1 dashboard. Kawal kebijakan dari hulu ke hilir secara real-time.
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- modern pulse line -->
            <div style="text-align: center; margin-top: 2.5rem; opacity: 0.5;">
                <div style="width: 60px; height: 2px; background: linear-gradient(90deg, transparent, #2DD4BF, transparent); margin: 0 auto;"></div>
            </div>
        </div>
        
        <!-- glow bottom element -->
        <div style="position: absolute; bottom: 0; left: 0; right: 0; height: 70px; background: linear-gradient(to top, #000000, transparent); pointer-events: none; z-index: 15;"></div>
    </section>

    <script>
        (function() {
            const itemsToReveal = document.querySelectorAll('.fade-up-mantap');
            
            const revealObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('visible');
                        revealObserver.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.15, rootMargin: "0px 0px -10px 0px" });
            
            itemsToReveal.forEach(el => revealObserver.observe(el));
            
            const forceVisible = () => {
                itemsToReveal.forEach(el => {
                    const rect = el.getBoundingClientRect();
                    if (rect.top < window.innerHeight - 40 && !el.classList.contains('visible')) {
                        el.classList.add('visible');
                        revealObserver.unobserve(el);
                    }
                });
            };
            window.addEventListener('load', forceVisible);
            setTimeout(forceVisible, 200);
            
            // effect mouse interaksi
            const heroSection = document.querySelector('.hero-banget');
            if(heroSection) {
                heroSection.addEventListener('mousemove', (e) => {
                    const orbs = document.querySelectorAll('.orb-ultra');
                    const x = (e.clientX / window.innerWidth) * 20;
                    const y = (e.clientY / window.innerHeight) * 20;
                    orbs.forEach((orb, idx) => {
                        if(idx === 0) orb.style.transform = `translate(${x * 0.03}%, ${y * 0.02}%) scale(1.05)`;
                        if(idx === 1) orb.style.transform = `translate(${-x * 0.02}%, ${-y * 0.03}%) scale(1.05)`;
                        if(idx === 2) orb.style.transform = `translate(${x * 0.01}%, ${y * 0.04}%) scale(1.03)`;
                    });
                });
            }
            
            const statNumbers = document.querySelectorAll('.stat-number-mega');
            statNumbers.forEach(stat => {
                stat.style.backgroundSize = '200% auto';
            });
        })();
    </script>
    <style>
        ::selection {
            background: #2DD4BF;
            color: black;
        }
        a {
            text-decoration: none;
        }
        body {
            background: #010101;
        }
        .card-3d, .btn-ultimate, .btn-glass-premium, .stat-card-glow, .badge-elite {
            will-change: transform;
        }
    </style>
</body>
</html>