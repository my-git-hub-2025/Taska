<?php
/**
 * Taska Parent – Feed (all posts for their children's taskas)
 */
require_once __DIR__ . '/../includes/auth.php';
$user = require_role('parent');

$myChildren = db_find_all('children.txt', 'parent_id', $user['id']);
$taskaIds   = array_unique(array_column($myChildren, 'taska_id'));
$childIds   = array_column($myChildren, 'id');

// Get all posts relevant to this parent
$allPosts = db_read('posts.txt');
$myPosts  = array_filter($allPosts, fn($p) =>
    in_array($p['taska_id'] ?? '', $taskaIds, true) ||
    in_array($p['child_id'] ?? '', $childIds, true)
);
$myPosts = array_reverse(array_values($myPosts));

// Category filter
$filterCat = $_GET['cat'] ?? '';
$filterChild = $_GET['child'] ?? '';
if ($filterCat) {
    $myPosts = array_filter($myPosts, fn($p) => ($p['category'] ?? '') === $filterCat);
}
if ($filterChild) {
    $myPosts = array_filter($myPosts, fn($p) =>
        ($p['child_id'] ?? '') === $filterChild ||
        (empty($p['child_id']) && in_array($p['taska_id'] ?? '', array_column(
            array_filter($myChildren, fn($c) => $c['id'] === $filterChild),
            'taska_id'
        ), true))
    );
}

$pageTitle = 'Updates Feed';
$activeNav = 'feed';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container-xl">
    <h4 class="section-heading"><i class="fa-solid fa-newspaper me-2"></i>Updates Feed</h4>

    <?php if (!$myChildren): ?>
    <div class="alert alert-info">
        <i class="fa-solid fa-info-circle me-2"></i>
        No children linked to your account. Please contact the admin.
    </div>
    <?php else: ?>

    <!-- Filters -->
    <div class="d-flex flex-wrap gap-2 mb-4 align-items-center">
        <span class="fw-semibold small me-1">Category:</span>
        <a href="<?= base_url('parent/feed.php') ?><?= $filterChild ? '?child=' . urlencode($filterChild) : '' ?>"
           class="btn btn-sm <?= !$filterCat ? 'btn-primary' : 'btn-outline-secondary' ?>">All</a>
        <?php foreach (['feed','rest','nappy','hygiene','health','other'] as $cat): ?>
        <a href="?cat=<?= $cat ?><?= $filterChild ? '&child=' . urlencode($filterChild) : '' ?>"
           class="btn btn-sm post-category-badge cat-<?= $cat ?> text-decoration-none <?= $filterCat === $cat ? 'fw-bold' : '' ?>">
            <?= ucfirst($cat) ?>
        </a>
        <?php endforeach; ?>

        <?php if (count($myChildren) > 1): ?>
        <span class="fw-semibold small ms-3 me-1">Child:</span>
        <a href="<?= base_url('parent/feed.php') ?><?= $filterCat ? '?cat=' . urlencode($filterCat) : '' ?>"
           class="btn btn-sm <?= !$filterChild ? 'btn-info' : 'btn-outline-secondary' ?>">All</a>
        <?php foreach ($myChildren as $c): ?>
        <a href="?child=<?= urlencode($c['id']) ?><?= $filterCat ? '&cat=' . urlencode($filterCat) : '' ?>"
           class="btn btn-sm <?= $filterChild === $c['id'] ? 'btn-info' : 'btn-outline-info' ?>">
            <?= htmlspecialchars($c['name']) ?>
        </a>
        <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <?php if ($myPosts): ?>
    <?php foreach ($myPosts as $post):
        $catClass   = 'cat-' . ($post['category'] ?? 'other');
        $teacher    = db_find('users.txt',   'id', $post['teacher_id'] ?? '');
        $taska      = !empty($post['taska_id']) ? db_find('taskas.txt',   'id', $post['taska_id']) : null;
        $child      = !empty($post['child_id']) ? db_find('children.txt', 'id', $post['child_id']) : null;
        $mediaFiles = $post['media'] ?? [];
    ?>
    <div class="card post-card">
        <div class="card-body">
            <!-- Header -->
            <div class="d-flex align-items-start gap-3 mb-3">
                <div class="avatar-circle bg-primary text-white flex-shrink-0"
                     style="width:44px;height:44px;font-size:1.1rem;background:var(--taska-primary)!important">
                    <?= strtoupper(substr($teacher['name'] ?? '?', 0, 1)) ?>
                </div>
                <div class="flex-grow-1">
                    <div class="fw-semibold"><?= htmlspecialchars($teacher['name'] ?? 'Teacher') ?></div>
                    <div class="small text-muted">
                        <?php if ($taska): ?>
                        <i class="fa-solid fa-house-chimney me-1"></i><?= htmlspecialchars($taska['name']) ?>
                        <?php endif; ?>
                        <?php if ($child): ?>
                        <i class="fa-solid fa-child ms-2 me-1 text-info"></i><?= htmlspecialchars($child['name']) ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="text-end">
                    <span class="post-category-badge <?= $catClass ?>">
                        <?= htmlspecialchars(ucfirst($post['category'] ?? 'other')) ?>
                    </span>
                    <div class="small text-muted mt-1">
                        <?= date('d M Y, H:i', strtotime($post['created_at'] ?? 'now')) ?>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <p class="mb-2"><?= nl2br(htmlspecialchars($post['content'] ?? '')) ?></p>

            <!-- Media -->
            <?php if ($mediaFiles): ?>
            <div class="post-media">
                <div class="row g-2">
                    <?php foreach ($mediaFiles as $media):
                        $ext = strtolower(pathinfo($media, PATHINFO_EXTENSION));
                        $isVideo = in_array($ext, ['mp4','mov','webm','avi'], true);
                    ?>
                    <div class="col-auto">
                        <?php if ($isVideo): ?>
                        <video controls style="max-height:280px;max-width:100%;border-radius:.5rem">
                            <source src="<?= base_url($media) ?>">
                        </video>
                        <?php else: ?>
                        <a href="<?= base_url($media) ?>" target="_blank">
                            <img src="<?= base_url($media) ?>" alt="update photo"
                                 style="max-height:280px;max-width:100%;border-radius:.5rem;object-fit:cover">
                        </a>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
    <?php else: ?>
    <div class="text-center py-5 text-muted">
        <i class="fa-solid fa-newspaper fa-3x mb-3 opacity-25"></i>
        <p>No updates yet for your child<?= count($myChildren) > 1 ? 'ren' : '' ?>.</p>
    </div>
    <?php endif; ?>

    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
