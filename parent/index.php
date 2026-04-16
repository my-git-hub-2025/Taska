<?php
/**
 * Taska Parent – Dashboard
 */
require_once __DIR__ . '/../includes/auth.php';
$user = require_role('parent');

// Find children linked to this parent
$myChildren = db_find_all('children.txt', 'parent_id', $user['id']);
$taskaIds   = array_unique(array_column($myChildren, 'taska_id'));

// Unread notifications
$allNotifs    = db_find_all('notifications.txt', 'user_id', $user['id']);
$unreadNotifs = array_filter($allNotifs, fn($n) => !$n['is_read']);

// Recent posts from their children's taskas
$allPosts = db_read('posts.txt');
$myPosts  = array_filter($allPosts, fn($p) =>
    in_array($p['taska_id'] ?? '', $taskaIds, true) ||
    in_array($p['child_id'] ?? '', array_column($myChildren, 'id'), true)
);
$recentPosts = array_slice(array_reverse(array_values($myPosts)), 0, 5);

$pageTitle = 'Parent Dashboard';
$activeNav = 'dashboard';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container-xl">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h4 class="section-heading mb-0">
            <i class="fa-solid fa-house-user me-2"></i>Welcome, <?= htmlspecialchars($user['name']) ?>
        </h4>
        <a href="<?= base_url('parent/feed.php') ?>" class="btn btn-primary">
            <i class="fa-solid fa-newspaper me-2"></i>View Feed
        </a>
    </div>

    <!-- Stats -->
    <div class="row g-4 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card stat-card text-white" style="background:linear-gradient(135deg,#4facfe,#00f2fe)">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small opacity-75 fw-semibold">My Children</div>
                        <div class="fs-2 fw-bold"><?= count($myChildren) ?></div>
                    </div>
                    <div class="icon-box"><i class="fa-solid fa-child"></i></div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card stat-card text-white" style="background:linear-gradient(135deg,#f093fb,#f5576c)">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small opacity-75 fw-semibold">Unread Notifications</div>
                        <div class="fs-2 fw-bold"><?= count($unreadNotifs) ?></div>
                    </div>
                    <div class="icon-box"><i class="fa-solid fa-bell"></i></div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card stat-card text-white" style="background:linear-gradient(135deg,#43e97b,#38f9d7)">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small opacity-75 fw-semibold">Total Updates</div>
                        <div class="fs-2 fw-bold"><?= count($myPosts) ?></div>
                    </div>
                    <div class="icon-box"><i class="fa-solid fa-newspaper"></i></div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card stat-card text-white" style="background:linear-gradient(135deg,#667eea,#764ba2)">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small opacity-75 fw-semibold">Taskas</div>
                        <div class="fs-2 fw-bold"><?= count($taskaIds) ?></div>
                    </div>
                    <div class="icon-box"><i class="fa-solid fa-house-chimney"></i></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- My Children -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white fw-semibold border-0">
                    <i class="fa-solid fa-child me-2 text-info"></i>My Children
                </div>
                <div class="card-body">
                    <?php if ($myChildren): ?>
                    <?php foreach ($myChildren as $child):
                        $taska = !empty($child['taska_id']) ? db_find('taskas.txt', 'id', $child['taska_id']) : null;
                        $age   = '';
                        if (!empty($child['dob'])) {
                            $diff = (new DateTime($child['dob']))->diff(new DateTime());
                            $age  = $diff->y > 0 ? $diff->y . 'y ' . $diff->m . 'm' : $diff->m . ' months';
                        }
                    ?>
                    <div class="d-flex align-items-center gap-3 mb-3 p-2 border rounded">
                        <div class="avatar-circle bg-info text-white flex-shrink-0" style="width:42px;height:42px;font-size:1.1rem;background:#0dcaf0!important">
                            <?= strtoupper(substr($child['name'], 0, 1)) ?>
                        </div>
                        <div>
                            <div class="fw-semibold"><?= htmlspecialchars($child['name']) ?></div>
                            <?php if ($age): ?>
                            <div class="small text-muted"><?= $age ?></div>
                            <?php endif; ?>
                            <?php if ($taska): ?>
                            <div class="small text-primary">
                                <i class="fa-solid fa-house-chimney me-1"></i><?= htmlspecialchars($taska['name']) ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <div class="alert alert-info py-2 small">
                        <i class="fa-solid fa-info-circle me-1"></i>
                        No children linked to your account yet. Please contact the admin.
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Recent Notifications -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white fw-semibold border-0 d-flex justify-content-between align-items-center">
                    <span><i class="fa-solid fa-bell me-2 text-warning"></i>Recent Notifications</span>
                    <a href="<?= base_url('parent/notifications.php') ?>" class="btn btn-sm btn-outline-warning">View All</a>
                </div>
                <div class="card-body p-0">
                    <?php
                    $recentNotifs = array_slice(array_reverse($allNotifs), 0, 5);
                    if ($recentNotifs):
                    ?>
                    <?php foreach ($recentNotifs as $notif): ?>
                    <div class="p-3 border-bottom <?= !$notif['is_read'] ? 'bg-light' : '' ?>">
                        <div class="d-flex gap-2">
                            <i class="fa-solid fa-bell text-warning mt-1 flex-shrink-0"></i>
                            <div>
                                <p class="mb-1 small"><?= htmlspecialchars($notif['message'] ?? '') ?></p>
                                <span class="notif-time"><?= date('d M H:i', strtotime($notif['created_at'] ?? 'now')) ?></span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <div class="p-4 text-center text-muted">No notifications yet.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Recent Posts Feed -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white fw-semibold border-0 d-flex justify-content-between align-items-center">
                    <span><i class="fa-solid fa-newspaper me-2 text-primary"></i>Latest Updates</span>
                    <a href="<?= base_url('parent/feed.php') ?>" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body p-0">
                    <?php if ($recentPosts): ?>
                    <?php foreach ($recentPosts as $post):
                        $catClass = 'cat-' . ($post['category'] ?? 'other');
                        $teacher  = db_find('users.txt', 'id', $post['teacher_id'] ?? '');
                        $child    = !empty($post['child_id']) ? db_find('children.txt', 'id', $post['child_id']) : null;
                    ?>
                    <div class="p-3 border-bottom">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="post-category-badge <?= $catClass ?>">
                                <?= htmlspecialchars(ucfirst($post['category'] ?? 'other')) ?>
                            </span>
                            <span class="small text-muted ms-auto">
                                <?= date('d M H:i', strtotime($post['created_at'] ?? 'now')) ?>
                            </span>
                        </div>
                        <p class="mb-0 small text-truncate"><?= htmlspecialchars($post['content'] ?? '') ?></p>
                    </div>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <div class="p-4 text-center text-muted">No updates from your child's taska yet.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
