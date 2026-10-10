<?php
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

// DELETE a room 
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM rooms WHERE id = $id");
    header("Location: " . SITE_URL . "/admin/rooms.php?deleted=1");
    exit();
}

// ADD or EDIT a room
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id          = (int)$_POST['room_id'];
    $name        = sanitize($_POST['name']);
    $description = sanitize($_POST['description']);
    $capacity    = (int)$_POST['capacity'];
    $price       = (float)$_POST['price_per_night'];
    $amenities   = sanitize($_POST['amenities']);
    $is_active   = isset($_POST['is_active']) ? 1 : 0;
    
    // Keep existing image URL by default
    $image_url   = sanitize($_POST['existing_image'] ?? ''); 

    // Handle new image upload via Cloudinary
    if (isset($_FILES['room_image']) && $_FILES['room_image']['error'] === 0) {
        $file = $_FILES['room_image'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array($ext, $allowed) && $file['size'] <= 2 * 1024 * 1024) {
            try {
                $uploadResult = $cloudinary->uploadApi()->upload($file['tmp_name'], [
                    'folder' => 'guesthouse/rooms',
                    'public_id' => 'room_' . uniqid(),
                    'overwrite' => true,
                    'resource_type' => 'image'
                ]);
                $image_url = $uploadResult['secure_url'];
            } catch (Exception $e) {
                $error_msg = "Image upload failed: " . $e->getMessage();
            }
        }
    }

    if (!isset($error_msg)) {
        if ($id === 0) {
            $stmt = $conn->prepare("INSERT INTO rooms (name, description, capacity, price_per_night, amenities, is_active, image) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssidsis", $name, $description, $capacity, $price, $amenities, $is_active, $image_url);
        } else {
            $stmt = $conn->prepare("UPDATE rooms SET name=?, description=?, capacity=?, price_per_night=?, amenities=?, is_active=?, image=? WHERE id=?");
            $stmt->bind_param("ssidsiss", $name, $description, $capacity, $price, $amenities, $is_active, $image_url, $id);
        }
        $stmt->execute();
        $stmt->close();
    }

    $redirect = SITE_URL . "/admin/rooms.php?saved=1";
    if (isset($error_msg)) $redirect .= "&error=" . urlencode($error_msg);
    header("Location: " . $redirect);
    exit();
}

$page_title = "Rooms";
require_once 'includes/admin_header.php';

$rooms = $conn->query("SELECT * FROM rooms ORDER BY created_at DESC");
?>

<!-- Success/Error messages -->
<?php if (isset($_GET['saved'])): ?>
<div class="alert alert-success alert-dismissible fade show mb-4">
    <i class="bi bi-check-circle me-2"></i>Room saved successfully.
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>
<?php if (isset($_GET['error'])): ?>
<div class="alert alert-danger alert-dismissible fade show mb-4">
    <i class="bi bi-exclamation-triangle me-2"></i><?= htmlspecialchars($_GET['error']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>
<?php if (isset($_GET['deleted'])): ?>
<div class="alert alert-warning alert-dismissible fade show mb-4">
    <i class="bi bi-trash me-2"></i>Room deleted successfully.
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!--PAGE TITLE + ADD BUTTON-->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Rooms</h4>
        <p class="text-muted small mb-0">Manage your room listings</p>
    </div>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#roomModal" onclick="resetForm()">
        <i class="bi bi-plus-lg me-2"></i>Add New Room
    </button>
</div>

<!--ROOMS TABLE-->
<div class="admin-card card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table admin-table mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">Room Name</th>
                        <th>Image</th>
                        <th>Capacity</th>
                        <th>Price/Night</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($rooms->num_rows === 0): ?>
                    <tr><td colspan="6" class="text-center text-muted py-5">No rooms found.</td></tr>
                    <?php else: ?>
                    <?php while ($room = $rooms->fetch_assoc()): ?>
                    <tr>
                        <td class="ps-3">
                            <div class="fw-semibold"><?= htmlspecialchars($room['name']) ?></div>
                            <div class="text-muted small"><?= htmlspecialchars(substr($room['description'], 0, 50)) ?>...</div>
                        </td>
                        <td>
                            <?php if (!empty($room['image'])): ?>
                                <?php $img_src = (strpos($room['image'], 'http') === 0) ? $room['image'] : SITE_URL . '/public/assets/images/uploads/' . $room['image']; ?>
                                <img src="<?= $img_src ?>" alt="Room" style="max-height: 50px; border-radius: 4px;">
                            <?php else: ?>
                                <span class="text-muted small">No image</span>
                            <?php endif; ?>
                        </td>
                        <td><i class="bi bi-people me-1"></i><?= $room['capacity'] ?></td>
                        <td class="fw-semibold text-primary"><?= formatPrice($room['price_per_night']) ?></td>
                        <td>
                            <?php if ($room['is_active']): ?><span class="status-badge status-confirmed">Visible</span>
                            <?php else: ?><span class="status-badge status-cancelled">Hidden</span><?php endif; ?>
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <button class="btn btn-sm btn-outline-primary edit-btn" data-bs-toggle="modal" data-bs-target="#roomModal"
                                        data-id="<?= $room['id'] ?>" data-name="<?= htmlspecialchars($room['name']) ?>"
                                        data-description="<?= htmlspecialchars($room['description']) ?>" data-capacity="<?= $room['capacity'] ?>"
                                        data-price="<?= $room['price_per_night'] ?>" data-amenities="<?= htmlspecialchars($room['amenities']) ?>"
                                        data-active="<?= $room['is_active'] ?>" data-image="<?= htmlspecialchars($room['image']) ?>">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <a href="<?= SITE_URL ?>/admin/rooms.php?delete=<?= $room['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this room?')">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!--ADD / EDIT MODAL -->
<div class="modal fade" id="roomModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <!-- IMPORTANT: enctype is required for file uploads -->
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle"><i class="bi bi-door-open me-2"></i>Add New Room</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="room_id" id="room_id" value="0">
                    <input type="hidden" name="existing_image" id="existing_image" value="">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Room Name</label>
                            <input type="text" name="name" id="field-name" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Capacity</label>
                            <input type="number" name="capacity" id="field-capacity" class="form-control" min="1" max="10" value="2" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Description</label>
                            <textarea name="description" id="field-description" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Price Per Night (R)</label>
                            <input type="number" name="price_per_night" id="field-price" class="form-control" min="0" step="0.01" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Room Image</label>
                            <input type="file" name="room_image" class="form-control" accept="image/*">
                            <div class="form-text">Max 2MB. Leave blank to keep existing image.</div>
                            <img id="modal-image-preview" src="" class="img-thumbnail mt-2 d-none" style="max-height: 100px;">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Amenities</label>
                            <input type="text" name="amenities" id="field-amenities" class="form-control" placeholder="WiFi,TV,Air Conditioning">
                        </div>
                        <div class="col-12">
                            <div class="form-check">
                                <input type="checkbox" name="is_active" id="field-active" class="form-check-input" checked>
                                <label class="form-check-label" for="field-active">Visible on website</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save me-2"></i>Save Room</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function resetForm() {
    document.getElementById('modalTitle').innerHTML = '<i class="bi bi-door-open me-2"></i>Add New Room';
    document.getElementById('room_id').value = '0';
    document.getElementById('existing_image').value = '';
    document.getElementById('field-name').value = '';
    document.getElementById('field-description').value = '';
    document.getElementById('field-capacity').value = '2';
    document.getElementById('field-price').value = '';
    document.getElementById('field-amenities').value = '';
    document.getElementById('field-active').checked = true;
    document.getElementById('modal-image-preview').classList.add('d-none');
}

document.querySelectorAll('.edit-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        document.getElementById('modalTitle').innerHTML = '<i class="bi bi-pencil me-2"></i>Edit Room';
        document.getElementById('room_id').value = this.dataset.id;
        document.getElementById('existing_image').value = this.dataset.image;
        document.getElementById('field-name').value = this.dataset.name;
        document.getElementById('field-description').value = this.dataset.description;
        document.getElementById('field-capacity').value = this.dataset.capacity;
        document.getElementById('field-price').value = this.dataset.price;
        document.getElementById('field-amenities').value = this.dataset.amenities;
        document.getElementById('field-active').checked = this.dataset.active === '1';
        
        // Show existing image preview if it exists
        const imgPreview = document.getElementById('modal-image-preview');
        if (this.dataset.image) {
            const imgSrc = this.dataset.image.startsWith('http') ? this.dataset.image : '<?= SITE_URL ?>/public/assets/images/uploads/' + this.dataset.image;
            imgPreview.src = imgSrc;
            imgPreview.classList.remove('d-none');
        } else {
            imgPreview.classList.add('d-none');
        }
    });
});
</script>

<?php require_once 'includes/admin_footer.php'; ?>