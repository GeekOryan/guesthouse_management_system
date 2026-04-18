<?php
// All logic first before any HTML
require_once dirname(__DIR__) . '/config/config.php';
require_once dirname(__DIR__) . '/includes/db.php';
require_once dirname(__DIR__) . '/includes/functions.php';

session_start();

// Mark message as read when viewed
if (isset($_GET['read'])) {
    $id = (int)$_GET['read'];
    $conn->query("UPDATE contact_messages SET is_read = 1 WHERE id = $id");
    header("Location: " . SITE_URL . "/admin/messages.php");
    exit();
}

// Delete a message
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $conn->query("DELETE FROM contact_messages WHERE id = $id");
    header("Location: " . SITE_URL . "/admin/messages.php?deleted=1");
    exit();
}

$page_title = "Messages";
require_once 'includes/admin_header.php';

// Fetch all messages newest first
$messages = $conn->query("SELECT * FROM contact_messages ORDER BY created_at DESC");

// Count unread
$unread = $conn->query("SELECT COUNT(*) as total FROM contact_messages WHERE is_read = 0")
               ->fetch_assoc()['total'];
?>

<?php if (isset($_GET['deleted'])): ?>
<div class="alert alert-warning alert-dismissible fade show mb-4">
    <i class="bi bi-trash me-2"></i>Message deleted.
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<!--PAGE TITLE -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-1">Messages</h4>
        <p class="text-muted small mb-0">
            Contact form submissions —
            <?php if ($unread > 0): ?>
            <span class="text-primary fw-semibold"><?= $unread ?> unread</span>
            <?php else: ?>
            all caught up! ✅
            <?php endif; ?>
        </p>
    </div>
</div>

<!-- MESSAGES LIST -->
<div class="admin-card card">
    <div class="card-body p-0">
        <?php if ($messages->num_rows === 0): ?>
        <div class="text-center text-muted py-5">
            <i class="bi bi-envelope-x d-block mb-2" style="font-size:2.5rem;"></i>
            No messages yet
        </div>

        <?php else: ?>
        <div class="list-group list-group-flush">
            <?php while ($msg = $messages->fetch_assoc()): ?>
            <div class="list-group-item px-4 py-3 <?= !$msg['is_read'] ? 'unread-message' : '' ?>">
                <div class="d-flex justify-content-between align-items-start gap-3">

                    <!-- Left: message info -->
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <?php if (!$msg['is_read']): ?>
                            <span class="unread-dot"></span>
                            <?php endif; ?>
                            <span class="fw-semibold"><?= $msg['name'] ?></span>
                            <span class="text-muted small">— <?= $msg['email'] ?></span>
                            <?php if (!empty($msg['phone'])): ?>
                            <span class="text-muted small">| <?= $msg['phone'] ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="small fw-semibold text-primary mb-1">
                            <?= $msg['subject'] ?>
                        </div>
                        <p class="text-muted small mb-0">
                            <?= nl2br(htmlspecialchars($msg['message'])) ?>
                        </p>
                    </div>

                    <!-- Right: date + actions -->
                    <div class="text-end" style="min-width:140px;">
                        <div class="text-muted small mb-2">
                            <?= date('d M Y H:i', strtotime($msg['created_at'])) ?>
                        </div>
                        <div class="d-flex gap-1 justify-content-end">
                            <!-- Mark as read -->
                            <?php if (!$msg['is_read']): ?>
                            <a href="<?= SITE_URL ?>/admin/messages.php?read=<?= $msg['id'] ?>"
                               class="btn btn-sm btn-outline-primary" title="Mark as read">
                                <i class="bi bi-envelope-open"></i>
                            </a>
                            <?php endif; ?>
                            <!-- Reply via email -->
                            <a href="mailto:<?= $msg['email'] ?>?subject=Re: <?= urlencode($msg['subject']) ?>"
                               class="btn btn-sm btn-outline-success" title="Reply by email">
                                <i class="bi bi-reply"></i>
                            </a>
                            <!-- Delete -->
                            <a href="<?= SITE_URL ?>/admin/messages.php?delete=<?= $msg['id'] ?>"
                               class="btn btn-sm btn-outline-danger" title="Delete"
                               onclick="return confirm('Delete this message?')">
                                <i class="bi bi-trash"></i>
                            </a>
                        </div>
                    </div>

                </div>
            </div>
            <?php endwhile; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.unread-message { background: #fffdf5; }
.unread-dot {
    width: 8px;
    height: 8px;
    background: var(--primary);
    border-radius: 50%;
    display: inline-block;
    flex-shrink: 0;
}
</style>

<?php require_once 'includes/admin_footer.php'; ?>
