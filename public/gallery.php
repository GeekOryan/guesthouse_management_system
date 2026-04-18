<?php
//  public/gallery.php
//  Gallery page — displays images from the gallery table
$page_title = "Gallery";
require_once '../includes/header.php';

// Fetch all gallery images ordered by sort_order
$gallery_result = $conn->query("SELECT * FROM gallery ORDER BY sort_order ASC, created_at DESC");
?>

<!--PAGE HEADER-->
<section class="page-header text-white text-center py-5">
    <div class="container">
        <p class="section-subtitle text-white mb-2">Our Property</p>
        <h1 class="mb-3">Photo Gallery</h1>
        <p class="opacity-75">Take a look around — we think you'll love what you see.</p>
    </div>
</section>

<!--GALLERY GRID-->
<section class="py-6">
    <div class="container">

        <?php if ($gallery_result->num_rows === 0): ?>
        <!-- No images yet -->
        <div class="text-center py-5">
            <i class="bi bi-images" style="font-size:3rem; color:var(--primary);"></i>
            <h4 class="mt-3">Gallery Coming Soon</h4>
            <p class="text-muted">We are busy uploading photos. Check back soon!</p>
        </div>

        <?php else: ?>
        <div class="row g-3" id="galleryGrid">
            <?php while ($image = $gallery_result->fetch_assoc()): ?>
            <div class="col-md-4 col-sm-6">
                <div class="gallery-item">
                    <img src="<?= SITE_URL ?>/public/assets/images/uploads/<?= $image['image_path'] ?>"
                         alt="<?= $image['caption'] ?? 'Gallery Image' ?>"
                         class="img-fluid w-100"
                         data-bs-toggle="modal"
                         data-bs-target="#lightboxModal"
                         data-src="<?= SITE_URL ?>/public/assets/images/uploads/<?= $image['image_path'] ?>"
                         data-caption="<?= $image['caption'] ?>">
                    <?php if (!empty($image['caption'])): ?>
                    <div class="gallery-caption"><?= $image['caption'] ?></div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
        <?php endif; ?>

    </div>
</section>

<!--LIGHTBOX MODAL-->
<div class="modal fade" id="lightboxModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content bg-dark border-0">
            <div class="modal-header border-0 pb-0">
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center p-3">
                <img src="" id="lightboxImage" class="img-fluid rounded" alt="">
                <p class="text-white mt-3 mb-0" id="lightboxCaption"></p>
            </div>
        </div>
    </div>
</div>

<!-- CTA -->
<section class="cta-section py-5 text-white text-center">
    <div class="container">
        <h3 class="mb-3">Like What You See?</h3>
        <p class="mb-4 opacity-75">Book your stay today and experience it in person.</p>
        <a href="<?= SITE_URL ?>/public/booking.php" class="btn btn-light px-5 text-primary fw-semibold">
            <i class="bi bi-calendar-check me-2"></i>Book Now
        </a>
    </div>
</section>

<!-- Lightbox JS -->
<script>
// When a gallery image is clicked, load it into the modal
document.querySelectorAll('[data-bs-target="#lightboxModal"]').forEach(function(img) {
    img.addEventListener('click', function() {
        document.getElementById('lightboxImage').src      = this.dataset.src;
        document.getElementById('lightboxCaption').textContent = this.dataset.caption || '';
    });
});
</script>

<?php require_once '../includes/footer.php'; ?>