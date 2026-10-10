<?php
// All logic first before any HTML
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/vendor/autoload.php'; // Load Cloudinary SDK

use Cloudinary\Cloudinary;
use Cloudinary\Configuration\Configuration;

// Initialize Cloudinary
Configuration::instance([
    'cloud' => [
        'cloud_name' => getenv('CLOUDINARY_CLOUD_NAME'),
        'api_key'    => getenv('CLOUDINARY_API_KEY'),
        'api_secret' => getenv('CLOUDINARY_API_SECRET'),
    ],
]);
$cloudinary = new Cloudinary();

// DELETE an image
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];

    // Get image URL from database
    $result = $conn->query("SELECT image_path FROM gallery WHERE id = $id");
    if ($result->num_rows > 0) {
        $image = $result->fetch_assoc();
        $image_url = $image['image_path'];

        // If it's a Cloudinary URL, delete from Cloudinary
        if (strpos($image_url, 'cloudinary.com') !== false) {
            try {
                // Extract public_id from URL
                preg_match('/upload\/v\d+\/(.+)$/', $image_url, $matches);
                if (isset($matches[1])) {
                    $public_id = $matches[1];
                    $cloudinary->uploadApi()->destroy($public_id);
                }
            } catch (Exception $e) {
                // Silently continue even if Cloudinary delete fails
            }
        }

        // Delete record from database
        $conn->query("DELETE FROM gallery WHERE id = $id");
    }

    header("Location: " . SITE_URL . "/admin/gallery.php?deleted=1");
    exit();
}

// UPLOAD a new image
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $caption    = sanitize($_POST['caption']);
    $sort_order = (int)$_POST['sort_order'];
    $upload_ok  = false;
    $image_url  = '';

    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $file     = $_FILES['image'];
        $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed  = ['jpg', 'jpeg', 'png', 'webp'];
        $max_size = 5 * 1024 * 1024; // 5MB

        if (!in_array($ext, $allowed)) {
            $error = "Only JPG, PNG and WEBP images are allowed.";
        } elseif ($file['size'] > $max_size) {
            $error = "Image must be under 5MB.";
        } else {
            try {
                // Upload to Cloudinary
                $uploadResult = $cloudinary->uploadApi()->upload($file['tmp_name'], [
                    'folder' => 'guesthouse/gallery',
                    'public_id' => 'gallery_' . uniqid(),
                    'overwrite' => false,
                    'resource_type' => 'image'
                ]);
                
                $image_url = $uploadResult['secure_url'];
                $upload_ok = true;
            } catch (Exception $e) {
                $error = "Upload failed: " . $e->getMessage();
            }
        }
    } else {
        $error = "Please select an image to upload.";
    }

    if ($upload_ok) {
        $stmt = $conn->prepare("
            INSERT INTO gallery (image_path, caption, sort_order)
            VALUES (?, ?, ?)
        ");
        $stmt->bind_param("ssi", $image_url, $caption, $sort_order);
        $stmt->execute();
        $stmt->close();

        header("Location: " . SITE_URL . "/admin/gallery.php?uploaded=1");
        exit();
    }
}

$page_title = "Gallery";
require_once 'includes/admin_header.php';

// Fetch all gallery images
$images = $conn->query("SELECT * FROM gallery ORDER BY sort_order ASC, created_at DESC");
?>

<!-- Messages -->
<?php if (isset($_GET['uploaded'])): ?>
<div class="alert alert-success alert-dismissible fade show mb-4">
    <i class="bi bi-check-circle me-2"></i>Image uploaded successfully.
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if (isset($_GET['deleted'])): ?>
<div class="alert alert-warning alert-dismissible fade show mb-4">
    <i class="bi bi-trash me-2"></i>Image deleted.
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if (isset($error)): ?>
<div class="alert alert-danger alert-dismissible fade show mb-4">
    <i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($error) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!--  PAGE TITLE + UPLOAD BUTTON -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Gallery</h4>
        <p class="text-muted small mb-0">Upload and manage property photos</p>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">
        <i class="bi bi-cloud-upload me-2"></i>Upload Image
    </button>
</div>

<!--IMAGE GRID -->
<?php if ($images->num_rows === 0): ?>
<div class="admin-card card">
    <div class="card-body text-center py-5 text-muted">
        <i class="bi bi-images d-block mb-2" style="font-size:3rem;"></i>
        No images yet. Upload your first photo!
    </div>
</div>

<?php else: ?>
<div class="row g-3">
    <?php while ($img = $images->fetch_assoc()): 
        // Handle both Cloudinary URLs and old local paths
        $display_src = $img['image_path'];
        if (strpos($img['image_path'], 'http') === false) {
            $display_src = SITE_URL . '/public/assets/images/uploads/' . $img['image_path'];
        }
    ?>
    <div class="col-md-3 col-sm-4 col-6">
        <div class="gallery-admin-card">
            <img src="<?= $display_src ?>"
                 alt="<?= htmlspecialchars($img['caption']) ?>"
                 class="img-fluid w-100">
            <div class="gallery-admin-overlay">
                <div class="text-white small mb-2">
                    <?= !empty($img['caption']) ? htmlspecialchars($img['caption']) : 'No caption' ?>
                </div>
                <a href="<?= SITE_URL ?>/admin/gallery.php?delete=<?= $img['id'] ?>"
                   class="btn btn-sm btn-danger"
                   onclick="return confirm('Delete this image?')">
                    <i class="bi bi-trash me-1"></i>Delete
                </a>
            </div>
        </div>
    </div>
    <?php endwhile; ?>
</div>
<?php endif; ?>

<!-- ===================== UPLOAD MODAL =================== -->
<div class="modal fade" id="uploadModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-cloud-upload me-2"></i>Upload Image
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Image File</label>
                        <input type="file" name="image" class="form-control"
                               accept=".jpg,.jpeg,.png,.webp" required>
                        <div class="form-text">JPG, PNG or WEBP. Max 5MB.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Caption (optional)</label>
                        <input type="text" name="caption" class="form-control"
                               placeholder="e.g. Swimming pool area">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Sort Order</label>
                        <input type="number" name="sort_order" class="form-control"
                               value="0" min="0">
                        <div class="form-text">Lower number = appears first. 0 is default.</div>
                    </div>

                    <!-- Image preview before upload -->
                    <div id="previewBox" style="display:none;">
                        <label class="form-label fw-semibold">Preview</label>
                        <img id="previewImg" src="" class="img-fluid rounded" alt="Preview">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-cloud-upload me-2"></i>Upload
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.gallery-admin-card {
    position: relative;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    height: 180px;
}
.gallery-admin-card img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s;
}
.gallery-admin-card:hover img { transform: scale(1.05); }
.gallery-admin-overlay {
    position: absolute;
    bottom: 0; left: 0; right: 0;
    background: linear-gradient(transparent, rgba(0,0,0,0.85));
    padding: 30px 10px 10px;
    opacity: 0;
    transition: opacity 0.3s;
    text-align: center;
}
.gallery-admin-card:hover .gallery-admin-overlay { opacity: 1; }
</style>

<!-- Image preview script -->
<script>
document.querySelector('input[name="image"]').addEventListener('change', function() {
    const file = this.files[0];
    if (file) {
        const reader  = new FileReader();
        reader.onload = function(e) {
            document.getElementById('previewImg').src = e.target.result;
            document.getElementById('previewBox').style.display = 'block';
        };
        reader.readAsDataURL(file);
    }
});
</script>

<?php require_once 'includes/admin_footer.php'; ?>