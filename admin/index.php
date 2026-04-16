<?php
/**
 * Taska Admin – Dashboard
 */
require_once __DIR__ . '/../includes/auth.php';
$user = require_role('admin');

$users    = db_read('users.txt');
$taskas   = db_read('taskas.txt');
$posts    = db_read('posts.txt');
$children = db_read('children.txt');

$teachers = array_filter($users, fn($u) => $u['role'] === 'teacher');
$parents  = array_filter($users, fn($u) => $u['role'] === 'parent');

$recentPosts = array_slice(array_reverse($posts), 0, 5);

$pageTitle = 'Admin Dashboard';
$activeNav = 'dashboard';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container-xl">
    <h4 class="section-heading"><i class="fa-solid fa-gauge me-2"></i>Dashboard</h4>

    <!-- Stats row -->
    <div class="row g-4 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card stat-card text-white" style="background:linear-gradient(135deg,#667eea,#764ba2)">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small opacity-75 fw-semibold">Total Users</div>
                        <div class="fs-2 fw-bold"><?= count($users) ?></div>
                    </div>
                    <div class="icon-box"><i class="fa-solid fa-users"></i></div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card stat-card text-white" style="background:linear-gradient(135deg,#f093fb,#f5576c)">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small opacity-75 fw-semibold">Taskas</div>
                        <div class="fs-2 fw-bold"><?= count($taskas) ?></div>
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
                        <div class="fs-2 fw-bold"><?= count($children) ?></div>
                    </div>
                    <div class="icon-box"><i class="fa-solid fa-child"></i></div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card stat-card text-white" style="background:linear-gradient(135deg,#43e97b,#38f9d7)">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small opacity-75 fw-semibold">Posts Today</div>
                        <?php
                        $today = date('Y-m-d');
                        $postsToday = count(array_filter($posts, fn($p) => str_starts_with($p['created_at'] ?? '', $today)));
                        ?>
                        <div class="fs-2 fw-bold"><?= $postsToday ?></div>
                    </div>
                    <div class="icon-box"><i class="fa-solid fa-newspaper"></i></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Quick actions -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white fw-semibold border-0">
                    <i class="fa-solid fa-bolt me-2 text-warning"></i>Quick Actions
                </div>
                <div class="card-body d-grid gap-2">
                    <a href="<?= base_url('admin/taskas.php') ?>" class="btn btn-outline-primary">
                        <i class="fa-solid fa-plus me-2"></i>Add New Taska
                    </a>
                    <a href="<?= base_url('admin/users.php') ?>" class="btn btn-outline-secondary">
                        <i class="fa-solid fa-users me-2"></i>Manage Users
                    </a>
                    <a href="<?= base_url('admin/teachers.php') ?>" class="btn btn-outline-success">
                        <i class="fa-solid fa-chalkboard-teacher me-2"></i>Manage Teachers
                    </a>
                    <a href="<?= base_url('admin/parents.php') ?>" class="btn btn-outline-warning">
                        <i class="fa-solid fa-house-user me-2"></i>Manage Parents
                    </a>
                    <a href="<?= base_url('admin/children.php') ?>" class="btn btn-outline-info">
                        <i class="fa-solid fa-child me-2"></i>Manage Children
                    </a>
                    <a href="<?= base_url('admin/assign.php') ?>" class="btn btn-outline-success">
                        <i class="fa-solid fa-link me-2"></i>Assign Teacher to Taska
                    </a>
                </div>
            </div>
        </div>

        <!-- Role summary -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white fw-semibold border-0">
                    <i class="fa-solid fa-pie-chart me-2 text-primary"></i>User Roles
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span><i class="fa-solid fa-crown text-warning me-2"></i>Admins</span>
                            <span class="badge bg-warning text-dark"><?= count(array_filter($users, fn($u) => $u['role'] === 'admin')) ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span><i class="fa-solid fa-chalkboard-teacher text-success me-2"></i>Teachers</span>
                            <span class="badge bg-success"><?= count($teachers) ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0">
                            <span><i class="fa-solid fa-users text-info me-2"></i>Parents</span>
                            <span class="badge bg-info"><?= count($parents) ?></span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Recent posts -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white fw-semibold border-0">
                    <i class="fa-solid fa-clock me-2 text-secondary"></i>Recent Posts
                </div>
                <div class="card-body p-0">
                    <?php if ($recentPosts): ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach ($recentPosts as $post):
                            $catClass = 'cat-' . ($post['category'] ?? 'other');
                            $teacher  = db_find('users.txt', 'id', $post['teacher_id'] ?? '');
                        ?>
                        <li class="list-group-item">
                            <div class="d-flex align-items-center gap-2">
                                <span class="post-category-badge <?= $catClass ?>">
                                    <?= htmlspecialchars(ucfirst($post['category'] ?? 'other')) ?>
                                </span>
                                <span class="small text-truncate flex-grow-1">
                                    <?= htmlspecialchars($teacher['name'] ?? 'Unknown') ?>
                                </span>
                                <span class="text-muted small">
                                    <?= date('d M', strtotime($post['created_at'] ?? 'now')) ?>
                                </span>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php else: ?>
                    <div class="p-3 text-muted small">No posts yet.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
