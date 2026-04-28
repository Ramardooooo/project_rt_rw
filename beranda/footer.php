<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lurahgo Footer Premium - Clean Version</title>
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Google Fonts: Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
    <!-- Leaflet CSS for modern map -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #0a0f1a;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* HANYA FOOTER YANG TAMPAK - dummy content dihapus total */
        .footer-premium {
            position: relative;
            background: linear-gradient(165deg, #030712 0%, #0a0f1f 50%, #050a15 100%);
            border-top: 1px solid rgba(56, 189, 248, 0.15);
            overflow: hidden;
            margin-top: 0;
        }

        /* animated background glow */
        .footer-glow {
            position: absolute;
            inset: 0;
            pointer-events: none;
        }

        .footer-glow::before {
            content: "";
            position: absolute;
            top: -50%;
            left: -20%;
            width: 80%;
            height: 80%;
            background: radial-gradient(circle, rgba(56, 189, 248, 0.08), transparent 70%);
            filter: blur(60px);
            animation: floatGlow 12s infinite alternate;
        }

        .footer-glow::after {
            content: "";
            position: absolute;
            bottom: -30%;
            right: -10%;
            width: 60%;
            height: 60%;
            background: radial-gradient(circle, rgba(168, 85, 247, 0.07), transparent 70%);
            filter: blur(70px);
            animation: floatGlow2 15s infinite alternate;
        }

        @keyframes floatGlow {
            0% { transform: translate(0%, 0%) scale(1); opacity: 0.5; }
            100% { transform: translate(5%, 8%) scale(1.2); opacity: 0.8; }
        }

        @keyframes floatGlow2 {
            0% { transform: translate(0%, 0%) scale(1); opacity: 0.4; }
            100% { transform: translate(-8%, -10%) scale(1.3); opacity: 0.7; }
        }

        /* container */
        .footer-container {
            position: relative;
            z-index: 10;
            max-width: 1280px;
            margin: 0 auto;
            padding: 4rem 2rem 2rem;
        }

        /* grid utama */
        .footer-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 2.5rem;
            margin-bottom: 3rem;
        }

        @media (max-width: 1024px) {
            .footer-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 2rem;
            }
        }

        @media (max-width: 640px) {
            .footer-grid {
                grid-template-columns: 1fr;
                gap: 2rem;
            }
        }

        /* brand section - tanpa petir */
        .brand-area h3 {
            font-size: 1.85rem;
            font-weight: 800;
            background: linear-gradient(135deg, #F1F5F9, #94A3F8, #38BDF8);
            background-clip: text;
            -webkit-background-clip: text;
            color: transparent;
            margin-bottom: 0.5rem;
        }

        .brand-tag {
            font-size: 0.8rem;
            color: #64748B;
            letter-spacing: 0.5px;
            margin-bottom: 1.2rem;
        }

        .brand-desc {
            color: #9CA3AF;
            font-size: 0.9rem;
            line-height: 1.6;
            margin-bottom: 1.8rem;
            max-width: 280px;
        }

        /* social icons premium */
        .social-links {
            display: flex;
            gap: 0.8rem;
            flex-wrap: wrap;
        }

        .social-icon {
            width: 42px;
            height: 42px;
            background: rgba(30, 41, 59, 0.6);
            backdrop-filter: blur(4px);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid rgba(56, 189, 248, 0.2);
            transition: all 0.3s cubic-bezier(0.2, 0.9, 0.4, 1.1);
            color: #CBD5E1;
            font-size: 1.1rem;
        }

        .social-icon:hover {
            transform: translateY(-5px);
            background: rgba(56, 189, 248, 0.15);
            border-color: #38BDF8;
            color: white;
            box-shadow: 0 8px 20px -8px rgba(56, 189, 248, 0.3);
        }

        /* link section styling */
        .link-section h4 {
            color: #E2E8F0;
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 1.4rem;
            letter-spacing: -0.2px;
            position: relative;
            display: inline-block;
        }

        .link-section h4::after {
            content: "";
            position: absolute;
            bottom: -8px;
            left: 0;
            width: 35px;
            height: 2px;
            background: linear-gradient(90deg, #38BDF8, #2DD4BF);
            border-radius: 2px;
        }

        .link-list {
            list-style: none;
            margin-top: 0.5rem;
        }

        .link-list li {
            margin-bottom: 0.75rem;
        }

        .link-list a {
            color: #94A3B8;
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.25s;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .link-list a i {
            font-size: 0.7rem;
            color: #475569;
            transition: all 0.25s;
        }

        .link-list a:hover {
            color: #38BDF8;
            transform: translateX(5px);
        }

        .link-list a:hover i {
            color: #38BDF8;
            transform: translateX(3px);
        }

        /* newsletter section - versi minimal (diubah sesuai permintaan) */
        .newsletter-section h4 {
            color: #E2E8F0;
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 0.8rem;
            position: relative;
            display: inline-block;
        }

        .newsletter-section h4::after {
            content: "";
            position: absolute;
            bottom: -8px;
            left: 0;
            width: 35px;
            height: 2px;
            background: linear-gradient(90deg, #38BDF8, #2DD4BF);
            border-radius: 2px;
        }

        .newsletter-text {
            color: #7E8B9C;
            font-size: 0.85rem;
            margin-bottom: 1.2rem;
            line-height: 1.5;
        }

        .newsletter-form {
            display: flex;
            flex-direction: column;
            gap: 0.8rem;
        }

        .newsletter-input {
            width: 100%;
            padding: 0.9rem 1rem;
            background: rgba(15, 23, 42, 0.7);
            border: 1px solid rgba(71, 85, 105, 0.5);
            border-radius: 16px;
            color: #F1F5F9;
            font-size: 0.85rem;
            transition: all 0.3s;
            outline: none;
        }

        .newsletter-input:focus {
            border-color: #38BDF8;
            box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.1);
            background: rgba(30, 41, 59, 0.8);
        }

        .btn-subscribe {
            background: linear-gradient(95deg, #1E293B, #0F172A);
            border: 1px solid rgba(56, 189, 248, 0.4);
            padding: 0.9rem 1rem;
            border-radius: 16px;
            font-weight: 600;
            font-size: 0.85rem;
            color: #E0F2FE;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-subscribe:hover {
            background: rgba(56, 189, 248, 0.15);
            border-color: #38BDF8;
            transform: translateY(-2px);
            box-shadow: 0 6px 14px -6px rgba(56, 189, 248, 0.2);
        }

        /* maps styling */
        .maps-section h4 {
            color: #E2E8F0;
            font-size: 1.1rem;
            font-weight: 700;
            margin-bottom: 1.4rem;
            letter-spacing: -0.2px;
            position: relative;
            display: inline-block;
        }

        .maps-section h4::after {
            content: "";
            position: absolute;
            bottom: -8px;
            left: 0;
            width: 35px;
            height: 2px;
            background: linear-gradient(90deg, #38BDF8, #2DD4BF);
            border-radius: 2px;
        }

        .map-container {
            border-radius: 20px;
            overflow: hidden;
            border: 1px solid rgba(56, 189, 248, 0.3);
            box-shadow: 0 10px 20px -5px rgba(0, 0, 0, 0.3);
            margin-bottom: 1rem;
            height: 180px;
        }

        #miniMap {
            height: 100%;
            width: 100%;
            z-index: 1;
        }

        .address-detail {
            color: #94A3B8;
            font-size: 0.75rem;
            line-height: 1.5;
            margin-top: 0.5rem;
            padding: 0.5rem;
            background: rgba(15, 23, 42, 0.5);
            border-radius: 12px;
        }

        .address-detail i {
            width: 20px;
            color: #38BDF8;
        }

        .btn-google-map {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(56, 189, 248, 0.1);
            border: 1px solid rgba(56, 189, 248, 0.3);
            padding: 0.5rem 1rem;
            border-radius: 30px;
            color: #38BDF8;
            font-size: 0.75rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s;
            margin-top: 0.8rem;
        }

        .btn-google-map:hover {
            background: rgba(56, 189, 248, 0.2);
            border-color: #38BDF8;
            transform: translateY(-2px);
        }

        /* bottom bar */
        .footer-bottom {
            border-top: 1px solid rgba(51, 65, 85, 0.4);
            padding-top: 1.8rem;
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
        }

        .copyright {
            color: #5B6E8C;
            font-size: 0.75rem;
        }

        .legal-links {
            display: flex;
            gap: 1.8rem;
        }

        .legal-links a {
            color: #5B6E8C;
            text-decoration: none;
            font-size: 0.75rem;
            transition: color 0.2s;
        }

        .legal-links a:hover {
            color: #38BDF8;
        }

        /* floating WA + back to top premium */
        .whatsapp-float {
            position: fixed;
            bottom: 90px;
            right: 24px;
            width: 56px;
            height: 56px;
            background: linear-gradient(145deg, #25D366, #128C7E);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 28px;
            box-shadow: 0 10px 25px -5px rgba(37, 211, 102, 0.4);
            transition: all 0.3s ease;
            z-index: 1000;
            text-decoration: none;
            border: 1px solid rgba(255,255,255,0.2);
        }

        .whatsapp-float:hover {
            transform: scale(1.08) translateY(-5px);
            box-shadow: 0 20px 30px -8px rgba(37, 211, 102, 0.5);
        }

        .whatsapp-badge {
            position: absolute;
            top: -6px;
            right: -6px;
            background: #EF4444;
            color: white;
            font-size: 10px;
            font-weight: bold;
            padding: 2px 6px;
            border-radius: 50px;
            border: 1px solid white;
        }

        .back-to-top {
            position: fixed;
            bottom: 24px;
            right: 24px;
            width: 48px;
            height: 48px;
            background: rgba(30, 41, 59, 0.85);
            backdrop-filter: blur(8px);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #CBD5E1;
            font-size: 20px;
            cursor: pointer;
            transition: all 0.3s;
            z-index: 1000;
            border: 1px solid rgba(56, 189, 248, 0.3);
            text-decoration: none;
            opacity: 0;
            visibility: hidden;
            transform: translateY(15px);
        }

        .back-to-top.visible {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .back-to-top:hover {
            background: #38BDF8;
            color: #0F172A;
            border-color: white;
            transform: translateY(-3px);
        }

        @media (max-width: 640px) {
            .footer-container {
                padding: 3rem 1.5rem 1.5rem;
            }
            .whatsapp-float {
                width: 48px;
                height: 48px;
                font-size: 24px;
                bottom: 80px;
            }
            .back-to-top {
                width: 42px;
                height: 42px;
                bottom: 20px;
            }
            .map-container {
                height: 160px;
            }
        }
    </style>
</head>
<body>
    <!-- LANGSUNG FOOTER TANPA DUMMY CONTENT -->
    <footer class="footer-premium">
        <div class="footer-glow"></div>
        
        <div class="footer-container">
            <div class="footer-grid">
                <!-- brand area - tanpa petir/emoji -->
                <div class="brand-area">
                    <h3>Lurahgo.id</h3>
                    <div class="brand-tag">Platform Digital RT/RW</div>
                    <p class="brand-desc">
                        Sistem manajemen kependudukan modern dengan teknologi canggih. 
                        Mudah, aman, dan terpercaya untuk seluruh Indonesia.
                    </p>
                    <div class="social-links">
                        <a href="#" class="social-icon"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#" class="social-icon"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>

                <!-- quick links -->
                <div class="link-section">
                    <h4>Quick Links</h4>
                    <ul class="link-list">
                        <li><a href="#"><i class="fas fa-chevron-right"></i> Beranda</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right"></i> Dashboard User</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right"></i> Ketua RT</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right"></i> Admin Panel</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right"></i> Pricing</a></li>
                    </ul>
                </div>

                <!-- support & help -->
                <div class="link-section">
                    <h4>Bantuan</h4>
                    <ul class="link-list">
                        <li><a href="#"><i class="fas fa-chevron-right"></i> Pusat Bantuan</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right"></i> Tutorial Video</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right"></i> FAQ</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right"></i> API Dokumentasi</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right"></i> Laporan Bug</a></li>
                    </ul>
                </div>

                <!-- NEW: Maps & Location Section -->
                <div class="maps-section">
                    <h4>Lokasi Kami</h4>
                    <div class="map-container">
                        <div id="miniMap"></div>
                    </div>
                    <div class="address-detail">
                        <i class="fas fa-map-pin"></i> Jl. Flamboyan V No.5B, RT.042/RW.001,<br>
                        Sungai Miai, Kec. Banjarmasin Utara,<br>
                        Kota Banjarmasin, Kalimantan Selatan 70123
                    </div>
                    <a href="https://maps.app.goo.gl/NjMQFje6evjXnnDe7" target="_blank" rel="noopener noreferrer" class="btn-google-map">
                        <i class="fab fa-google"></i> Buka di Google Maps
                    </a>
                </div>
            </div>

            <!-- bottom bar -->
            <div class="footer-bottom">
                <div class="copyright">
                    <i class="far fa-copyright"></i> 2026 Lurahgo.id | Dibuat Oleh Ramardo. Semua hak cipta dilindungi.
                </div>
                <div class="legal-links">
                    <a href="#">Kebijakan Privasi</a>
                    <a href="#">Syarat & Ketentuan</a>
                    <a href="#">Cookie Settings</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Floating WhatsApp Button Premium -->
    <a href="https://wa.me/6288245012642?text=Halo%20Lurahgo.id%2C%20saya%20ingin%20konsultasi%20mengenai%20platform" 
       class="whatsapp-float" target="_blank" rel="noopener noreferrer">
        <i class="fab fa-whatsapp"></i>
        <span class="whatsapp-badge">99+</span>
    </a>

    <!-- Back to Top Button -->
    <button id="backToTopBtn" class="back-to-top" aria-label="Kembali ke atas">
        <i class="fas fa-arrow-up"></i>
    </button>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        (function() {
            // Back to top dengan animasi smooth & intersection observer
            const backBtn = document.getElementById('backToTopBtn');
            
            function toggleBackButton() {
                if (window.scrollY > 300) {
                    backBtn.classList.add('visible');
                } else {
                    backBtn.classList.remove('visible');
                }
            }
            
            window.addEventListener('scroll', () => {
                requestAnimationFrame(toggleBackButton);
            });
            
            toggleBackButton();
            
            if (backBtn) {
                backBtn.addEventListener('click', () => {
                    window.scrollTo({
                        top: 0,
                        behavior: 'smooth'
                    });
                });
            }
            
            // Newsletter handler dengan feedback halus
            const formNews = document.getElementById('premiumNewsForm');
            if (formNews) {
                formNews.addEventListener('submit', async (e) => {
                    e.preventDefault();
                    const emailInput = document.getElementById('newsEmail');
                    const email = emailInput.value.trim();
                    
                    if (!email || !email.includes('@')) {
                        alert('Masukkan email yang valid!');
                        return;
                    }
                    
                    const btn = formNews.querySelector('button');
                    const originalText = btn.innerHTML;
                    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
                    btn.disabled = true;
                    
                    // Simulasi pengiriman (bisa diganti dengan fetch API nyata)
                    setTimeout(() => {
                        alert(`Terima kasih! ${email} telah berlangganan newsletter Lurahgo.`);
                        emailInput.value = '';
                        btn.innerHTML = originalText;
                        btn.disabled = false;
                    }, 700);
                });
            }

            // Initialize Map - SMK ISFI Banjarmasin coordinates
            // Based on Google Maps link: SMK ISFI Banjarmasin at Jl. Flamboyan V No.5B
            // Coordinates: -3.2920, 114.5982 (approximate center of Banjarmasin Utara area)
            var map = L.map('miniMap').setView([-3.2920, 114.5982], 16);
            
            L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OSM</a> &copy; CartoDB',
                subdomains: 'abcd',
                maxZoom: 19,
                minZoom: 10
            }).addTo(map);
            
            // Custom marker icon (simple but modern)
            var customIcon = L.divIcon({
                html: '<div style="background: linear-gradient(135deg, #38BDF8, #2DD4BF); width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 0 0 3px rgba(56,189,248,0.3), 0 4px 12px rgba(0,0,0,0.3);"><i class="fas fa-map-marker-alt" style="color: white; font-size: 16px; text-shadow: 0 1px 1px rgba(0,0,0,0.2);"></i></div>',
                className: 'custom-marker',
                iconSize: [32, 32],
                iconAnchor: [16, 32],
                popupAnchor: [0, -32]
            });
            
            var marker = L.marker([-3.2920, 114.5982], { icon: customIcon }).addTo(map);
            marker.bindPopup('<b>SMK ISFI Banjarmasin</b><br>Jl. Flamboyan V No.5B, Sungai Miai<br>Banjarmasin Utara, Kalimantan Selatan').openPopup();
            
            // Optional: add a subtle circle to indicate area
            L.circle([-3.2920, 114.5982], {
                color: '#38BDF8',
                weight: 1,
                opacity: 0.4,
                fillColor: '#38BDF8',
                fillOpacity: 0.08,
                radius: 100
            }).addTo(map);
        })();
    </script>
</body>
</html>