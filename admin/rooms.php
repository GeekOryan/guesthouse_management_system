<?php
// All logic first before any HTML
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/functions.php';


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

    if ($id === 0) {
        $stmt = $conn->prepare("
            INSERT INTO rooms (name, description, capacity, price_per_night, amenities, is_active)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("ssidsi", $name, $description, $capacity, $price, $amenities, $is_active);
    } else {
        $stmt = $conn->prepare("
            UPDATE rooms SET name=?, description=?, capacity=?, price_per_night=?, amenities=?, is_active=?
            WHERE id=?
        ");
        $stmt->bind_param("ssidsii", $name, $description, $capacity, $price, $amenities, $is_active, $id);
    }

    $stmt->execute();
    $stmt->close();

    header("Location: " . SITE_URL . "/admin/rooms.php?saved=1");
    exit();
}

// NOW load the header — all redirects are done
$page_title = "Rooms";
require_once 'includes/admin_header.php';

//Fetch all rooms
$rooms = $conn->query("SELECT * FROM rooms ORDER BY created_at DESC");
?>

<!-- Success messages -->
<?php if (isset($_GET['saved'])): ?>
<div class="alert alert-success alert-dismissible fade show mb-4">
    <i class="bi bi-check-circle me-2"></i>Room saved successfully.
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
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#roomModal"
            onclick="resetForm()">
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
                        <th>Capacity</th>
                        <th>Price/Night</th>
                        <th>Amenities</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($rooms->num_rows === 0): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-5">
                            No rooms found. Add your first room!
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php while ($room = $rooms->fetch_assoc()): ?>
                    <tr>
                        <td class="ps-3">
                            <div class="fw-semibold"><?= $room['name'] ?></div>
                            <div class="text-muted small"><?= substr($room['description'], 0, 50) ?>...</div>
                        </td>
                        <td>
                            <i class="bi bi-people me-1"></i><?= $room['capacity'] ?>
                        </td>
                        <td class="fw-semibold text-primary">
                            <?= formatPrice($room['price_per_night']) ?>
                        </td>
                        <td>
                            <?php
                            $amenities = explode(',', $room['amenities']);
                            foreach (array_slice($amenities, 0, 2) as $a):
                            ?>
                            <span class="amenity-badge"><?= trim($a) ?></span>
                            <?php endforeach; ?>
                            <?php if (count($amenities) > 2): ?>
                            <span class="text-muted small">+<?= count($amenities) - 2 ?> more</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($room['is_active']): ?>
                            <span class="status-badge status-confirmed">Visible</span>
                            <?php else: ?>
                            <span class="status-badge status-cancelled">Hidden</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <!-- Edit button — loads room data into the form -->
                                <button class="btn btn-sm btn-outline-primary edit-btn"
                                        data-bs-toggle="modal"
                                        data-bs-target="#roomModal"
                                        data-id="<?= $room['id'] ?>"
                                        data-name="<?= htmlspecialchars($room['name']) ?>"
                                        data-description="<?= htmlspecialchars($room['description']) ?>"
                                        data-capacity="<?= $room['capacity'] ?>"
                                        data-price="<?= $room['price_per_night'] ?>"
                                        data-amenities="<?= htmlspecialchars($room['amenities']) ?>"
                                        data-active="<?= $room['is_active'] ?>">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <!-- Delete button -->
                                <a href="<?= SITE_URL ?>/admin/rooms.php?delete=<?= $room['id'] ?>"
                                   class="btn btn-sm btn-outline-danger"
                                   onclick="return confirm('Delete this room? This cannot be undone.')">
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
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">
                    <i class="bi bi-door-open me-2"></i>Add New Room
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <!-- Hidden field — empty for new room, has ID for edit -->
                <input type="hidden" name="room_id" id="room_id" value="0">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Room Name</label>
                            <input type="text" name="name" id="field-name"
                                   class="form-control" placeholder="e.g. Deluxe Double" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Capacity (guests)</label>
                            <input type="number" name="capacity" id="field-capacity"
                                   class="form-control" min="1" max="10" value="2" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Description</label>
                            <textarea name="description" id="field-description"
                                      class="form-control" rows="3"
                                      placeholder="Describe the room..."></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Price Per Night (R)</label>
                            <input type="number" name="price_per_night" id="field-price"
                                   class="form-control" min="0" step="0.01"
                                   placeholder="650.00" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Visibility</label>
                            <div class="form-check mt-2">
                                <input type="checkbox" name="is_active" id="field-active"
                                       class="form-check-input" checked>
                                <label class="form-check-label" for="field-active">
                                    Visible on website
                                </label>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Amenities</label>
                            <input type="text" name="amenities" id="field-amenities"
                                   class="form-control"
                                   placeholder="WiFi,TV,Air Conditioning,En-suite Bathroom">
                            <div class="form-text">Separate each amenity with a comma</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-2"></i>Save Room
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Reset form for adding a new room
function resetForm() {
    document.getElementById('modalTitle').innerHTML     = '<i class="bi bi-door-open me-2"></i>Add New Room';
    document.getElementById('room_id').value            = '0';
    document.getElementById('field-name').value         = '';
    document.getElementById('field-description').value  = '';
    document.getElementById('field-capacity').value     = '2';
    document.getElementById('field-price').value        = '';
    document.getElementById('field-amenities').value    = '';
    document.getElementById('field-active').checked     = true;
}

// Fill form with existing room data for editing
document.querySelectorAll('.edit-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        document.getElementById('modalTitle').innerHTML     = '<i class="bi bi-pencil me-2"></i>Edit Room';
        document.getElementById('room_id').value            = this.dataset.id;
        document.getElementById('field-name').value         = this.dataset.name;
        document.getElementById('field-description').value  = this.dataset.description;
        document.getElementById('field-capacity').value     = this.dataset.capacity;
        document.getElementById('field-price').value        = this.dataset.price;
        document.getElementById('field-amenities').value    = this.dataset.amenities;
        document.getElementById('field-active').checked     = this.dataset.active === '1';
    });
});
</script>

<?php require_once 'includes/admin_footer.php'; ?>
