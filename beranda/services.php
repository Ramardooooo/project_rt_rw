<section id="services" class="animate-on-scroll animate-fade-up py-24 bg-gradient-to-br from-slate-50 via-white to-gray-50">
    <div class="max-w-7xl mx-auto px-6">
        <div class="text-center mb-20">
            <span class="inline-block text-sm font-semibold tracking-wider text-indigo-500 bg-indigo-50 px-4 py-2 rounded-full mb-4">
                LAYANAN KAMI
            </span>
            <h2 class="text-5xl font-bold text-gray-900 mb-4 tracking-tight">
                Solusi Modern untuk 
                <span class="bg-gradient-to-r from-indigo-600 to-purple-600 bg-clip-text text-transparent">Manajemen RT/RW</span>
            </h2>
            <p class="text-gray-600 text-lg max-w-2xl mx-auto leading-relaxed">
                Kelola surat, administrasi, dan informasi warga dengan platform digital terintegrasi
            </p>
        </div>

        <div class="grid md:grid-cols-3 gap-8">
            <!-- Card 1 -->
            <div class="group relative bg-white/80 backdrop-blur-sm rounded-2xl p-8 transition-all duration-500 hover:shadow-2xl hover:-translate-y-2 border border-gray-100 hover:border-indigo-200">
                <div class="absolute inset-0 bg-gradient-to-br from-indigo-50 to-purple-50 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                
                <div class="relative z-10">
                    <div class="w-16 h-16 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl flex items-center justify-center mb-6 shadow-lg shadow-indigo-200 group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-users text-2xl text-white"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-3">Data Warga</h3>
                    <p class="text-gray-600 mb-6 leading-relaxed">Sistem database terintegrasi untuk mengelola data kependudukan dengan aman dan efisien.</p>
                    
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-indigo-600">Akses Cepat</span>
                        <a href="<?php echo $_SESSION['role'] === 'admin' ? 'dashboard_admin' : ($_SESSION['role'] === 'ketua' ? 'dashboard_ketua' : 'dashboard_user'); ?>" 
                           class="inline-flex items-center gap-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white px-6 py-2.5 rounded-xl hover:shadow-lg hover:shadow-indigo-200 transition-all duration-300 hover:scale-105">
                            <span>Masuk</span>
                            <i class="fas fa-arrow-right text-sm group-hover:translate-x-1 transition-transform"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Card 2 -->
            <div class="group relative bg-white/80 backdrop-blur-sm rounded-2xl p-8 transition-all duration-500 hover:shadow-2xl hover:-translate-y-2 border border-gray-100 hover:border-purple-200">
                <div class="absolute inset-0 bg-gradient-to-br from-purple-50 to-pink-50 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                
                <div class="relative z-10">
                    <div class="w-16 h-16 bg-gradient-to-br from-purple-500 to-pink-500 rounded-2xl flex items-center justify-center mb-6 shadow-lg shadow-purple-200 group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-user-shield text-2xl text-white"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-3">Manajemen Pengguna</h3>
                    <p class="text-gray-600 mb-6 leading-relaxed">Kontrol akses berbasis role, atur hak akses admin, ketua RT, dan warga dengan mudah.</p>
                    
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-purple-600">Akses Cepat</span>
                        <a href="<?php echo $_SESSION['role'] === 'admin' ? 'dashboard_admin' : ($_SESSION['role'] === 'ketua' ? 'dashboard_ketua' : 'dashboard_user'); ?>" 
                           class="inline-flex items-center gap-2 bg-gradient-to-r from-purple-600 to-pink-600 text-white px-6 py-2.5 rounded-xl hover:shadow-lg hover:shadow-purple-200 transition-all duration-300 hover:scale-105">
                            <span>Masuk</span>
                            <i class="fas fa-arrow-right text-sm group-hover:translate-x-1 transition-transform"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Card 3 -->
            <div class="group relative bg-white/80 backdrop-blur-sm rounded-2xl p-8 transition-all duration-500 hover:shadow-2xl hover:-translate-y-2 border border-gray-100 hover:border-emerald-200">
                <div class="absolute inset-0 bg-gradient-to-br from-emerald-50 to-teal-50 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                
                <div class="relative z-10">
                    <div class="w-16 h-16 bg-gradient-to-br from-emerald-500 to-teal-500 rounded-2xl flex items-center justify-center mb-6 shadow-lg shadow-emerald-200 group-hover:scale-110 transition-transform duration-300">
                        <i class="fas fa-bullhorn text-2xl text-white"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-3">Informasi RT/RW</h3>
                    <p class="text-gray-600 mb-6 leading-relaxed">Platform digital untuk pengumuman, jadwal kegiatan, dan informasi penting lainnya.</p>
                    
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-emerald-600">Akses Cepat</span>
                        <a href="<?php echo $_SESSION['role'] === 'admin' ? 'dashboard_admin' : ($_SESSION['role'] === 'ketua' ? 'dashboard_ketua' : 'dashboard_user'); ?>" 
                           class="inline-flex items-center gap-2 bg-gradient-to-r from-emerald-600 to-teal-600 text-white px-6 py-2.5 rounded-xl hover:shadow-lg hover:shadow-emerald-200 transition-all duration-300 hover:scale-105">
                            <span>Masuk</span>
                            <i class="fas fa-arrow-right text-sm group-hover:translate-x-1 transition-transform"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Decorative Elements -->
        <div class="absolute inset-0 -z-10 overflow-hidden pointer-events-none">
            <div class="absolute top-20 left-10 w-72 h-72 bg-indigo-100 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob"></div>
            <div class="absolute top-40 right-10 w-72 h-72 bg-purple-100 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob animation-delay-2000"></div>
            <div class="absolute bottom-20 left-1/2 w-72 h-72 bg-pink-100 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob animation-delay-4000"></div>
        </div>
    </div>
</section>

<style>
@keyframes blob {
    0%, 100% { transform: translate(0px, 0px) scale(1); }
    33% { transform: translate(30px, -50px) scale(1.1); }
    66% { transform: translate(-20px, 20px) scale(0.9); }
}

.animate-blob {
    animation: blob 7s infinite;
}

.animation-delay-2000 {
    animation-delay: 2s;
}

.animation-delay-4000 {
    animation-delay: 4s;
}
</style>