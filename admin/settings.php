<?php
// All logic first before any HTML
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/functions.php';

session_start();

// SAVE settings
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $fields = [
        'site_name',
        'site_email',
        'site_phone',
        'site_address',
        'check_in_time',
        'check_out_time'
    ];

    foreach ($fields as $field) {
        $value = sanitize($_POST[$field]);
        $stmt  = $conn->prepare("
            UPDATE settings SET setting_value = ? WHERE setting_key = ?
        ");
        $stmt->bind_param("ss", $value, $field);
        $stmt->execute();
        $stmt->close();
    }

    // CHANGE PASSWORD (only if filled in)
    if (!empty($_POST['new_password'])) {
        $new_password     = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        if ($new_password !== $confirm_password) {
            $password_error = "Passwords do not match.";
        } elseif (strlen($new_password) < 6) {
            $password_error = "Password must be at least 6 characters.";
        } else {
            $hashed = password_hash($new_password, PASSWORD_BCRYPT);
            $stmt   = $conn->prepare("UPDATE admins SET password = ? WHERE id = ?");
            $admin_id = $_SESSION['admin_id'];
            $stmt->bind_param("si", $hashed, $admin_id);
            $stmt->execute();
            $stmt->close();
            $password_success = true;
        }
    }

    if (!isset($password_error)) {
        header("Location: " . SITE_URL . "/admin/settings.php?saved=1");
        exit();
    }
}

$page_title = "Settings";
require_once 'includes/admin_header.php';

// Fetch all current settings
$settings_result = $conn->query("SELECT * FROM settings");
$settings = [];
while ($row = $settings_result->fetch_assoc()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}
?>

<!-- Messages -->
<?php if (isset($_GET['saved'])): ?>
<div class="alert alert-success alert-dismissible fade show mb-4">
    <i class="bi bi-check-circle me-2"></i>Settings saved successfully.
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if (isset($password_success)): ?>
<div class="alert alert-success alert-dismissible fade show mb-4">
    <i class="bi bi-check-circle me-2"></i>Password changed successfully.
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if (isset($password_error)): ?>
<div class="alert alert-danger alert-dismissible fade show mb-4">
    <i class="bi bi-exclamation-triangle me-2"></i><?= $password_error ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!--PAGE TITLE-->
<div class="mb-4">
    <h4 class="fw-bold mb-1">Settings</h4>
    <p class="text-muted small mb-0">Manage your guesthouse information</p>
</div>

<form method="POST">
<div class="row g-4">

    <!--LEFT COLUMN -->
    <div class="col-lg-7">

        <!-- Site Information -->
        <div class="admin-card card mb-4">
            <div class="card-header">
                <i class="bi bi-info-circle me-2"></i>Guesthouse Information
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-semibold">Guesthouse Name</label>
                        <input type="text" name="site_name" class="form-control"
                               value="<?= htmlspecialchars($settings['site_name'] ?? '') ?>"
                               required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Email Address</label>
                        <input type="email" name="site_email" class="form-control"
                               value="<?= htmlspecialchars($settings['site_email'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Phone Number</label>
                        <input type="text" name="site_phone" class="form-control"
                               value="<?= htmlspecialchars($settings['site_phone'] ?? '') ?>">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold">Address</label>
                        <input type="text" name="site_address" class="form-control"
                               value="<?= htmlspecialchars($settings['site_address'] ?? '') ?>">
                    </div>
                </div>
            </div>
        </div>

        <!-- Check-in / Check-out Times -->
        <div class="admin-card card mb-4">
            <div class="card-header">
                <i class="bi bi-clock me-2"></i>Check-in & Check-out Times
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Check-in Time</label>
                        <input type="time" name="check_in_time" class="form-control"
                               value="<?= $settings['check_in_time'] ?? '14:00' ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Check-out Time</label>
                        <input type="time" name="check_out_time" class="form-control"
                               value="<?= $settings['check_out_time'] ?? '10:00' ?>">
                    </div>
                </div>
            </div>
        </div>

        <!-- Save Button -->
        <button type="submit" class="btn btn-primary px-5">
            <i class="bi bi-save me-2"></i>Save Settings
        </button>

    </div>

    <!-- RIGHT COLUMN -->
    <div class="col-lg-5">

        <!-- Change Password -->
        <div class="admin-card card mb-4">
            <div class="card-header">
                <i class="bi bi-lock me-2"></i>Change Password
            </div>
            <div class="card-body p-4">
                <p class="text-muted small mb-3">
                    Leave blank if you do not want to change your password.
                </p>
                <div class="mb-3">
                    <label class="form-label fw-semibold">New Password</label>
                    <input type="password" name="new_password" class="form-control"
                           placeholder="Enter new password">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Confirm New Password</label>
                    <input type="password" name="confirm_password" class="form-control"
                           placeholder="Confirm new password">
                </div>
            </div>
        </div>

        <!-- Quick Info Card -->
        <div class="admin-card card">
            <div class="card-header">
                <i class="bi bi-lightbulb me-2"></i>Quick Info
            </div>
            <div class="card-body p-4">
                <ul class="list-unstyled small text-muted mb-0">
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-success me-2"></i>
                        Changes take effect immediately on the website
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-success me-2"></i>
                        Guesthouse name appears in the navbar and footer
                    </li>
                    <li class="mb-2">
                        <i class="bi bi-check-circle text-success me-2"></i>
                        Contact details appear on the contact page
                    </li>
                    <li>
                        <i class="bi bi-check-circle text-success me-2"></i>
                        Check-in times appear in the footer and about page
                    </li>
                </ul>
            </div>
        </div>

    </div>

</div>
</form>

<?php require_once 'includes/admin_footer.php'; ?>