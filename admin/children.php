<?php
/**
 * Taska Admin – Manage Children
 */
require_once __DIR__ . '/../includes/auth.php';
$user = require_role('admin');

$message = '';
$msgType = 'success';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $name      = trim($_POST['name'] ?? '');
        $dob       = trim($_POST['dob'] ?? '');
        $parent_id = trim($_POST['parent_id'] ?? '');
        $taska_id  = trim($_POST['taska_id'] ?? '');
        if (!$name) {
            $message = 'Child name is required.';
            $msgType = 'danger';
        } else {
            db_insert('children.txt', [
                'name'      => $name,
                'dob'       => $dob,
                'parent_id' => $parent_id,
                'taska_id'  => $taska_id,
                'gender'    => trim($_POST['gender'] ?? ''),
                'notes'     => trim($_POST['notes'] ?? ''),
            ]);
            $message = 'Child "' . htmlspecialchars($name) . '" added.';
        }
    } elseif ($action === 'edit') {
        $id = $_POST['id'] ?? '';
        if ($id) {
            db_update('children.txt', $id, [
                'name'      => trim($_POST['name'] ?? ''),
                'dob'       => trim($_POST['dob'] ?? ''),
                'parent_id' => trim($_POST['parent_id'] ?? ''),
                'taska_id'  => trim($_POST['taska_id'] ?? ''),
                'gender'    => trim($_POST['gender'] ?? ''),
                'notes'     => trim($_POST['notes'] ?? ''),
            ]);
            $message = 'Child updated.';
        }
    } elseif ($action === 'delete') {
        $id = $_POST['id'] ?? '';
        if ($id) {
            db_delete('children.txt', $id);
            $message = 'Child record deleted.';
        }
    }
}

$children = db_read('children.txt');
$parents  = db_find_all('users.txt', 'role', 'parent');
$taskas   = db_read('taskas.txt');

$pageTitle = 'Manage Children';
$activeNav = 'children';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container-xl">
    <div class="page-header-row d-flex align-items-center justify-content-between mb-3">
        <h4 class="section-heading mb-0"><i class="fa-solid fa-child me-2"></i>Manage Children</h4>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addChildModal">
            <i class="fa-solid fa-plus me-2"></i>Add Child
        </button>
    </div>

    <?php if ($message): ?>
    <div class="alert alert-<?= $msgType ?> alert-dismissible fade show py-2 flash-alert" role="alert">
        <?= htmlspecialchars($message) ?>
        <button type="button" class="btn-close btn-sm" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>DOB / Age</th>
                        <th>Gender</th>
                        <th>Parent</th>
                        <th>Taska</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($children as $child):
                    $parentUser  = $child['parent_id'] ? db_find('users.txt', 'id', $child['parent_id']) : null;
                    $taskaRecord = $child['taska_id']  ? db_find('taskas.txt', 'id', $child['taska_id']) : null;
                    $age = '';
                    if (!empty($child['dob'])) {
                        $born = new DateTime($child['dob']);
                        $now  = new DateTime();
                        $diff = $born->diff($now);
                        if ($diff->y > 0) {
                            $age = $diff->y . 'y ' . $diff->m . 'm';
                        } elseif ($diff->m > 0) {
                            $age = $diff->m . 'm';
                        } else {
                            $age = $diff->d . 'd';
                        }
                    }
                ?>
                <tr>
                    <td>
                        <i class="fa-solid fa-child text-info me-2"></i>
                        <?= htmlspecialchars($child['name']) ?>
                    </td>
                    <td class="small text-muted">
                        <?= $child['dob'] ? htmlspecialchars($child['dob']) : '—' ?>
                        <?php if ($age): ?><br><span class="text-primary"><?= $age ?></span><?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars(ucfirst($child['gender'] ?? '—')) ?></td>
                    <td><?= $parentUser ? htmlspecialchars($parentUser['name']) : '<span class="text-muted">—</span>' ?></td>
                    <td><?= $taskaRecord ? htmlspecialchars($taskaRecord['name']) : '<span class="text-muted">—</span>' ?></td>
                    <td class="text-end">
                        <button class="btn btn-sm btn-outline-secondary me-1"
                                onclick="editChild(<?= htmlspecialchars(json_encode($child)) ?>)">
                            <i class="fa-solid fa-pen"></i>
                        </button>
                        <form method="POST" class="d-inline">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= htmlspecialchars($child['id']) ?>">
                            <button class="btn btn-sm btn-outline-danger btn-confirm-delete" type="submit">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (!$children): ?>
                <tr><td colspan="6" class="text-center text-muted py-4">No children registered yet.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Child Modal -->
<div class="modal fade" id="addChildModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa-solid fa-plus me-2"></i>Add Child</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Child Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="Full name" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Date of Birth</label>
                            <input type="date" name="dob" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Gender</label>
                            <select name="gender" class="form-select">
                                <option value="">— Select —</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Parent</label>
                            <select name="parent_id" class="form-select">
                                <option value="">— No parent linked —</option>
                                <?php foreach ($parents as $p): ?>
                                <option value="<?= htmlspecialchars($p['id']) ?>"><?= htmlspecialchars($p['name']) ?> (<?= htmlspecialchars($p['email']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Taska</label>
                            <select name="taska_id" class="form-select">
                                <option value="">— Not assigned —</option>
                                <?php foreach ($taskas as $t): ?>
                                <option value="<?= htmlspecialchars($t['id']) ?>"><?= htmlspecialchars($t['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Notes</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Allergies, special needs, etc."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save me-2"></i>Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Child Modal -->
<div class="modal fade" id="editChildModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="id" id="ecId">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa-solid fa-pen me-2"></i>Edit Child</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Child Name</label>
                            <input type="text" name="name" id="ecName" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Date of Birth</label>
                            <input type="date" name="dob" id="ecDob" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Gender</label>
                            <select name="gender" id="ecGender" class="form-select">
                                <option value="">— Select —</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Parent</label>
                            <select name="parent_id" id="ecParent" class="form-select">
                                <option value="">— No parent linked —</option>
                                <?php foreach ($parents as $p): ?>
                                <option value="<?= htmlspecialchars($p['id']) ?>"><?= htmlspecialchars($p['name']) ?> (<?= htmlspecialchars($p['email']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Taska</label>
                            <select name="taska_id" id="ecTaska" class="form-select">
                                <option value="">— Not assigned —</option>
                                <?php foreach ($taskas as $t): ?>
                                <option value="<?= htmlspecialchars($t['id']) ?>"><?= htmlspecialchars($t['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Notes</label>
                            <textarea name="notes" id="ecNotes" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save me-2"></i>Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editChild(c) {
    document.getElementById('ecId').value     = c.id;
    document.getElementById('ecName').value   = c.name;
    document.getElementById('ecDob').value    = c.dob || '';
    document.getElementById('ecGender').value = c.gender || '';
    document.getElementById('ecParent').value = c.parent_id || '';
    document.getElementById('ecTaska').value  = c.taska_id || '';
    document.getElementById('ecNotes').value  = c.notes || '';
    new bootstrap.Modal(document.getElementById('editChildModal')).show();
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
