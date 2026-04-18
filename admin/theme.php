<?php
// ============================================================
//  admin/theme.php
//  Theme customisation — colours, fonts, dark mode
// ============================================================

require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- Save theme settings ------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fields = [
        'theme_color',
        'theme_color_dark',
        'theme_bg',
        'theme_font',
        'dark_mode',
    ];

    foreach ($fields as $field) {
        $value = isset($_POST[$field]) ? sanitize($_POST[$field]) : '0';
        $stmt  = $conn->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = ?");
        $stmt->bind_param("ss", $value, $field);
        $stmt->execute();
        $stmt->close();
    }

    // --- Handle logo upload ---------------------------------
    if (isset($_FILES['site_logo']) && $_FILES['site_logo']['error'] === 0) {
        $file    = $_FILES['site_logo'];
        $ext     = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'svg'];

        if (in_array($ext, $allowed) && $file['size'] <= 2 * 1024 * 1024) {
            $old_logo = getSetting('site_logo');
            if (!empty($old_logo) && file_exists(UPLOAD_PATH . '/' . $old_logo)) {
                unlink(UPLOAD_PATH . '/' . $old_logo);
            }
            $filename = 'logo_' . uniqid() . '.' . $ext;
            if (!is_dir(UPLOAD_PATH)) mkdir(UPLOAD_PATH, 0755, true);
            if (move_uploaded_file($file['tmp_name'], UPLOAD_PATH . '/' . $filename)) {
                $stmt = $conn->prepare("UPDATE settings SET setting_value = ? WHERE setting_key = 'site_logo'");
                $stmt->bind_param("s", $filename);
                $stmt->execute();
                $stmt->close();
            }
        }
    }

    header("Location: " . SITE_URL . "/admin/theme.php?saved=1");
    exit();
}

$page_title = "Theme & Appearance";
require_once 'includes/admin_header.php';

// Fetch current theme settings
$theme = [
    'theme_color'      => getSetting('theme_color')      ?: '#8B6914',
    'theme_color_dark' => getSetting('theme_color_dark') ?: '#6B5010',
    'theme_bg'         => getSetting('theme_bg')         ?: '#faf9f7',
    'theme_font'       => getSetting('theme_font')       ?: 'Inter',
    'dark_mode'        => getSetting('dark_mode')        ?: '0',
    'site_logo'        => getSetting('site_logo')        ?: '',
];

$fonts = [
    'Inter'            => 'Inter — Modern & Clean',
    'Playfair Display' => 'Playfair Display — Elegant & Classic',
    'Poppins'          => 'Poppins — Friendly & Round',
];

$presets = [
    ['name'=>'Gold',    'primary'=>'#8B6914','dark'=>'#6B5010','bg'=>'#faf9f7'],
    ['name'=>'Navy',    'primary'=>'#1a3a5c','dark'=>'#122840','bg'=>'#f0f4f8'],
    ['name'=>'Forest',  'primary'=>'#2d6a4f','dark'=>'#1b4332','bg'=>'#f0f7f4'],
    ['name'=>'Ruby',    'primary'=>'#9b2335','dark'=>'#7a1a28','bg'=>'#fdf5f6'],
    ['name'=>'Slate',   'primary'=>'#475569','dark'=>'#334155','bg'=>'#f8fafc'],
    ['name'=>'Violet',  'primary'=>'#6d28d9','dark'=>'#5b21b6','bg'=>'#faf5ff'],
    ['name'=>'Teal',    'primary'=>'#0f766e','dark'=>'#0d5e58','bg'=>'#f0fdfa'],
    ['name'=>'Crimson', 'primary'=>'#dc2626','dark'=>'#b91c1c','bg'=>'#fef2f2'],
];
?>

<!-- Google Fonts — load all three so previews work -->
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;700&family=Playfair+Display:wght@400;600;700&family=Poppins:wght@300;400;500;700&display=swap" rel="stylesheet">

<?php if (isset($_GET['saved'])): ?>
<div class="alert alert-success alert-dismissible fade show mb-4">
    <i class="bi bi-check-circle me-2"></i>Theme saved! Changes are live on your website.
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!-- ===================== PAGE TITLE ====================== -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Theme & Appearance</h4>
        <p class="text-muted small mb-0">Customise how your guesthouse website looks</p>
    </div>
    <a href="<?= SITE_URL ?>/public/index.php" target="_blank"
       class="btn btn-outline-primary btn-sm">
        <i class="bi bi-eye me-1"></i>Preview Website
    </a>
</div>

<form method="POST" enctype="multipart/form-data">
<div class="row g-4">

    <!-- =============== LEFT COLUMN ====================== -->
    <div class="col-lg-8">

        <!-- Colour Scheme -->
        <div class="admin-card card mb-4">
            <div class="card-header">
                <i class="bi bi-palette me-2"></i>Colour Scheme
            </div>
            <div class="card-body p-4">
                <div class="row g-4">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Primary Colour</label>
                        <input type="color" name="theme_color" id="theme_color"
                               class="form-control form-control-color w-100"
                               value="<?= htmlspecialchars($theme['theme_color']) ?>">
                        <div class="form-text">Buttons, links, badges, accents</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Primary Dark</label>
                        <input type="color" name="theme_color_dark" id="theme_color_dark"
                               class="form-control form-control-color w-100"
                               value="<?= htmlspecialchars($theme['theme_color_dark']) ?>">
                        <div class="form-text">Hover states and darker accents</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Background Colour</label>
                        <input type="color" name="theme_bg" id="theme_bg"
                               class="form-control form-control-color w-100"
                               value="<?= htmlspecialchars($theme['theme_bg']) ?>">
                        <div class="form-text">Page background colour</div>
                    </div>
                </div>

                <!-- Colour Presets -->
                <div class="mt-4">
                    <label class="form-label fw-semibold">Quick Presets</label>
                    <div class="d-flex flex-wrap gap-2 mt-1">
                        <?php foreach ($presets as $p): ?>
                        <button type="button" class="preset-btn"
                                style="background:<?= $p['primary'] ?>;"
                                data-primary="<?= $p['primary'] ?>"
                                data-dark="<?= $p['dark'] ?>"
                                data-bg="<?= $p['bg'] ?>"
                                title="<?= $p['name'] ?>">
                        </button>
                        <?php endforeach; ?>
                    </div>
                    <div class="form-text mt-1">Click a preset to apply it instantly — then save.</div>
                </div>
            </div>
        </div>

        <!-- Font Style -->
        <div class="admin-card card mb-4">
            <div class="card-header">
                <i class="bi bi-type me-2"></i>Font Style
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <?php foreach ($fonts as $value => $label): ?>
                    <div class="col-md-4">
                        <label class="font-option <?= $theme['theme_font'] === $value ? 'selected' : '' ?>"
                               for="font_<?= str_replace(' ', '_', $value) ?>">
                            <input type="radio" name="theme_font"
                                   value="<?= $value ?>"
                                   id="font_<?= str_replace(' ', '_', $value) ?>"
                                   <?= $theme['theme_font'] === $value ? 'checked' : '' ?>
                                   class="d-none">
                            <div class="font-preview" style="font-family:'<?= $value ?>',sans-serif;">
                                Aa
                            </div>
                            <div class="fw-semibold small mt-2"><?= $value ?></div>
                            <div style="font-family:'<?= $value ?>',sans-serif;font-size:0.75rem;color:#888;">
                                The quick brown fox
                            </div>
                        </label>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Dark Mode -->
        <div class="admin-card card mb-4">
            <div class="card-header">
                <i class="bi bi-moon-stars me-2"></i>Dark Mode
            </div>
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <div class="fw-semibold mb-1">Enable Dark Mode</div>
                        <div class="text-muted small">
                            Applies a dark background to both the public website and admin panel
                        </div>
                    </div>
                    <!-- FIX: dark mode toggle is now correctly inside the card -->
                    <div class="form-check form-switch ms-4">
                        <input class="form-check-input" type="checkbox"
                               name="dark_mode" id="dark_mode"
                               value="1"
                               style="width:3rem; height:1.5rem;"
                               <?= $theme['dark_mode'] === '1' ? 'checked' : '' ?>>
                    </div>
                </div>
                <!-- Dark mode preview box -->
                <div class="p-3 rounded" id="darkPreview"
                     style="background:<?= $theme['dark_mode']==='1' ? '#1a1a2e' : '#ffffff' ?>;
                            border:1px solid #ddd; transition:background 0.3s;">
                    <span id="darkPreviewText"
                          style="color:<?= $theme['dark_mode']==='1' ? '#ffffff' : '#333333' ?>;">
                        <i class="bi bi-<?= $theme['dark_mode']==='1' ? 'moon-stars-fill' : 'sun' ?> me-2"></i>
                        <?= $theme['dark_mode']==='1' ? 'Dark mode is ON' : 'Dark mode is OFF' ?>
                    </span>
                </div>
            </div>
        </div>

    </div>

    <!-- =============== RIGHT COLUMN ===================== -->
    <div class="col-lg-4">

        <!-- Logo Upload -->
        <div class="admin-card card mb-4">
            <div class="card-header">
                <i class="bi bi-image me-2"></i>Site Logo
            </div>
            <div class="card-body p-4">
                <div class="text-center mb-3">
                    <?php if (!empty($theme['site_logo'])): ?>
                    <img src="<?= SITE_URL ?>/public/assets/images/uploads/<?= $theme['site_logo'] ?>"
                         alt="Current Logo" id="logoPreview"
                         style="max-height:80px;max-width:100%;object-fit:contain;">
                    <?php else: ?>
                    <div id="logoPreview" class="text-muted small py-3">
                        <i class="bi bi-image d-block mb-1" style="font-size:2rem;"></i>
                        No logo uploaded
                    </div>
                    <?php endif; ?>
                </div>
                <input type="file" name="site_logo" id="site_logo"
                       class="form-control" accept=".jpg,.jpeg,.png,.webp,.svg">
                <div class="form-text mt-1">PNG or SVG recommended. Max 2MB.</div>
            </div>
        </div>

        <!-- Live Preview -->
        <div class="admin-card card mb-4">
            <div class="card-header">
                <i class="bi bi-eye me-2"></i>Live Colour Preview
            </div>
            <div class="card-body p-2">
                <div id="colorPreview"
                     style="background:<?= $theme['theme_bg'] ?>;border-radius:6px;overflow:hidden;">
                    <!-- Mini navbar -->
                    <div style="background:#1a1a2e;padding:8px 12px;display:flex;align-items:center;gap:8px;">
                        <div style="width:10px;height:10px;border-radius:50%;background:<?= $theme['theme_color'] ?>;"></div>
                        <div style="background:#333;height:6px;width:60px;border-radius:3px;"></div>
                        <div style="margin-left:auto;">
                            <div id="previewBtn"
                                 style="background:<?= $theme['theme_color'] ?>;color:white;font-size:8px;padding:3px 8px;border-radius:3px;">
                                Book Now
                            </div>
                        </div>
                    </div>
                    <!-- Mini hero -->
                    <div style="background:linear-gradient(<?= $theme['theme_color'] ?>88,<?= $theme['theme_color'] ?>44);padding:20px 12px;text-align:center;">
                        <div id="previewHeroText"
                             style="color:white;font-size:10px;font-weight:bold;font-family:'<?= $theme['theme_font'] ?>',sans-serif;">
                            Your Guesthouse Name
                        </div>
                    </div>
                    <!-- Mini content -->
                    <div style="padding:10px 12px;">
                        <div id="previewAccent"
                             style="height:4px;width:40px;border-radius:2px;background:<?= $theme['theme_color'] ?>;margin-bottom:6px;">
                        </div>
                        <div style="background:#ddd;height:6px;border-radius:3px;margin-bottom:4px;width:80%;"></div>
                        <div style="background:#ddd;height:6px;border-radius:3px;width:60%;"></div>
                    </div>
                </div>
                <div class="form-text text-center mt-2">Updates as you pick colours</div>
            </div>
        </div>

        <!-- Save Button -->
        <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
            <i class="bi bi-save me-2"></i>Save & Apply Theme
        </button>
        <div class="form-text text-center mt-2">Changes go live on your website immediately</div>

    </div>
</div>
</form>

<style>
.preset-btn {
    width: 32px; height: 32px;
    border-radius: 50%;
    border: 3px solid transparent;
    cursor: pointer;
    transition: transform 0.2s, border-color 0.2s;
}
.preset-btn:hover {
    transform: scale(1.2);
    border-color: #fff;
    box-shadow: 0 0 0 2px #aaa;
}
.font-option {
    display: block;
    border: 2px solid #eee;
    border-radius: 8px;
    padding: 16px 12px;
    text-align: center;
    cursor: pointer;
    transition: border-color 0.2s, background 0.2s;
}
.font-option:hover  { border-color: var(--primary); }
.font-option.selected {
    border-color: var(--primary);
    background: #faf7f0;
}
.font-preview {
    font-size: 2.5rem;
    font-weight: 700;
    color: var(--dark);
    line-height: 1;
}
.form-control-color {
    height: 50px;
    padding: 4px;
    border-radius: 8px;
    cursor: pointer;
}
</style>

<?php require_once 'includes/admin_footer.php'; ?>

<script>
// ── All JS runs AFTER admin_footer loads Bootstrap ───────────
const primaryInput = document.getElementById('theme_color');
const darkInput    = document.getElementById('theme_color_dark');
const bgInput      = document.getElementById('theme_bg');

// Live colour preview update
function updatePreview() {
    const primary = primaryInput.value;
    const bg      = bgInput.value;
    const checked = document.querySelector('input[name="theme_font"]:checked');
    const font    = checked ? checked.value : 'Inter';

    document.getElementById('previewBtn').style.background      = primary;
    document.getElementById('previewAccent').style.background   = primary;
    document.getElementById('colorPreview').style.background    = bg;
    document.getElementById('previewHeroText').style.fontFamily = "'" + font + "', sans-serif";
}

primaryInput.addEventListener('input', updatePreview);
darkInput.addEventListener('input',    updatePreview);
bgInput.addEventListener('input',      updatePreview);

// Preset colour buttons
document.querySelectorAll('.preset-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        primaryInput.value = this.dataset.primary;
        darkInput.value    = this.dataset.dark;
        bgInput.value      = this.dataset.bg;
        updatePreview();
    });
});

// Font card selection
document.querySelectorAll('.font-option').forEach(function(card) {
    card.addEventListener('click', function() {
        document.querySelectorAll('.font-option').forEach(function(c) {
            c.classList.remove('selected');
        });
        this.classList.add('selected');
        updatePreview();
    });
});

// Dark mode toggle preview
const darkToggle = document.getElementById('dark_mode');
if (darkToggle) {
    darkToggle.addEventListener('change', function() {
        const preview = document.getElementById('darkPreview');
        const text    = document.getElementById('darkPreviewText');
        if (this.checked) {
            preview.style.background = '#1a1a2e';
            text.style.color         = '#ffffff';
            text.innerHTML = '<i class="bi bi-moon-stars-fill me-2"></i>Dark mode is ON';
        } else {
            preview.style.background = '#ffffff';
            text.style.color         = '#333333';
            text.innerHTML = '<i class="bi bi-sun me-2"></i>Dark mode is OFF';
        }
    });
}

// Logo file preview
const logoInput = document.getElementById('site_logo');
if (logoInput) {
    logoInput.addEventListener('change', function() {
        const file = this.files[0];
        if (file) {
            const reader  = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('logoPreview');
                if (preview.tagName === 'IMG') {
                    preview.src = e.target.result;
                } else {
                    preview.outerHTML = '<img src="' + e.target.result + '" id="logoPreview" alt="Logo Preview" style="max-height:80px;max-width:100%;object-fit:contain;">';
                }
            };
            reader.readAsDataURL(file);
        }
    });
}
</script>