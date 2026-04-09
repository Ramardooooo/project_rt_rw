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

<section class="py-20 bg-white">
    <div class="max-w-6xl mx-auto px-6">

        <div class="text-center mb-16">
            <span class="text-sm font-medium text-slate-500 tracking-wide uppercase">
                Pengumuman
            </span>
            <h2 class="mt-3 text-4xl font-bold text-slate-900">
                Update Terbaru
            </h2>
            <p class="mt-4 text-slate-600 max-w-xl mx-auto">
                Informasi kegiatan RT/RW dan sistem terbaru
            </p>
        </div>

        <?php if (empty($announcements)): ?>
            <div class="text-center py-20 border border-slate-200 rounded-xl">
                <h3 class="text-lg font-semibold text-slate-800 mb-2">
                    Belum ada pengumuman
                </h3>
                <p class="text-slate-500">
                    Silakan cek kembali nanti
                </p>
            </div>
        <?php else: ?>

        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">

            <?php foreach ($announcements as $announcement): ?>
            <article class="border border-slate-200 rounded-xl p-6 hover:border-slate-300 transition-all duration-300 bg-white">

                <?php if ($announcement['is_new']): ?>
                    <span class="text-xs font-semibold text-slate-500 uppercase">
                        Baru
                    </span>
                <?php endif; ?>

                <h3 class="mt-2 text-lg font-semibold text-slate-900 leading-snug line-clamp-2">
                    <?= htmlspecialchars($announcement['title']) ?>
                </h3>

                <p id="preview-<?= $announcement['id'] ?>" class="mt-3 text-sm text-slate-600 line-clamp-3">
                    <?= htmlspecialchars($announcement['preview']) ?>
                </p>

                <?php if (strlen($announcement['content']) > 120): ?>
                    <div id="full-<?= $announcement['id'] ?>" class="hidden mt-3 text-sm text-slate-600 leading-relaxed">
                        <?= $announcement['content'] ?>
                    </div>

                    <button onclick="toggleContent(<?= $announcement['id'] ?>, this)" 
                        class="mt-4 text-sm font-medium text-slate-700 hover:text-slate-900 transition">
                        Baca selengkapnya
                    </button>
                <?php endif; ?>

                <div class="mt-6 pt-4 border-t border-slate-100 flex justify-between text-xs text-slate-500">
                    <span><?= number_format($announcement['views']) ?> views</span>
                    <span><?= $announcement['date'] ?></span>
                </div>

            </article>
            <?php endforeach; ?>

        </div>

        <?php endif; ?>
    </div>
</section>

<script>
function toggleContent(id, btn) {
    const full = document.getElementById('full-' + id);
    const preview = document.getElementById('preview-' + id);

    if (full.classList.contains('hidden')) {
        full.classList.remove('hidden');
        preview.classList.add('hidden');
        btn.textContent = 'Tutup';
    } else {
        full.classList.add('hidden');
        preview.classList.remove('hidden');
        btn.textContent = 'Baca selengkapnya';
    }
}
</script>