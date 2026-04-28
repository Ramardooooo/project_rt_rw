<?php
include 'config/database.php';

// Add views column
$result = mysqli_query($conn, "SHOW COLUMNS FROM announcements LIKE 'views'");
if (mysqli_num_rows($result) == 0) {
    mysqli_query($conn, "ALTER TABLE announcements ADD COLUMN views INT DEFAULT 0");
}

// Update views
$result = mysqli_query($conn, "SELECT id FROM announcements ORDER BY created_at DESC LIMIT 6");
$ids = [];
while ($row = mysqli_fetch_assoc($result)) {
    $ids[] = (int)$row['id'];
}
if (!empty($ids)) {
    $ids_str = implode(',', $ids);
    mysqli_query($conn, "UPDATE announcements SET views = COALESCE(views, 0) + 1 WHERE id IN ($ids_str)");
}

// Get announcements
$result = mysqli_query($conn, "SELECT id, title, content, DATE_FORMAT(created_at, '%d %b %Y, %H:%i') as date, COALESCE(views, 0) as views, created_at > DATE_SUB(NOW(), INTERVAL 3 DAY) as is_new FROM announcements ORDER BY created_at DESC LIMIT 6");
$announcements = mysqli_fetch_all($result, MYSQLI_ASSOC);
foreach ($announcements as &$announcement) {
    $announcement['preview'] = strlen($announcement['content']) > 120 ? substr($announcement['content'], 0, 120) . '...' : $announcement['content'];
    $announcement['content'] = nl2br(htmlspecialchars($announcement['content']));
}
?>

<section class="py-24 bg-gradient-to-br from-white via-slate-50 to-slate-100">
    <div class="max-w-7xl mx-auto px-6">

        <div class="text-center mb-16">
            <span class="inline-block px-4 py-1.5 rounded-full bg-indigo-100 text-indigo-700 text-sm font-semibold tracking-wide uppercase shadow-sm">
                Pengumuman
            </span>
            <h2 class="mt-5 text-5xl font-extrabold tracking-tight bg-gradient-to-r from-slate-900 to-slate-700 bg-clip-text text-transparent">
                Update Terbaru
            </h2>
            <p class="mt-4 text-slate-600 text-lg max-w-2xl mx-auto">
                Informasi kegiatan RT/RW dan sistem terbaru
            </p>
            <div class="mt-4 w-24 h-1 bg-indigo-500 rounded-full mx-auto"></div>
        </div>

        <?php if (empty($announcements)): ?>
            <div class="text-center py-20 bg-white rounded-2xl shadow-sm border border-slate-200">
                <div class="text-6xl mb-4">📭</div>
                <h3 class="text-xl font-semibold text-slate-800 mb-2">
                    Belum ada pengumuman
                </h3>
                <p class="text-slate-500">
                    Silakan cek kembali nanti
                </p>
            </div>
        <?php else: ?>

        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">

            <?php foreach ($announcements as $announcement): ?>
            <article class="group bg-white rounded-2xl shadow-md hover:shadow-2xl transition-all duration-500 transform hover:-translate-y-2 border border-slate-100 overflow-hidden flex flex-col">

                <div class="p-6 flex flex-col flex-grow">

                    <?php if ($announcement['is_new']): ?>
                        <div class="flex justify-between items-start">
                            <span class="px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 text-xs font-bold uppercase tracking-wide shadow-sm">
                                🔔 Baru
                            </span>
                        </div>
                    <?php else: ?>
                        <div class="h-6"></div>
                    <?php endif; ?>

                    <h3 class="mt-3 text-xl font-bold text-slate-800 leading-tight group-hover:text-indigo-600 transition-colors duration-300 line-clamp-2">
                        <?= htmlspecialchars($announcement['title']) ?>
                    </h3>

                    <div class="mt-4 flex items-center gap-3 text-xs text-slate-400">
                        <span class="flex items-center gap-1">👁️ <?= number_format($announcement['views']) ?> views</span>
                        <span class="w-1 h-1 bg-slate-300 rounded-full"></span>
                        <span class="flex items-center gap-1">📅 <?= $announcement['date'] ?></span>
                    </div>

                    <div class="mt-4 border-t border-slate-100 pt-4">
                        <p id="preview-<?= $announcement['id'] ?>" class="text-sm text-slate-600 leading-relaxed">
                            <?= htmlspecialchars($announcement['preview']) ?>
                        </p>

                        <?php if (strlen($announcement['content']) > 120): ?>
                            <div id="full-<?= $announcement['id'] ?>" class="hidden mt-3 text-sm text-slate-700 leading-relaxed bg-slate-50 p-3 rounded-lg">
                                <?= $announcement['content'] ?>
                            </div>

                            <button onclick="toggleContent(<?= $announcement['id'] ?>, this)" 
                                class="mt-4 text-sm font-semibold text-indigo-600 hover:text-indigo-800 transition flex items-center gap-1 group/btn">
                                <span class="btn-text-<?= $announcement['id'] ?>">Baca selengkapnya</span>
                                <svg class="w-4 h-4 transition-transform duration-300 group-hover/btn:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                            </button>
                        <?php endif; ?>
                    </div>

                </div>

            </article>
            <?php endforeach; ?>

        </div>

        <div class="text-center mt-12">
            <a href="#" class="inline-flex items-center gap-2 px-6 py-3 bg-white border border-indigo-200 text-indigo-600 font-semibold rounded-full shadow-sm hover:shadow-md hover:bg-indigo-50 transition-all">
                Lihat semua pengumuman
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                </svg>
            </a>
        </div>

        <?php endif; ?>
    </div>
</section>

<script>
function toggleContent(id, btn) {
    const full = document.getElementById('full-' + id);
    const preview = document.getElementById('preview-' + id);
    const btnSpan = btn.querySelector('.btn-text-' + id) || btn;

    if (full.classList.contains('hidden')) {
        full.classList.remove('hidden');
        preview.classList.add('hidden');
        if (btnSpan.tagName === 'SPAN') {
            btnSpan.textContent = 'Tutup';
        } else {
            btn.textContent = 'Tutup';
        }
        btn.querySelector('svg')?.classList.add('rotate-90');
    } else {
        full.classList.add('hidden');
        preview.classList.remove('hidden');
        if (btnSpan.tagName === 'SPAN') {
            btnSpan.textContent = 'Baca selengkapnya';
        } else {
            btn.textContent = 'Baca selengkapnya';
        }
        btn.querySelector('svg')?.classList.remove('rotate-90');
    }
}
</script>
<style>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.rotate-90 {
    transform: rotate(90deg);
}
</style>