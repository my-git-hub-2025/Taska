<?php
/**
 * Taska Admin – Manage Users
 */
require_once __DIR__ . '/../includes/auth.php';
$user = require_role('admin');

$message = '';
$msgType = 'success';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'change_role') {
        $uid  = $_POST['user_id'] ?? '';
        $role = $_POST['role'] ?? '';
        if ($uid && in_array($role, ['admin', 'teacher', 'parent'], true)) {
            if ($uid === $user['id']) {
                $message = 'You cannot change your own role.';
                $msgType = 'danger';
            } else {
                db_update('users.txt', $uid, ['role' => $role]);
                $message = 'Role updated successfully.';
            }
        }
    } elseif ($action === 'delete_user') {
        $uid = $_POST['user_id'] ?? '';
        if ($uid === $user['id']) {
            $message = 'You cannot delete your own account.';
            $msgType = 'danger';
        } elseif ($uid) {
            db_delete('users.txt', $uid);
            $message = 'User deleted.';
        }
    }
}

$allUsers = db_read('users.txt');
$search   = trim($_GET['q'] ?? '');
if ($search) {
    $allUsers = array_filter($allUsers, function ($u) use ($search) {
        return stripos($u['name'], $search) !== false || stripos($u['email'], $search) !== false;
    });
}

$pageTitle = 'Manage Users';
$activeNav = 'users';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container-xl">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h4 class="section-heading mb-0"><i class="fa-solid fa-users me-2"></i>Manage Users</h4>
    </div>

    <?php if ($message): ?>
    <div class="alert alert-<?= $msgType ?> alert-dismissible fade show py-2 flash-alert" role="alert">
        <?= htmlspecialchars($message) ?>
        <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- Search -->
    <form method="GET" class="mb-3 d-flex gap-2 flex-wrap">
        <input type="text" name="q" class="form-control" placeholder="Search by name or email…"
               value="<?= htmlspecialchars($search) ?>">
        <button class="btn btn-primary"><i class="fa-solid fa-magnifying-glass"></i></button>
        <?php if ($search): ?>
        <a href="<?= base_url('admin/users.php') ?>" class="btn btn-outline-secondary">Clear</a>
        <?php endif; ?>
    </form>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th data-sort="name" style="cursor:pointer">Name <i class="fa-solid fa-sort fa-xs text-muted"></i></th>
                        <th data-sort="email" style="cursor:pointer">Email <i class="fa-solid fa-sort fa-xs text-muted"></i></th>
                        <th>Role</th>
                        <th>Registered</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($allUsers as $u):
                    $roleColor = ['admin'=>'warning','teacher'=>'success','parent'=>'info'][$u['role']] ?? 'secondary';
                ?>
                <tr>
                    <td>
                        <span class="avatar-circle bg-primary text-white me-2 d-inline-flex" style="background:var(--taska-primary)!important">
                            <?= strtoupper(substr($u['name'], 0, 1)) ?>
                        </span>
                        <?= htmlspecialchars($u['name']) ?>
                        <?php if ($u['id'] === $user['id']): ?>
                        <span class="badge bg-secondary ms-1">You</span>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td>
                        <span class="badge bg-<?= $roleColor ?>">
                            <?= ucfirst($u['role']) ?>
                        </span>
                    </td>
                    <td class="text-muted small"><?= date('d M Y', strtotime($u['created_at'] ?? 'now')) ?></td>
                    <td class="text-end">
                        <?php if ($u['id'] !== $user['id']): ?>
                        <!-- Change role form -->
                        <form method="POST" class="d-inline">
                            <input type="hidden" name="action" value="change_role">
                            <input type="hidden" name="user_id" value="<?= htmlspecialchars($u['id']) ?>">
                            <select name="role" class="form-select form-select-sm d-inline-block w-auto">
                                <option value="admin"   <?= $u['role']==='admin'  ?'selected':''?>>Admin</option>
                                <option value="teacher" <?= $u['role']==='teacher'?'selected':''?>>Teacher</option>
                                <option value="parent"  <?= $u['role']==='parent' ?'selected':''?>>Parent</option>
                            </select>
                            <button class="btn btn-sm btn-outline-secondary ms-1">
                                <i class="fa-solid fa-check"></i>
                            </button>
                        </form>
                        <!-- Delete -->
                        <form method="POST" class="d-inline ms-1">
                            <input type="hidden" name="action" value="delete_user">
                            <input type="hidden" name="user_id" value="<?= htmlspecialchars($u['id']) ?>">
                            <button class="btn btn-sm btn-outline-danger btn-confirm-delete">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </form>
                        <?php else: ?>
                        <span class="text-muted small">—</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (!$allUsers): ?>
                <tr><td colspan="5" class="text-center text-muted py-4">No users found.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
