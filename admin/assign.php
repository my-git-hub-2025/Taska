<?php
/**
 * Taska Admin – Assign Teachers to Taskas
 */
require_once __DIR__ . '/../includes/auth.php';
$user = require_role('admin');

$message = '';
$msgType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'assign') {
        $teacher_id = trim($_POST['teacher_id'] ?? '');
        $taska_id   = trim($_POST['taska_id'] ?? '');
        if (!$teacher_id || !$taska_id) {
            $message = 'Please select both a teacher and a taska.';
            $msgType = 'danger';
        } else {
            // Check if assignment already exists
            $existing = array_filter(db_read('assignments.txt'), fn($a) =>
                $a['teacher_id'] === $teacher_id && $a['taska_id'] === $taska_id
            );
            if ($existing) {
                $message = 'This teacher is already assigned to that taska.';
                $msgType = 'warning';
            } else {
                db_insert('assignments.txt', [
                    'teacher_id' => $teacher_id,
                    'taska_id'   => $taska_id,
                ]);
                $message = 'Teacher assigned successfully.';
            }
        }
    } elseif ($action === 'remove') {
        $id = $_POST['assignment_id'] ?? '';
        if ($id) {
            db_delete('assignments.txt', $id);
            $message = 'Assignment removed.';
        }
    }
}

$teachers    = db_find_all('users.txt', 'role', 'teacher');
$taskas      = db_read('taskas.txt');
$assignments = db_read('assignments.txt');

$pageTitle = 'Assign Teachers';
$activeNav = 'assign';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container-xl">
    <h4 class="section-heading"><i class="fa-solid fa-link me-2"></i>Assign Teachers to Taskas</h4>

    <?php if ($message): ?>
    <div class="alert alert-<?= $msgType ?> alert-dismissible fade show py-2 flash-alert" role="alert">
        <?= htmlspecialchars($message) ?>
        <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Assignment form -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold">
                    <i class="fa-solid fa-plus me-2 text-primary"></i>New Assignment
                </div>
                <div class="card-body">
                    <?php if (!$teachers): ?>
                    <div class="alert alert-warning py-2 small">
                        <i class="fa-solid fa-exclamation-triangle me-1"></i>
                        No teachers registered. <a href="<?= base_url('admin/users.php') ?>">Manage users</a>.
                    </div>
                    <?php elseif (!$taskas): ?>
                    <div class="alert alert-warning py-2 small">
                        <i class="fa-solid fa-exclamation-triangle me-1"></i>
                        No taskas created. <a href="<?= base_url('admin/taskas.php') ?>">Manage taskas</a>.
                    </div>
                    <?php else: ?>
                    <form method="POST">
                        <input type="hidden" name="action" value="assign">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Teacher</label>
                            <select name="teacher_id" class="form-select" required>
                                <option value="">— Select teacher —</option>
                                <?php foreach ($teachers as $t): ?>
                                <option value="<?= htmlspecialchars($t['id']) ?>">
                                    <?= htmlspecialchars($t['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Taska</label>
                            <select name="taska_id" class="form-select" required>
                                <option value="">— Select taska —</option>
                                <?php foreach ($taskas as $t): ?>
                                <option value="<?= htmlspecialchars($t['id']) ?>">
                                    <?= htmlspecialchars($t['name']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fa-solid fa-link me-2"></i>Assign
                        </button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Current assignments -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold">
                    <i class="fa-solid fa-list me-2 text-secondary"></i>Current Assignments
                </div>
                <?php if ($assignments): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Teacher</th>
                                <th>Taska</th>
                                <th>Assigned</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($assignments as $a):
                            $teacher = db_find('users.txt', 'id', $a['teacher_id']);
                            $taska   = db_find('taskas.txt', 'id', $a['taska_id']);
                        ?>
                        <tr>
                            <td>
                                <span class="avatar-circle bg-success text-white me-2 d-inline-flex" style="background:#198754!important">
                                    <?= strtoupper(substr($teacher['name'] ?? '?', 0, 1)) ?>
                                </span>
                                <?= htmlspecialchars($teacher['name'] ?? 'Unknown') ?>
                            </td>
                            <td>
                                <i class="fa-solid fa-house-chimney text-primary me-1"></i>
                                <?= htmlspecialchars($taska['name'] ?? 'Unknown') ?>
                            </td>
                            <td class="small text-muted">
                                <?= date('d M Y', strtotime($a['created_at'] ?? 'now')) ?>
                            </td>
                            <td class="text-end">
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="action" value="remove">
                                    <input type="hidden" name="assignment_id" value="<?= htmlspecialchars($a['id']) ?>">
                                    <button class="btn btn-sm btn-outline-danger btn-confirm-delete" type="submit">
                                        <i class="fa-solid fa-unlink me-1"></i>Remove
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="card-body text-muted">No assignments yet.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
