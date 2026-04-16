<?php
/**
 * Taska Parent – Notifications
 */
require_once __DIR__ . '/../includes/auth.php';
$user = require_role('parent');

// Mark all as read
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'mark_all_read') {
    $notifs = db_find_all('notifications.txt', 'user_id', $user['id']);
    foreach ($notifs as $n) {
        if (!$n['is_read']) {
            db_update('notifications.txt', $n['id'], ['is_read' => true]);
        }
    }
    header('Location: ' . base_url('parent/notifications.php'));
    exit;
}

$notifs = array_reverse(db_find_all('notifications.txt', 'user_id', $user['id']));
$unreadCount = count(array_filter($notifs, fn($n) => !$n['is_read']));

$pageTitle = 'Notifications';
$activeNav = 'notifications';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container-xl">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h4 class="section-heading mb-0">
            <i class="fa-solid fa-bell me-2"></i>Notifications
            <?php if ($unreadCount > 0): ?>
            <span class="badge bg-warning text-dark ms-2"><?= $unreadCount ?> new</span>
            <?php endif; ?>
        </h4>
        <?php if ($unreadCount > 0): ?>
        <form method="POST">
            <input type="hidden" name="action" value="mark_all_read">
            <button type="submit" class="btn btn-sm btn-outline-secondary">
                <i class="fa-solid fa-check-double me-1"></i>Mark all read
            </button>
        </form>
        <?php endif; ?>
    </div>

    <?php if ($notifs): ?>
    <?php foreach ($notifs as $notif):
        $post = !empty($notif['post_id']) ? db_find('posts.txt', 'id', $notif['post_id']) : null;
        $catClass = $post ? 'cat-' . ($post['category'] ?? 'other') : 'cat-other';
    ?>
    <div class="notif-item <?= !$notif['is_read'] ? 'unread' : '' ?>"
         data-id="<?= htmlspecialchars($notif['id']) ?>"
         style="cursor:pointer">
        <div class="d-flex align-items-start gap-3">
            <i class="fa-solid fa-bell text-warning mt-1 flex-shrink-0 fa-lg"></i>
            <div class="flex-grow-1">
                <p class="mb-1"><?= htmlspecialchars($notif['message'] ?? '') ?></p>
                <?php if ($post): ?>
                <div class="d-flex align-items-center gap-2">
                    <span class="post-category-badge <?= $catClass ?> small">
                        <?= htmlspecialchars(ucfirst($post['category'] ?? 'other')) ?>
                    </span>
                    <p class="mb-0 small text-muted text-truncate" style="max-width:300px">
                        <?= htmlspecialchars($post['content'] ?? '') ?>
                    </p>
                    <a href="<?= base_url('parent/feed.php') ?>" class="btn btn-sm btn-outline-primary ms-auto">
                        <i class="fa-solid fa-eye me-1"></i>View
                    </a>
                </div>
                <?php endif; ?>
            </div>
            <div class="notif-time text-nowrap">
                <?= date('d M Y H:i', strtotime($notif['created_at'] ?? 'now')) ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php else: ?>
    <div class="text-center py-5 text-muted">
        <i class="fa-solid fa-bell-slash fa-3x mb-3 opacity-25"></i>
        <p>No notifications yet.</p>
    </div>
    <?php endif; ?>
</div>

<script>
// Mark notification as read when clicking
document.querySelectorAll('.notif-item[data-id]').forEach(function(el) {
    el.addEventListener('click', function() {
        var id = this.dataset.id;
        this.classList.remove('unread');
        fetch(window.location.origin + '<?= base_url("api/notifications.php") ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=mark_read&id=' + encodeURIComponent(id)
        });
    });
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
