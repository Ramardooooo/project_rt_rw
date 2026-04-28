<?php
// Fixed testimonials.php - safe null handling, correct column names, no warnings
include 'config/database.php';
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
<section class="py-20 bg-gray-50">
    <div class="max-w-7xl mx-auto px-6">
        <h2 class="text-4xl font-serif text-center mb-16 text-gray-800">
            Rating & Testimoni Pengguna
        </h2>
        <div class="grid md:grid-cols-3 gap-8" id="testimonials-grid">
<?php
$stmt = mysqli_prepare($conn, 'SELECT * FROM testimonials WHERE name IS NOT NULL AND name != "" AND description IS NOT NULL AND description != "" ORDER BY created_at DESC LIMIT 6');
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($result)) {
    $nama = $row['name'] ?? 'Anonymous';
    $pesan = $row['description'] ?? 'No description provided.';
    $rating = $row['rating'] ?? 0;
    
    $avatar = 'https://randomuser.me/api/portraits/' . ($rating % 2 ? 'men' : 'women') . '/' . rand(1,99) . '.jpg';
    
    $safe_name = mysqli_real_escape_string($conn, $nama);
    $user_result = mysqli_query($conn, "SELECT profile_photo FROM users WHERE username = '" . $safe_name . "' LIMIT 1");
    if ($user_row = mysqli_fetch_assoc($user_result)) {
        $user_photo = $user_row['profile_photo'] ?? '';
        if (!empty($user_photo)) {
            $avatar = '../account/uploads/profiles/' . $user_photo;
            if (!file_exists($avatar)) $avatar = $user_photo;
        }
    }
    
    $rating_stars = '';
    for ($i = 1; $i <= 5; $i++) {
        $rating_stars .= ($i <= $rating) ? '<i class="fas fa-star text-yellow-400"></i>' : '<i class="fas fa-star text-gray-300"></i>';
    }
    $date = date('d M Y', strtotime($row['created_at'] ?? time()));
    
    echo '<div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center mb-4">
            <img src="' . htmlspecialchars($avatar) . '" alt="' . htmlspecialchars($nama) . '" class="w-12 h-12 rounded-full mr-4 object-cover">
            <div>
                <h4 class="font-semibold text-gray-800">' . htmlspecialchars($nama) . '</h4>
                <p class="text-sm text-gray-500">' . $date . '</p>
            </div>
        </div>
        <p class="text-gray-600 italic mb-4">" ' . htmlspecialchars($pesan) . ' "</p>
        <div class="flex mt-4">' . $rating_stars . '</div>
    </div>';
}
mysqli_stmt_close($stmt);
?>
        </div>

        <div class="mt-16 pt-12 border-t border-gray-200">
            <div class="max-w-2xl mx-auto">
                <div class="bg-white rounded-2xl shadow-xl p-8 lg:p-12">
                    <?php if (!$has_testimoni): ?>
                    <h3 class="text-2xl font-serif font-bold text-gray-800 text-center mb-6">Kirim Testimoni Anda</h3>
                    <p class="text-center text-gray-600 mb-8">Bantu kami improve layanan dengan rating dan feedback Anda!</p>
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
                                echo '<div class="bg-green-100 border border-green-400 text-green-700 px-6 py-4 rounded-xl mb-6"><i class="fas fa-check-circle mr-3"></i> Terima kasih atas testimoni Anda!</div>';
                            } else {
                                echo '<div class="bg-red-100 border border-red-400 text-red-700 px-6 py-4 rounded-xl mb-6"><i class="fas fa-exclamation-triangle mr-3"></i> Gagal: ' . htmlspecialchars(mysqli_error($conn)) . '</div>';
                            }
                            mysqli_stmt_close($stmt);
                        } else {
                            echo '<div class="bg-red-100 border border-red-400 text-red-700 px-6 py-4 rounded-xl mb-6"><i class="fas fa-exclamation-triangle mr-3"></i> ' . implode('<br>', $errors) . '</div>';
                        }
                    } ?>
                    <form method="POST" class="space-y-6">
                        <input type="hidden" name="testimonial_submit" value="1">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nama</label>
                            <input type="text" name="name" value="<?php echo htmlspecialchars($_SESSION['username'] ?? ''); ?>" readonly class="w-full px-4 py-3 bg-gray-100 border rounded-xl cursor-not-allowed">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Rating</label>
                            <div class="flex space-x-1 text-2xl mb-2">
                                <i class="far fa-star cursor-pointer text-gray-400 star" onclick="setRating(1)"></i>
                                <i class="far fa-star cursor-pointer text-gray-400 star" onclick="setRating(2)"></i>
                                <i class="far fa-star cursor-pointer text-gray-400 star" onclick="setRating(3)"></i>
                                <i class="far fa-star cursor-pointer text-gray-400 star" onclick="setRating(4)"></i>
                                <i class="far fa-star cursor-pointer text-gray-400 star" onclick="setRating(5)"></i>
                            </div>
                            <input type="hidden" id="rating" name="rating" value="0" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Testimoni Anda</label>
                            <textarea name="description" rows="4" required placeholder="Ceritakan pengalaman Anda..." class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-blue-500"></textarea>
                        </div>
                        <button type="submit" class="w-full bg-green-500 hover:bg-green-600 text-white font-semibold py-4 px-6 rounded-xl">
                            Kirim Testimoni
                        </button>
                    </form>
                    <?php else: ?>
                    <div class="text-center py-12">
                        <i class="fas fa-star text-3xl text-green-500 mb-6 block"></i>
                        <h3 class="text-2xl font-bold text-green-600 mb-4">Terima kasih!</h3>
                        <p class="text-gray-600 max-w-md mx-auto">Testimoni Anda tercatat dan akan ditampilkan setelah diverifikasi.</p>
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
            icon.classList.replace('far', 'fas');
            icon.classList.replace('text-gray-400', 'text-yellow-400');
        } else {
            icon.classList.replace('fas', 'far');
            icon.classList.replace('text-yellow-400', 'text-gray-400');
        }
    });
}
</script>
