<?php
/**
 * Shared HTML header partial.
 * Expects $pageTitle (string) and $user (array) to be set before including.
 */
$pageTitle = $pageTitle ?? 'Taska';
$activeNav = $activeNav ?? '';
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#4f46e5">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <title><?= htmlspecialchars($pageTitle) ?> – Taska</title>
    <!-- Bootstrap 5 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- Custom styles -->
    <link rel="stylesheet" href="<?= base_url('css/style.css') ?>">
</head>
<body class="<?= isset($user['role']) ? 'role-' . htmlspecialchars($user['role']) : 'guest' ?>">

<?php if (isset($user) && $user): ?>
<!-- Top navigation bar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="<?= base_url($user['role'] === 'admin' ? 'admin/index.php' : ($user['role'] === 'teacher' ? 'teacher/index.php' : 'parent/index.php')) ?>">
            <i class="fa-solid fa-baby me-2"></i>Taska
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarMain">
            <ul class="navbar-nav me-auto">
                <?php if ($user['role'] === 'admin'): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= $activeNav === 'dashboard' ? 'active' : '' ?>" href="<?= base_url('admin/index.php') ?>">
                            <i class="fa-solid fa-gauge me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $activeNav === 'users' ? 'active' : '' ?>" href="<?= base_url('admin/users.php') ?>">
                            <i class="fa-solid fa-users me-1"></i>Users
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $activeNav === 'taskas' ? 'active' : '' ?>" href="<?= base_url('admin/taskas.php') ?>">
                            <i class="fa-solid fa-house-chimney me-1"></i>Taskas
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $activeNav === 'children' ? 'active' : '' ?>" href="<?= base_url('admin/children.php') ?>">
                            <i class="fa-solid fa-child me-1"></i>Children
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $activeNav === 'assign' ? 'active' : '' ?>" href="<?= base_url('admin/assign.php') ?>">
                            <i class="fa-solid fa-link me-1"></i>Assign
                        </a>
                    </li>
                <?php elseif ($user['role'] === 'teacher'): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= $activeNav === 'dashboard' ? 'active' : '' ?>" href="<?= base_url('teacher/index.php') ?>">
                            <i class="fa-solid fa-gauge me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $activeNav === 'post' ? 'active' : '' ?>" href="<?= base_url('teacher/post.php') ?>">
                            <i class="fa-solid fa-plus-circle me-1"></i>New Post
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $activeNav === 'posts' ? 'active' : '' ?>" href="<?= base_url('teacher/posts.php') ?>">
                            <i class="fa-solid fa-list me-1"></i>My Posts
                        </a>
                    </li>
                <?php elseif ($user['role'] === 'parent'): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= $activeNav === 'dashboard' ? 'active' : '' ?>" href="<?= base_url('parent/index.php') ?>">
                            <i class="fa-solid fa-gauge me-1"></i>Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $activeNav === 'feed' ? 'active' : '' ?>" href="<?= base_url('parent/feed.php') ?>">
                            <i class="fa-solid fa-newspaper me-1"></i>Feed
                        </a>
                    </li>
                <?php endif; ?>
            </ul>

            <ul class="navbar-nav ms-auto align-items-center">
                <!-- Notification bell -->
                <?php
                require_once __DIR__ . '/db.php';
                $unreadCount = 0;
                if ($user['role'] === 'parent') {
                    $unread = array_filter(
                        db_find_all('notifications.txt', 'user_id', $user['id']),
                        fn($n) => !$n['is_read']
                    );
                    $unreadCount = count($unread);
                }
                ?>
                <?php if ($user['role'] === 'parent'): ?>
                <li class="nav-item me-2">
                    <a class="nav-link position-relative" href="<?= base_url('parent/notifications.php') ?>">
                        <i class="fa-solid fa-bell fa-lg"></i>
                        <?php if ($unreadCount > 0): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                            <?= $unreadCount ?>
                        </span>
                        <?php endif; ?>
                    </a>
                </li>
                <?php endif; ?>

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center gap-2" href="#" data-bs-toggle="dropdown">
                        <span class="avatar-circle">
                            <?= strtoupper(substr($user['name'], 0, 1)) ?>
                        </span>
                        <span><?= htmlspecialchars($user['name']) ?></span>
                        <span class="badge bg-light text-primary"><?= ucfirst($user['role']) ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><span class="dropdown-item-text small text-muted"><?= htmlspecialchars($user['email']) ?></span></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?= base_url('logout.php') ?>"><i class="fa-solid fa-right-from-bracket me-2"></i>Logout</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>
<?php endif; ?>

<main class="<?= isset($user) ? 'container-fluid py-4' : '' ?>">
