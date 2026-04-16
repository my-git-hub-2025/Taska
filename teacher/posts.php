<?php
/**
 * Taska Teacher – All Posts
 */
require_once __DIR__ . '/../includes/auth.php';
$user = require_role('teacher');

$message = '';
$msgType = 'success';

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $pid = $_POST['post_id'] ?? '';
    $post = db_find('posts.txt', 'id', $pid);
    if ($post && $post['teacher_id'] === $user['id']) {
        // Delete media files
        foreach ($post['media'] ?? [] as $mediaPath) {
            $fullPath = __DIR__ . '/../' . $mediaPath;
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
        }
        db_delete('posts.txt', $pid);
        // Remove related notifications
        $notifs = db_find_all('notifications.txt', 'post_id', $pid);
        foreach ($notifs as $n) {
            db_delete('notifications.txt', $n['id']);
        }
        $message = 'Post deleted.';
    }
}

if (isset($_GET['posted'])) {
    $message = 'Post published successfully!';
}

$myPosts = array_reverse(db_find_all('posts.txt', 'teacher_id', $user['id']));

// Filter by category
$filterCat = $_GET['cat'] ?? '';
if ($filterCat) {
    $myPosts = array_filter($myPosts, fn($p) => ($p['category'] ?? '') === $filterCat);
}

$pageTitle = 'My Posts';
$activeNav = 'posts';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container-xl">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h4 class="section-heading mb-0"><i class="fa-solid fa-list me-2"></i>My Posts</h4>
        <a href="<?= base_url('teacher/post.php') ?>" class="btn btn-primary">
            <i class="fa-solid fa-plus me-2"></i>New Post
        </a>
    </div>

    <?php if ($message): ?>
    <div class="alert alert-<?= $msgType ?> alert-dismissible fade show py-2 flash-alert" role="alert">
        <?= htmlspecialchars($message) ?>
        <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- Category filter -->
    <div class="mb-3 d-flex flex-wrap gap-2">
        <a href="<?= base_url('teacher/posts.php') ?>"
           class="btn btn-sm <?= !$filterCat ? 'btn-primary' : 'btn-outline-secondary' ?>">All</a>
        <?php foreach (['feed','rest','nappy','hygiene','health','other'] as $cat): ?>
        <a href="<?= base_url('teacher/posts.php') ?>?cat=<?= $cat ?>"
           class="btn btn-sm post-category-badge cat-<?= $cat ?> text-decoration-none">
            <?= ucfirst($cat) ?>
        </a>
        <?php endforeach; ?>
    </div>

    <?php if ($myPosts): ?>
    <?php foreach ($myPosts as $post):
        $catClass    = 'cat-' . ($post['category'] ?? 'other');
        $child       = !empty($post['child_id']) ? db_find('children.txt', 'id', $post['child_id']) : null;
        $taska       = !empty($post['taska_id']) ? db_find('taskas.txt',   'id', $post['taska_id']) : null;
        $mediaFiles  = $post['media'] ?? [];
    ?>
    <div class="card post-card">
        <div class="card-body">
            <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <span class="post-category-badge <?= $catClass ?>">
                        <?= htmlspecialchars(ucfirst($post['category'] ?? 'other')) ?>
                    </span>
                    <?php if ($taska): ?>
                    <span class="badge bg-light text-dark border">
                        <i class="fa-solid fa-house-chimney me-1 text-primary"></i><?= htmlspecialchars($taska['name']) ?>
                    </span>
                    <?php endif; ?>
                    <?php if ($child): ?>
                    <span class="badge bg-light text-dark border">
                        <i class="fa-solid fa-child me-1 text-info"></i><?= htmlspecialchars($child['name']) ?>
                    </span>
                    <?php endif; ?>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="text-muted small text-nowrap">
                        <?= date('d M Y H:i', strtotime($post['created_at'] ?? 'now')) ?>
                    </span>
                    <form method="POST" class="d-inline">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="post_id" value="<?= htmlspecialchars($post['id']) ?>">
                        <button class="btn btn-sm btn-outline-danger btn-confirm-delete" type="submit">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </form>
                </div>
            </div>

            <p class="mb-2"><?= nl2br(htmlspecialchars($post['content'] ?? '')) ?></p>

            <!-- Media -->
            <?php if ($mediaFiles): ?>
            <div class="post-media d-flex flex-wrap gap-2">
                <?php foreach ($mediaFiles as $media):
                    $ext = strtolower(pathinfo($media, PATHINFO_EXTENSION));
                    $isVideo = in_array($ext, ['mp4','mov','webm','avi'], true);
                ?>
                <?php if ($isVideo): ?>
                <video controls style="max-height:200px;border-radius:.5rem">
                    <source src="<?= base_url($media) ?>">
                </video>
                <?php else: ?>
                <a href="<?= base_url($media) ?>" target="_blank">
                    <img src="<?= base_url($media) ?>" alt="media" style="max-height:200px;border-radius:.5rem">
                </a>
                <?php endif; ?>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
    <?php else: ?>
    <div class="text-center py-5 text-muted">
        <i class="fa-solid fa-newspaper fa-3x mb-3 opacity-25"></i>
        <p>No posts found.</p>
        <a href="<?= base_url('teacher/post.php') ?>" class="btn btn-primary">
            <i class="fa-solid fa-plus me-2"></i>Create First Post
        </a>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
