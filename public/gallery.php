<?php
$page_title = "Gallery";
require_once '../includes/header.php';

// Fetch all gallery images ordered by sort_order
$images_result = $conn->query("SELECT * FROM gallery ORDER BY sort_order ASC, created_at DESC");
?>

<!-- PAGE HEADER -->
<section class="page-header text-white text-center py-5">
    <div class="container">
        <p class="section-subtitle text-white mb-2">Memories</p>
        <h1 class="mb-3">Our Gallery</h1>
        <p class="opacity-75 mx-auto" style="max-width:500px;">
            Take a visual tour of our guesthouse, facilities, and the beautiful surroundings.
        </p>
    </div>
</section>

<!-- GALLERY GRID -->
<section class="py-6">
    <div class="container">
        <?php if ($images_result->num_rows === 0): ?>
        <div class="text-center py-5">
            <i class="bi bi-images" style="font-size:3rem; color:var(--primary);"></i>
            <h4 class="mt-3">No photos yet</h4>
            <p class="text-muted">Check back soon for updates!</p>
        </div>
        <?php else: ?>
        <div class="row g-4">
            <?php while ($img = $images_result->fetch_assoc()): 
                // CLOUDINARY FIX: Check if it's a full URL or a local path
                $img_src = $img['image_path'];
                if (strpos($img_src, 'http') !== 0) {
                    // It's an old local path, so we add the local directory
                    $img_src = SITE_URL . '/public/assets/images/uploads/' . $img_src;
                }
                // If it starts with 'http', $img_src remains the Cloudinary URL untouched.
            ?>
            <div class="col-md-4 col-sm-6">
                <div class="gallery-card">
                    <img src="<?= htmlspecialchars($img_src) ?>" 
                         alt="<?= htmlspecialchars($img['caption'] ?: 'Gallery Image') ?>" 
                         class="img-fluid w-100"
                         style="object-fit: cover; height: 250px; cursor: pointer;"
                         data-bs-toggle="modal" 
                         data-bs-target="#imageModal"
                         onclick="openModal('<?= htmlspecialchars($img_src) ?>', '<?= htmlspecialchars($img['caption']) ?>')">
                    
                    <?php if (!empty($img['caption'])): ?>
                    <div class="gallery-caption">
                        <?= htmlspecialchars($img['caption']) ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- LIGHTBOX MODAL -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-transparent border-0">
            <div class="modal-body p-0 position-relative">
                <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3 z-3" data-bs-dismiss="modal" aria-label="Close"></button>
                <img id="modalImage" src="" class="img-fluid w-100 rounded shadow-lg" alt="Full size">
                <h5 id="modalCaption" class="text-white text-center mt-3 bg-dark bg-opacity-50 py-2 rounded-bottom"></h5>
            </div>
        </div>
    </div>
</div>

<style>
.gallery-card {
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    transition: transform 0.3s, box-shadow 0.3s;
    background: #fff;
}
.gallery-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}
.gallery-caption {
    padding: 12px 16px;
    font-size: 0.9rem;
    color: #555;
    text-align: center;
    background: #fff;
    border-top: 1px solid #eee;
}
</style>

<script>
function openModal(src, caption) {
    document.getElementById('modalImage').src = src;
    document.getElementById('modalCaption').textContent = caption;
}
</script>

<?php require_once '../includes/footer.php'; ?>