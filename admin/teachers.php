<?php
/**
 * Taska Admin – Manage Teachers
 */
require_once __DIR__ . '/../includes/auth.php';
$user = require_role('admin');

$message = '';
$msgType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $name     = trim($_POST['name'] ?? '');
        $email    = strtolower(trim($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';

        if (!$name || !$email || !$password) {
            $message = 'Name, email, and password are required.';
            $msgType = 'danger';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = 'Invalid email address.';
            $msgType = 'danger';
        } elseif (strlen($password) < 6) {
            $message = 'Password must be at least 6 characters.';
            $msgType = 'danger';
        } elseif (db_find('users.txt', 'email', $email)) {
            $message = 'Email already registered.';
            $msgType = 'danger';
        } else {
            db_insert('users.txt', [
                'name'     => $name,
                'email'    => $email,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'role'     => 'teacher',
                'avatar'   => '',
            ]);
            $message = 'Teacher created successfully.';
        }
    } elseif ($action === 'edit') {
        $id       = $_POST['id'] ?? '';
        $name     = trim($_POST['name'] ?? '');
        $email    = strtolower(trim($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';

        $target = $id ? db_find('users.txt', 'id', $id) : null;
        if (!$target || ($target['role'] ?? '') !== 'teacher') {
            $message = 'Teacher not found.';
            $msgType = 'danger';
        } elseif (!$name || !$email) {
            $message = 'Name and email are required.';
            $msgType = 'danger';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = 'Invalid email address.';
            $msgType = 'danger';
        } else {
            $existing = db_find('users.txt', 'email', $email);
            if ($existing && $existing['id'] !== $id) {
                $message = 'Email already in use.';
                $msgType = 'danger';
            } elseif ($password !== '' && strlen($password) < 6) {
                $message = 'Password must be at least 6 characters.';
                $msgType = 'danger';
            } else {
                $updates = [
                    'name'  => $name,
                    'email' => $email,
                ];
                if ($password !== '') {
                    $updates['password'] = password_hash($password, PASSWORD_DEFAULT);
                }
                db_update('users.txt', $id, $updates);
                $message = 'Teacher updated successfully.';
            }
        }
    } elseif ($action === 'delete') {
        $id = $_POST['id'] ?? '';
        if ($id === $user['id']) {
            $message = 'You cannot delete your own account.';
            $msgType = 'danger';
        } elseif ($id) {
            $target = db_find('users.txt', 'id', $id);
            if (!$target || ($target['role'] ?? '') !== 'teacher') {
                $message = 'Teacher not found.';
                $msgType = 'danger';
            } else {
                db_delete('users.txt', $id);
                $message = 'Teacher deleted.';
            }
        }
    }
}

$search = trim($_GET['q'] ?? '');
$teachers = array_values(array_filter(db_read('users.txt'), function ($u) use ($search) {
    if (($u['role'] ?? '') !== 'teacher') return false;
    if ($search === '') return true;
    return stripos($u['name'] ?? '', $search) !== false || stripos($u['email'] ?? '', $search) !== false;
}));

$pageTitle = 'Manage Teachers';
$activeNav = 'teachers';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container-xl">
    <div class="page-header-row d-flex align-items-center justify-content-between mb-3">
        <h4 class="section-heading mb-0"><i class="fa-solid fa-chalkboard-teacher me-2"></i>Manage Teachers</h4>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTeacherModal">
            <i class="fa-solid fa-plus me-2"></i>Add Teacher
        </button>
    </div>

    <?php if ($message): ?>
    <div class="alert alert-<?= $msgType ?> alert-dismissible fade show py-2 flash-alert" role="alert">
        <?= htmlspecialchars($message) ?>
        <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <form method="GET" class="mb-3 d-flex gap-2 flex-wrap">
        <input type="text" name="q" class="form-control" placeholder="Search by name or email…"
               value="<?= htmlspecialchars($search) ?>">
        <button class="btn btn-primary"><i class="fa-solid fa-magnifying-glass"></i></button>
        <?php if ($search): ?>
        <a href="<?= base_url('admin/teachers.php') ?>" class="btn btn-outline-secondary">Clear</a>
        <?php endif; ?>
    </form>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Registered</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($teachers as $t): ?>
                <tr>
                    <td><?= htmlspecialchars($t['name']) ?></td>
                    <td><?= htmlspecialchars($t['email']) ?></td>
                    <td class="text-muted small"><?= date('d M Y', strtotime($t['created_at'] ?? 'now')) ?></td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-outline-secondary me-1"
                                onclick='editTeacher(<?= json_encode([
                                    'id' => $t['id'],
                                    'name' => $t['name'],
                                    'email' => $t['email'],
                                ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>)'>
                            <i class="fa-solid fa-pen"></i>
                        </button>
                        <form method="POST" class="d-inline">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= htmlspecialchars($t['id']) ?>">
                            <button class="btn btn-sm btn-outline-danger btn-confirm-delete" type="submit">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (!$teachers): ?>
                <tr><td colspan="4" class="text-center text-muted py-4">No teachers found.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="addTeacherModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa-solid fa-plus me-2"></i>Add Teacher</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Name</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Password</label>
                        <input type="password" name="password" class="form-control" minlength="6" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-2"></i>Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="editTeacherModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="etId">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa-solid fa-pen me-2"></i>Edit Teacher</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Name</label>
                        <input type="text" name="name" id="etName" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Email</label>
                        <input type="email" name="email" id="etEmail" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">New Password (optional)</label>
                        <input type="password" name="password" class="form-control" minlength="6">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-2"></i>Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editTeacher(t) {
    document.getElementById('etId').value = t.id;
    document.getElementById('etName').value = t.name;
    document.getElementById('etEmail').value = t.email;
    new bootstrap.Modal(document.getElementById('editTeacherModal')).show();
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
