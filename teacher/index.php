<?php
/**
 * Taska Teacher – Dashboard
 */
require_once __DIR__ . '/../includes/auth.php';
$user = require_role('teacher');

// Get teacher's assigned taskas
$assignments = db_find_all('assignments.txt', 'teacher_id', $user['id']);
$taskaIds    = array_column($assignments, 'taska_id');
$myTaskas    = array_filter(db_read('taskas.txt'), fn($t) => in_array($t['id'], $taskaIds, true));

// Get posts by this teacher
$myPosts    = db_find_all('posts.txt', 'teacher_id', $user['id']);
$recentPosts = array_slice(array_reverse($myPosts), 0, 5);

// Count children in assigned taskas
$allChildren = db_read('children.txt');
$myChildren  = array_filter($allChildren, fn($c) => in_array($c['taska_id'] ?? '', $taskaIds, true));

$pageTitle = 'Teacher Dashboard';
$activeNav = 'dashboard';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container-xl">
    <div class="page-header-row d-flex align-items-center justify-content-between mb-3">
        <h4 class="section-heading mb-0">
            <i class="fa-solid fa-chalkboard-teacher me-2"></i>Welcome, <?= htmlspecialchars($user['name']) ?>
        </h4>
        <a href="<?= base_url('teacher/post.php') ?>" class="btn btn-primary">
            <i class="fa-solid fa-plus me-2"></i>New Post
        </a>
    </div>

    <!-- Stats -->
    <div class="row g-4 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card stat-card text-white" style="background:linear-gradient(135deg,#667eea,#764ba2)">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small opacity-75 fw-semibold">My Taskas</div>
                        <div class="fs-2 fw-bold"><?= count($myTaskas) ?></div>
                    </div>
                    <div class="icon-box"><i class="fa-solid fa-house-chimney"></i></div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card stat-card text-white" style="background:linear-gradient(135deg,#4facfe,#00f2fe)">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small opacity-75 fw-semibold">Children</div>
                        <div class="fs-2 fw-bold"><?= count($myChildren) ?></div>
                    </div>
                    <div class="icon-box"><i class="fa-solid fa-child"></i></div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card stat-card text-white" style="background:linear-gradient(135deg,#43e97b,#38f9d7)">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small opacity-75 fw-semibold">Total Posts</div>
                        <div class="fs-2 fw-bold"><?= count($myPosts) ?></div>
                    </div>
                    <div class="icon-box"><i class="fa-solid fa-newspaper"></i></div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card stat-card text-white" style="background:linear-gradient(135deg,#f093fb,#f5576c)">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <?php
                        $today = date('Y-m-d');
                        $todayPosts = count(array_filter($myPosts, fn($p) => str_starts_with($p['created_at'] ?? '', $today)));
                        ?>
                        <div class="small opacity-75 fw-semibold">Posts Today</div>
                        <div class="fs-2 fw-bold"><?= $todayPosts ?></div>
                    </div>
                    <div class="icon-box"><i class="fa-solid fa-calendar-day"></i></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- My Taskas -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white fw-semibold border-0">
                    <i class="fa-solid fa-house-chimney me-2 text-primary"></i>My Taskas
                </div>
                <div class="card-body">
                    <?php if ($myTaskas): ?>
                    <?php foreach ($myTaskas as $taska):
                        $taskaChildren = array_filter($allChildren, fn($c) => $c['taska_id'] === $taska['id']);
                    ?>
                    <div class="border rounded p-3 mb-3">
                        <div class="fw-semibold"><?= htmlspecialchars($taska['name']) ?></div>
                        <?php if (!empty($taska['address'])): ?>
                        <div class="small text-muted mt-1">
                            <i class="fa-solid fa-location-dot me-1"></i><?= htmlspecialchars($taska['address']) ?>
                        </div>
                        <?php endif; ?>
                        <div class="mt-2">
                            <span class="badge bg-info">
                                <i class="fa-solid fa-child me-1"></i><?= count($taskaChildren) ?> children
                            </span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <div class="alert alert-info py-2 small">
                        <i class="fa-solid fa-info-circle me-1"></i>
                        You have not been assigned to a taska yet. Please contact the admin.
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Recent posts -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white fw-semibold border-0 d-flex justify-content-between align-items-center">
                    <span><i class="fa-solid fa-clock me-2 text-secondary"></i>Recent Posts</span>
                    <a href="<?= base_url('teacher/posts.php') ?>" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body p-0">
                    <?php if ($recentPosts): ?>
                    <?php foreach ($recentPosts as $post):
                        $catClass = 'cat-' . ($post['category'] ?? 'other');
                        $child    = !empty($post['child_id']) ? db_find('children.txt', 'id', $post['child_id']) : null;
                    ?>
                    <div class="p-3 border-bottom">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="post-category-badge <?= $catClass ?>">
                                <?= htmlspecialchars(ucfirst($post['category'] ?? 'other')) ?>
                            </span>
                            <?php if ($child): ?>
                            <span class="small text-muted">
                                <i class="fa-solid fa-child me-1"></i><?= htmlspecialchars($child['name']) ?>
                            </span>
                            <?php endif; ?>
                            <span class="small text-muted ms-auto">
                                <?= date('d M Y H:i', strtotime($post['created_at'] ?? 'now')) ?>
                            </span>
                        </div>
                        <p class="mb-0 small text-truncate"><?= htmlspecialchars($post['content'] ?? '') ?></p>
                    </div>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <div class="p-4 text-center text-muted">
                        <i class="fa-solid fa-newspaper fa-2x mb-2 opacity-25"></i>
                        <p class="mb-2">No posts yet.</p>
                        <a href="<?= base_url('teacher/post.php') ?>" class="btn btn-sm btn-primary">
                            <i class="fa-solid fa-plus me-1"></i>Create first post
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
